<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 5:58 PM
 */

namespace Projects\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

class EmployeeProject extends BaseModel
{
    public $table = 'EmployeeProjects';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","add","save","delete");
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

    public function executePreSaveActions($obj)
    {
        if (empty($obj->status)) {
            $obj->status = "Current";
        }
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function executePreUpdateActions($obj)
    {
        if (empty($obj->status)) {
            $obj->status = "Current";
        }
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('projects', 'admin'),
            new ModuleAccess('projects', 'user'),
        ];
    }

    public function isCustomFieldsEnabled()
    {
        return true;
    }
}
