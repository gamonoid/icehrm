<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 12:28 PM
 */

namespace FieldNames\Common\Model;

use Model\BaseModel;

class FieldNameMapping extends BaseModel
{
    public $table = 'FieldNameMappings';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getAnonymousAccess()
    {
        return array("get","element");
    }
}
