<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/21/17
 * Time: 2:35 AM
 */

namespace Model;

class Notification extends BaseModel
{
    public $table = 'Notifications';

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }
}
