<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 7:36 AM
 */

namespace Settings\Admin\Api;

use Classes\AbstractModuleManager;
use Classes\SystemTasks\SystemTasksService;
use Settings\Rest\SettingsRestEndPoint;

class SettingsAdminManager extends AbstractModuleManager
{

    public function initialize()
    {
        SystemTasksService::getInstance()->registerTaskCreator((new SettingTaskCreator()));
    }

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

        //This is a fixed module, store model classes in Models.inc.php
    }

    public function getInitializer()
    {
        return new SettingsInitialize();
    }

    public function setupRestEndPoints()
    {
        \Classes\Macaw::get(REST_API_PATH.'settings', function () {
            $restEndPoint = new SettingsRestEndPoint();
            $restEndPoint->process('getMobileSettings', []);
        });
    }
}
