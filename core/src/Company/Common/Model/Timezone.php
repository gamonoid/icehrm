<?php
namespace Company\Common\Model;

use Model\BaseModel;

class Timezone extends BaseModel
{
    public $table = 'Timezones';

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
        return array("get","element");
    }
}
