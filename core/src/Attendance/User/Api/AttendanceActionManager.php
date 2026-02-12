<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/18/17
 * Time: 5:05 AM
 */

namespace Attendance\User\Api;

use Attendance\Common\Model\Attendance;
use AttendanceSheets\Common\Model\EmployeeAttendanceSheet;
use Classes\BaseService;
use Classes\IceConstants;
use Classes\IceResponse;
use Classes\SettingsManager;
use Classes\SubActionManager;
use TimeSheets\Common\Model\EmployeeTimeSheet;
use Utils\LogManager;
use Utils\NetworkUtils;

class AttendanceActionManager extends SubActionManager
{

    public function getPunch($req)
    {
        $date = $req->date;
        $arr = explode(" ", $date);
        $date = $arr[0];

        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);

        //Find any open punch
        $attendance = new Attendance();
        $attendance->Load(
            "employee = ? and DATE_FORMAT( in_time,  '%Y-%m-%d' ) = ? and out_time is NULL",
            array($employee->id,$date)
        );

        if ($attendance->employee == $employee->id) {
            //found an open punch
            return new IceResponse(IceResponse::SUCCESS, $attendance);
        } else {
            return new IceResponse(IceResponse::SUCCESS, null);
        }
    }

    public function savePunch($req)
    {

        $limitPunchInOutTimes = SettingsManager::getInstance()->getSetting('Attendance: Limit Punch in-Out Times') === '1';
        $useServerTime = SettingsManager::getInstance()->getSetting('Attendance: Use Department Time Zone');
        $currentEmployeeTimeZone = BaseService::getInstance()->getCurrentEmployeeTimeZone();

        if ($useServerTime == '1' && !empty($currentEmployeeTimeZone)) {
            date_default_timezone_set('Asia/Colombo');

            $date = new \DateTime("now", new \DateTimeZone('Asia/Colombo'));

            $date->setTimezone(new \DateTimeZone($currentEmployeeTimeZone));
            $req->time = $date->format('Y-m-d H:i:s');
        }

        $req->date = $req->time;

        //check if there is an open punch
        /* @var \Attendance\Common\Model\Attendance */
        $openPunch = $this->getPunch($req)->getData();

        if (empty($openPunch)) {
            $openPunch = new Attendance();
        }

        $dateTime = $req->date;
        $arr = explode(" ", $dateTime);
        $date = $arr[0];
		$time = $arr[1];

		if ($limitPunchInOutTimes) {
			if ( empty($openPunch->in_time) ) {
				$resp = $this->ensureAttendancePunchInTimeIsValid($time);
				if ($resp->getStatus() === IceResponse::ERROR) {
					return $resp;
				}
			} else {
				$resp = $this->ensureAttendancePunchOutTimeIsValid($time);
				if ($resp->getStatus() === IceResponse::ERROR) {
					return $resp;
				}
			}
		}

        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);

        //check if dates are differnet
        $arr = explode(" ", $openPunch->in_time);
        $inDate = $arr[0];
        if (!empty($openPunch->in_time) && $inDate != $date) {
            return new IceResponse(IceResponse::ERROR, "Attendance entry should be within a single day");
        }

        //compare dates
        if (!empty($openPunch->in_time) && strtotime($dateTime) <= strtotime($openPunch->in_time)) {
            return new IceResponse(IceResponse::ERROR, "Punch-in time should be earlier than Punch-out time");
        }

        //Find all punches for the day
        $attendance = new Attendance();
        $attendanceList = $attendance->Find(
            "employee = ? and DATE_FORMAT( in_time,  '%Y-%m-%d' ) = ?",
            array($employee->id,$date)
        );

        foreach ($attendanceList as $attendance) {
            if (!empty($openPunch->in_time)) {
                if ($openPunch->id == $attendance->id) {
                    continue;
                }
                if (strtotime($attendance->out_time) >= strtotime($dateTime)
                    && strtotime($attendance->in_time) <= strtotime($dateTime)
                ) {
                    //-1---0---1---0 || ---0--1---1---0
                    return new IceResponse(IceResponse::ERROR, "Time entry is overlapping with an existing one");
                } elseif (strtotime($attendance->out_time) >= strtotime($openPunch->in_time)
                    && strtotime($attendance->in_time) <= strtotime($openPunch->in_time)
                ) {
                    //---0---1---0---1 || ---0--1---1---0
                    return new IceResponse(IceResponse::ERROR, "Time entry is overlapping with an existing one");
                } elseif (strtotime($attendance->out_time) <= strtotime($dateTime)
                    && strtotime($attendance->in_time) >= strtotime($openPunch->in_time)
                ) {
                    //--1--0---0--1--
                    return new IceResponse(
                        IceResponse::ERROR,
                        "Time entry is overlapping with an existing one ".$attendance->id
                    );
                }
            } else {
                if (strtotime($attendance->out_time) >= strtotime($dateTime)
                    && strtotime($attendance->in_time) <= strtotime($dateTime)
                ) {
                    //---0---1---0
                    return new IceResponse(IceResponse::ERROR, "Time entry is overlapping with an existing one");
                }
            }
        }
        if (!empty($openPunch->in_time)) {
            $openPunch->out_time = $dateTime;
            if (empty($openPunch->note)) {
                $openPunch->note = $req->note;
            } else {
                $openPunch->note .= " | ";
                $openPunch->note .= $req->note;
            }
            $openPunch->image_out = $req->image;
            $openPunch->out_ip = NetworkUtils::getClientIp();
            $openPunch->work_from_home = !empty($req->work_from_home) ? 1 : 0;
            $this->baseService->audit(IceConstants::AUDIT_ACTION, "Punch Out \ time:".$openPunch->out_time);
        } else {
            $openPunch->in_time = $dateTime;
            $openPunch->note = $req->note;
            $openPunch->image_in = $req->image;
            $openPunch->employee = $employee->id;
            $openPunch->in_ip = NetworkUtils::getClientIp();
            $openPunch->work_from_home = !empty($req->work_from_home) ? 1 : 0;
            $this->baseService->audit(IceConstants::AUDIT_ACTION, "Punch In \ time:".$openPunch->in_time);
        }
        $ok = $openPunch->Save();
        if (!$ok) {
            LogManager::getInstance()->error('Attendance Error:'.$openPunch->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, "Error occurred while saving attendance");
        }
        return new IceResponse(IceResponse::SUCCESS, $openPunch);
    }

	private function ensureAttendanceTimeIsValid($time, $type = 'in') {
		$settingPrefix = 'Attendance: Punch ' . ucfirst($type) . ' ';
		$startTime = SettingsManager::getInstance()->getSetting($settingPrefix . 'Start Time');
		$endTime = SettingsManager::getInstance()->getSetting($settingPrefix . 'End Time');
		
		// Convert time strings to DateTime objects for comparison, ignoring seconds
		$punchTime = \DateTime::createFromFormat('H:i', substr($time, 0, 5));
		$startDateTime = \DateTime::createFromFormat('H:i', $startTime);
		$endDateTime = \DateTime::createFromFormat('H:i', $endTime);
		
		if ($punchTime >= $startDateTime && $punchTime <= $endDateTime) {
			return new IceResponse(IceResponse::SUCCESS);
		}

		if ('in' === $type) {
			return new IceResponse(IceResponse::ERROR, "You are only allowed to punch-in between $startTime and $endTime. Current time is $time");
		}

		return new IceResponse(IceResponse::ERROR, "You are only allowed to punch-out between $startTime and $endTime. Current time is $time");
	}

    private function ensureAttendancePunchInTimeIsValid($time) {
        return $this->ensureAttendanceTimeIsValid($time);
    }

    private function ensureAttendancePunchOutTimeIsValid($time) {
        return $this->ensureAttendanceTimeIsValid($time, 'out');
    }

    public function createPreviousAttendnaceSheet($req)
    {
        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);

        $timeSheet = new EmployeeAttendanceSheet();
        $timeSheet->Load("id = ?", array($req->id));
        if ($timeSheet->id != $req->id) {
            return new IceResponse(IceResponse::ERROR, "Attendance Sheet not found");
        }

        if ($timeSheet->employee != $employee->id) {
            return new IceResponse(IceResponse::ERROR, "You don't have permissions to add this Attendance Sheet");
        }

        $end = date("Y-m-d", strtotime("last Saturday", strtotime($timeSheet->date_start)));
        $start = date("Y-m-d", strtotime("last Sunday", strtotime($end)));

        $tempTimeSheet = new EmployeeTimeSheet();
        $tempTimeSheet->Load("employee = ? and date_start = ?", array($employee->id, $start));
        if ($employee->id == $tempTimeSheet->employee) {
            return new IceResponse(IceResponse::ERROR, "Attendance Sheet already exists");
        }

        $newTimeSheet = new EmployeeTimeSheet();
        $newTimeSheet->employee = $employee->id;
        $newTimeSheet->date_start = $start;
        $newTimeSheet->date_end = $end;
        $newTimeSheet->status = "Pending";
        $ok = $newTimeSheet->Save();
        if (!$ok) {
            LogManager::getInstance()->info("Error creating time sheet : ".$newTimeSheet->ErrorMsg());
            return new IceResponse(IceResponse::ERROR, "Error creating Attendance Sheet");
        }

        return new IceResponse(IceResponse::SUCCESS, "");
    }
}
