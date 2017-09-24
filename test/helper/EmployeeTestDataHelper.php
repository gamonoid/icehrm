<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 9/7/17
 * Time: 7:49 AM
 */

namespace Test\Helper;

use Employees\Common\Model\Employee;

class EmployeeTestDataHelper
{
    public static function insertRandomEmployee()
    {
        $emp = new Employee();
        $emp->employee_id = self::randomString(4).time();
        $emp->first_name = self::randomString();
        $emp->last_name = self::randomString();
        $emp->birthday = '1984-12-19';
        $emp->gender = 'Male';
        $emp->marital_status = 'Married';
        $emp->job_title = null;
        $emp->joined_date = '2005-08-03';
        $emp->job_title = null;
        $emp->department = null;
        $emp->Save();
        return $emp->id;
    }

    public static function randomString($length = 6)
    {
        $str = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }
}
