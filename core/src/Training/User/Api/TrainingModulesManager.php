<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 7:54 AM
 */

namespace Training\User\Api;

use Classes\AbstractModuleManager;
use Classes\BaseService;

class TrainingModulesManager extends AbstractModuleManager
{

    public function initializeUserClasses()
    {
        $user = BaseService::getInstance()->currentUser;
        if (defined('MODULE_TYPE') && MODULE_TYPE != 'admin') {
            $this->addUserClass("EmployeeTrainingSession");
        }
    }

    public function initializeFieldMappings()
    {
        $this->addFileFieldMapping('EmployeeTrainingSession', 'proof', 'name');
    }

    public function initializeDatabaseErrorMappings()
    {
    }

    public function setupModuleClassDefinitions()
    {

        $this->addModelClass('EmployeeTrainingSession');
    }
}
