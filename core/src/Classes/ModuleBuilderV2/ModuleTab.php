<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:47 AM
 */

namespace Classes\ModuleBuilderV2;

class ModuleTab
{
    public $modelPath;
    public $name;
    public $class;
    public $label;
    public $adapterName;
    public $filter;
    public $orderBy;
    public $isActive = false;
    public $isInsideGroup = false;
    public $options = array();

    public function __construct(
        $modelPath,
        $name,
        $class,
        $label,
        $adapterName,
        $filter,
        $orderBy,
        $isActive = false,
        $options = array()
    ) {
    
        $this->modelPath = $modelPath;
        $this->name = $name;
        $this->class = $class;
        $this->label = $label;
        $this->adapterName = $adapterName;
        $this->filter = $filter;
        $this->orderBy = $orderBy;
        $this->isActive = $isActive;

        $this->options = array_merge(
            $options,
            [
            "setObjectTypeName" => "'{$this->name}'",
            "setAccess" => "data.permissions.{$this->name} ? data.permissions.{$this->name} : {}",
            "setDataPipe" => 'new IceDataPipe(modJsList.tab' . $this->name . ')',
            "setRemoteTable" => true,
            ]
        );
    }

    public function getHTML()
    {
        $active = ($this->isActive)?"active":"";
        if (!$this->isInsideGroup) {
            return '<li class="' . $active . '"><a id="tab' . $this->name
            . '" href="#tabPage' . $this->name . '">' . t($this->label) . '</a></li>';
        } else {
            return '<li class="' . $active . '"><a id="tab' . $this->name
            . '" href="#tabPage' . $this->name . '">' . t($this->label) . '</a></li>';
        }
    }

    public function getPageHTML()
    {
        $active = ($this->isActive)?" active":"";
        $html = '<div class="tab-pane'.$active.'" id="tabPage'.$this->name.'">'.
            '<div id="'.$this->name.'Table" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>'.
            '<div id="'.$this->name.'Form"></div>'.
            '<div id="'.$this->name.'FilterForm"></div>'.
            '</div>';

        return $html;
    }

    public function getJSObjectCode()
    {
        $js = "";
        if (empty($this->filter)) {
            $js.= "modJsList['tab" . $this->name . "'] = new " .
                $this->adapterName . "('" . $this->class . "','" . $this->name . "','','".$this->orderBy. "');\r\n";
        } else {
            $js.= "modJsList['tab" . $this->name . "'] = new " .
                $this->adapterName . "('" . $this->class . "','" . $this->name . "'," .
                $this->filter . ",'".$this->orderBy. "');\r\n";
        }

        foreach ($this->options as $key => $val) {
            $js.= "modJsList['tab" . $this->name . "'].".$key."(".$val. ");\r\n";
        }

        return $js;
    }
}
