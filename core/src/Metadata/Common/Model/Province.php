<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:02 PM
 */

namespace Metadata\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class Province extends BaseModel
{
    public $table = 'Province';

    public function getAdminAccess()
    {
        return array("get", "element", "save", "delete");
    }

    public function getAnonymousAccess()
    {
        return array("get", "element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('metadata', 'admin'),
        ];
    }
}
