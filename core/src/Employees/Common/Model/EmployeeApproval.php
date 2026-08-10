<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 10:41 AM
 */

namespace Employees\Common\Model;

use Classes\ModuleAccess;
use Model\BaseModel;

class EmployeeApproval extends BaseModel
{

    public $table = 'EmployeeApprovals';

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    /**
     * Read-only for managers. This table IS the approval chain — (type, element,
     * approver, level, status, active). It has no `employee` column, so
     * BaseService::managerRecordScopeAllows() finds no owner and waves the role grant
     * through for EVERY row company-wide; granting "add"/"save" therefore let any
     * Manager rewrite anyone's chain. Live-verified: a manager forged a level-1 row
     * naming themselves approver on an unrelated leave, and repointed an existing
     * row's approver from another employee to themselves — which then satisfies the
     * approver check in ApprovalStatus and isApproverForLeave.
     *
     * The workflow does not need these verbs: ApprovalStatus builds and advances the
     * chain with direct ->Save() calls on the model, which never pass through
     * checkSecureAccess.
     */
    public function getManagerAccess()
    {
        return array("get","element");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('employees', 'admin'),
            new ModuleAccess('employees', 'user'),
        ];
    }
}
