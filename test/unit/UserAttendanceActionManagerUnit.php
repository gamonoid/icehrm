<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 9/24/17
 * Time: 2:19 PM
 */

namespace unit;

use Attendance\User\Api\AttendanceActionManager;
use Classes\BaseService;

class UserAttendanceActionManagerUnit extends \TestTemplate
{

    protected $actionMgr;
    protected function setUp()
    {
        parent::setUp();
        $this->actionMgr = new AttendanceActionManager();
        $this->actionMgr->setBaseService(BaseService::getInstance());
    }

    public function testTran()
    {

        $punch = $this->actionMgr->getPunch(['date' => date('Y-m-d H:i:s')]);
        self::assertEquals('SUCCESS', $punch->getStatus());
    }
}
