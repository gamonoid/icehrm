<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/21/17
 * Time: 2:35 AM
 */

namespace Model;

class Audit extends BaseModel
{
    public $table = 'AuditLog';

    public function getManagerAccess()
    {
        return array();
    }

    public function getUserAccess()
    {
        return array();
    }
}
