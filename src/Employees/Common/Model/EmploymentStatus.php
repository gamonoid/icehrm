<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 10:40 AM
 */

namespace Employees\Common\Model;

use Model\BaseModel;

class EmploymentStatus extends BaseModel
{

    public $table = 'EmploymentStatus';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save");
    }

    public function getUserAccess()
    {
        return array();
    }
}
