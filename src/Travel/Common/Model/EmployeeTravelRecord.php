<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 8:05 AM
 */

namespace Travel\Common\Model;

use Classes\SettingsManager;
use Model\ApproveModel;

class EmployeeTravelRecord extends ApproveModel
{
    public $table = 'EmployeeTravelRecords';

    public $notificationModuleName = "Travel Management";
    public $notificationUnitName = "TravelRequest";
    public $notificationUnitPrefix = "A";
    public $notificationUnitAdminUrl
        = "g=modules&n=travel&m=module_Travel_Management#tabSubordinateEmployeeTravelRecord";
    public $preApproveSettingName = "Travel: Pre-Approve Travel Request";

    public function isMultiLevelApprovalsEnabled()
    {
        return (SettingsManager::getInstance()->getSetting('Travel: Enable Multi Level Approvals') == '1');
    }

    public function getAdminAccess()
    {
        return array("get", "element", "save", "delete");
    }

    public function getManagerAccess()
    {
        return array("get", "element", "save", "delete");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element", "save", "delete");
    }

    public function fieldsNeedToBeApproved()
    {
        return array(
            "travel_from",
            "travel_to",
            "travel_date",
            "return_date",
            "funding",
            "currency"
        );
    }

    public function getType()
    {
        return 'EmployeeTravelRecord';
    }

    public function allowIndirectMapping()
    {
        if (SettingsManager::getInstance()->getSetting('Travel: Allow Indirect Admins to Approve') == '1') {
            return true;
        }
        return false;
    }
}
