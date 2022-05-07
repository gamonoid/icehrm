<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/13/17
 * Time: 8:04 AM
 */

namespace Attendance\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class Attendance extends BaseModel
{
    public $table = 'Attendance';

    public function getAdminAccess()
    {
        return array('get','element','save','delete');
    }

    public function getManagerAccess()
    {
        return array('get','element','save','delete');
    }

    public function getUserAccess()
    {
        return array('get');
    }

    public function getUserOnlyMeAccess()
    {
        return array('element','save','delete');
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('attendance', 'admin'),
            new ModuleAccess('attendance', 'user'),
            new ModuleAccess('attendance_sheets', 'user'),
        ];
    }

    public function postProcessGetData($obj)
    {
        if (empty($obj->out_time)) {
            $obj->hours = 0;
            return $obj;
        }

        $seconds = strtotime($obj->out_time) - strtotime($obj->in_time);
        $hours = round(($seconds / (60 * 60)), 2);
        $obj->hours = $hours;

        return $obj;
    }
}
