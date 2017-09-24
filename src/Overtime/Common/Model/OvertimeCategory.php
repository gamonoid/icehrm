<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:13 PM
 */

namespace Overtime\Common\Model;

use Model\BaseModel;

class OvertimeCategory extends BaseModel
{
    public $table = 'OvertimeCategories';

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
