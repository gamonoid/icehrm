<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 8:25 AM
 */

namespace Documents\Common\Model;

use Classes\BaseService;
use Classes\FileService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Employees\Common\Model\Employee;
use Model\BaseModel;

class CompanyDocument extends BaseModel
{
    public $table = 'CompanyDocuments';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('documents', 'admin'),
            new ModuleAccess('documents', 'user'),
        ];
    }

    // @codingStandardsIgnoreStart
    public function Find($whereOrderBy, $bindarr = false, $cache = false, $pkeysArr = false, $extra = array())
    {
        // @codingStandardsIgnoreEnd
        $res = parent::Find($whereOrderBy, $bindarr, $pkeysArr, $extra);

        $user = BaseService::getInstance()->getCurrentUser();
        if ($user->user_level == 'Admin') {
            foreach ($res as $entry) {
                $file = FileService::getInstance()->getFileData($entry->attachment);
                $entry->type = $file->type;
                $entry->size = $file->size_text;
            }
            return $res;
        }
        $emp = BaseService::getInstance()->getCurrentProfileId();
        $employee = new Employee();
        $employee->Load("id = ?", array($emp));

        $data = array();

        foreach ($res as $entry) {
            if ($entry->status != 'Active') {
                continue;
            }

            $file = FileService::getInstance()->getFileData($entry->attachment);
            $entry->type = $file->type;
            $entry->size = $file->size_text;

            if (!empty($entry->share_departments)) {
                $shareDepartments = json_decode($entry->share_departments, true);
                if (count($shareDepartments) == 1 && $shareDepartments[0] == "NULL") {
                    //Shared with All Departments
                    $data[] = $entry;
                    continue;
                } else {
                    if (in_array($employee->department, $shareDepartments)) {
                        //Document is shared with employee's department
                        $data[] = $entry;
                        continue;
                    }
                }
            }

            if (!empty($entry->share_employees)) {
                $shareEmployees = json_decode($entry->share_employees, true);
                if (in_array($employee->id, $shareEmployees)) {
                    //Document is shared with the employee
                    $data[] = $entry;
                    continue;
                }
            }
        }

        return $data;
    }
}
