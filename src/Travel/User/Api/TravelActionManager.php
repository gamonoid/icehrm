<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 8:16 AM
 */

namespace Travel\User\Api;

use Classes\Approval\ApproveModuleActionManager;

class TravelActionManager extends ApproveModuleActionManager
{

    public function getModelClass()
    {
        return "EmployeeTravelRecord";
    }

    public function getItemName()
    {
        return "TravelRequest";
    }

    public function getModuleName()
    {
        return "Travel Management";
    }

    public function getModuleTabUrl()
    {
        return "g=modules&n=travel&m=module_Travel_Management#tabSubordinateEmployeeTravelRecord";
    }
}
