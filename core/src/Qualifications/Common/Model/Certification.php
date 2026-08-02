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

class Certification extends BaseModel
{
    public $table = 'Certifications';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
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
     * Block deletion of a certification still attached to employees
     * (EmployeeCertifications.certification_id).
     */
    public function executePreDeleteActions($obj)
    {
        $rows = BaseService::getInstance()->getDB()->Execute(
            'SELECT COUNT(*) c FROM EmployeeCertifications WHERE certification_id = ?',
            array($obj->id)
        );
        $count = (is_array($rows) && isset($rows[0]['c'])) ? (int) $rows[0]['c'] : 0;
        if ($count > 0) {
            return new IceResponse(
                IceResponse::ERROR,
                'This certification cannot be deleted because it is assigned to '
                . $count . ' employee' . ($count === 1 ? '' : 's') . '.'
            );
        }
        return new IceResponse(IceResponse::SUCCESS, null);
    }
}
