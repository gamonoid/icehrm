<?php
namespace Projects\Admin\Api;

use Classes\FileService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Employees\Common\Model\Employee;
use Projects\Common\Model\EmployeeProject;
use Projects\Common\Model\Project;
use TimeSheets\Common\Model\EmployeeTimeEntry;
use TimeSheets\Common\Model\EmployeeTimeSheet;

class ProjectsApiController extends IceApiController
{
    public function registerEndPoints()
    {
        // Get project time statistics
        self::register(
            REST_API_PATH . 'projects/(:num)/time-stats',
            self::GET,
            function ($id) {
                $this->getProjectTimeStats($id);
            }
        );

        // Add employee to project
        self::register(
            REST_API_PATH . 'projects/(:num)/employees',
            self::POST,
            function ($id) {
                $this->addEmployeeToProject($id);
            }
        );

        // Remove employee from project
        self::register(
            REST_API_PATH . 'projects/(:num)/employees/(:num)',
            self::DELETE,
            function ($projectId, $employeeId) {
                $this->removeEmployeeFromProject($projectId, $employeeId);
            }
        );

        // Get available employees (not assigned to project)
        self::register(
            REST_API_PATH . 'projects/(:num)/available-employees',
            self::GET,
            function ($id) {
                $this->getAvailableEmployees($id);
            }
        );
    }

    private function getProjectTimeStats($projectId)
    {
        $restEndpoint = new RestEndPoint();

        // Load the project
        $project = new Project();
        $project->Load("id = ?", [$projectId]);

        if (empty($project->id)) {
            $restEndpoint->sendResponse(new IceResponse(
                IceResponse::ERROR,
                'Project not found'
            ));
            return;
        }

        // Get all time entries for this project
        $timeEntry = new EmployeeTimeEntry();
        $entries = $timeEntry->Find("project = ?", [$projectId]);

        // Build employee time data (approved vs pending)
        $employeeTimeData = [];
        $monthlyTimeData = [];
        $employeeCache = [];

        foreach ($entries as $entry) {
            // Get the timesheet to check status
            $timeSheet = new EmployeeTimeSheet();
            $timeSheet->Load("id = ?", [$entry->timesheet]);

            if (empty($timeSheet->id)) {
                continue;
            }

            // Calculate duration in hours
            $startTime = strtotime($entry->date_start);
            $endTime = strtotime($entry->date_end);
            $durationSeconds = $endTime - $startTime;
            if ($durationSeconds < 0) {
                $durationSeconds = 0;
            }
            $durationHours = round($durationSeconds / 3600, 2);

            // Get employee name
            $employeeId = $entry->employee;
            if (!isset($employeeCache[$employeeId])) {
                $emp = new Employee();
                $emp->Load("id = ?", [$employeeId]);
                $employeeCache[$employeeId] = $emp->first_name . ' ' . $emp->last_name;
            }
            $employeeName = $employeeCache[$employeeId];

            // Initialize employee data if not exists
            if (!isset($employeeTimeData[$employeeId])) {
                $employeeTimeData[$employeeId] = [
                    'employee_id' => $employeeId,
                    'employee_name' => $employeeName,
                    'approved_hours' => 0,
                    'pending_hours' => 0,
                ];
            }

            // Add time based on status
            if ($timeSheet->status === 'Approved') {
                $employeeTimeData[$employeeId]['approved_hours'] += $durationHours;
            } elseif ($timeSheet->status === 'Pending' || $timeSheet->status === 'Submitted') {
                $employeeTimeData[$employeeId]['pending_hours'] += $durationHours;
            }

            // Monthly breakdown
            $month = date('Y-m', strtotime($entry->date_start));
            $monthLabel = date('M Y', strtotime($entry->date_start));

            if (!isset($monthlyTimeData[$month])) {
                $monthlyTimeData[$month] = [
                    'month' => $month,
                    'month_label' => $monthLabel,
                    'approved_hours' => 0,
                    'pending_hours' => 0,
                    'total_hours' => 0,
                ];
            }

            if ($timeSheet->status === 'Approved') {
                $monthlyTimeData[$month]['approved_hours'] += $durationHours;
            } elseif ($timeSheet->status === 'Pending' || $timeSheet->status === 'Submitted') {
                $monthlyTimeData[$month]['pending_hours'] += $durationHours;
            }
            $monthlyTimeData[$month]['total_hours'] += $durationHours;
        }

        // Sort monthly data by month
        ksort($monthlyTimeData);

        // Calculate totals
        $totalApproved = 0;
        $totalPending = 0;
        foreach ($employeeTimeData as $data) {
            $totalApproved += $data['approved_hours'];
            $totalPending += $data['pending_hours'];
        }

        // Get assigned employees from EmployeeProjects table
        $employeeProject = new EmployeeProject();
        $assignedEmployees = $employeeProject->Find("project = ?", [$projectId]);
        $assignedEmployeesList = [];

        foreach ($assignedEmployees as $assignment) {
            $emp = new Employee();
            $emp->Load("id = ?", [$assignment->employee]);
			$emp = FileService::getInstance()->updateSmallProfileImage($emp);
            if (!empty($emp->id)) {
                $assignedEmployeesList[] = [
                    'id' => $emp->id,
                    'employee_id' => $emp->employee_id,
                    'first_name' => $emp->first_name,
                    'last_name' => $emp->last_name,
                    'image' => $emp->image,
                    'status' => $assignment->status,
                    'date_start' => $assignment->date_start,
                    'date_end' => $assignment->date_end,
                ];
            }
        }

        $result = [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'status' => $project->status,
            ],
            'summary' => [
                'total_approved_hours' => round($totalApproved, 2),
                'total_pending_hours' => round($totalPending, 2),
                'total_hours' => round($totalApproved + $totalPending, 2),
                'employee_count' => count($employeeTimeData),
            ],
            'employee_breakdown' => array_values($employeeTimeData),
            'monthly_breakdown' => array_values($monthlyTimeData),
            'assigned_employees' => $assignedEmployeesList,
        ];

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $result));
    }

    private function addEmployeeToProject($projectId)
    {
        $restEndpoint = new RestEndPoint();
        $body = $restEndpoint->getRequestBody();

        if (empty($body['employee_id'])) {
            $restEndpoint->sendResponse(new IceResponse(
                IceResponse::ERROR,
                'Employee ID is required'
            ));
            return;
        }

        // Check if project exists
        $project = new Project();
        $project->Load("id = ?", [$projectId]);
        if (empty($project->id)) {
            $restEndpoint->sendResponse(new IceResponse(
                IceResponse::ERROR,
                'Project not found'
            ));
            return;
        }

        // Check if employee exists
        $employee = new Employee();
        $employee->Load("id = ?", [$body['employee_id']]);
        if (empty($employee->id)) {
            $restEndpoint->sendResponse(new IceResponse(
                IceResponse::ERROR,
                'Employee not found'
            ));
            return;
        }

        // Check if already assigned
        $existing = new EmployeeProject();
        $existing->Load("employee = ? AND project = ?", [$body['employee_id'], $projectId]);
        if (!empty($existing->id)) {
            $restEndpoint->sendResponse(new IceResponse(
                IceResponse::ERROR,
                'Employee is already assigned to this project'
            ));
            return;
        }

        // Create assignment
        $assignment = new EmployeeProject();
        $assignment->employee = $body['employee_id'];
        $assignment->project = $projectId;
        $assignment->status = 'Current';
        $assignment->date_start = date('Y-m-d');
        $assignment->Save();

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, [
            'id' => $assignment->id,
            'employee_id' => $employee->employee_id,
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'image' => $employee->image,
            'status' => $assignment->status,
        ]));
    }

    private function removeEmployeeFromProject($projectId, $employeeId)
    {
        $restEndpoint = new RestEndPoint();

        // Find the assignment
        $assignment = new EmployeeProject();
        $assignment->Load("employee = ? AND project = ?", [$employeeId, $projectId]);

        if (empty($assignment->id)) {
            $restEndpoint->sendResponse(new IceResponse(
                IceResponse::ERROR,
                'Employee is not assigned to this project'
            ));
            return;
        }

        // Delete the assignment
        $assignment->Delete();

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, 'Employee removed from project'));
    }

    private function getAvailableEmployees($projectId)
    {
        $restEndpoint = new RestEndPoint();

        // Get all active employees
        $employee = new Employee();
        $allEmployees = $employee->Find("status = ?", ['Active']);

        // Get already assigned employee IDs
        $employeeProject = new EmployeeProject();
        $assigned = $employeeProject->Find("project = ?", [$projectId]);
        $assignedIds = array_map(function ($a) {
            return $a->employee;
        }, $assigned);

        // Filter out assigned employees
        $availableEmployees = [];
        foreach ($allEmployees as $emp) {
            if (!in_array($emp->id, $assignedIds)) {
                $availableEmployees[] = [
                    'id' => $emp->id,
                    'employee_id' => $emp->employee_id,
                    'first_name' => $emp->first_name,
                    'last_name' => $emp->last_name,
                    'image' => $emp->image,
                ];
            }
        }

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $availableEmployees));
    }
}
