<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/21/17
 * Time: 2:34 AM
 */

namespace Model;

class Report extends BaseModel
{
    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getManagerAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function postProcessGetData($entry)
    {
        $entry->icon = '<img src="'.BASE_URL.'images/file-icons/'.strtolower($entry->output).".png".'"/>';
        return $entry;
    }

    public function getCustomFilterQuery($filter)
    {
        $filter = json_decode($filter, true);
        if ($filter['type'] === 'Reports') {
            $query = ' and report_group <> ?';
        } elseif ($filter['type'] === 'Exports') {
            $query = ' and report_group = ?';
        } else {
            $query = '';
        }

        $queryData = array('Payroll');

        return array($query, $queryData);
    }

    public $table = 'Reports';
}
