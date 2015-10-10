<?php
class NotificationManager{
	
	var $baseService;
	
	public function setBaseService($baseService){
		$this->baseService = $baseService;
	}
	
	public function addNotification($toUser, $message, $action, $type){
		$profileVar = SIGN_IN_ELEMENT_MAPPING_FIELD_NAME;
		$profileClass = ucfirst(SIGN_IN_ELEMENT_MAPPING_FIELD_NAME);
		$userEmp = new User();
		$userEmp->load("profile = ?",array($toUser));
		
		if(!empty($userEmp->$profileVar) && $userEmp->$profileVar == $toUser){
			$toUser = $userEmp->id;
		}else{
			return;
		}
		
		$noti = new Notification();
		$user = $this->baseService->getCurrentUser();
		$noti->fromUser = $user->id;
		$noti->fromProfile = $user->$profileVar;
		$noti->toUser = $toUser;
		$noti->message = $message;
		
		if(!empty($noti->fromProfile)){
			$profile = $this->baseService->getElement($profileClass,$noti->fromProfile,null,true);
			if(!empty($profile)){
				$fs = new FileService();
				$profile = $fs->updateProfileImage($profile);
				$noti->image = $profile->image;
			}
		}
		
		if(empty($noti->image)){
			$noti->image = BASE_URL."images/user_male.png";	
		}

		$noti->action = $action;
		$noti->type = $type;
		$noti->time = date('Y-m-d H:i:s');
		$noti->status = 'Unread';
		
		$ok = $noti->Save();
		if(!$ok){
			LogManager::getInstance()->info("Error adding notification: ".$noti->ErrorMsg());
		}
	}
	
	public function clearNotifications($userId){
		$notification = new Notification();
		
		$listUnread = $notification->Find("toUser = ? and status = ?",array($userId,'Unread'));
		
		foreach($listUnread as $not){
			$not->status = "Read";
			$not->Save();	
		}
	}
	
	public function getLatestNotificationsAndCounts($userId){
		$notification = new Notification();	
		
		$listUnread = $notification->Find("toUser = ? and status = ?",array($userId,'Unread'));
		$unreadCount = count($listUnread);
		
		$limit = ($unreadCount < 20)?20:$unreadCount;
		
		$list = $notification->Find("toUser = ? order by time desc limit ?",array($userId,$limit));
		
		return array($unreadCount, $list);

	}
	
}