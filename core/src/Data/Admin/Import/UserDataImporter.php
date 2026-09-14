<?php

namespace Data\Admin\Import;

use Data\Admin\Api\AbstractDataImporter;

class UserDataImporter extends AbstractDataImporter
{

    protected $processed = array();

    public function getModelObject()
    {
        return "\\Users\\Common\\Model\\User";
    }

    public function getModelObjectName()
    {
        return 'User';
    }

    public function fixBeforeSave($object, $data)
    {
        if (empty($object->password)) {
            $object->password = \Classes\PasswordManager::createPasswordHash($object->password);
        }

        return $object;
    }
}
