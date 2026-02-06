<?php
namespace AttendanceSheets\User\Api;

use AttendanceSheets\Common\Model\EmployeeAttendanceSheet;
use Classes\BaseService;
use Classes\IceConstants;
use Classes\IceResponse;
use Classes\SubActionManager;
use Employees\Common\Model\Employee;
use Utils\LogManager;

class AttendanceSheetsActionManager extends SubActionManager
{

    public function getTimeEntries($req)
    {
        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);
        $list = EmployeeAttendanceSheet::getAttendanceEntries($req->id);
        $mappingStr = $req->sm;
        $map = json_decode($mappingStr);
        if (!$list) {
            LogManager::getInstance()->info("Error finding attendance");
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

        $timeSheet = new EmployeeAttendanceSheet();
        $timeSheet->Load("id = ?", array($req->id));
        if ($timeSheet->id != $req->id) {
            return new IceResponse(IceResponse::ERROR, "Attendance Sheet not found");
        }

        if ($req->status == 'Submitted' && $employee->id == $timeSheet->employee) {
        } elseif (!in_array($timeSheet->employee, $subordinatesIds) && $this->user->user_level != 'Admin') {
            return new IceResponse(
                IceResponse::ERROR,
                "This Attendance Sheet does not belong to any of your subordinates"
            );
        }

        $oldStatus = $timeSheet->status;
        $timeSheet->status = $req->status;

        //Auto approve admin timesheets
        if ($req->status == 'Submitted' && BaseService::getInstance()->getCurrentUser()->user_level == 'Admin') {
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
            "Attendance sheet [".$timeSheetEmployee->first_name." ".$timeSheetEmployee->last_name
            ." - ".date("M d, Y (l)", strtotime($timeSheet->date_start))." to "
            .date("M d, Y (l)", strtotime($timeSheet->date_end))."] status changed from:".$oldStatus." to:".$req->status
        );

        if ($timeSheet->status == "Submitted" && $employee->id == $timeSheet->employee) {
            $notificationMsg = $employee->first_name." ".$employee->last_name
                ." submitted attendance sheet from "
                .date("M d, Y (l)", strtotime($timeSheet->date_start))
                ." to ".date("M d, Y (l)", strtotime($timeSheet->date_end));
            $this->baseService->notificationManager->addNotification(
                $employee->supervisor,
                $notificationMsg,
                '{"type":"url","url":"g=modules&n=attendance_sheets'
                    .'&m=module_Time_Management#tabSubEmployeeTimeSheetAll"}',
                IceConstants::NOTIFICATION_TIMESHEET
            );
        } elseif ($timeSheet->status == "Approved" || $timeSheet->status == "Rejected") {
            $notificationMsg = $employee->first_name." ".$employee->last_name." ".$timeSheet->status
                ." attendance sheet from ".date("M d, Y (l)", strtotime($timeSheet->date_start))
                ." to ".date("M d, Y (l)", strtotime($timeSheet->date_end));
            $this->baseService->notificationManager->addNotification(
                $timeSheet->employee,
                $notificationMsg,
                '{"type":"url","url":"g=modules&n=attendance_sheets'
                .'&m=module_Time_Management#tabEmployeeTimeSheetApproved"}',
                IceConstants::NOTIFICATION_TIMESHEET
            );
        }

        return new IceResponse(IceResponse::SUCCESS, "");
    }

    public function createPreviousTimesheet($req)
    {
        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);

        $timeSheet = new EmployeeAttendanceSheet();
        $timeSheet->Load("id = ?", array($req->id));
        if ($timeSheet->id != $req->id) {
            return new IceResponse(IceResponse::ERROR, "Employee attendance sheet not found");
        }

        if ($timeSheet->employee != $employee->id) {
            return new IceResponse(IceResponse::ERROR, "You don't have permissions to add this attendance sheet");
        }

        $end = date("Y-m-d", strtotime("last Saturday", strtotime($timeSheet->date_start)));
        $start = date("Y-m-d", strtotime("last Sunday", strtotime($end)));

        $tempTimeSheet = new EmployeeAttendanceSheet();
        $tempTimeSheet->Load("employee = ? and date_start = ?", array($employee->id, $start));
        if ($employee->id == $tempTimeSheet->employee) {
            return new IceResponse(IceResponse::ERROR, "Attendance sheet already exists");
        }

        $newTimeSheet = new EmployeeAttendanceSheet();
        $newTimeSheet->employee = $employee->id;
        $newTimeSheet->date_start = $start;
        $newTimeSheet->date_end = $end;
        $newTimeSheet->status = "Pending";
        $ok = $newTimeSheet->Save();
        if (!$ok) {
            LogManager::getInstance()->info("Error creating attendance sheet : ".$newTimeSheet->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, "Error creating attendance sheet");
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
        $timeSheet = new EmployeeAttendanceSheet();
        $list = $timeSheet->Find("employee in (".$subordinatesIds.")", array());
        if (!$list) {
            LogManager::getInstance()->info($timeSheet->ErrorMsg());
        }
        if (!empty($mappingStr)) {
            $list = $this->baseService->populateMapping($list, $map);
        }

        return new IceResponse(IceResponse::SUCCESS, $list);
    }
}
