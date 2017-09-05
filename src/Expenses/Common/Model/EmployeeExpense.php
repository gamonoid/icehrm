<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 12:10 PM
 */

namespace Expenses\Common\Model;

use Classes\SettingsManager;
use Model\ApproveModel;

class EmployeeExpense extends ApproveModel
{
    var $_table = 'EmployeeExpenses';

    var $notificationModuleName = "Expense Management";
    var $notificationUnitName = "Expense";
    var $notificationUnitPrefix = "An";
    var $notificationUnitAdminUrl = "g=modules&n=expenses&m=module_Finance#tabSubordinateEmployeeExpense";
    var $preApproveSettingName = "Expense: Pre-Approve Expenses";

    public function isMultiLevelApprovalsEnabled()
    {
        return (SettingsManager::getInstance()->getSetting('Expense: Enable Multi Level Approvals') == '1');
    }

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get");
    }

    public function getUserOnlyMeAccess()
    {
        return array("element","save","delete");
    }

    public function fieldsNeedToBeApproved()
    {
        return array(
            "amount",
            "category",
            "payment_method",
            "currency"
        );
    }

    public function getType()
    {
        return 'EmployeeExpense';
    }

    public function allowIndirectMapping()
    {
        if (SettingsManager::getInstance()->getSetting('Expense: Allow Indirect Admins to Approve') == '1') {
            return true;
        }
        return false;
    }
}
