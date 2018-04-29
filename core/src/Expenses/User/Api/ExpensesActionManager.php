<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 12:20 PM
 */

namespace Expenses\User\Api;

use Classes\Approval\ApproveModuleActionManager;

class ExpensesActionManager extends ApproveModuleActionManager
{

    public function getModelClass()
    {
        return "EmployeeExpense";
    }

    public function getItemName()
    {
        return "Expense";
    }

    public function getModuleName()
    {
        return "Expense Management";
    }

    public function getModuleTabUrl()
    {
        return "g=modules&n=expenses&m=module_Finance#tabSubordinateEmployeeExpense";
    }
}
