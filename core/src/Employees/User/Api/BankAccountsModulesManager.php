<?php
namespace Employees\User\Api;

use Classes\AbstractModuleManager;

/**
 * Self-service "Bank Accounts" module (About You → Bank Accounts). Employees list and
 * maintain their own accounts; numbers are masked by EmployeeBankAccount.
 */
class BankAccountsModulesManager extends AbstractModuleManager
{
    public function initializeUserClasses()
    {
        if (defined('MODULE_TYPE') && MODULE_TYPE != 'admin') {
            $this->addUserClass("EmployeeBankAccount");
        }
    }

    public function initializeFieldMappings()
    {
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('EmployeeBankAccount');
    }
}
