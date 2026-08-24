<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:24 PM
 */

namespace TimeSheets\User\Api;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceConstants;
use Classes\IceResponse;
use Classes\SettingsManager;
use Classes\StatusChangeLogManager;
use Classes\SubActionManager;
use Employees\Common\Model\Employee;
use Leaves\Admin\Api\LeaveUtil;
use Leaves\Common\Model\EmployeeLeave;
use Leaves\Common\Model\EmployeeLeaveDay;
use Leaves\Common\Model\HoliDay;
use Leaves\Common\Model\LeaveType;
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
		$timeSheet = new EmployeeTimeSheet();
		$timeSheet->Load('id = ?', [$req->id]);
		// Ownership gate — $req->id is a request-supplied timesheet id.
		if (!empty($timeSheet->id)
			&& !$this->baseService->currentUserCanAccessEmployeeData($timeSheet->employee)) {
			return new IceResponse(IceResponse::ERROR, 'Permission denied', 403);
		}
		$timeSheet->total_time = $timeSheet->getTotalTime();
		$employee = $this->baseService->getElement('Employee', $timeSheet->employee, null, true);
		$employee = FileService::getInstance()->updateSmallProfileImage($employee);
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

		$emp = new \stdClass();
		$emp->name = $employee->first_name.' '.$employee->last_name;
		$emp->image = $employee->image;

        return new IceResponse(IceResponse::SUCCESS, [$list, $emp, $timeSheet]);
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
        $note = isset($req->note) ? trim((string) $req->note) : '';

        //Auto approve admin timesheets
        if ($req->status == 'Submitted'
            && BaseService::getInstance()->getCurrentUser()->user_level == 'Admin'
            && empty($employee->supervisor)
        ) {
            $timeSheet->status = 'Approved';
        }

        if ($oldStatus == $req->status) {
            return new IceResponse(IceResponse::SUCCESS, "");
        }

        // Store the note (e.g. a rejection reason) as the timesheet's current note.
        $timeSheet->note = $note;

        $ok = $timeSheet->Save();
        if (!$ok) {
            LogManager::getInstance()->info($timeSheet->ErrorMsg());
        }

        // Record the status change (with any note) so it shows in the approval log.
        StatusChangeLogManager::getInstance()->addLog(
            'EmployeeTimeSheet',
            $timeSheet->id,
            BaseService::getInstance()->getCurrentUser()->id,
            $oldStatus,
            $timeSheet->status,
            $note
        );

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

    /**
     * Bulk-approve timesheets (Direct Reports). Each is approved through
     * changeTimeSheetStatus, so the subordinate/permission check, notification and
     * approval-log entry all run per timesheet exactly as for a single approval.
     */
    public function bulkApproveTimeSheets($req)
    {
        $ids = isset($req->ids) && is_array($req->ids) ? $req->ids : array();
        $note = isset($req->note) ? $req->note : '';
        $approved = 0;
        $failed = array();
        foreach ($ids as $id) {
            $statusReq = new \stdClass();
            $statusReq->id = $id;
            $statusReq->status = 'Approved';
            $statusReq->note = $note;
            $resp = $this->changeTimeSheetStatus($statusReq);
            if ($resp->getStatus() === IceResponse::SUCCESS) {
                $approved++;
            } else {
                $failed[] = array('id' => $id, 'message' => $resp->getData());
            }
        }

        return new IceResponse(IceResponse::SUCCESS, array('approved' => $approved, 'failed' => $failed));
    }

    public function createPreviousTimesheet($req)
    {
        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);
		$user = BaseService::getInstance()->getCurrentUser();
        $timeSheet = new EmployeeTimeSheet();
        $timeSheet->Load("id = ?", array($req->id));
        if ($timeSheet->id != $req->id) {
            return new IceResponse(IceResponse::ERROR, "Timesheet not found");
        }

        if ($timeSheet->employee != $employee->id && $user->user_level !== 'Admin') {
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

	public function createNextWeekTimesheet($req)
	{
		$employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);
		$user = BaseService::getInstance()->getCurrentUser();
		$timeSheet = new EmployeeTimeSheet();
		$timeSheet->Load("id = ?", array($req->id));
		if ($timeSheet->id != $req->id) {
			return new IceResponse(IceResponse::ERROR, "Timesheet not found");
		}

		if ($timeSheet->employee != $employee->id && $user->user_level !== 'Admin') {
			return new IceResponse(IceResponse::ERROR, "You don't have permissions to add this Timesheet");
		}

		$start = date("Y-m-d", strtotime("next Sunday", strtotime($timeSheet->date_end)));
		$end = date("Y-m-d", strtotime("next Saturday", strtotime($start)));


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
        if (empty($subordinatesIds)) {
            return new IceResponse(IceResponse::SUCCESS, []);
        }
        $list = $timeSheet->Find("employee in (".$subordinatesIds.")", array());

        if (!empty($mappingStr)) {
            $list = $this->baseService->populateMapping($list, $map);
        }

        return new IceResponse(IceResponse::SUCCESS, $list);
    }

    public function getEmployeeTimeEntries($req)
    {

        $req->start = strtotime($req->start);
        $req->end = strtotime($req->end);

        // Ownership gate — $req->e is a request-supplied employee id.
        if (!$this->baseService->currentUserCanAccessEmployeeData($req->e)) {
            return new IceResponse(IceResponse::ERROR, 'Permission denied', 403);
        }

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
		$dateTotals = [];
        foreach ($list as $entry) {
            $data[] = $this->workScheduleToEvent($entry);
			$date = date('Y-m-d', strtotime($entry->date_start));
			$dateTotals[$date] = (float)$dateTotals[$date] + floatval(CalendarTools::getTimeDiffInHours($entry->date_start, $entry->date_end));
        }

		$totalHoursEvents = $this->createDateTotalEvents($dateTotals);
		$data = array_merge($data, $totalHoursEvents);

        // Add employee leave days

        if (class_exists('\Leaves\Common\Model\EmployeeLeave')) {
            $days = LeaveUtil::getEmployeeLeaveDaysBetweenDays($employee->id, $startDate, $endDate);
            foreach ($days as $day) {
                if ($day->leave->status !== 'Approved' && $day->leave->status !== 'Pending') {
                    continue;
                }
                $data[] = $this->leaveDayToEvent($day);
            }
        }

        // Add holidays to time sheet
        if (class_exists('\Leaves\Common\Model\HoliDay')) {

            $country = new Country();
            $country->Load('code = ?', [$employee->country]);

            $leaveUtil = new LeaveUtil();
            $holidays = $leaveUtil->getHolidays($startDate, $endDate, $country->id, $employee->id);
            $holidays = array_values($holidays);

            foreach ($holidays as $holiday) {
                $data[] = $this->holidayToEvent($holiday);
            }
        }

        echo json_encode($data);
        exit();
    }

	public function createDateTotalEvents($dateTotals) {
		$events = [];
		foreach($dateTotals as $day => $total) {
			$event = array();
			$event['id'] = $day;
			$event['start'] = $day." 23:59:59+00:00";
			$event['end'] = $day." 23:59:59+00:00";
			$event['title'] = 'Total Hours: '.$total;

			$event['color'] = '#3f78b9';
			$event['backgroundColor'] = '#3f78b9';
			$event['textColor'] = "#FFF";

			$events[] = $event;
		}
		return $events;
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
            $event['title'] = sprintf('Holiday (%s)', $holiday->name);
        } else {
            $event['title'] = sprintf('Holiday (%s / %s)', $holiday->name, $holiday->status);
        }

        $event['start'] = $holiday->dateh;
        $event['end'] = $holiday->dateh;

        $eventBackgroundColor = "#3c8dbc";

        $event['color'] = $eventBackgroundColor;
        $event['backgroundColor'] = $eventBackgroundColor;
        $event['textColor'] = "#FFF";

        return $event;
    }

    public function leaveDayToEvent($leaveDay)
    {
        $event = array();
        $event['id'] = "ld_".$leaveDay->id;
        if ($leaveDay->leave_type == "Full Day") {
            $event['title'] = sprintf('%s Leave', $leaveDay->leave->status);
        } else {
            $event['title'] = sprintf('%s Leave (%s)', $leaveDay->leave->status, $leaveDay->leave_type);
        }

        $event['start'] = $leaveDay->leave_date;
        $event['end'] = $leaveDay->leave_date;
        if ($leaveDay->leave->status === 'Pending') {
            $eventBackgroundColor = "#cc9900";
        } else {
            $eventBackgroundColor = "#739900";
        }


        $event['color'] = $eventBackgroundColor;
        $event['backgroundColor'] = $eventBackgroundColor;
        $event['textColor'] = "#FFF";

        return $event;
    }

    public function getAllData($req)
    {

        $timeSheet = new EmployeeTimeSheet();
        $timeSheet->Load("id = ?", array($req->currentId));

        // Ownership gate — currentId is a request-supplied timesheet id; getAllData
        // also writes when $req->save == '1'.
        if (!empty($timeSheet->id)
            && !$this->baseService->currentUserCanAccessEmployeeData($timeSheet->employee)) {
            return new IceResponse(IceResponse::ERROR, 'Permission denied', 403);
        }

        $cal = new PayrollCalculations();

        $rowTable = BaseService::getInstance()->getFullQualifiedModelClassName($req->rowTable);
        $columnTable = BaseService::getInstance()->getFullQualifiedModelClassName($req->columnTable);
        $valueTable = $req->valueTable;
        $save = $req->save;

        $project = new $rowTable();

        if (SettingsManager::getInstance()->getSetting("Projects: Make All Projects Available to Employees") == "1") {
            $projectList = $project->Find("1 = 1 order by name");
        } else {
            $projectList = $project->Find(
                "WHERE id in (select project from EmployeeProjects where employee = ?) order by name",
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

	public function getLeaveMessage($req)
	{
		if (!class_exists('Leaves\Common\Model\EmployeeLeave')) {
			return new IceResponse(IceResponse::SUCCESS, '');
		}

		$timeSheet = new EmployeeTimeSheet();
		$timeSheet->Load("id = ?", array($req->id));

		// Ownership gate — $req->id is a request-supplied timesheet id; this returns
		// that employee's approved-leave dates.
		if (!empty($timeSheet->id)
			&& !$this->baseService->currentUserCanAccessEmployeeData($timeSheet->employee)) {
			return new IceResponse(IceResponse::ERROR, 'Permission denied', 403);
		}

		$employeeLeave = new EmployeeLeave();

		$employeeId = $timeSheet->employee;
		$startDate = $timeSheet->date_start;
		$endDate = $timeSheet->date_end;

		$leaves = $employeeLeave->Find(
			"employee = ? and ((date_start >= ? and date_start <= ?) 
            or (date_end >= ? and date_end <= ?)) and status = ?",
			array($employeeId, $startDate, $endDate, $startDate, $endDate, "Approved")
		);

		if (empty($leaves)) {
			return new IceResponse(IceResponse::SUCCESS, '');
		}


		$dayStrings = [];
		foreach ($leaves as $leave) {
			$employeeLeaveDay = new EmployeeLeaveDay();
			$days = $employeeLeaveDay->Find("employee_leave = ?", array($leave->id));
			foreach ($days as $day) {
				if (strtotime($day->leave_date) >= strtotime($startDate)
					&& strtotime($day->leave_date) <= strtotime($endDate)
				) {
					$dayStrings[] = sprintf('%s (%s)', $day->leave_date, $day->leave_type);
				}
			}
		}

		$str = 'You have approved leave request(s) on ';

		for ($i = 0; $i<count($dayStrings); $i++) {
			if ($i != 0) {
				$str .= ', ';
			}
			$str .= $dayStrings[$i];
		}

		return new IceResponse(IceResponse::SUCCESS, $str);
	}

	/**
	 * Structured approved-leave days that overlap the timesheet week, for the
	 * timesheet grid's leave notice and its submit validation. Each entry:
	 * { date: 'Y-m-d', type: 'Full Day'|'Half Day - ...', half: bool }.
	 */
	/**
	 * Status-change / approval log for a timesheet (submitted, approved, rejected
	 * with any notes), newest first — shown at the bottom of the timesheet view.
	 */
	public function getTimeSheetLogs($req)
	{
		// Ownership gate — $req->id is a request-supplied timesheet id whose approval
		// log this returns. Load the sheet and scope to its owner.
		$timeSheet = new EmployeeTimeSheet();
		$timeSheet->Load("id = ?", array($req->id));
		if (!empty($timeSheet->id)
			&& !$this->baseService->currentUserCanAccessEmployeeData($timeSheet->employee)) {
			return new IceResponse(IceResponse::ERROR, 'Permission denied', 403);
		}

		$resp = StatusChangeLogManager::getInstance()->getLogs('EmployeeTimeSheet', $req->id);
		$logs = $resp->getData();
		if (!is_array($logs)) {
			$logs = array();
		}
		// Newest first.
		usort($logs, function ($a, $b) {
			return strcmp((string) $b['time'], (string) $a['time']);
		});

		return new IceResponse(IceResponse::SUCCESS, $logs);
	}

	public function getLeaveDaysForTimeSheet($req)
	{
		$timeSheet = new EmployeeTimeSheet();
		$timeSheet->Load("id = ?", array($req->id));
		if (empty($timeSheet->id)) {
			return new IceResponse(IceResponse::SUCCESS, array());
		}

		// Ownership gate — $req->id is a request-supplied timesheet id.
		if (!$this->baseService->currentUserCanAccessEmployeeData($timeSheet->employee)) {
			return new IceResponse(IceResponse::ERROR, 'Permission denied', 403);
		}

		$map = $this->getLeaveDayMap($timeSheet);
		$out = array();
		foreach ($map as $date => $info) {
			$out[] = array('date' => $date, 'type' => $info['type'], 'half' => $info['half']);
		}
		usort($out, function ($a, $b) {
			return strcmp($a['date'], $b['date']);
		});

		return new IceResponse(IceResponse::SUCCESS, $out);
	}

	/**
	 * Total approved leave (in days: full = 1, half = 0.5) for each of the given
	 * timesheet ids — used by the Direct Reports "Leave Time" column. Also reports
	 * whether the leave module is installed, so the UI can hide the column when it
	 * is not.
	 */
	public function getLeaveDaysCountForTimeSheets($req)
	{
		$available = class_exists('Leaves\\Common\\Model\\EmployeeLeave');
		$counts = array();
		if ($available) {
			$ids = isset($req->ids) && is_array($req->ids) ? $req->ids : array();
			foreach ($ids as $id) {
				$timeSheet = new EmployeeTimeSheet();
				$timeSheet->Load("id = ?", array($id));
				$days = 0;
				// Skip sheets the caller may not see rather than fail the whole batch;
				// this column is populated for the caller's own direct reports.
				if (!empty($timeSheet->id)
					&& $this->baseService->currentUserCanAccessEmployeeData($timeSheet->employee)) {
					$map = $this->getLeaveDayMap($timeSheet);
					foreach ($map as $info) {
						$days += $info['half'] ? 0.5 : 1;
					}
				}
				$counts[(string) $id] = $days;
			}
		}

		return new IceResponse(IceResponse::SUCCESS, array('available' => $available, 'counts' => $counts));
	}

	/**
	 * Every leave request that overlaps the timesheet period, for the "Leave in
	 * this period" list on the timesheet view (a manager opening a direct report's
	 * sheet sees why days are empty). Unlike getLeaveDaysForTimeSheet — which
	 * drives the grid's submit validation and is therefore approved-only — this
	 * returns ALL statuses (Pending, Approved, Rejected, Cancellation Requested,
	 * Cancelled, Processing) so the UI can show where each request stands, plus
	 * the day-by-day breakdown inside the period ('Full Day', 'Half Day - Morning',
	 * '3 Hours - Afternoon', …).
	 *
	 * Returns ['available' => bool, 'requests' => [...]]; available is false when
	 * the leave module is not installed, so the UI can hide the section entirely.
	 */
	public function getLeaveRequestsForTimeSheet($req)
	{
		if (!class_exists('Leaves\\Common\\Model\\EmployeeLeave')) {
			return new IceResponse(IceResponse::SUCCESS, array('available' => false, 'requests' => array()));
		}

		$timeSheet = new EmployeeTimeSheet();
		$timeSheet->Load("id = ?", array($req->id));
		if (empty($timeSheet->id)) {
			return new IceResponse(IceResponse::SUCCESS, array('available' => true, 'requests' => array()));
		}

		// Ownership gate — $req->id is a request-supplied timesheet id; this returns
		// that employee's leave requests.
		if (!$this->baseService->currentUserCanAccessEmployeeData($timeSheet->employee)) {
			return new IceResponse(IceResponse::ERROR, 'Permission denied', 403);
		}

		$startDate = $timeSheet->date_start;
		$endDate = $timeSheet->date_end;

		// Plain interval overlap, so a leave that spans the whole week (starting
		// before it and ending after it) is included too.
		$employeeLeave = new EmployeeLeave();
		$leaves = $employeeLeave->Find(
			"employee = ? and date_start <= ? and date_end >= ? order by date_start",
			array($timeSheet->employee, $endDate, $startDate)
		);

		$typeNames = array();
		$requests = array();
		foreach ($leaves as $leave) {
			$days = array();
			$totalDays = 0;
			$employeeLeaveDay = new EmployeeLeaveDay();
			$leaveDays = $employeeLeaveDay->Find(
				"employee_leave = ? and leave_date >= ? and leave_date <= ? order by leave_date",
				array($leave->id, $startDate, $endDate)
			);
			foreach ($leaveDays as $day) {
				$amount = class_exists('Leaves\\Admin\\Api\\LeaveUtil')
					? LeaveUtil::getLeaveTime($day->leave_type) : 0;
				$totalDays += $amount;
				$days[] = array(
					'date' => date('Y-m-d', strtotime($day->leave_date)),
					// The raw stored type ('Full Day', 'Half Day - Morning',
					// '2 Hours - Afternoon', …) — shown as-is so any partial-day
					// type configured for this install renders correctly.
					'type' => $day->leave_type,
					'amount' => $amount,
				);
			}

			$typeId = $leave->leave_type;
			if (!isset($typeNames[$typeId])) {
				$name = '';
				if (class_exists('Leaves\\Common\\Model\\LeaveType')) {
					$leaveType = new LeaveType();
					$leaveType->Load("id = ?", array($typeId));
					$name = empty($leaveType->id) ? '' : $leaveType->name;
				}
				$typeNames[$typeId] = $name;
			}

			$requests[] = array(
				'id' => $leave->id,
				'leave_type' => $typeNames[$typeId],
				'date_start' => $leave->date_start,
				'date_end' => $leave->date_end,
				'status' => $leave->status,
				'details' => $leave->details,
				'days' => $days,
				'days_total' => $totalDays,
			);
		}

		return new IceResponse(IceResponse::SUCCESS, array('available' => true, 'requests' => $requests));
	}

	/**
	 * Approved leave days overlapping the timesheet, keyed by date:
	 * [ 'Y-m-d' => ['type' => ..., 'half' => bool] ]. When a date has both a
	 * half and a full-day leave, the stricter (full day) wins.
	 */
	private function getLeaveDayMap($timeSheet)
	{
		$map = array();
		if (!class_exists('Leaves\\Common\\Model\\EmployeeLeave')) {
			return $map;
		}

		$employeeLeave = new EmployeeLeave();
		$leaves = $employeeLeave->Find(
			"employee = ? and ((date_start >= ? and date_start <= ?)
            or (date_end >= ? and date_end <= ?)) and status = ?",
			array(
				$timeSheet->employee,
				$timeSheet->date_start, $timeSheet->date_end,
				$timeSheet->date_start, $timeSheet->date_end,
				"Approved",
			)
		);

		foreach ($leaves as $leave) {
			$employeeLeaveDay = new EmployeeLeaveDay();
			$days = $employeeLeaveDay->Find("employee_leave = ?", array($leave->id));
			foreach ($days as $day) {
				if (strtotime($day->leave_date) < strtotime($timeSheet->date_start)
					|| strtotime($day->leave_date) > strtotime($timeSheet->date_end)
				) {
					continue;
				}
				$date = date('Y-m-d', strtotime($day->leave_date));
				$isHalf = (stripos($day->leave_type, 'Half Day') !== false);
				// Only overwrite when upgrading a half day to the stricter full day.
				if (!isset($map[$date]) || (!$isHalf && $map[$date]['half'])) {
					$map[$date] = array('type' => $day->leave_type, 'half' => $isHalf);
				}
			}
		}

		return $map;
	}

	/**
	 * Reject submission when time is logged on an approved-leave day: no time on a
	 * full-day leave, at most 4 hours on a half-day leave. Returns an error string
	 * or null when the timesheet is valid.
	 */
	private function validateAgainstLeave($timesheet, $req = null)
	{
		$map = $this->getLeaveDayMap($timesheet);
		if (empty($map)) {
			return null;
		}

		// Merge the already-saved entries with the edits being saved ($req), keyed
		// per date+project, so validation reflects the timesheet AFTER this save —
		// letting us block a save/submit before any invalid time is persisted.
		$byCell = array();
		$entry = new EmployeeTimeEntry();
		$entries = $entry->Find("timesheet = ?", array($timesheet->id));
		foreach ($entries as $e) {
			$date = date('Y-m-d', strtotime($e->date_start));
			$byCell[$date.'|'.$e->project] = floatval(CalendarTools::getTimeDiffInHours($e->date_start, $e->date_end));
		}
		if (is_object($req) || is_array($req)) {
			foreach ($req as $key => $val) {
				if (!is_array($val) || $val[1].'' == '-1') {
					continue;
				}
				$byCell[$val[0].'|'.$val[1]] = floatval($val[2]);
			}
		}

		$totals = array();
		foreach ($byCell as $cellKey => $hrs) {
			$date = substr($cellKey, 0, strpos($cellKey, '|'));
			$totals[$date] = (isset($totals[$date]) ? $totals[$date] : 0) + $hrs;
		}

		$errors = array();
		foreach ($map as $date => $info) {
			$hrs = isset($totals[$date]) ? round($totals[$date], 2) : 0;
			if (!$info['half'] && $hrs > 0) {
				$errors[] = "$date is a full-day approved leave — no time can be logged for that day.";
			} elseif ($info['half'] && $hrs > 4) {
				$errors[] = "$date is a half-day approved leave — at most 4 hours can be logged (you logged {$hrs}).";
			}
		}

		return empty($errors) ? null : implode(' ', $errors);
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

        // Ownership gate. currentId is a request-supplied timesheet id, so without this
        // any employee could rewrite or delete another employee's time entries (only
        // the status was checked). Own timesheet, or a manager over its owner.
        if (!$this->baseService->currentUserCanAccessEmployeeData($timesheet->employee)) {
            return new IceResponse(IceResponse::ERROR, 'Permission denied', 403);
        }

        if ($timesheet->status !== 'Submitted' && $timesheet->status !== 'Pending' && $timesheet->status !== 'Rejected') {
            return new IceResponse(IceResponse::ERROR, true);
        }

        // Block save/submit when time is logged on approved-leave days (no time on
        // a full-day leave, at most 4 hours on a half-day). Validated on the merged
        // result BEFORE any entry is persisted.
        $leaveError = $this->validateAgainstLeave($timesheet, $req);
        if ($leaveError !== null) {
            return new IceResponse(IceResponse::ERROR, $leaveError);
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
            } elseif ((int)$val[2] === 0) {
				$data->Delete();
				continue;
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
