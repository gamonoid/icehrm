<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 12:13 PM
 */

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
}
