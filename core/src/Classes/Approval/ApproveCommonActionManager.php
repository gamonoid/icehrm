<?php
namespace Classes\Approval;

use Classes\BaseService;
use Classes\IceResponse;
use Classes\StatusChangeLogManager;
use Classes\SubActionManager;
use Employees\Common\Model\EmployeeAccess;
use Employees\Common\Model\EmployeeApproval;

abstract class ApproveCommonActionManager extends SubActionManager
{

    /**
     * May the current user READ this record's approval history?
     *
     * Three groups may. Anyone with access to the record owner's employee data (an
     * admin, a holder of the all-employee-data role, the owner's supervisor, a
     * department head); the owner themselves, who is entitled to see what happened to
     * their own request; and anyone named in THIS record's multi-level approval chain.
     *
     * That last group cannot be derived from the reporting line. approver1/2/3 are set
     * per employee and are routinely NOT that employee's supervisor — that is the whole
     * point of a multi-level chain. Checking employee-data access alone locks every
     * chain approver out of the workflow they were explicitly assigned to.
     */
    protected function currentUserCanReviewRecord($obj)
    {
        // A record with no employee column has no owner to scope by; the caller's own
        // checks apply.
        if (!isset($obj->employee)) {
            return true;
        }

        // currentUserCanAccessEmployeeData() already returns true for one's own data,
        // which is what lets the owner read their own history.
        if (BaseService::getInstance()->currentUserCanAccessEmployeeData($obj->employee)) {
            return true;
        }

        return $this->isApprovalChainApprover($obj);
    }

    /**
     * May the current user DECIDE this record — approve, reject or otherwise move its
     * status?
     *
     * Read access is not enough. currentUserCanReviewRecord() deliberately admits the
     * record's own owner, so reusing it here would let an employee approve their own
     * overtime or expense claim by posting changeStatus directly. Everyone except an
     * admin (or a holder of the all-employee-data role, who may make corrections on any
     * record including their own) is therefore excluded from deciding their own request,
     * whatever their role or position in the chain — the same rule the leave workflow
     * applies in changeLeaveStatus.
     *
     * This decides only WHO may act at all. Which chain level may act *right now* is
     * still enforced by ApprovalStatus::updateApprovalStatus, which refuses anyone who
     * is not the currently-active approver.
     */
    protected function currentUserCanDecideRecord($obj)
    {
        if (!isset($obj->employee)) {
            return true;
        }

        $currentUser = BaseService::getInstance()->getCurrentUser();
        $privileged = (!empty($currentUser) && $currentUser->user_level === 'Admin')
            || EmployeeAccess::hasAccessToAllEmployeeData();
        if ($privileged) {
            return true;
        }

        $profileId = BaseService::getInstance()->getCurrentProfileId();
        if (!empty($profileId) && $obj->employee == $profileId) {
            return false;
        }

        if (BaseService::getInstance()->currentUserCanAccessEmployeeData($obj->employee)) {
            return true;
        }

        return $this->isApprovalChainApprover($obj);
    }

    /**
     * Is the current user named as an approver in this record's own approval chain?
     * The owner is never treated as an approver of their own record, whatever the chain
     * happens to say.
     */
    protected function isApprovalChainApprover($obj)
    {
        $profileId = BaseService::getInstance()->getCurrentProfileId();
        if (empty($profileId) || empty($obj->id)) {
            return false;
        }

        if (isset($obj->employee) && $obj->employee == $profileId) {
            return false;
        }

        $approval = new EmployeeApproval();
        $approval->Load(
            'type = ? and element = ? and approver = ?',
            array($this->getModelClass(), $obj->id, $profileId)
        );

        return !empty($approval->id);
    }

    public function getLogs($req)
    {

        $class = $this->getModelClass();

        // Ownership gate. $req->id is a request-supplied record id (overtime, travel,
        // ...) whose status log this returns. Without a check any employee could read
        // any employee's approval history. Load the record and scope to its owner — or
        // to the approval chain the caller sits on, so an approver can read the history
        // of the very request they are being asked to decide.
        $nsClass = BaseService::getInstance()->getFullQualifiedModelClassName($class);
        $obj = new $nsClass();
        $obj->Load("id = ?", array($req->id));
        if (empty($obj->id)) {
            return new IceResponse(IceResponse::ERROR, "Not found");
        }
        if (!$this->currentUserCanReviewRecord($obj)) {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }

        $logs = StatusChangeLogManager::getInstance()->getLogs($class, $req->id);
        return new IceResponse(IceResponse::SUCCESS, $logs);
    }
}
