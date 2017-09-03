<?php
namespace Data\Admin\Import;

use Data\Admin\Api\AbstractDataImporter;

class EmployeeDataImporter extends AbstractDataImporter
{

    var $processed = array();

    public function getModelObject()
    {
        return "\\Employees\\Common\\Model\\Employee";
    }

    public function fixBeforeSave($object, $data)
    {

        if (empty($object->status)) {
            $object->status = "Active";
        }

        return $object;
    }
}
