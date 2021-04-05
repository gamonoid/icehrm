<?php

namespace Model;

trait CustomFieldTrait
{
    public function getObjectName()
    {
        return $this->objectName;
    }

    public function isCustomFieldsEnabled()
    {
        return $this->allowCustomFields;
    }
}