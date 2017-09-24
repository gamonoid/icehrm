<?php
/*

Copyright 2017 Thilina Hasantha (thilina@gamonoid.com | http://lk.linkedin.com/in/thilinah)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions
of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED
TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
IN THE SOFTWARE.

 */

namespace Classes\Approval;

use Classes\BaseService;
use Classes\IceResponse;
use Employees\Common\Model\Employee;
use Employees\Common\Model\EmployeeApproval;
use Model\BaseModel;
use Utils\LogManager;

class ApprovalStatus
{

    const APP_ST_APPROVED = 1;
    const APP_ST_REJECTED = 0;

    private static $me = null;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$me)) {
            self::$me = new ApprovalStatus();
        }

        return self::$me;
    }

    public function isDirectApproval($employeeId)
    {
        $emp = new Employee();
        $emp->Load("id = ?", array($employeeId));
        if (empty($emp->approver1) && empty($emp->approver2) && empty($emp->approver3)) {
            return true;
        }
        return false;
    }

    public function getResolvedStatuses($type, $id)
    {
        $employeeApproval = new EmployeeApproval();
        $eas = $employeeApproval->Find("type = ? and element = ? and status > -1 order by level", array($type, $id));
        return $eas;
    }

    public function approvalChainExists($type, $id)
    {
        $list = $this->getAllStatuses($type, $id);
        return count($list) > 0;
    }

    public function getAllStatuses($type, $id)
    {
        $employeeApproval = new EmployeeApproval();
        $eas = $employeeApproval->Find("type = ? and element = ? order by level", array($type, $id));
        return $eas;
    }

    public function initializeApprovalChain($type, $id)
    {
        /* @var \Model\BaseModel $element */
        $nsClass = BaseService::getInstance()->getFullQualifiedModelClassName($type);
        $element = new $nsClass();
        $element->Load("id = ?", array($id));
        $employeeId = $element->employee;

        for ($i = 1; $i < 4; $i++) {
            $approver = $this->getApproverByLevel($i, $employeeId);
            if (!empty($approver)) {
                $employeeApproval = new EmployeeApproval();
                $employeeApproval->type = $type;
                $employeeApproval->element = $id;
                $employeeApproval->approver = $approver;
                $employeeApproval->level = $i;
                $employeeApproval->status = -1;
                $employeeApproval->active = 0;
                $employeeApproval->created = date("Y-m-d H:i:s");
                $employeeApproval->updated = date("Y-m-d H:i:s");
                $ok = $employeeApproval->Save();
                if (!$ok) {
                    LogManager::getInstance()->error("Error:".$employeeApproval->ErrorMsg());
                }
            } else {
                LogManager::getInstance()->error("Approver is empty level:".$i);
            }
        }
    }

    public function updateApprovalStatus($type, $id, $currentEmployee, $status)
    {
        if (!$this->approvalChainExists($type, $id)) {
            return new IceResponse(IceResponse::SUCCESS, array(null, null));
        }

        if ($status != 0 && $status != 1) {
            return new IceResponse(IceResponse::ERROR, "Invalid data");
        }
        /* @var BaseModel $element */
        $nsClass = BaseService::getInstance()->getFullQualifiedModelClassName($type);
        $element = new $nsClass();
        $element->Load("id = ?", array($id));

        $eas = $this->getAllStatuses($type, $id);
        $level = 0;
        //check if the element is already rejected
        foreach ($eas as $ea) {
            if ($ea->status == 0) {
                return new IceResponse(IceResponse::ERROR, "This item is already rejected");
            } elseif ($ea->active == 1) {
                $level = intval($ea->level);
            }
        }

        $currentAL = null;
        if ($level > 0) {
            $currentAL = new EmployeeApproval();
            $currentAL->Load("type = ? and element = ? and level = ?", array($type, $id, $level));
        }

        $nextAL = null;
        if ($level < 3) {
            $nextAL = new EmployeeApproval();
            $nextAL->Load("type = ? and element = ? and level = ?", array($type, $id, intval($level)+1));

            if (empty($nextAL->id)) {
                $nextAL = null;
            }
        }

        //Check if the current employee is allowed to approve
        if ($level > 0 && $currentEmployee != $currentAL->approver) {
            return new IceResponse(IceResponse::ERROR, "You are not allowed to approve or reject");
        }

        if (!empty($currentAL)) {
            //Now mark the approval status
            $currentAL->status = $status;
            $currentAL->Save();
        }

        if (!empty($nextAL)) {
            /* @var EmployeeApproval $ea */
            foreach ($eas as $ea) {
                if ($ea->id == $nextAL->id) {
                    $nextAL->active = 1;
                    $nextAL->Save();
                } else {
                    $ea->active = 0;
                    $ea->Save();
                }
            }
        }

        if (!empty($currentAL)) {
            $oldCurrAlId = $currentAL->id;
            $currentAL = new EmployeeApproval();
            $currentAL->Load("id = ?", array($oldCurrAlId));
        }

        return new IceResponse(IceResponse::SUCCESS, array($currentAL, $nextAL));
    }

    /**
     * @param $level
     * @param $employeeId
     * @return int $approverId
     */
    private function getApproverByLevel($level, $employeeId)
    {
        $emp = new Employee();
        $emp->Load("id = ?", array($employeeId));
        $approver = null;
        $alevel = "approver".$level;
        return $emp->$alevel;
    }
}
