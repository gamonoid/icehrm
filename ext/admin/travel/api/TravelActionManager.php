<?php
class TravelActionManager extends ApproveAdminActionManager{

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
        return "g=modules&n=travel&m=module_Travel_Management";
    }
}
