<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/21/17
 * Time: 2:36 AM
 */

namespace Model;

class Cron extends BaseModel
{
    public $table = 'Crons';

    public function getAdminAccess()
    {
        return array();
    }

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }
}
