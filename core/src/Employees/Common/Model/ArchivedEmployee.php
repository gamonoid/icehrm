<?php
namespace Employees\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class ArchivedEmployee extends BaseModel
{

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","add","save");
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
        return array("element","add","save");
    }

    public function getUserOnlyMeAccessField()
    {
        return "id";
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
        ];
    }

    public $table = 'ArchivedEmployees';
}
