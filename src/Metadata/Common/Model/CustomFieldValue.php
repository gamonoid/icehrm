<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:08 PM
 */

namespace Metadata\Common\Model;

use Model\BaseModel;

class CustomFieldValue extends BaseModel
{
    var $_table = 'CustomFieldValues';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array("get","element","save","delete");
    }

    public function getAnonymousAccess()
    {
        return array();
    }
}
