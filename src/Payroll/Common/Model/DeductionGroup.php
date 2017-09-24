<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:16 PM
 */

namespace Payroll\Common\Model;

use Model\BaseModel;

class DeductionGroup extends BaseModel
{
    public $table = 'DeductionGroup';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }
}
