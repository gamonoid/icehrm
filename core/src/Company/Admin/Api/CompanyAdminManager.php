<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 7:48 AM
 */

namespace Company\Admin\Api;

use Classes\AbstractModuleManager;
use Company\Common\Model\CompanyStructure;

class CompanyAdminManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
    }

    public function initializeFieldMappings()
    {
    }

    public function initializeDatabaseErrorMappings()
    {
        $this->addDatabaseErrorMapping(
            "CONSTRAINT `Fk_Employee_CompanyStructures` FOREIGN KEY (`department`) 
            REFERENCES `CompanyStructures` (`id`)",
            "Can not delete a company structure while employees are assigned to it"
        );
        $this->addDatabaseErrorMapping(
            "CONSTRAINT `Fk_CompanyStructures_Own` FOREIGN KEY (`parent`) REFERENCES ",
            "Can not delete a parent structure"
        );
    }

    public function setupModuleClassDefinitions()
    {
        $this->addModelClass('CompanyStructure');
        $this->addModelClass('Timezone');
    }
    public function getDashboardItemData()
    {
        $data = array();
        $company = new CompanyStructure();
        $data['numberOfCompanyStuctures'] = $company->Count("1 = 1");
        return $data;
    }
}
