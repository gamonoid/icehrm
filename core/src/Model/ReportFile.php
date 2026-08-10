<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/21/17
 * Time: 2:34 AM
 */

namespace Model;

use Classes\ModuleAccess;

class ReportFile extends BaseModel
{
    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    /**

     * No module grants Manager access to this model (module meta.json user_levels),

     * so no manager-facing screen reads it. The inherited BaseModel default

     * would expose the whole table on the generic service.php path.

     */

    public function getManagerAccess()

    {

        return array();

    }

    public function getUserOnlyMeAccess()
    {
        return array("get","element","delete");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('report_files', 'admin'),
        ];
    }

    public function postProcessGetData($entry)
    {
        $data = explode(".", $entry->name);
        $entry->icon = '<img src="'.BASE_URL.'images/file-icons/'.strtolower($data[count($data)-1]).".png".'"/>';
        return $entry;
    }

    public $table = 'ReportFiles';
}
