<?php
namespace Dashboard\Admin\Api;

use Attendance\Common\Model\Attendance;
use Candidates\Common\Model\Candidate;
use Classes\IceResponse;
use Classes\SubActionManager;
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use JobPositions\Common\Model\Job;
use Leaves\Common\Model\EmployeeLeave;
use Projects\Common\Model\Project;
use TimeSheets\Common\Model\EmployeeTimeEntry;
use Training\Common\Model\Course;
use Users\Common\Model\User;

class DashboardActionManager extends SubActionManager
{

    public function getInitData($req)
    {
        $data = array();
        $employees = new Employee();
        $data['numberOfEmployees'] = $employees->Count("1 = 1");

        $company = new CompanyStructure();
        $data['numberOfCompanyStuctures'] = $company->Count("1 = 1");

        $user = new User();
        $data['numberOfUsers'] = $user->Count("1 = 1");

        $project = new Project();
        $data['numberOfProjects'] = $project->Count("status = 'Active'");

        $attendance = new Attendance();
        $data['numberOfAttendanceLastWeek'] = $attendance->Count(
            "in_time > '".date("Y-m-d H:i:s", strtotime("-1 week"))."'"
        );
        if (empty($data['numberOfAttendanceLastWeek'])) {
            $data['numberOfAttendanceLastWeek'] = 0;
        }

        $empLeave = new EmployeeLeave();
        $data['numberOfLeaves'] = $empLeave->Count("date_start > '".date("Y-m-d")."'");

        $timeEntry = new EmployeeTimeEntry();
        $data['numberOfAttendanceLastWeek'] = $timeEntry->Count(
            "in_time > '".date("Y-m-d H:i:s", strtotime("-1 week"))."'"
        );

        $candidate = new Candidate();
        $data['numberOfCandidates'] = $candidate->Count("1 = 1");

        $job = new Job();
        $data['numberOfJobs'] = $job->Count("status = 'Active'");

        $course = new Course();
        $data['numberOfCourses'] = $course->Count("1 = 1");

        return new IceResponse(IceResponse::SUCCESS, $data);
    }
}
