<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:12 PM
 */

namespace Qualifications\User\Api;

use Classes\AbstractModuleManager;

class QualificationsModulesManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
        if (defined('MODULE_TYPE') && MODULE_TYPE != 'admin') {
            $this->addUserClass("EmployeeSkill");
            $this->addUserClass("EmployeeEducation");
            $this->addUserClass("EmployeeCertification");
            $this->addUserClass("EmployeeLanguage");
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

        $this->addModelClass('EmployeeSkill');
        $this->addModelClass('EmployeeEducation');
        $this->addModelClass('EmployeeCertification');
        $this->addModelClass('EmployeeLanguage');
    }
}
