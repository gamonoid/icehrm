<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 8:07 AM
 */

namespace Travel\Admin\Api;

use Classes\AbstractModuleManager;

class TravelAdminManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
        if (defined('MODULE_TYPE') && MODULE_TYPE != 'admin') {
            $this->addUserClass("EmployeeImmigration");
            $this->addUserClass("EmployeeTravelRecord");
        }
    }

    public function initializeFieldMappings()
    {
        $this->addFileFieldMapping('EmployeeImmigration', 'attachment1', 'name');
        $this->addFileFieldMapping('EmployeeImmigration', 'attachment2', 'name');
        $this->addFileFieldMapping('EmployeeImmigration', 'attachment3', 'name');

        $this->addFileFieldMapping('EmployeeTravelRecord', 'attachment1', 'name');
        $this->addFileFieldMapping('EmployeeTravelRecord', 'attachment2', 'name');
        $this->addFileFieldMapping('EmployeeTravelRecord', 'attachment3', 'name');
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {

        $this->addModelClass('ImmigrationDocument');
        $this->addModelClass('EmployeeImmigration');
        $this->addModelClass('EmployeeTravelRecord');
        $this->addModelClass('EmployeeTravelRecordApproval');
    }
}
