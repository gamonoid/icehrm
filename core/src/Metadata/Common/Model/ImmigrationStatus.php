<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:06 PM
 */

namespace Metadata\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class ImmigrationStatus extends BaseModel
{
    public $table = 'ImmigrationStatus';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function getAnonymousAccess()
    {
        return array("get","element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('metadata', 'admin'),
        ];
    }
}
