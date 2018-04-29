<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 4:18 PM
 */

namespace Payroll\Admin\Api;

use Classes\AbstractModuleManager;

class PayrollAdminManager extends AbstractModuleManager
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

        $this->addModelClass('Payroll');
        $this->addModelClass('PayrollColumn');
        $this->addModelClass('PayrollData');
        $this->addModelClass('PayFrequency');
        $this->addModelClass('PayrollColumnTemplate');
        $this->addModelClass('Deduction');
        $this->addModelClass('DeductionGroup');
        $this->addModelClass('PayslipTemplate');
        $this->addModelClass('PayrollCalculations');
    }
}
