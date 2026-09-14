<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 8:04 AM
 */

namespace Travel\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class EmployeeImmigration extends BaseModel
{
    public $table = 'EmployeeImmigrations';

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

    /**

     * No module grants Employee access to this model (module meta.json user_levels),

     * so no employee-facing screen reads it. The inherited BaseModel default

     * would expose the whole table on the generic service.php path.

     */

    public function getUserAccess()

    {

        return array();

    }

    public function getUserOnlyMeAccess()
    {
        return array("element","add","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
        ];
    }

    /**
     * A team list exists for this model: the adapter opts into `type=sub`
     * (isSubProfileTable), so BaseService::getData() may scope its rows to the
     * caller's direct reports. See BaseModel::allowsSubordinateList().
     */
    public function allowsSubordinateList()
    {
        return true;
    }

}
