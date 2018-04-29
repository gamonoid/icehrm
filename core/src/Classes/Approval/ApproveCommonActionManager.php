<?php
namespace Classes\Approval;

use Classes\IceResponse;
use Classes\StatusChangeLogManager;
use Classes\SubActionManager;

abstract class ApproveCommonActionManager extends SubActionManager
{

    public function getLogs($req)
    {

        $class = $this->getModelClass();
        $logs = StatusChangeLogManager::getInstance()->getLogs($class, $req->id);
        return new IceResponse(IceResponse::SUCCESS, $logs);
    }
}
