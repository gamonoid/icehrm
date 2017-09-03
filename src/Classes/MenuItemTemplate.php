<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/20/17
 * Time: 9:51 AM
 */

namespace Classes;

class MenuItemTemplate
{

    public $templateName;
    public $params;

    public function __construct($templateName, $params)
    {
        $this->templateName = $templateName;
        $this->params = $params;
    }

    public function getHtml()
    {
        return UIManager::getInstance()->populateTemplate($this->templateName, 'menu', $this->params);
    }
}
