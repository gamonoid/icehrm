<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 10:40 AM
 */

namespace Employees\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class EmployeeStatus extends BaseModel
{

    public $table = 'EmployeeStatus';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    /**

     * No module grants Manager access to this model (module meta.json user_levels),

     * so no manager-facing screen reads it. The inherited BaseModel default

     * would expose the whole table on the generic service.php path.

     */

    public function getManagerAccess()

    {

        return array();

    }

    public function getUserAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
        ];
    }
}
