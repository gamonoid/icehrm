<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 12:29 PM
 */

namespace FieldNames\Admin\Api;

use Classes\AbstractModuleManager;

class FieldNamesAdminManager extends AbstractModuleManager
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
        $this->addModelClass('FieldNameMapping');
        $this->addModelClass('CustomField');
    }
}
