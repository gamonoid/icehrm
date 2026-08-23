<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:24 PM
 */

namespace Leaves\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class LeaveGroup extends BaseModel
{
    public $table = 'LeaveGroups';
    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    /**
     * Leave groups are configured on admin::leaves, whose meta.json declares
     * user_levels ["Admin"] — no other module surfaces this model, so a Manager or
     * an Employee has no screen that reads it and needs no grant here. The
     * inherited BaseModel defaults ("get","element") would hand both levels the
     * whole table through the generic path for no functional reason.
     *
     * The employee-facing leave flow does read group membership
     * (LeaveUtil::getEmployeeLeaveGroups), but through LeaveGroupEmployee with a
     * direct ActiveRecord Find() that never passes through checkSecureAccess, so
     * these empty matrices cannot affect applying for leave.
     */
    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('leaves', 'admin'),
        ];
    }

    /**
     * Support filtering leave groups by an employee: only the groups the
     * employee is a member of (via the LeaveGroupEmployees join table).
     *
     * The group ids are resolved here (in PHP) and inlined as "id in (..)"
     * rather than using a SQL sub-select — the ActiveRecord Find() treats any
     * clause containing the word "where" as a pre-built join query, which a
     * sub-select would falsely trigger.
     */
    public function getCustomFilterQuery($filter)
    {
        $query = "";
        $queryData = array();
        foreach ($filter as $k => $v) {
            if ($v === null || $v === '' || $v === 'NULL') {
                continue;
            }
            if ($k == 'employee') {
                $member = new LeaveGroupEmployee();
                $rows = $member->Find('employee = ?', array($v));
                $ids = array();
                foreach ($rows as $row) {
                    $ids[] = intval($row->leave_group);
                }
                if (empty($ids)) {
                    $ids = array(0); // employee in no group -> match nothing
                }
                $query .= " and id in (" . implode(',', $ids) . ")";
            }
        }
        return array($query, $queryData);
    }

    /**
     * Columns this model's select boxes may request (see
     * BaseModel::fieldValueFields). Derived from the pickers that actually exist,
     * so this allows today's usage and nothing more.
     */
    public function fieldValueFields()
    {
        return array('id', 'name');
    }

}
