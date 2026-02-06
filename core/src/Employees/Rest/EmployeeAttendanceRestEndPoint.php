<?php

namespace Employees\Rest;

use Attendance\Common\Model\Attendance;
use Classes\Data\Query\DataQuery;
use Classes\Data\Query\Filter;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Users\Common\Model\User;

class EmployeeAttendanceRestEndPoint extends RestEndPoint
{
    public function getModelObject($id)
    {
        $obj = new Attendance();
        $obj->Load("id = ?", array($id));
        return $obj;
    }

    public function listAll(User $user, $parameter = null)
    {
        $query = new DataQuery('Attendance');
        $query->addFilter(new Filter('employee', $parameter));
        $mapping = <<<JSON
{
  "employee": [ "Employee", "id", "first_name+last_name" ]
}
JSON;
        $query->setFieldMapping($mapping);

        $limit = 10;
        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $limit = intval($_GET['limit']);
        }
        $query->setLength($limit);

        $query->setSortColumn('in_time');
        $query->setSortingEnabled(true);
        $query->setSortOrder('DESC');

        if (!empty($_GET['filters'])) {
            $query->setFilters($_GET['filters']);
        }

        if (isset($_GET['sortField']) && !empty($_GET['sortField'])) {
            $query->setSortColumn($_GET['sortField']);
            $query->setSortOrder(
                empty($_GET['sortOrder']) || $_GET['sortOrder'] === 'ascend' ? 'ASC' : 'DESC'
            );
        }

        return $this->listByQuery($query);
    }

    public function getSummary(User $user, $parameter = null)
    {
        $attendance = new Attendance();

        $total = $attendance->Count("employee = ?", [$parameter]);

        // Get total hours worked (approximate)
        $sql = "SELECT SUM(TIMESTAMPDIFF(SECOND, in_time, out_time)) / 3600 as total_hours
                FROM Attendance
                WHERE employee = ? AND out_time IS NOT NULL";
        $result = $attendance->Execute($sql, [$parameter]);
        $totalHours = 0;
        if ($result && is_array($result) && isset($result[0]['total_hours'])) {
            $totalHours = round($result[0]['total_hours'] ?? 0, 1);
        }

        // Get attendance this month
        $thisMonth = $attendance->Count(
            "employee = ? AND DATE_FORMAT(in_time, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')",
            [$parameter]
        );

        // Get average hours per day
        $avgHours = $total > 0 ? round($totalHours / $total, 1) : 0;

        return new IceResponse(IceResponse::SUCCESS, [
            'total' => $total,
            'totalHours' => $totalHours,
            'thisMonth' => $thisMonth,
            'avgHours' => $avgHours,
        ]);
    }
}
