<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/21/17
 * Time: 2:32 AM
 */

namespace Model;

class File extends BaseModel
{
    public $table = 'Files';
    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getUserAccess()
    {
        return array();
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

}
