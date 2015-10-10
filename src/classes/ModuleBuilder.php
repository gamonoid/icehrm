<?php

class ModuleBuilder {
    var $modules = array();

    public function addModuleOrGroup($module){
        $this->modules[] = $module;
    }

    public function getTabHeadersHTML(){
        $html = "";
        foreach($this->modules as $module){
            $html .= $module->getHTML()."\r\n";
        }
        return $html;
    }

    public function getTabPagesHTML(){
        $html = "";
        foreach($this->modules as $module){
            if(get_class($module) == "ModuleTab"){
                $html .= $module->getPageHTML()."\r\n";
            }else{
                foreach($module->modules as $mod){
                    $html .= $mod->getPageHTML()."\r\n";
                }
            }

        }
        return $html;
    }

    public function getModJsHTML(){
        $html = "var modJsList = new Array();\r\n";
        $activeModule = "";
        foreach($this->modules as $module){
            if(get_class($module) == "ModuleTab"){
                $html .= $module->getJSObjectCode()."\r\n";
                if($module->isActive){
                    $activeModule =  $module->name;
                }
            }else{

                foreach($module->modules as $mod){
                    if($module->isActive && $activeModule == ""){
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

class ModuleTab{
    public $name;
    var $class;
    var $label;
    var $adapterName;
    var $filter;
    var $orderBy;
    public $isActive = false;
    public $isInsideGroup = false;
    var $options = array();

    public function __construct($name, $class, $label, $adapterName, $filter, $orderBy, $isActive = false, $options = array()){
        $this->name = $name;
        $this->class = $class;
        $this->label = $label;
        $this->adapterName = $adapterName;
        $this->filter = $filter;
        $this->orderBy = $orderBy;
        $this->isActive = $isActive;
        $this->options = $options;
    }

    public function getHTML(){
        $active = ($this->isActive)?"active":"";
        if(!$this->isInsideGroup) {
            return '<li class="' . $active . '"><a id="tab' . $this->name . '" href="#tabPage' . $this->name . '">' . $this->label . '</a></li>';
        }else{
            return '<li class="' . $active . '"><a id="tab' . $this->name . '" href="#tabPage' . $this->name . '">' . $this->label . '</a></li>';
        }
    }

    public function getPageHTML(){
        $active = ($this->isActive)?" active":"";
        $html = '<div class="tab-pane'.$active.'" id="tabPage'.$this->name.'">'.
			'<div id="'.$this->name.'" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>'.
			'<div id="'.$this->name.'Form" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;"></div>'.
            '</div>';

        return $html;
    }

    public function getJSObjectCode()
    {
        $js = '';
        if (empty($this->filter)) {
            $js.= "modJsList['tab" . $this->name . "'] = new " . $this->adapterName . "('" . $this->class . "','" . $this->name . "','','".$this->orderBy."');";
        } else {
            $js.= "modJsList['tab" . $this->name . "'] = new " . $this->adapterName . "('" . $this->class . "','" . $this->name . "'," . $this->filter . ",'".$this->orderBy."');";
        }

        foreach($this->options as $key => $val){
            $js.= "modJsList['tab" . $this->name . "'].".$key."(".$val.");";
        }

        return $js;
    }

}

class ModuleTabGroup{
    var $name;
    var $label;
    var $isActive = false;
    public $modules = array();

    public function __construct($name, $label){
        $this->name = $name;
        $this->label = $label;
    }

    public function addModuleTab($moduleTab){
        if($moduleTab->isActive){
            $this->isActive = true;
            $moduleTab->isActive = false;
        }
        $moduleTab->isInsideGroup = true;
        $this->modules[] = $moduleTab;
    }

    public function getHTML(){
        $html = "";
        $active = ($this->isActive)?" active":"";

        $html.= '<li class="dropdown'.$active.'">'."\r\n".
                 '<a href="#" id="'.$this->name.'" class="dropdown-toggle" data-toggle="dropdown" aria-controls="'.$this->name.'-contents">'.$this->label.' <span class="caret"></span></a>'."\r\n".
			        '<ul class="dropdown-menu" role="menu" aria-labelledby="'.$this->name.'" id="'.$this->name.'-contents">';

        foreach($this->modules as $module){
            $html.= $module->getHTML();
        }

        $html .= "</ul></li>";

        return $html;

    }
}

