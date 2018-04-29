<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 6:07 PM
 */

namespace Qualifications\Common\Model;

use Model\BaseModel;

class Certification extends BaseModel
{
    public $table = 'Certifications';

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
        return array();
    }
}
