<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 1/7/18
 * Time: 6:29 AM
 */

namespace Leaves\Admin\Api;

use Classes\AbstractInitialize;
use Classes\IceResponse;
use Leaves\Common\Model\LeavePeriod;
use Utils\CalendarTools;
use Utils\LogManager;

class LeaveInitializer extends AbstractInitialize
{
    /* @var LeavesActionManager $leaveActionManager*/
    protected $leaveActionManager;

    public function init()
    {
        if (!defined('MODULE_NAME') || MODULE_NAME !== 'leaves') {
            return;
        }

        if (LeaveUtil::isCountryBasedLeavePeriodsEnabled()) {
            return;
        }

        for ($i = 0; $i < 5; $i++) {
            $resp = LeaveUtil::getCurrentLeavePeriod(date('Y-m-d H:i:s'), date('Y-m-d H:i:s'));
            if ($resp->getStatus() === IceResponse::ERROR) {
                $lastLeavePeriod = LeaveUtil::getLastLeavePeriod();
                $leavePeriod = $this->buildCurrentLeavePeriod($lastLeavePeriod);
                $ok = $leavePeriod->Save();
                if (!$ok) {
                    LogManager::getInstance()->error("Error creating leave period :". $leavePeriod->ErrorMsg());
                }
            } else {
                break;
            }
        }
    }

    protected function buildCurrentLeavePeriod($lastLeavePeriod)
    {
        $startDate = null;
        $endDate = null;
        if ($lastLeavePeriod === null) {
            $startDate = date('Y-01-01');
            $endDate = date('Y-12-31');
        } else {
            $startDate = CalendarTools::addMonthsToDateTime($lastLeavePeriod->date_start, 12, 'Y-m-d');
            $endDate = CalendarTools::addMonthsToDateTime($lastLeavePeriod->date_end, 12, 'Y-m-d');
        }

        $leavePeriod = new LeavePeriod();
        $leavePeriod->date_start = $startDate;
        $leavePeriod->date_end = $endDate;
        $leavePeriod->name = 'Period '.$startDate.' to '.$endDate;
        $leavePeriod->status = 'Active';

        return $leavePeriod;
    }

    /**
     * @return mixed
     */
    public function getLeaveActionManager()
    {
        return $this->leaveActionManager;
    }

    /**
     * @param mixed $leaveActionManager
     */
    public function setLeaveActionManager($leaveActionManager)
    {
        $this->leaveActionManager = $leaveActionManager;
    }
}
