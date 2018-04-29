<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:47 AM
 */

namespace Classes\ModuleBuilder;

class ModuleBuilder
{
    public $modules = array();

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
        $html = "var modJsList = new Array();\r\n";
        $activeModule = "";
        /* @var ModuleTab $module */
        foreach ($this->modules as $module) {
            if (get_class($module) == ModuleTab::class) {
                $html .= $module->getJSObjectCode()."\r\n";
                if ($module->isActive) {
                    $activeModule =  $module->name;
                }
            } else {
                /* @var ModuleTab $mod */
                foreach ($module->modules as $mod) {
                    if ($module->isActive && $activeModule == "") {
                        $activeModule =  $mod->name;
                    }
                    $html .= $mod->getJSObjectCode()."\r\n";
                }
            }
        }

        $html .= "var modJs = modJsList['tab".$activeModule."'];\r\n";
        return $html;
    }
}
