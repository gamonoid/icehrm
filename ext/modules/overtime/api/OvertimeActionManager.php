<?php
class OvertimeActionManager extends ApproveModuleActionManager{

    public function getModelClass(){
        return "EmployeeOvertime";
    }

    public function getItemName(){
        return "Overtime Request";
    }

    public function getModuleName(){
        return "Overtime Management";
    }

    public function getModuleTabUrl(){
        return "g=modules&n=overtime&m=module_Time_Management#SubordinateEmployeeOvertime";
    }
}
