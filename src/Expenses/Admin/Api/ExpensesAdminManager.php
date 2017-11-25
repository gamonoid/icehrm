<?php
namespace Expenses\Admin\Api;

use Classes\AbstractModuleManager;

class ExpensesAdminManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
        if (defined('MODULE_TYPE') && MODULE_TYPE != 'admin') {
            $this->addUserClass("EmployeeExpense");
        }
    }

    public function initializeFieldMappings()
    {
        $this->addFileFieldMapping('EmployeeExpense', 'attachment1', 'name');
        $this->addFileFieldMapping('EmployeeExpense', 'attachment2', 'name');
        $this->addFileFieldMapping('EmployeeExpense', 'attachment3', 'name');
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {

        $this->addModelClass('ExpensesCategory');
        $this->addModelClass('ExpensesPaymentMethod');
        $this->addModelClass('EmployeeExpense');
        $this->addModelClass('EmployeeExpenseApproval');
    }

    public function initCalculationHooks()
    {
        $this->addCalculationHook(
            'ExpensePayrollUtils_getApprovedExpensesTotal',
            'Total Approved Expenses',
            ExpensePayrollUtils::class,
            'getApprovedExpensesTotal'
        );
    }
}
