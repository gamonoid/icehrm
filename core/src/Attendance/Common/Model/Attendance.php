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

    /**
     * Build the list filter query. Handles the employee filter (exact match,
     * preserving the previous default behaviour) and a "date" filter that
     * matches the whole calendar day against the `in_time` datetime column.
     *
     * @param  \stdClass|string $filter decoded filter object (see
     *                                  BaseService::getData / core/data.php)
     * @return array [whereClause, bindValues]
     */
    public function getCustomFilterQuery($filter)
    {
        // The framework hands us a decoded stdClass; normalise to an array
        // (and tolerate a raw JSON string just in case).
        if (is_string($filter)) {
            $filter = json_decode($filter, true);
        } else {
            $filter = json_decode(json_encode($filter), true);
        }
        if (empty($filter) || !is_array($filter)) {
            return array('', array());
        }

        $query = '';
        $queryData = array();

        if (isset($filter['employee']) && $filter['employee'] !== '' && $filter['employee'] !== 'NULL') {
            if (is_array($filter['employee'])) {
                $placeholders = implode(',', array_fill(0, count($filter['employee']), '?'));
                $query .= ' and employee in (' . $placeholders . ')';
                foreach ($filter['employee'] as $emp) {
                    $queryData[] = $emp;
                }
            } else {
                $query .= ' and employee = ?';
                $queryData[] = $filter['employee'];
            }
        }

        // in_time is a datetime, so filter across the whole day (00:00:00–23:59:59)
        // rather than an exact-equality match that could never hit.
        if (isset($filter['date']) && $filter['date'] !== '' && $filter['date'] !== 'NULL') {
            $query .= ' and in_time >= ? and in_time <= ?';
            $queryData[] = $filter['date'] . ' 00:00:00';
            $queryData[] = $filter['date'] . ' 23:59:59';
        }

        return array($query, $queryData);
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
