<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:47 AM
 */

namespace Classes\ModuleBuilderV2;

use Classes\BaseService;
use Classes\PermissionManager;

class ModuleBuilder
{
    public $modules = array();
    public $user = null;

    public function __construct()
    {
        $this->user = BaseService::getInstance()->getCurrentUser();
    }

    /**
     * @param ModuleTab $module
     */
    public function addModuleOrGroup($module)
    {
        $this->modules[] = $module;
    }

    public function getTabHeadersHTML()
    {
        $html = "";
        foreach ($this->modules as $module) {
            $html .= $module->getHTML()."\r\n";
        }
        return $html;
    }

    public function getTabPagesHTML()
    {
        $html = "";
        /* @var ModuleTab $module */
        foreach ($this->modules as $module) {
            if (get_class($module) === ModuleTab::class) {
                $html .= $module->getPageHTML()."\r\n";
            } else {
                /* @var ModuleTab $mod */
                foreach ($module->modules as $mod) {
                    $html .= $mod->getPageHTML()."\r\n";
                }
            }
        }
        return $html;
    }

    public function getModJsHTML()
    {
        $moduleData = [
            'user_level' => $this->user->user_level,
            'permissions' => [
            ]
        ];

        $html = "var modJsList = new Array();\r\n";
        $activeModule = "";
        /* @var ModuleTab $module */
        foreach ($this->modules as $module) {
            if (get_class($module) == ModuleTab::class) {
                $html .= $module->getJSObjectCode()."\r\n";

                $modelClass = $module->modelPath;
                $moduleData['permissions'][$module->name] = PermissionManager::checkGeneralAccess(new $modelClass());

                if ($module->isActive) {
                    $activeModule =  $module->name;
                }
            } else {
                /* @var ModuleTab $mod */
                foreach ($module->modules as $mod) {
                    $modelClass = $mod->modelPath;
                    $moduleData['permissions'][$mod->name] = PermissionManager::checkGeneralAccess(new $modelClass());

                    if ($module->isActive && $activeModule == "") {
                        $activeModule =  $mod->name;
                    }
                    $html .= $mod->getJSObjectCode()."\r\n";
                }
            }
        }

        $html .= "var modJs = modJsList['tab".$activeModule."'];\r\n";

        $html = "var data = ".json_encode($moduleData).";\r\n".$html;

        return $html;
    }
}
