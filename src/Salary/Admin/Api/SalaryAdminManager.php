<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 11:09 PM
 */

namespace Salary\Admin\Api;

use Classes\AbstractModuleManager;

class SalaryAdminManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('SalaryComponentType');
        $this->addModelClass('SalaryComponent');
        $this->addModelClass('PayrollEmployee');
    }
}
