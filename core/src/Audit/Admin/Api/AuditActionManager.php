<?php
namespace Audit\Admin\Api;

use Classes\SubActionManager;
use Model\Audit;
use Utils\LogManager;

class AuditActionManager extends SubActionManager
{

    public function addAudit($type, $data)
    {

        $audit = new Audit();
        $audit->user = $this->user->id;
        $audit->ip = $_SERVER['REMOTE_ADDR'];
        $audit->time = date("Y-m-d H:i:s");
        $audit->time = gmdate('Y-m-d H:i:s', strtotime($audit->time));
        $audit->type = $type;
        $audit->details = $data;

        $currentEmpId = $this->getCurrentProfileId();
        if (!empty($currentEmpId)) {
            $employee = $this->baseService->getElement('Employee', $this->getCurrentProfileId(), null, true);
            $audit->employee = $employee->first_name." ".$employee->last_name." [EmpId = ".$employee->employee_id."]";
        }

        $ok = $audit->Save();
        if (!$ok) {
            LogManager::getInstance()->info("Error adding audit:".$audit->ErrorMsg());
        }
    }
}
