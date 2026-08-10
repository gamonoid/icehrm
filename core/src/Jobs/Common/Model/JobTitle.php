<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 12:52 PM
 */

namespace Jobs\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

class JobTitle extends BaseModel
{
    public $table = 'JobTitles';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
        ];
    }

    /**
     * Block deletion of a job title that is still assigned to employees
     * (Employees.job_title references JobTitles.id). Returning a non-SUCCESS
     * response here aborts the delete — see BaseService::deleteElement().
     */
    public function executePreDeleteActions($obj)
    {
        if (class_exists('\\Employees\\Common\\Model\\Employee')) {
            $employee = new \Employees\Common\Model\Employee();
            $assigned = $employee->Find('job_title = ?', array($obj->id));
            $count = is_array($assigned) ? count($assigned) : 0;
            if ($count > 0) {
                return new IceResponse(
                    IceResponse::ERROR,
                    'This job title cannot be deleted because it is assigned to '
                    . $count . ' employee' . ($count === 1 ? '' : 's') . '.'
                );
            }
        }
        return new IceResponse(IceResponse::SUCCESS, null);
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
