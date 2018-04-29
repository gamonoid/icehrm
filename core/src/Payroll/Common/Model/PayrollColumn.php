<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:14 PM
 */

namespace Payroll\Common\Model;

use Model\BaseModel;

class PayrollColumn extends BaseModel
{
    public $table = 'PayrollColumns';
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
        return array("get","element");
    }
}
