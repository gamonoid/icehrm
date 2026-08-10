<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/21/17
 * Time: 2:35 AM
 */

namespace Model;

class DataEntryBackup extends BaseModel
{
    public $table = 'DataEntryBackups';

    public function getUserAccess()
    {
        return array();
    }

    public function getUserOnlyMeAccess()
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
