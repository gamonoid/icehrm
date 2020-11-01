<?php
namespace Dashboard\Admin\Rest;

use Classes\IceResponse;
use Classes\RestEndPoint;
use Classes\SystemTasks\SystemTasksService;
use Users\Common\Model\User;

class DashboardRestEndpoint extends RestEndPoint
{
    public function getTaskList(User $user)
    {
        $systemTasksService = SystemTasksService::getInstance();
        //return new IceResponse(IceResponse::SUCCESS, []);
        return new IceResponse(IceResponse::SUCCESS, $systemTasksService->getAdminTasks());
    }
}
