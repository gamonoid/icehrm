<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 12:06 PM
 */

namespace Expenses\Admin\Api;

use Classes\Approval\ApproveAdminActionManager;

class ExpensesActionManager extends ApproveAdminActionManager
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
        return "g=modules&n=expenses&m=module_Finance";
    }

    public function getModuleSubordinateTabUrl()
    {
        return "g=modules&n=expenses&m=module_Finance#tabSubordinateEmployeeExpense";
    }

    public function getModuleApprovalTabUrl()
    {
        return "g=modules&n=expenses&m=module_Finance#tabEmployeeExpenseApproval";
    }
}
