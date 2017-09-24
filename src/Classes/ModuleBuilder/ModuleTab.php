<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:47 AM
 */

namespace Classes\ModuleBuilder;

class ModuleTab
{
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
        $name,
        $class,
        $label,
        $adapterName,
        $filter,
        $orderBy,
        $isActive = false,
        $options = array()
    ) {
        $this->name = $name;
        $this->class = $class;
        $this->label = $label;
        $this->adapterName = $adapterName;
        $this->filter = $filter;
        $this->orderBy = $orderBy;
        $this->isActive = $isActive;
        $this->options = $options;
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
            '<div id="'.$this->name.'" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>'.
            '<div id="'.$this->name.
            'Form" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;"></div>'.
            '</div>';

        return $html;
    }

    public function getJSObjectCode()
    {
        $js = '';
        if (empty($this->filter)) {
            $js.= "modJsList['tab" . $this->name . "'] = new " .
                $this->adapterName . "('" . $this->class . "','" . $this->name . "','','".$this->orderBy."');";
        } else {
            $js.= "modJsList['tab" . $this->name . "'] = new " .
                $this->adapterName . "('" . $this->class . "','" . $this->name . "'," .
                $this->filter . ",'".$this->orderBy."');";
        }

        foreach ($this->options as $key => $val) {
            $js.= "modJsList['tab" . $this->name . "'].".$key."(".$val.");";
        }

        return $js;
    }
}
