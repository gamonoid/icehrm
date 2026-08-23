<?php
/**
 * Created by PhpStorm.
 * User: Thilina
 * Date: 8/19/17
 * Time: 2:37 PM
 */

namespace Leaves\Common\Model;

use Classes\BaseService;
use Employees\Common\Model\EmployeeApproval;

class EmployeeLeaveApprove extends EmployeeLeave
{
    // @codingStandardsIgnoreStart
    public function Find($whereOrderBy, $bindarr = false, $cache = false, $pkeysArr = false, $extra = array())
    {
        // @codingStandardsIgnoreEnd
        $currentEmployee = BaseService::getInstance()->getCurrentProfileId();
        $approveal = new EmployeeApproval();
        $approveals = $approveal->Find(
            "type = ? and approver = ? and status = -1 and active = 1",
            array('EmployeeLeave',$currentEmployee)
        );
        $ids = array();
        foreach ($approveals as $appr) {
            $ids[] = $appr->element;
        }

		if (empty($ids)) {
			return [];
		}

        $obj = new EmployeeLeave();
        $data = $obj->Find("id in (".implode(",", $ids).")", array());

        return $data;
    }
}
