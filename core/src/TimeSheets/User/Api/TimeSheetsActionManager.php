<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:24 PM
 */

namespace TimeSheets\User\Api;

use Classes\BaseService;
use Classes\IceConstants;
use Classes\IceResponse;
use Classes\SettingsManager;
use Classes\SubActionManager;
use Employees\Common\Model\Employee;
use Leaves\Common\Model\HoliDay;
use Metadata\Common\Model\Country;
use Payroll\Common\Model\PayrollCalculations;
use Projects\Common\Model\Project;
use TimeSheets\Common\Model\EmployeeTimeEntry;
use TimeSheets\Common\Model\EmployeeTimeSheet;
use Utils\CalendarTools;
use Utils\LogManager;

class TimeSheetsActionManager extends SubActionManager
{
    public function getTimeEntries($req)
    {
        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);
        $timeSheetEntry = new EmployeeTimeEntry();
        $list = $timeSheetEntry->Find("timesheet = ? order by date_start", array($req->id));
        $mappingStr = $req->sm;
        $map = json_decode($mappingStr);
        if (!$list) {
            LogManager::getInstance()->info($timeSheetEntry->ErrorMsg());
        }

        if (!empty($mappingStr)) {
            $list = $this->baseService->populateMapping($list, $map);
        }
        return new IceResponse(IceResponse::SUCCESS, $list);
    }

    public function changeTimeSheetStatus($req)
    {
        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);

        $subordinate = new Employee();
        $subordinates = $subordinate->Find("supervisor = ?", array($employee->id));

        $subordinatesIds = array();
        foreach ($subordinates as $sub) {
            $subordinatesIds[] = $sub->id;
        }

        $timeSheet = new EmployeeTimeSheet();
        $timeSheet->Load("id = ?", array($req->id));
        if ($timeSheet->id != $req->id) {
            return new IceResponse(IceResponse::ERROR, "Timesheet not found");
        }

        if ($req->status == 'Submitted' && $employee->id == $timeSheet->employee) {
        } elseif (!in_array($timeSheet->employee, $subordinatesIds) && $this->user->user_level != 'Admin') {
            return new IceResponse(IceResponse::ERROR, "This Timesheet does not belong to any of your subordinates");
        }

        $oldStatus = $timeSheet->status;
        $timeSheet->status = $req->status;

        //Auto approve admin timesheets
        if ($req->status == 'Submitted' && BaseService::getInstance()->getCurrentUser()->user_level == "Admin") {
            $timeSheet->status = 'Approved';
        }

        if ($oldStatus == $req->status) {
            return new IceResponse(IceResponse::SUCCESS, "");
        }

        $ok = $timeSheet->Save();
        if (!$ok) {
            LogManager::getInstance()->info($timeSheet->ErrorMsg());
        }

        $timeSheetEmployee = $this->baseService->getElement('Employee', $timeSheet->employee, null, true);

        $this->baseService->audit(
            IceConstants::AUDIT_ACTION,
            "Timesheet [".$timeSheetEmployee->first_name." ".$timeSheetEmployee->last_name
            ." - ".date("M d, Y (l)", strtotime($timeSheet->date_start))." to "
            .date("M d, Y (l)", strtotime($timeSheet->date_end))."] status changed from:"
            .$oldStatus." to:".$req->status
        );

        if ($timeSheet->status == "Submitted" && $employee->id == $timeSheet->employee) {
            $notificationMsg = $employee->first_name." ".$employee->last_name
                ." submitted timesheet from ".date("M d, Y (l)", strtotime($timeSheet->date_start))
                ." to ".date("M d, Y (l)", strtotime($timeSheet->date_end));
            $this->baseService->notificationManager->addNotification(
                $employee->supervisor,
                $notificationMsg,
                '{"type":"url","url":"g=modules&n=time_sheets&m=module_Time_Management#tabSubEmployeeTimeSheetAll"}',
                IceConstants::NOTIFICATION_TIMESHEET
            );
        } elseif ($timeSheet->status == "Approved" || $timeSheet->status == "Rejected") {
            $notificationMsg = $employee->first_name." ".$employee->last_name." ".$timeSheet->status
                ." timesheet from ".date("M d, Y (l)", strtotime($timeSheet->date_start))." to "
                .date("M d, Y (l)", strtotime($timeSheet->date_end));
            $this->baseService->notificationManager->addNotification(
                $timeSheet->employee,
                $notificationMsg,
                '{"type":"url","url":"g=modules&n=time_sheets&m=module_Time_Management#tabEmployeeTimeSheetApproved"}',
                IceConstants::NOTIFICATION_TIMESHEET
            );
        }

        return new IceResponse(IceResponse::SUCCESS, "");
    }

    public function createPreviousTimesheet($req)
    {
        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);

        $timeSheet = new EmployeeTimeSheet();
        $timeSheet->Load("id = ?", array($req->id));
        if ($timeSheet->id != $req->id) {
            return new IceResponse(IceResponse::ERROR, "Timesheet not found");
        }

        if ($timeSheet->employee != $employee->id) {
            return new IceResponse(IceResponse::ERROR, "You don't have permissions to add this Timesheet");
        }

        $end = date("Y-m-d", strtotime("last Saturday", strtotime($timeSheet->date_start)));
        $start = date("Y-m-d", strtotime("last Sunday", strtotime($end)));

        $tempTimeSheet = new EmployeeTimeSheet();
        $tempTimeSheet->Load("employee = ? and date_start = ?", array($employee->id, $start));
        if ($employee->id == $tempTimeSheet->employee) {
            return new IceResponse(IceResponse::ERROR, "Timesheet already exists");
        }

        $newTimeSheet = new EmployeeTimeSheet();
        $newTimeSheet->employee = $employee->id;
        $newTimeSheet->date_start = $start;
        $newTimeSheet->date_end = $end;
        $newTimeSheet->status = "Pending";
        $ok = $newTimeSheet->Save();
        if (!$ok) {
            LogManager::getInstance()->info("Error creating time sheet : ".$newTimeSheet->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, "Error creating Timesheet");
        }

        return new IceResponse(IceResponse::SUCCESS, "");
    }

    public function getSubEmployeeTimeSheets($req)
    {

        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);

        $subordinate = new Employee();
        $subordinates = $subordinate->Find("supervisor = ?", array($employee->id));

        $subordinatesIds = "";
        foreach ($subordinates as $sub) {
            if ($subordinatesIds != "") {
                $subordinatesIds.=",";
            }
            $subordinatesIds.=$sub->id;
        }
        $subordinatesIds.="";

        $mappingStr = $req->sm;
        $map = json_decode($mappingStr);
        $timeSheet = new EmployeeTimeSheet();
        $list = $timeSheet->Find("employee in (".$subordinatesIds.")", array());
        if (!$list) {
            LogManager::getInstance()->info($timeSheet->ErrorMsg());
        }
        if (!empty($mappingStr)) {
            $list = $this->baseService->populateMapping($list, $map);
        }

        return new IceResponse(IceResponse::SUCCESS, $list);
    }

    public function getEmployeeTimeEntries($req)
    {

        $req->start = strtotime($req->start);
        $req->end = strtotime($req->end);

        $employee = $this->baseService->getElement('Employee', $req->e, null, true);

        $currEmployee = $employee->id;
        $timeEntry = new EmployeeTimeEntry();
        $startDate = date("Y-m-d H:i:s", $req->start);
        $endDate = date("Y-m-d H:i:s", $req->end);

        $list = $timeEntry->Find(
            "employee = ? and ((date_start >= ? and date_start <= ? ) or (date_end >= ? and date_end <= ?))",
            array($currEmployee, $startDate,$endDate,$startDate,$endDate)
        );

        if (!$list) {
            LogManager::getInstance()->info($timeEntry->ErrorMsg());
        }

        $map = json_decode('{"employee":["Employee","id","first_name+last_name"]}');
        $list = $this->baseService->populateMapping($list, $map);

        $data = array();
        foreach ($list as $leave) {
            $data[] = $this->workScheduleToEvent($leave);
        }

        $holiday = new HoliDay();
        $holidays = $holiday->Find("1=1", array());

        foreach ($holidays as $holiday) {
            $data[] = $this->holidayToEvent($holiday);
        }

        echo json_encode($data);
        exit();
    }

    public function workScheduleToEvent($schedule)
    {
        $event = array();
        $event['id'] = $schedule->id;

        $event['start'] = $schedule->date_start."+00:00";
        $event['end'] = $schedule->date_end."+00:00";

        $diff = CalendarTools::getTimeDiffInHours($schedule->date_start, $schedule->date_end);

        if (!empty($schedule->project)) {
            $project = new Project();
            $project->Load("id = ?", array($schedule->project));
            $event['title'] = $diff . " h - ".$project->name;
        } else {
            $event['title'] = $diff . ' h';
        }

        $eventBackgroundColor = "#FFF";

        $event['color'] = $eventBackgroundColor;
        $event['backgroundColor'] = $eventBackgroundColor;
        $event['textColor'] = "#0a69b7";

        $schedule = BaseService::getInstance()->cleanUpAdoDB($schedule);
        unset($schedule->keysToIgnore);
        $event['event'] = json_encode($schedule);

        return $event;
    }

    public function holidayToEvent($holiday)
    {
        $event = array();
        $event['id'] = "hd_".$holiday->id;
        if ($holiday->status == "Full Day") {
            $event['title'] = $holiday->name;
        } else {
            $event['title'] = $holiday->name." (".$holiday->status.")";
        }

        if (!empty($holiday->country)) {
            $country = new Country();
            $country->Load("id = ?", array($holiday->country));
            $event['title'] .=" / ".$country->name." only";
        }

        $event['start'] = $holiday->dateh;
        $event['end'] = $holiday->dateh;

        $eventBackgroundColor = "#3c8dbc";

        $event['color'] = $eventBackgroundColor;
        $event['backgroundColor'] = $eventBackgroundColor;
        $event['textColor'] = "#FFF";

        return $event;
    }

    public function getAllData($req)
    {

        $timeSheet = new EmployeeTimeSheet();
        $timeSheet->Load("id = ?", array($req->currentId));

        $cal = new PayrollCalculations();

        $rowTable = BaseService::getInstance()->getFullQualifiedModelClassName($req->rowTable);
        $columnTable = BaseService::getInstance()->getFullQualifiedModelClassName($req->columnTable);
        $valueTable = $req->valueTable;
        $save = $req->save;

        $project = new $rowTable();

        if (SettingsManager::getInstance()->getSetting("Projects: Make All Projects Available to Employees") == "1") {
            $projectList = $project->Find("1 = 1");
        } else {
            $projectList = $project->Find(
                "id in (select project from EmployeeProjects where employee = ?)",
                array(BaseService::getInstance()->getCurrentProfileId())
            );
        }

        $projects = array();
        foreach ($projectList as $project) {
            $p = new \stdClass();
            $p->id = $project->id;
            $p->name = $project->name;
            $projects[] = $p;
        }

        //Add total
        $p = new \stdClass();
        $p->id = -1;
        $p->name = "Total";
        $projects[] = $p;

        $column = new $columnTable();
        $columns = [];
        $days = CalendarTools::getDaysBetweenDates($timeSheet->date_start, $timeSheet->date_end);
        foreach ($days as $dayObj) {
            $day = new \stdClass();
            $day->id = $dayObj->format('Y-m-d');
            $day->name = $dayObj->format("(D) d M");
            if ($timeSheet->status == 'Approved') {
                $day->editable = 'No';
            } else {
                $day->editable = 'Yes';
            }
            $day->default_value = '0.00';
            $columns[] = $day;
        }

        $timeEntry  = new EmployeeTimeEntry();
        $timeEntries = $timeEntry->Find("timesheet = ?", array($timeSheet->id));

        $dateTotals = array();

        //Build value map
        $valueMap = array();
        foreach ($timeEntries as $val) {
            $date = explode(" ", $val->date_start)[0];
            if (!isset($valueMap[$val->project])) {
                $valueMap[$val->project] = array();
            }
            if (!isset($valueMap[$val->project][$date])) {
                $val->date = $date;
                $val->amount = floatval(CalendarTools::getTimeDiffInHours($val->date_start, $val->date_end));
                $valueMap[$val->project][$date] = $val;

                if (!isset($dateTotals[$date])) {
                    $dval = new \stdClass();
                    $dval->project = -1;
                    $dval->date = $date;
                    $dval->amount = 0;
                    $dateTotals[$date] = $dval;
                }

                $dateTotals[$date]->amount = $dateTotals[$date]->amount + floatval($val->amount);
            }
        }

        $values = array();
        foreach ($valueMap as $key => $val) {
            $values = array_merge($values, array_values($val));
        }

        if ($save == "1") {
            foreach ($values as $value) {
                if (empty($value->id)) {
                    $value->Save();
                }
            }
        }

        $values = array_merge($values, array_values($dateTotals));

        return new IceResponse(IceResponse::SUCCESS, array($projects,$columns,$values));
    }

    public function updateAllData($req)
    {

        $resp = $this->updateData($req);

        if ($resp->getStatus() == IceResponse::SUCCESS) {
            $timesheet = new EmployeeTimeSheet();
            $timesheet->Load("id = ?", array($req->currentId));

            $req->id = $timesheet->id;
            $req->status = 'Submitted';
            $this->changeTimeSheetStatus($req);
        }
        return $resp;
    }

    public function updateData($req)
    {
        $timesheet = new EmployeeTimeSheet();
        $timesheet->Load("id = ?", array($req->currentId));

        if (empty($timesheet->id)) {
            return new IceResponse(IceResponse::ERROR, true);
        }

        if ($timesheet->status == 'Submitted') {
            return new IceResponse(IceResponse::ERROR, true);
        }
        foreach ($req as $key => $val) {
            if (!is_array($val) || $val[1].'' == '-1') {
                continue;
            }
            $data = new EmployeeTimeEntry();
            $data->Load(
                "project = ? and timesheet = ? and date(date_start) = ?",
                array($val[1],$req->currentId, $val[0])
            );
            if (empty($data->id)) {
                $data->project = $val[1];
                $data->employee = $timesheet->employee;
                $data->details = '';
                $data->created = date('Y-m-d H:i:s');
                $data->status = 'Active';
                $data->timesheet = $req->currentId;
            }
            $time = floatval($val[2]) * 60 * 60;
            $data->date_start = $val[0].' 00:00:00';
            $data->time_start = '00:00:00';
            $data->date_end = date('Y-m-d H:i:s', strtotime($data->date_start) + $time);
            $data->time_end = date('H:i:s', strtotime($data->date_end));

            $ok = $data->Save();
            if (!$ok) {
                LogManager::getInstance()->error("Error saving payroll data:".$data->ErrorMsg());
            }
        }
        return new IceResponse(IceResponse::SUCCESS, true);
    }
}
