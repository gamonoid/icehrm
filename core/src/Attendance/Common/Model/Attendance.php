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
        return array('get','element','add','save','delete');
    }

    public function getManagerAccess()
    {
        return array('get','element','add','save','delete');
    }

    public function getUserAccess()
    {
        return array('get');
    }

    public function getUserOnlyMeAccess()
    {
        return array('element','add','save','delete');
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
     * Evidence fields an employee must not set by hand. getUserOnlyMeAccess() grants
     * add/save on their own rows, and the generic save path copies every column — so
     * without this an employee could POST a=add&t=Attendance with a chosen in_ip and
     * map_lat/map_lng, forging the IP and GPS evidence the admin "View Map" screen shows.
     * These are captured server-side by the punch flow (savePunch), never client-authored.
     * Admin (and an all-employee-data role) keep manual correction.
     */
    public function getProtectedFields($user)
    {
        if (!empty($user)
            && (in_array($user->user_level, array('Admin', 'Restricted Admin'), true)
                || \Employees\Common\Model\EmployeeAccess::hasAccessToAllEmployeeData())
        ) {
            return array();
        }

        return array(
            'in_ip', 'out_ip',
            'map_lat', 'map_lng', 'map_snapshot',
            'map_out_lat', 'map_out_lng', 'map_out_snapshot',
        );
    }

    /**
     * Punch invariants, enforced for every write path. The business rules
     * (single calendar day, in < out) previously lived only in
     * AttendanceActionManager::savePunch, so the generic a=add / a=save route bypassed
     * them entirely and could store a backdated 24-hour entry. validateSave() runs
     * inside BaseService::addElement for both add and update, so the rules now hold
     * wherever the row is written.
     */
    public function validateSave($obj)
    {
        $inTime = !empty($obj->in_time) ? strtotime($obj->in_time) : false;
        $outTime = !empty($obj->out_time) ? strtotime($obj->out_time) : false;

        if (!empty($obj->in_time) && $inTime === false) {
            return new \Classes\IceResponse(\Classes\IceResponse::ERROR, 'Invalid punch-in time');
        }
        if (!empty($obj->out_time) && $outTime === false) {
            return new \Classes\IceResponse(\Classes\IceResponse::ERROR, 'Invalid punch-out time');
        }

        if ($inTime !== false && $outTime !== false) {
            if ($outTime <= $inTime) {
                return new \Classes\IceResponse(
                    \Classes\IceResponse::ERROR,
                    'Punch-in time should be less than punch-out time'
                );
            }
            if (date('Y-m-d', $inTime) !== date('Y-m-d', $outTime)) {
                return new \Classes\IceResponse(
                    \Classes\IceResponse::ERROR,
                    'Attendance entry should be within a single day'
                );
            }
        }

        // Overlap check, mirroring savePunch. Without it an employee could stack
        // overlapping rows on the same day through the generic save path and inflate
        // their recorded hours. Admin levels are exempt so HR corrections and data
        // imports are not blocked.
        $user = \Classes\BaseService::getInstance()->getCurrentUser();
        $isAdmin = !empty($user)
            && (in_array($user->user_level, array('Admin', 'Restricted Admin'), true)
                || \Employees\Common\Model\EmployeeAccess::hasAccessToAllEmployeeData());

        if (!$isAdmin && $inTime !== false && !empty($obj->employee)) {
            $existing = new Attendance();
            $sameDay = $existing->Find(
                "employee = ? and DATE_FORMAT(in_time, '%Y-%m-%d') = ?",
                array($obj->employee, date('Y-m-d', $inTime))
            );
            foreach ($sameDay as $row) {
                if (!empty($obj->id) && (string) $row->id === (string) $obj->id) {
                    continue; // editing this very row
                }
                $rowIn = strtotime($row->in_time);
                $rowOut = !empty($row->out_time) ? strtotime($row->out_time) : null;
                if ($rowOut === null) {
                    return new \Classes\IceResponse(
                        \Classes\IceResponse::ERROR,
                        'There is a non closed attendance entry for this day.'
                        . ' Please close it before adding a new one'
                    );
                }
                $newOut = ($outTime !== false) ? $outTime : $inTime;
                if ($inTime < $rowOut && $newOut > $rowIn) {
                    return new \Classes\IceResponse(
                        \Classes\IceResponse::ERROR,
                        'Time entry is overlapping with an existing one'
                    );
                }
            }
        }

        return parent::validateSave($obj);
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
