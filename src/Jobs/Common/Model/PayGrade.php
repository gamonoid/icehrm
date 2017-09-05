<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 12:53 PM
 */

namespace Jobs\Common\Model;

use Model\BaseModel;

class PayGrade extends BaseModel
{
    var $_table = 'PayGrades';

    public function getAdminAccess()
    {
        return array("get","element","save","delete");
    }

    public function getUserAccess()
    {
        return array();
    }
}
