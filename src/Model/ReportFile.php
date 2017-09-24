<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/21/17
 * Time: 2:34 AM
 */

namespace Model;

class ReportFile extends BaseModel
{
    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserOnlyMeAccess()
    {
        return array("get","element","delete");
    }

    public function getUserAccess()
    {
        return array("get","element");
    }

    public function postProcessGetData($entry)
    {
        $data = explode(".", $entry->name);
        $entry->icon = '<img src="'.BASE_URL.'images/file-icons/'.strtolower($data[count($data)-1]).".png".'"/>';
        return $entry;
    }

    public $table = 'ReportFiles';
}
