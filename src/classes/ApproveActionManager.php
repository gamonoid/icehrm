<?php
abstract class ApproveAdminActionManager extends SubActionManager{
    
    public abstract function getModelClass();
    public abstract function getItemName();
    public abstract function getModuleName();
    public abstract function getModuleTabUrl();

    public function changeStatus($req){

        $class = $this->getModelClass();
        $itemName = $this->getItemName();


        $obj = new $class();
        $obj->Load("id = ?",array($req->id));

        if($obj->id != $req->id){
            return new IceResponse(IceResponse::ERROR,"$itemName not found");
        }

        if($this->user->user_level != 'Admin' && $this->user->user_level != 'Manager'){
            return new IceResponse(IceResponse::ERROR,"Only an admin or manager can do this");
        }

        $oldStatus = $obj->status;
        $obj->status = $req->status;
        $ok = $obj->Save();
        if(!$ok){
            LogManager::getInstance()->info($obj->ErrorMsg());
            return new IceResponse(IceResponse::ERROR,"Error occurred while saving $itemName information. Please contact admin");
        }

        $this->baseService->audit(IceConstants::AUDIT_ACTION, "$itemName status changed from:".$oldStatus." to:".$obj->status." id:".$obj->id);

        $currentEmpId = $this->getCurrentProfileId();

        if(!empty($currentEmpId)){
            $employee = $this->baseService->getElement('Employee',$currentEmpId);

            $notificationMsg = "Your $itemName has been $obj->status by ".$employee->first_name." ".$employee->last_name;
            if(!empty($req->reason)){
                $notificationMsg.=" (Note:".$req->reason.")";
            }

            $this->baseService->notificationManager->addNotification($obj->employee,$notificationMsg,'{"type":"url","url":"'.$this->getModuleTabUrl().'"}',$this->getModuleName(), null, false, true);

        }


        return new IceResponse(IceResponse::SUCCESS,"");
    }

}


abstract class ApproveModuleActionManager extends SubActionManager{

    public abstract function getModelClass();
    public abstract function getItemName();
    public abstract function getModuleName();
    public abstract function getModuleTabUrl();
    
    public function cancel($req){

        $employee = $this->baseService->getElement('Employee',$this->getCurrentProfileId(),null,true);

        $class = $this->getModelClass();
        $itemName = $this->getItemName();
        $obj = new $class();
        $obj->Load("id = ?",array($req->id));
        if($obj->id != $req->id){
            return new IceResponse(IceResponse::ERROR,"$itemName record not found");
        }


        if($this->user->user_level != 'Admin' && $this->getCurrentProfileId() != $obj->employee){
            return new IceResponse(IceResponse::ERROR,"Only an admin or owner of the $itemName can do this");
        }

        if($obj->status != 'Approved'){
            return new IceResponse(IceResponse::ERROR,"Only an approved $itemName can be cancelled");
        }

        $obj->status = 'Cancellation Requested';
        $ok = $obj->Save();
        if(!$ok){
            LogManager::getInstance()->error("Error occurred while cancelling the $itemName:".$obj->ErrorMsg());
            return new IceResponse(IceResponse::ERROR,"Error occurred while cancelling the $itemName. Please contact admin.");
        }


        $this->baseService->audit(IceConstants::AUDIT_ACTION, "Expense cancellation | start:".$obj->date_start."| end:".$obj->date_end);
        $notificationMsg = $employee->first_name." ".$employee->last_name." cancelled a expense. Visit expense management module to approve";

        $this->baseService->notificationManager->addNotification($employee->supervisor,$notificationMsg,'{"type":"url","url":"'.$this->getModuleTabUrl().'"}',
            $this->getModuleTabUrl(), null, false, true);
        return new IceResponse(IceResponse::SUCCESS,$obj);
    }
}



class ApproveModel extends ICEHRM_Record {

    public function executePreSaveActions($obj){
        $preApprove = SettingsManager::getInstance()->getSetting($this->preApproveSettingName);
        $sendNotificationEmail = true;
        if(empty($obj->status)){
            if($preApprove == "1"){
                $obj->status = "Approved";
                $sendNotificationEmail = false;
            }else{
                $obj->status = "Pending";
            }
        }

        if($preApprove){
            return new IceResponse(IceResponse::SUCCESS,$obj);
        }

        $currentEmpId = BaseService::getInstance()->getCurrentProfileId();

        //Auto approve if the current user is an admin

        if(!empty($currentEmpId)){
            $employee = BaseService::getInstance()->getElement('Employee',$currentEmpId);

            if(!empty($employee->supervisor)) {
                $notificationMsg = "A new ".$this->notificationUnitName." has been added by " . $employee->first_name . " " . $employee->last_name . ". Please visit ".$this->notificationModuleName." module to review it";

                BaseService::getInstance()->notificationManager->addNotification($employee->supervisor, $notificationMsg, '{"type":"url","url":"'.$this->notificationUnitAdminUrl.'"}', $this->notificationModuleName, null, false, $sendNotificationEmail);
            }else{

                $user = BaseService::getInstance()->getCurrentUser();

                if($user->user_level == "Admin"){
                    //Auto approve
                    $obj->status = "Approved";
                    $notificationMsg = "Your ".$this->notificationUnitName." is auto approved since you are an administrator and do not have any supervisor assigned";
                    BaseService::getInstance()->notificationManager->addNotification(null, $notificationMsg, '{"type":"url","url":"'.$this->notificationUnitAdminUrl.'"}', $this->notificationModuleName, $user->id, false, $sendNotificationEmail);
                }else{
                    //If the user do not have a supervisor, notify all admins
                    $admins = BaseService::getInstance()->getAllAdmins();
                    foreach($admins as $admin){
                        $notificationMsg = "A new ".$this->notificationUnitName." has been added by " . $employee->first_name . " " . $employee->last_name . ". Please visit ".$this->notificationModuleName." module to review it. You are getting this notification since you are an administrator and the user do not have any supervisor assigned.";
                        BaseService::getInstance()->notificationManager->addNotification(null, $notificationMsg, '{"type":"url","url":"'.$this->notificationUnitAdminUrl.'"}', $this->notificationModuleName, $admin->id, false, $sendNotificationEmail);
                    }
                }


            }
        }

        return new IceResponse(IceResponse::SUCCESS,$obj);
    }

    public function executePreUpdateActions($obj){

        $preApprove = SettingsManager::getInstance()->getSetting($this->preApproveSettingName);
        $sendNotificationEmail = true;

        $fieldsToCheck = $this->fieldsNeedToBeApproved();

        $travelRequest = new EmployeeTravelRecord();
        $travelRequest->Load('id = ?',array($obj->id));

        $needToApprove = false;
        if($preApprove != "1"){
            foreach($fieldsToCheck as $field){
                if($obj->$field != $travelRequest->$field) {
                    $needToApprove = true;
                    break;
                }
            }
        }else{
            $sendNotificationEmail = false;
        }

        if($preApprove){
            return new IceResponse(IceResponse::SUCCESS,$obj);
        }

        if($needToApprove && $obj->status != 'Pending'){
            $currentEmpId = BaseService::getInstance()->getCurrentProfileId();

            //Auto approve if the current user is an admin

            if(!empty($currentEmpId)){
                $employee = BaseService::getInstance()->getElement('Employee',$currentEmpId);

                if(!empty($employee->supervisor)) {
                    $notificationMsg = $this->notificationUnitPrefix." ".$this->notificationUnitName." has been updated by " . $employee->first_name . " " . $employee->last_name . ". Please visit ".$this->notificationModuleName." module to review it";

                    BaseService::getInstance()->notificationManager->addNotification($employee->supervisor, $notificationMsg, '{"type":"url","url":"'.$this->notificationUnitAdminUrl.'"}', $this->notificationModuleName, null, false, $sendNotificationEmail);
                }else{

                    $user = BaseService::getInstance()->getCurrentUser();

                    if($user->user_level == "Admin"){

                    }else{
                        //If the user do not have a supervisor, notify all admins
                        $admins = BaseService::getInstance()->getAllAdmins();
                        foreach($admins as $admin){
                            $notificationMsg = $this->notificationUnitPrefix." ".$this->notificationUnitName." request has been updated by " . $employee->first_name . " " . $employee->last_name . ". Please visit ".$this->notificationModuleName." module to review it. You are getting this notification since you are an administrator and the user do not have any supervisor assigned.";
                            BaseService::getInstance()->notificationManager->addNotification(null, $notificationMsg, '{"type":"url","url":"g=admin&n=travel&m=admin_Employees"}', "Travel Module", $admin->id, false, $sendNotificationEmail);
                        }
                    }


                }
            }
        }

        return new IceResponse(IceResponse::SUCCESS,$obj);
    }
}