<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:16 PM
 */

namespace Payroll\Common\Model;

use Model\BaseModel;

class PayrollData extends BaseModel
{
    public $table = 'PayrollData';

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
        return array();
    }
}
