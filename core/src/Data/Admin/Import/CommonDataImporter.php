<?php


namespace Data\Admin\Import;

use Classes\BaseService;
use Data\Admin\Api\AbstractDataImporter;

class CommonDataImporter extends AbstractDataImporter
{

    protected $processed = array();
    protected $modelObjectName;

    public function getModelObject()
    {
        return BaseService::getInstance()->getFullQualifiedModelClassName($this->modelObjectName);
    }

    public function getModelObjectName()
    {
        return $this->modelObjectName;
    }

    public function setModelObjectName($name)
    {
        return $this->modelObjectName = $name;
    }

    public function fixBeforeSave($object, $data)
    {
        return $object;
    }
}
