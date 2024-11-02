<?php

namespace Classes;

class NewsService
{
	protected $userId;
	public function __construct($userId)
	{
		$this->userId = $userId;
	}

	public function canShowNews( $newsId ) {
		$userMeta = UserMetaService::get($this->userId, 'news_dismiss_info');
		if ($userMeta === null ) {
			return true;
		}
		$newDismissInfo = $userMeta->meta_value;
		$newsDismiss = json_decode($newDismissInfo, true);
		if ( empty($newsDismiss) || empty($newsDismiss['time']) || empty($newsDismiss['id']) || $newsId != $newsDismiss['id'] ) {
			return true;
		}

		if ( time() > (int)$newsDismiss['time']  ) {
			return true;
		}

		return false;
	}

	public function newsDismissed($newsId, $period) {
		$newsData = [
			'id' => $newsId,
			'time' => time() + $period,
		];

		UserMetaService::add($this->userId, 'news_dismiss_info', json_encode($newsData));
	}
}
