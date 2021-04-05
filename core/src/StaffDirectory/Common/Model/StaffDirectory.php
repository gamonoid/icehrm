<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 7:40 AM
 */

namespace StaffDirectory\Common\Model;

use Classes\FileService;
use Employees\Common\Model\Employee;
use Model\BaseModel;

class StaffDirectory extends Employee
{
    // @codingStandardsIgnoreStart
    public function Find($whereOrderBy, $bindarr = false, $cache = false, $pkeysArr = false, $extra = array())
    {
        // @codingStandardsIgnoreEnd
        $res = parent::Find($whereOrderBy, $bindarr, $pkeysArr, $extra);
        $data = array();
        //$img = '<img src="_img_" class="img-circle" style="width:45px;height: 45px;" alt="User Image">';
        foreach ($res as $entry) {
            $emp = new BaseModel();
            $emp->id = $entry->id;
            $emp->first_name = $entry->first_name;
            $emp->last_name = $entry->last_name;
            $emp = FileService::getInstance()->updateSmallProfileImage($emp);
            $emp->job_title = $entry->job_title;
            $emp->department = $entry->department;
            $emp->work_phone = $entry->work_phone;
            $emp->work_email = $entry->work_email;
            $emp->joined_date = $entry->joined_date;
            $emp->gender = $entry->gender;
            $emp->_org = $entry;
            $data[] = $emp;
        }

        return $data;
    }

    public function isCustomFieldsEnabled()
    {
        return false;
    }

    // @codingStandardsIgnoreStart
    public function Insert()
    {
        return;
    }

    public function Delete()
    {
        return;
    }
    // @codingStandardsIgnoreEnd
}
