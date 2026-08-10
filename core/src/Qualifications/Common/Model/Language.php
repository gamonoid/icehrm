<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:08 PM
 */

namespace Qualifications\Common\Model;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\ModuleAccess;
use Model\BaseModel;

class Language extends BaseModel
{
    public $table = 'Languages';

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
     * Block deletion of a language still attached to employees
     * (EmployeeLanguages.language_id).
     */
    public function executePreDeleteActions($obj)
    {
        $rows = BaseService::getInstance()->getDB()->Execute(
            'SELECT COUNT(*) c FROM EmployeeLanguages WHERE language_id = ?',
            array($obj->id)
        );
        $count = (is_array($rows) && isset($rows[0]['c'])) ? (int) $rows[0]['c'] : 0;
        if ($count > 0) {
            return new IceResponse(
                IceResponse::ERROR,
                'This language cannot be deleted because it is assigned to '
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
        return array('description', 'id', 'name');
    }

}
