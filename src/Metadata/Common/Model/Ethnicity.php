<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 3:07 PM
 */

namespace Metadata\Common\Model;

use Model\BaseModel;

class Ethnicity extends BaseModel
{
    public $table = 'Ethnicity';

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
