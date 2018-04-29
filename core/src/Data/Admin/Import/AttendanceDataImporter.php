<?php
namespace Data\Admin\Import;

use Attendance\Common\Model\Attendance;
use Data\Admin\Api\AbstractDataImporter;

class AttendanceDataImporter extends AbstractDataImporter
{

    protected $processed = array();

    public function getModelObject()
    {
        return "\\Attendance\\Common\\Model\\Attendance";
    }

    public function fixBeforeSave($object, $data)
    {
        return $object;
    }

    public function isDuplicate($obj)
    {
        $old = new Attendance();
        $data = $old->Find(
            "employee = ? and in_time = ? and out_time = ?",
            array($obj->employee, $obj->in_time, $obj->out_time)
        );

        if (count($data)>0) {
            return true;
        }

        return false;
    }
}
