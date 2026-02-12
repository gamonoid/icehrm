<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 7:44 AM
 */

namespace Training\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class TrainingSession extends BaseModel
{
    public $table = 'TrainingSessions';

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
        return array("get", "element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('training', 'admin'),
            new ModuleAccess('training', 'user'),
        ];
    }
}
