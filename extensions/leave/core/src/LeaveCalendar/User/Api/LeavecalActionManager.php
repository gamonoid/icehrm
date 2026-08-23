<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:25 PM
 */

namespace LeaveCalendar\User\Api;

use Classes\SubActionManager;
use Employees\Common\Model\Employee;
use Leaves\Admin\Api\LeaveUtil;
use Leaves\Common\Model\EmployeeLeave;
use Leaves\Common\Model\EmployeeLeaveDay;
use Leaves\Common\Model\HoliDay;
use Leaves\Common\Model\LeaveType;
use Metadata\Common\Model\Country;
use Utils\CalendarTools;
use Utils\LogManager;

class LeavecalActionManager extends SubActionManager
{

    public function getLeavesForMeAndSubordinates($req)
    {

        $req->start = strtotime($req->start);
        $req->end = strtotime($req->end);

        $shareCalendar = $this->baseService->settingsManager->getSetting("Leave: Share Calendar to Whole Company");

        $map = json_decode(
            '{"employee":["Employee","id","first_name+last_name"],"leave_type":["LeaveType","id","name"]}'
        );

        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);

        if ($shareCalendar != "1") {
            $subordinate = new Employee();
            $subordinates = $subordinate->Find("supervisor = ?", array($employee->id));

            $subordinatesIds = $employee->id;
            foreach ($subordinates as $sub) {
                if ($subordinatesIds != "") {
                    $subordinatesIds.=",";
                }
                $subordinatesIds.=$sub->id;
            }

            $employeeLeave = new EmployeeLeave();
            $startDate = date("Y-m-d H:i:s", $req->start);
            $endDate = date("Y-m-d H:i:s", $req->end);

            $list = $employeeLeave->Find(
                "employee in (".$subordinatesIds
                .") and status in ('Approved','Pending') and ((date_start >= ? and date_start <= ? )"
                ." or (date_end >= ? and date_end <= ?))",
                array($startDate,$endDate,$startDate,$endDate)
            );
        } else {
            $employeeLeave = new EmployeeLeave();
            $startDate = date("Y-m-d H:i:s", $req->start);
            $endDate = date("Y-m-d H:i:s", $req->end);

            $list = $employeeLeave->Find(
                "status in ('Approved','Pending') and ((date_start >= ? and date_start <= ? ) 
                or (date_end >= ? and date_end <= ?))",
                array($startDate,$endDate,$startDate,$endDate)
            );
        }

        if (!$list) {
            LogManager::getInstance()->info($employeeLeave->ErrorMsg());
        }
        if (!empty($map)) {
            $list = $this->baseService->populateMapping($list, $map);
        }

        $leaveType = new LeaveType();
        $leaveTypesTemp = $leaveType->Find("1=1");
        $leaveTypes = array();
        foreach ($leaveTypesTemp as $leaveType) {
            $leaveTypes[$leaveType->name] = $leaveType;
        }

        $data = array();
        $mode = CalendarTools::getCalendarMode($req->start, $req->end);
        foreach ($list as $leave) {
            $tmpEvents = $this->leaveToEvents($leave, $leaveTypes);
            foreach ($tmpEvents as $event) {
                $data[] =  $event;
            }
        }
        /*
        if($mode == CalendarTools::MODE_MONTH){
            foreach($list as $leave){
                $data[] = $this->leaveToEvent($leave, $leaveTypes);
            }
        }else{
            foreach($list as $leave){
                $tmpEvents = $this->leaveToEvents($leave, $leaveTypes);
                foreach($tmpEvents as $event){
                    $data[] =  $event;
                }
            }
        }
        */
        $holiday = new HoliDay();
        $holidays = $holiday->Find("1=1", array());

        foreach ($holidays as $holiday) {
            $data[] = $this->holidayToEvent($holiday);
        }

        echo json_encode($data);
        exit();
    }

    public function leaveToEvent($leave, $leaveTypes)
    {
        $event = array();
        $event['id'] = $leave->id;
        $event['title'] = $leave->employee." (".$leave->leave_type.")";
        $event['start'] = $leave->date_start;
        $event['end'] = $leave->date_end;

        if (empty($leaveTypes[$leave->leave_type]->leave_color)) {
            if ($leave->status == "Pending") {
                $eventBackgroundColor = "#cc9900";
            } else {
                $eventBackgroundColor = "#336633";
            }
            $event['title'] = $leave->employee." (".$leave->leave_type.")";
        } else {
            $eventBackgroundColor = $leaveTypes[$leave->leave_type]->leave_color;
            $event['title'] = $leave->employee." (".$leave->status.")";
        }
        $event['color'] = $eventBackgroundColor;
        $event['backgroundColor'] = $eventBackgroundColor;
        $event['textColor'] = "#FFF";

        return $event;
    }

    public function leaveToEvents($leave, $leaveTypes)
    {

        $leaveDay = new EmployeeLeaveDay();
        $leaveDays = $leaveDay->Find("employee_leave = ?", array($leave->id));
        $events = array();
        foreach ($leaveDays as $leaveDay) {
            $event = array();
            $event['id'] = $leaveDay->id;
            $event['title'] = $leave->employee." (".$leave->leave_type.")";

            if ($leaveDay->leave_type == 'Full Day') {
                $event['allDay'] = true;
            } else {
                $event['allDay'] = false;
            }
            $time = LeaveUtil::leaveTypeToTime($leaveDay->leave_date, $leaveDay->leave_type);
            $event['start'] = $time[0];
            $event['end'] = $time[1];
            if (empty($leaveTypes[$leave->leave_type]->leave_color)) {
                if ($leave->status == "Pending") {
                    $eventBackgroundColor = "#cc9900";
                } else {
                    $eventBackgroundColor = "#336633";
                }
                $event['title'] = $leave->employee." (".$leave->leave_type.")";
            } else {
                $eventBackgroundColor = $leaveTypes[$leave->leave_type]->leave_color;
                $event['title'] = $leave->employee." (".$leave->status.")";
            }
            $event['color'] = $eventBackgroundColor;
            $event['backgroundColor'] = $eventBackgroundColor;
            $event['textColor'] = "#FFF";

            $events[] = $event;
        }

        return $events;
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
}
