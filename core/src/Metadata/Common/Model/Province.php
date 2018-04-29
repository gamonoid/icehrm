<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:02 PM
 */

namespace Metadata\Common\Model;

use Model\BaseModel;

class Province extends BaseModel
{
    public $table = 'Province';

    public function getAdminAccess()
    {
        return array("get", "element", "save", "delete");
    }

    public function getUserAccess()
    {
        return array();
    }

    public function getAnonymousAccess()
    {
        return array("get", "element");
    }
}
