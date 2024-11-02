<?php
namespace Dashboard\Admin\Rest;

use Classes\IceResponse;
use Classes\NewsService;
use Classes\RestEndPoint;
use Classes\SystemTasks\SystemTasksService;
use Users\Common\Model\User;

class DashboardRestEndpoint extends RestEndPoint
{
    public function getTaskList(User $user)
    {
        $systemTasksService = SystemTasksService::getInstance();
        return new IceResponse(IceResponse::SUCCESS, $systemTasksService->getAdminTasks());
    }

	public function canShowNews(User $user, $id)
	{
		$newsService = new NewsService($user->id);
		return new IceResponse(IceResponse::SUCCESS, $newsService->canShowNews($id));
	}

	public function dismissNews(User $user)
	{
		$newsService = new NewsService($user->id);
		$body = $this->getRequestBody();
		$newsService->newsDismissed($body['id'], $body['period']);
		return new IceResponse(IceResponse::SUCCESS, true);
	}
}
