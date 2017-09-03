<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:25 AM
 */

namespace Classes\Approval;

use Classes\BaseService;
use Classes\IceConstants;
use Classes\IceResponse;
use Utils\LogManager;

abstract class ApproveModuleActionManager extends ApproveCommonActionManager
{

    abstract public function getModelClass();
    abstract public function getItemName();
    abstract public function getModuleName();
    abstract public function getModuleTabUrl();

    public function cancel($req)
    {

        $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);

        $class = $this->getModelClass();
        $itemName = $this->getItemName();
        /* @var \Model\BaseModel $obj */
        $nsClass = BaseService::getInstance()->getFullQualifiedModelClassName($class);
        $obj = new $nsClass();
        $obj->Load("id = ?", array($req->id));
        if ($obj->id != $req->id) {
            return new IceResponse(IceResponse::ERROR, "$itemName record not found");
        }

        if ($this->user->user_level != 'Admin' && $this->getCurrentProfileId() != $obj->employee) {
            return new IceResponse(IceResponse::ERROR, "Only an admin or owner of the $itemName can do this");
        }

        if ($obj->status != 'Approved') {
            return new IceResponse(IceResponse::ERROR, "Only an approved $itemName can be cancelled");
        }

        $obj->status = 'Cancellation Requested';
        $ok = $obj->Save();
        if (!$ok) {
            LogManager::getInstance()->error("Error occurred while cancelling the $itemName:".$obj->ErrorMsg());
            return new IceResponse(
                IceResponse::ERROR,
                "Error occurred while cancelling the $itemName. Please contact admin."
            );
        }

        $this->baseService->audit(
            IceConstants::AUDIT_ACTION,
            "Expense cancellation | start:".$obj->date_start."| end:".$obj->date_end
        );
        $notificationMsg
            = $employee->first_name." ".$employee->last_name
            ." cancelled a expense. Visit expense management module to approve";

        $this->baseService->notificationManager->addNotification(
            $employee->supervisor,
            $notificationMsg,
            '{"type":"url","url":"'.$this->getModuleTabUrl().'"}',
            $this->getModuleName(),
            null,
            false,
            true
        );
        return new IceResponse(IceResponse::SUCCESS, $obj);
    }
}
