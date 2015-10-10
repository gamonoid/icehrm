<?php
class NotificationManager{
	
	var $baseService;
	
	public function setBaseService($baseService){
		$this->baseService = $baseService;
	}
	
	public function addNotification($toEmployee, $message, $action, $type, $toUserId = null, $fromSystem = false){
		
		$userEmp = new User();
		
		if(!empty($toEmployee)){
			$userEmp->load("employee = ?",array($toEmployee));
			
			if(!empty($userEmp->employee) && $userEmp->employee == $toEmployee){
				$toUser = $userEmp->id;
			}else{
				return;
			}	
		}else if(!empty($toUserId)){
			$toUser = $toUserId;
		}
		
		
		$noti = new Notification();
		if($fromSystem){
			$noti->fromUser = 0;
			$noti->fromEmployee = 0;
			$noti->image = BASE_URL."images/icehrm.png";
		}else{
			$user = $this->baseService->getCurrentUser();
			$noti->fromUser = $user->id;
			$noti->fromEmployee = $user->employee;
		}
		
		$noti->toUser = $toUser;
		$noti->message = $message;
		
		if(!empty($noti->fromEmployee) && $noti->fromEmployee != 0){
			$employee = $this->baseService->getElement('Employee',$noti->fromEmployee,null,true);
			if(!empty($employee)){
				$employee = FileService::getInstance()->updateProfileImage($employee);
				$noti->image = $employee->image;
			}
		}
		
		if(empty($noti->image)){
			if($employee->gender == 'Male'){
				$noti->image = BASE_URL."images/user_male.png";
			}else{
				$noti->image = BASE_URL."images/user_female.png";
			}
			
		}

		$noti->action = $action;
		$noti->type = $type;
		$noti->time = date('Y-m-d H:i:s');
		$noti->status = 'Unread';
		
		$ok = $noti->Save();
		if(!$ok){
			error_log("Error adding notification: ".$noti->ErrorMsg());
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
	
	public function getNotificationByTypeAndDate($type, $date){
		$noti = new Notification();
		$noti->Load("date(time) = ? and type = ?",array($date,$type));	
		if(!empty($noti->id) && $noti->type = $type){
			return $noti;
		}
		return null;
	}
	
	public function getLatestNotificationsAndCounts($userId){
		$notification = new Notification();	
		
		$listUnread = $notification->Find("toUser = ? and status = ?",array($userId,'Unread'));
		$unreadCount = count($listUnread);
		
		$limit = ($unreadCount < 10)?10:$unreadCount;
		
		$list = $notification->Find("toUser = ? order by time desc limit ?",array($userId,$limit));
		
		$newList = array();
		$fs = FileService::getInstance();
		
		foreach($list as $noti){
			if($noti->fromEmployee > 0){
				$employee = $this->baseService->getElement('Employee',$noti->fromEmployee,null,true);
				if(!empty($employee)){
					$employee = $fs->updateProfileImage($employee);
					$noti->image = $employee->image;
					
					if(empty($noti->image)){
						if($employee->gender == 'Male'){
							$noti->image = BASE_URL."images/user_male.png";
						}else{
							$noti->image = BASE_URL."images/user_female.png";
						}
							
					}
					$newList[] = $noti;
				}	
			}else{
				$noti->image = BASE_URL."images/icehrm.png";
				$newList[] = $noti;
			}
				
		}
		
		return array($unreadCount, $list);

	}
	
}