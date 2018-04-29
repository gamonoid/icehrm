<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:17 PM
 */

namespace Payroll\Common\Model;

use Model\BaseModel;

class PayslipTemplate extends BaseModel
{
    public $table = 'PayslipTemplates';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }
}
