<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/13/17
 * Time: 8:04 AM
 */

namespace Attendance\Common\Model;

use Classes\FileService;
use Classes\ModuleAccess;
use Employees\Common\Model\Employee;
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
        } else {
            $seconds = strtotime($obj->out_time) - strtotime($obj->in_time);
            $hours = round(($seconds / (60 * 60)), 2);
            $obj->hours = $hours;
        }
        if ($obj->map_lat && $obj->map_lng) {
            $obj->map_link_in = $this->getGoogleMapImage($obj->map_lat, $obj->map_lng);
        }

        if ($obj->map_out_lat && $obj->map_out_lng) {
            $obj->map_link_out = $this->getGoogleMapImage($obj->map_out_lat, $obj->map_out_lng);
        }
        $obj->has_map_snapshot = !empty($obj->map_snapshot) || !empty($obj->map_out_snapshot);
        $obj->map_snapshot = null;
        $obj->map_out_snapshot = null;

        $employee = new Employee();
        $employee->Load('id = ?', [$obj->employee]);
        $employee = FileService::getInstance()->updateSmallProfileImage($employee);
        $obj->image = $employee->image;

        return $obj;
    }

    public function postProcessGetElement($obj)
    {
        $in_snap = $obj->map_snapshot;
        $out_snap = $obj->map_out_snapshot;
        $obj = $this->postProcessGetData($obj);
        $obj->map_snapshot = $in_snap;
        $obj->map_out_snapshot = $out_snap;
        $employee = new Employee();
        $employee->Load('id = ?', [$obj->employee]);
        $obj->employee_Name = $employee->first_name . ' ' . $employee->last_name;
        $obj->first_name = $employee->first_name;
        $obj->last_name = $employee->last_name;
        $employee = FileService::getInstance()->updateSmallProfileImage($employee);
        $obj->image = $employee->image;

        return $obj;
    }

    public function getGoogleMapImage($latitude, $longitude) {
        return sprintf('https://maps.google.com?q=%s,%s', $latitude, $longitude);
    }
}
