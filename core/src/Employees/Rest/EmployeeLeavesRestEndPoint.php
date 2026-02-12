<?php

namespace Employees\Rest;

use Classes\Data\Query\DataQuery;
use Classes\Data\Query\Filter;
use Classes\IceResponse;
use Classes\RestEndPoint;
use Leaves\Common\Model\EmployeeLeave;
use Users\Common\Model\User;

class EmployeeLeavesRestEndPoint extends RestEndPoint
{
    public function getModelObject($id)
    {
        $obj = new EmployeeLeave();
        $obj->Load("id = ?", array($id));
        return $obj;
    }

    public function listAll(User $user, $parameter = null)
    {
        $query = new DataQuery('EmployeeLeave');
        $query->addFilter(new Filter('employee', $parameter));
        $mapping = <<<JSON
{
  "leave_type": [ "LeaveType", "id", "name" ],
  "leave_period": [ "LeavePeriod", "id", "name" ],
  "employee": [ "Employee", "id", "first_name+last_name" ]
}
JSON;
        $query->setFieldMapping($mapping);

        $limit = 10;
        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $limit = intval($_GET['limit']);
        }
        $query->setLength($limit);

        $query->setSortColumn('date_start');
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
        $employeeLeave = new EmployeeLeave();

        $approved = $employeeLeave->Count("employee = ? AND status = 'Approved'", [$parameter]);
        $pending = $employeeLeave->Count("employee = ? AND status = 'Pending'", [$parameter]);
        $rejected = $employeeLeave->Count("employee = ? AND status = 'Rejected'", [$parameter]);
        $cancelled = $employeeLeave->Count("employee = ? AND status IN ('Cancelled', 'Cancellation Requested')", [$parameter]);
        $total = $employeeLeave->Count("employee = ?", [$parameter]);

        return new IceResponse(IceResponse::SUCCESS, [
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'cancelled' => $cancelled,
            'total' => $total,
        ]);
    }
}
