<?php
class TravelActionManager extends ApproveModuleActionManager{

    public function getModelClass(){
        return "EmployeeTravelRecord";
    }

    public function getItemName(){
        return "TravelRequest";
    }

    public function getModuleName(){
        return "Travel Management";
    }

    public function getModuleTabUrl(){
        return "g=admin&n=travel&m=admin_Employees#tabEmployeeTravelRecord";
    }
}


/*
class TravelActionManager extends SubActionManager{
    public function cancelTravel($req){

        $employee = $this->baseService->getElement('Employee',$this->getCurrentProfileId(),null,true);

        $employeeTravel = new EmployeeTravelRecord();
        $employeeTravel->Load("id = ?",array($req->id));
        if($employeeTravel->id != $req->id){
            return new IceResponse(IceResponse::ERROR,"Travel record not found");
        }


        if($this->user->user_level != 'Admin' && $this->getCurrentProfileId() != $employeeTravel->employee){
            return new IceResponse(IceResponse::ERROR,"Only an admin or owner of the travel request can do this");
        }

        if($employeeTravel->status != 'Approved'){
            return new IceResponse(IceResponse::ERROR,"Only an approved travel request can be cancelled");
        }

        $employeeTravel->status = 'Cancellation Requested';
        $ok = $employeeTravel->Save();
        if(!$ok){
            LogManager::getInstance()->error("Error occured while cancelling the travel:".$employeeTravel->ErrorMsg());
            return new IceResponse(IceResponse::ERROR,"Error occurred while cancelling the travel request. Please contact admin.");
        }


        $this->baseService->audit(IceConstants::AUDIT_ACTION, "Travel cancellation \ start:".$employeeTravel->date_start."\ end:".$employeeTravel->date_end);
        $notificationMsg = $employee->first_name." ".$employee->last_name." cancelled a travel. Visit travel management module to approve";

        $this->baseService->notificationManager->addNotification($employee->supervisor,$notificationMsg,'{"type":"url","url":"g=admin&n=travel&m=admin_Employees#tabEmployeeTravelRecord"}',
            "Travel Module", null, false, true);
        return new IceResponse(IceResponse::SUCCESS,$employeeTravel);
    }
}
*/