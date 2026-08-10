<?php

namespace Classes\Approval;

use Classes\BaseService;
use Classes\IceConstants;
use Classes\IceResponse;
use Classes\StatusChangeLogManager;
use Model\BaseModel;
use Utils\LogManager;

abstract class ApproveAdminActionManager extends ApproveCommonActionManager
{

    abstract public function getModelClass();
    abstract public function getItemName();
    abstract public function getModuleName();
    abstract public function getModuleTabUrl();
    abstract public function getModuleSubordinateTabUrl();
    abstract public function getModuleApprovalTabUrl();

    public function changeStatus($req)
    {

        $class = $this->getModelClass();
        $itemName = $this->getItemName();
        /* @var BaseModel $obj */
        $nsClass = BaseService::getInstance()->getFullQualifiedModelClassName($class);
        $obj = new $nsClass();
        $obj->Load("id = ?", array($req->id));

        if ($obj->id != $req->id) {
            return new IceResponse(IceResponse::ERROR, "$itemName not found");
        }

        // Ownership gate. $req->id is any approvable record (overtime, travel, ...) and
        // this method approves/rejects it. The previous check was commented out, so a
        // Manager reaching admin=overtime / admin=travel could approve or reject ANY
        // employee's request, not only their subordinates'. Scope to the record owner:
        // Admin (or all-employee-data role) any; a Manager only those they manage — plus
        // anyone named in this record's own approval chain, who is authorised by the
        // chain rather than by the reporting line. Nobody but an admin may decide their
        // OWN request — see currentUserCanDecideRecord.
        if (!$this->currentUserCanDecideRecord($obj)) {
            return new IceResponse(IceResponse::ERROR, "Permission denied", 403);
        }

        //Check if this needs to be multi-approved
        $apStatus = 0;
        if ($req->status == "Approved") {
            $apStatus = 1;
        }

        if ($req->status == "Approved" || $req->status == "Rejected") {
            $approvalResp = ApprovalStatus::getInstance()->updateApprovalStatus(
                $class,
                $obj->id,
                BaseService::getInstance()->getCurrentProfileId(),
                $apStatus
            );

            if ($approvalResp->getStatus() == IceResponse::SUCCESS) {
                $objResp = $approvalResp->getObject();
                $currentAp  = $objResp[0];
                $nextAp     = $objResp[1];
                $sendApprovalEmailto = null;
                if (empty($currentAp) && empty($nextAp)) {
                    //No multi level approvals
                    LogManager::getInstance()->debug($obj->id."|No multi level approvals|");
                    if ($req->status == "Approved") {
                        $req->status = "Approved";
                    }
                } elseif (empty($currentAp) && !empty($nextAp)) {
                    //Approval process is defined, but this person is a supervisor
                    LogManager::getInstance()->debug(
                        $obj->id."|Approval process is defined, but this person is a supervisor|"
                    );
                    $sendApprovalEmailto = $nextAp->approver;
                    if ($req->status == "Approved") {
                        $req->status = "Processing";
                    }
                } elseif (!empty($currentAp) && empty($nextAp)) {
                    //All multi level approvals completed, now we can approve
                    LogManager::getInstance()->debug(
                        $obj->id."|All multi level approvals completed, now we can approve|"
                    );
                    if ($req->status == "Approved") {
                        $req->status = "Approved";
                    }
                } else {
                    //Current employee is an approver and we have another approval level left
                    LogManager::getInstance()->debug(
                        $obj->id."|Current employee is an approver and we have another approval level left|"
                    );
                    $sendApprovalEmailto = $nextAp->approver;
                    if ($req->status == "Approved") {
                        $req->status = "Processing";
                    }
                }
            } else {
                return $approvalResp;
            }
        }

        $oldStatus = $obj->status;
        $obj->status = $req->status;

        if ($oldStatus == $req->status && $req->status != "Processing") {
            return new IceResponse(IceResponse::SUCCESS, "");
        }

        $ok = $obj->Save();

        if (!$ok) {
            LogManager::getInstance()->info($obj->ErrorMsg());
            return new IceResponse(
                IceResponse::ERROR,
                "Error occurred while saving $itemName information. Please contact admin"
            );
        }

        StatusChangeLogManager::getInstance()->addLog(
            $class,
            $obj->id,
            BaseService::getInstance()->getCurrentUser()->id,
            $oldStatus,
            $req->status,
            isset($req->reason) ? (string) $req->reason : ""
        );

        $this->baseService->audit(
            IceConstants::AUDIT_ACTION,
            "$itemName status changed from:".$oldStatus." to:".$obj->status." id:".$obj->id
        );

        $currentEmpId = $this->getCurrentProfileId();

        if (!empty($currentEmpId)) {
            $employee = $this->baseService->getElement('Employee', $currentEmpId, null, true);

            $notificationMsg
                = "Your $itemName has been $obj->status by ".$employee->first_name." ".$employee->last_name;
            if (!empty($req->reason)) {
                $notificationMsg.=" (Note:".$req->reason.")";
            }

            $this->baseService->notificationManager->addNotification(
                $obj->employee,
                $notificationMsg,
                '{"type":"url","url":"'.$this->getModuleTabUrl().'"}',
                $this->getModuleName(),
                null,
                false,
                true
            );
        }

        if (!empty($sendApprovalEmailto)) {
            $employee = $this->baseService->getElement(
                'Employee',
                BaseService::getInstance()->getCurrentProfileId(),
                null,
                true
            );

            $notificationMsg
                = "You have been assigned ".$itemName." for approval by ".
                    $employee->first_name." ".$employee->last_name;

            $this->baseService->notificationManager->addNotification(
                $sendApprovalEmailto,
                $notificationMsg,
                '{"type":"url","url":"'.$this->getModuleApprovalTabUrl().'"}',
                $this->getModuleName(),
                null,
                false,
                true
            );
        }

        return new IceResponse(IceResponse::SUCCESS, "");
    }
}
