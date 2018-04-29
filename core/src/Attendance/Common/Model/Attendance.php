<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/13/17
 * Time: 8:04 AM
 */

namespace Attendance\Common\Model;

use Model\BaseModel;

class Attendance extends BaseModel
{
    public $table = 'Attendance';

    public function getAdminAccess()
    {
        return array('get','element','save','delete');
    }

    public function getManagerAccess()
    {
        return array('get','element','save','delete');
    }

    public function getUserAccess()
    {
        return array('get');
    }

    public function getUserOnlyMeAccess()
    {
        return array('element','save','delete');
    }
}
