<?php
namespace StaffDirectory\Rest;

use Classes\RestEndPoint;
use Users\Common\Model\User;
use Classes\Data\Query\DataQuery;

class StaffDirectoryRestEndPoint extends RestEndPoint
{
    public function listAll(User $user)
    {
        $query = new DataQuery('StaffDirectory');
        $query->setFieldMapping(
            '{"job_title":["JobTitle","id","name"],"department":["CompanyStructure","id","title"]}'
        );
        $query->setOrderBy('first_name');
        return $this->listByQuery($query);
    }
}
