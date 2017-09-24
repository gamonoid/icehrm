<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:48 AM
 */

namespace Classes\ModuleBuilder;

class ModuleTabGroup
{
    public $name;
    public $label;
    public $isActive = false;
    public $modules = array();

    public function __construct($name, $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    public function addModuleTab($moduleTab)
    {
        if ($moduleTab->isActive) {
            $this->isActive = true;
            $moduleTab->isActive = false;
        }
        $moduleTab->isInsideGroup = true;
        $this->modules[] = $moduleTab;
    }

    public function getHTML()
    {
        $html = "";
        $active = ($this->isActive)?" active":"";

        $html.= '<li class="dropdown'.$active.'">'."\r\n".
            '<a href="#" id="'.$this->name.
            '" class="dropdown-toggle" data-toggle="dropdown" aria-controls="'.$this->name.
            '-contents">'.$this->label.' <span class="caret"></span></a>'."\r\n".
            '<ul class="dropdown-menu" role="menu" aria-labelledby="'.$this->name.'" id="'.$this->name.'-contents">';

        foreach ($this->modules as $module) {
            $html.= $module->getHTML();
        }

        $html .= "</ul></li>";

        return $html;
    }
}
