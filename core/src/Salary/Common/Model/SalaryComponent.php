<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 11:07 PM
 */

namespace Salary\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class SalaryComponent extends BaseModel
{
    public $table = 'SalaryComponent';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('salary', 'admin'),
        ];
    }
}
