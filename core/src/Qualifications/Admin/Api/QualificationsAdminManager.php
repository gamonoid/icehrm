<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:09 PM
 */

namespace Qualifications\Admin\Api;

use Classes\AbstractModuleManager;

class QualificationsAdminManager extends AbstractModuleManager
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

        $this->addModelClass('Skill');
        $this->addModelClass('Education');
        $this->addModelClass('Certification');
        $this->addModelClass('Language');
    }
}
