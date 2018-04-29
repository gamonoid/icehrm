<?php
namespace Dashboard\User\Api;

use Attendance\Common\Model\Attendance;
use Candidates\Common\Model\Candidate;
use Classes\IceResponse;
use Classes\SubActionManager;
use Employees\Common\Model\Employee;
use JobPositions\Common\Model\Job;
use Leaves\Common\Model\EmployeeLeave;
use Leaves\User\Api\LeavesActionManager;
use Projects\Common\Model\EmployeeProject;
use TimeSheets\Common\Model\EmployeeTimeEntry;
use TimeSheets\Common\Model\EmployeeTimeSheet;
use Training\Common\Model\Course;

class DashboardActionManager extends SubActionManager
{

    public function getPendingLeaves($req)
    {

        $lam = new LeavesActionManager();
        $leavePeriod = $lam->getCurrentLeavePeriod(date("Y-m-d H:i:s"), date("Y-m-d H:i:s"));

        $leave = new EmployeeLeave();
        $pendingLeaves = $leave->Find("status = ? and employee = ?", array("Pending", $this->getCurrentProfileId()));

        return new IceResponse(IceResponse::SUCCESS, count($pendingLeaves));
    }

    public function getInitData($req)
    {
        $data = array();

        $emp = new Employee();
        $data['numberOfEmployees'] = $emp->Count(
            "status = 'Active' and supervisor = ?",
            array($this->getCurrentProfileId())
        );

        $data['lastTimeSheetHours'] = $this->getLastTimeSheetHours($req)->getData();
        $data['activeProjects'] = $this->getEmployeeActiveProjects($req)->getData();
        $data['pendingLeaves'] = $this->getPendingLeaves($req)->getData();
        $candidate = new Candidate();
        $data['numberOfCandidates'] = $candidate->Count("1 = 1");

        $job = new Job();
        $data['numberOfJobs'] = $job->Count("status = 'Active'");

        $attendance = new Attendance();
        $data['numberOfAttendanceLastWeek'] = $attendance->Count(
            "in_time > '".date("Y-m-d H:i:s", strtotime("-1 week"))."'"
        );

        $course = new Course();
        $data['numberOfCourses'] = $course->Count("1 = 1");

        return new IceResponse(IceResponse::SUCCESS, $data);
    }

    public function getLastTimeSheetHours($req)
    {
        $timeSheet = new EmployeeTimeSheet();
        $timeSheet->Load("employee = ? order by date_end desc limit 1", array($this->getCurrentProfileId()));

        if (empty($timeSheet->employee)) {
            return new IceResponse(IceResponse::SUCCESS, "0:00");
        }

        $timeSheetEntry = new EmployeeTimeEntry();
        $list = $timeSheetEntry->Find("timesheet = ?", array($timeSheet->id));

        $seconds = 0;
        foreach ($list as $entry) {
            $seconds += (strtotime($entry->date_end) - strtotime($entry->date_start));
        }

        $minutes = (int)($seconds/60);
        $rem = $minutes % 60;
        $hours = ($minutes - $rem)/60;
        if ($rem < 10) {
            $rem = "0".$rem;
        }
        return new IceResponse(IceResponse::SUCCESS, $hours.":".$rem);
    }

    public function getEmployeeActiveProjects($req)
    {
        $project = new EmployeeProject();
        $projects = $project->Find("employee = ? and status =?", array($this->getCurrentProfileId(),'Current'));

        return new IceResponse(IceResponse::SUCCESS, count($projects));
    }
}
