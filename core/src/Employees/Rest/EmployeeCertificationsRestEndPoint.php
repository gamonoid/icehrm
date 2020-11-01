<?php

namespace Employees\Rest;

use Classes\Data\Query\DataQuery;
use Classes\Data\Query\Filter;
use Classes\RestEndPoint;
use Qualifications\Common\Model\EmployeeCertification;
use Qualifications\Common\Model\EmployeeEducation;
use Users\Common\Model\User;

class EmployeeCertificationsRestEndpoint extends RestEndPoint
{
    public function getModelObject($id)
    {
        $obj = new EmployeeCertification();
        $obj->Load("id = ?", array($id));
        return $obj;
    }

    public function listAll(User $user, $parameter = null)
    {
        $query = new DataQuery('EmployeeCertification');
        $query->addFilter(new Filter('employee', $parameter));
        $mapping = <<<JSON
{
  "certification_id": [ "Certification", "id", "name" ],
  "employee": [ "Employee", "id", "first_name+last_name" ]
}
JSON;
        $query->setFieldMapping($mapping);

        $limit = self::DEFAULT_LIMIT;
        if (isset($_GET['limit']) && intval($_GET['limit']) > 0) {
            $limit = intval($_GET['limit']);
        }
        $query->setLength($limit);

        if (!empty($_GET['filters'])) {
            $query->setFilters($_GET['filters']);
        }

        if (isset($_GET['sortField']) && !empty($_GET['sortField'])) {
            $query->setSortColumn($_GET['sortField']);
            $query->setSortingEnabled(true);
            $query->setSortOrder(
                empty($_GET['sortOrder']) || $_GET['sortOrder'] === 'ascend' ? 'ASC' : 'DESC'
            );
        }

        return $this->listByQuery($query);
    }
}
