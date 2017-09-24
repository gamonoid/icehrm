<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:15 PM
 */

namespace Payroll\Common\Model;

use Model\BaseModel;

class Deduction extends BaseModel
{
    public $table = 'Deductions';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }
}
