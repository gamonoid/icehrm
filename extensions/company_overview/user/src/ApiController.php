<?php
namespace Company_overviewUser;

use Attendance\Common\Model\Attendance;
use Classes\BaseService;
use Classes\FileService;
use Classes\IceApiController;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Company\Common\Model\CompanyStructure;
use Employees\Common\Model\Employee;
use Leaves\Common\Model\EmployeeLeave;

class ApiController extends IceApiController
{
    public function registerEndPoints() {
        // Get upcoming approved leaves
        self::register(
            REST_API_PATH . 'company_overview/leaves/upcoming', self::GET, function ($pathParams = null) {
                $this->getUpcomingLeaves();
            }
        );

        // Get company structure for OrganizationChart
        self::register(
            REST_API_PATH . 'company_overview/company-structure', self::GET, function ($pathParams = null) {
                $this->getCompanyStructure();
            }
        );

        // Get manager hierarchy for OrganizationChart
        self::register(
            REST_API_PATH . 'company_overview/manager-hierarchy', self::GET, function ($pathParams = null) {
                $this->getManagerHierarchy();
            }
        );

        // Get top employees by attendance time last week
        self::register(
            REST_API_PATH . 'company_overview/top-attendance', self::GET, function ($pathParams = null) {
                $this->getTopAttendance();
            }
        );
    }

    private function getUpcomingLeaves() {
        $restEndpoint = new RestEndPoint();
        $today = date('Y-m-d');

        $leave = new EmployeeLeave();
        $leaves = $leave->Find(
            "status = 'Approved' AND date_start >= ? ORDER BY date_start ASC LIMIT 10",
            [$today]
        );

        $result = [];
        $fs = FileService::getInstance();

        foreach ($leaves as $leaveItem) {
            $employee = new Employee();
            $employee->Load('id = ?', [$leaveItem->employee]);
            $employee = $fs->updateSmallProfileImage($employee);

            $result[] = [
                'id' => $leaveItem->id,
                'employee_id' => $leaveItem->employee,
                'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                'employee_image' => $employee->image,
                'date_start' => $leaveItem->date_start,
                'date_end' => $leaveItem->date_end,
                'leave_type' => $leaveItem->leave_type,
                'details' => $leaveItem->details,
            ];
        }

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $result));
    }

    private function getCompanyStructure() {
        $restEndpoint = new RestEndPoint();

        $structure = new CompanyStructure();
        $structures = $structure->Find("1=1 ORDER BY id ASC", []);

        // Build tree structure for OrganizationChart
        $nodes = [];
        $nodeMap = [];

        foreach ($structures as $dept) {
            $node = [
                'id' => (string)$dept->id,
                'name' => $dept->title,
                'title' => $dept->description ?? '',
            ];
            $nodeMap[$dept->id] = $node;
            $nodes[] = [
                'data' => $node,
                'parent' => $dept->parent ? (string)$dept->parent : null,
            ];
        }

        // Build hierarchical structure
        $tree = $this->buildTree($nodes);

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $tree));
    }

    private function getManagerHierarchy() {
        $restEndpoint = new RestEndPoint();
        $fs = FileService::getInstance();

        $employee = new Employee();
        $employees = $employee->Find("status = 'Active' ORDER BY id ASC", []);

        $nodes = [];

        foreach ($employees as $emp) {
            $emp = $fs->updateSmallProfileImage($emp);

            $node = [
                'id' => (string)$emp->id,
                'name' => $emp->first_name . ' ' . $emp->last_name,
                'title' => '', // Will be populated with job title if needed
                'image' => $emp->image,
            ];

            $nodes[] = [
                'data' => $node,
                'parent' => $emp->supervisor ? (string)$emp->supervisor : null,
            ];
        }

        // Build hierarchical structure
        $tree = $this->buildTree($nodes);

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, $tree));
    }

    private function getTopAttendance() {
        $restEndpoint = new RestEndPoint();
        $fs = FileService::getInstance();

        // Calculate last week's date range
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-7 days'));

        $attendance = new Attendance();

        // Get top 10 employees by total hours worked last week
        $sql = "SELECT employee,
                       SUM(TIMESTAMPDIFF(SECOND, in_time, out_time)) / 3600 as total_hours,
                       COUNT(*) as attendance_count
                FROM Attendance
                WHERE DATE(in_time) >= ?
                  AND DATE(in_time) <= ?
                  AND out_time IS NOT NULL
                GROUP BY employee
                ORDER BY total_hours DESC
                LIMIT 10";

        $results = $attendance->Execute($sql, [$startDate, $endDate]);

        $topEmployees = [];

        if ($results && is_array($results)) {
            foreach ($results as $row) {
                $employee = new Employee();
                $employee->Load('id = ?', [$row['employee']]);
                $employee = $fs->updateSmallProfileImage($employee);

                $topEmployees[] = [
                    'employee_id' => $row['employee'],
                    'employee_name' => $employee->first_name . ' ' . $employee->last_name,
                    'employee_image' => $employee->image,
                    'department' => $employee->department,
                    'total_hours' => round((float)$row['total_hours'], 1),
                    'attendance_count' => (int)$row['attendance_count'],
                ];
            }
        }

        $restEndpoint->sendResponse(new IceResponse(IceResponse::SUCCESS, [
            'employees' => $topEmployees,
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
        ]));
    }

    private function buildTree($nodes) {
        // Create a map of id to node with children array
        $map = [];
        foreach ($nodes as $node) {
            $id = $node['data']['id'];
            $map[$id] = array_merge($node['data'], ['children' => []]);
        }

        // Build tree by assigning children to parents
        $roots = [];
        foreach ($nodes as $node) {
            $id = $node['data']['id'];
            $parentId = $node['parent'];

            if ($parentId && isset($map[$parentId])) {
                $map[$parentId]['children'][] = &$map[$id];
            } else {
                $roots[] = &$map[$id];
            }
        }

        // Return first root if single root, otherwise wrap in container
        if (count($roots) === 1) {
            return $roots[0];
        } elseif (count($roots) > 1) {
            return [
                'id' => 'root',
                'name' => 'Organization',
                'title' => '',
                'children' => $roots,
            ];
        }

        return null;
    }
}
