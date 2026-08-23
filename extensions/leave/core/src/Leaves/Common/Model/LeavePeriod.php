<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:21 PM
 */

namespace Leaves\Common\Model;

use Classes\IceResponse;
use Classes\ModuleAccess;
use Leaves\Admin\Api\LeaveUtil;
use Model\BaseModel;

class LeavePeriod extends BaseModel
{
    public $table = 'LeavePeriods';

    public static function getCurrentLeavePeriod()
    {

        $leavePeriod = new LeavePeriod();
        $date = date("Y-m-d");
        $leavePeriodResp = LeaveUtil::getCurrentLeavePeriod($date, $date);
        if ($leavePeriodResp->getStatus() === IceResponse::SUCCESS) {
            return $leavePeriodResp;
        }
        return new IceResponse(IceResponse::ERROR, null);
    }

    public function getAdminAccess()
    {
        return array("get","element","add","save","delete");
    }

    public function getModuleAccess()
    {
        return [
            new ModuleAccess('leaves', 'admin'),
        ];
    }

    public function validateSave($obj)
    {
        $leavePeriod = new LeavePeriod();
        $leavePeriods = $leavePeriod->Find("1=1");

        if (strtotime($obj->date_end) <= strtotime($obj->date_start)) {
            return new IceResponse(IceResponse::ERROR, "Start date should be less than end date");
        }

        foreach ($leavePeriods as $lp) {
            if (!empty($obj->id) && $obj->id == $lp->id) {
                continue;
            }

            if (LeaveUtil::isCountryBasedLeavePeriodsEnabled() && $obj->country !== $lp->country) {
                continue;
            }

            if (strtotime($lp->date_end) >= strtotime($obj->date_end)
                && strtotime($lp->date_start) <= strtotime($obj->date_end)
            ) {
                //-1---0---1---0 || ---0--1---1---0
                return new IceResponse(
                    IceResponse::ERROR,
                    "Leave period is overlapping with an existing one"
                );
            } elseif (strtotime($lp->date_end) >= strtotime($obj->date_start)
                && strtotime($lp->date_start) <= strtotime($obj->date_start)
            ) {
                //---0---1---0---1 || ---0--1---1---0
                return new IceResponse(
                    IceResponse::ERROR,
                    "Leave period is overlapping with an existing one"
                );
            } elseif (strtotime($lp->date_end) <= strtotime($obj->date_end)
                && strtotime($lp->date_start) >= strtotime($obj->date_start)
            ) {
                //--1--0---0--1--
                return new IceResponse(
                    IceResponse::ERROR,
                    "Leave period is overlapping with an existing one"
                );
            }
        }
        return new IceResponse(IceResponse::SUCCESS, "");
    }

    /**
     * Columns this model's select boxes may request (see
     * BaseModel::fieldValueFields). Derived from the pickers that actually exist,
     * so this allows today's usage and nothing more.
     */
    public function fieldValueFields()
    {
        return array('id', 'name');
    }

}
