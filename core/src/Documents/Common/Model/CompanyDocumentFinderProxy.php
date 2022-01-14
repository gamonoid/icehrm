<?php

namespace Documents\Common\Model;

use Classes\BaseService;
use Classes\FileService;
use Employees\Common\Model\Employee;

class CompanyDocumentFinderProxy extends CompanyDocument
{
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

            if (!empty($entry->share_employees)) {
                $shareEmployees = json_decode($entry->share_employees, true);
                if (in_array($employee->id, $shareEmployees)) {
                    //Document is shared with the employee
                    $data[] = $entry;
                    continue;
                }
                // When the document is assigned to an employee, share department value is ignored

                if (is_array($shareEmployees) && count($shareEmployees) === 0) {
                    continue;
                }
            }

            if (empty($entry->share_departments)) {
                // When share departments is null / all employees can access
                $data[] = $entry;
                continue;
            }

            $shareDepartments = json_decode($entry->share_departments, true);
            if (count($shareDepartments) == 0 || empty($shareDepartments)) {
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

        return $data;
    }
}
