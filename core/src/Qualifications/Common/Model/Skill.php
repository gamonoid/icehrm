<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:07 PM
 */

namespace Qualifications\Common\Model;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

class Skill extends BaseModel
{
    public $table = 'Skills';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('qualifications', 'admin'),
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
        ];
    }

    /**
     * Block deletion of a skill still attached to employees (EmployeeSkills.skill_id).
     * A non-SUCCESS response aborts the delete — see BaseService::deleteElement().
     */
    public function executePreDeleteActions($obj)
    {
        $rows = BaseService::getInstance()->getDB()->Execute(
            'SELECT COUNT(*) c FROM EmployeeSkills WHERE skill_id = ?',
            array($obj->id)
        );
        $count = (is_array($rows) && isset($rows[0]['c'])) ? (int) $rows[0]['c'] : 0;
        if ($count > 0) {
            return new IceResponse(
                IceResponse::ERROR,
                'This skill cannot be deleted because it is assigned to '
                . $count . ' employee' . ($count === 1 ? '' : 's') . '.'
            );
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
