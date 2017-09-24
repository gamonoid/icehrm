<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:56 PM
 */

namespace Loans\Common\Model;

use Model\BaseModel;

class EmployeeCompanyLoan extends BaseModel
{
    public $table = 'EmployeeCompanyLoans';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("get","element");
    }
}
