<?php

namespace Model;

class SystemData extends BaseModel
{
    public $table = 'SystemData';

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
