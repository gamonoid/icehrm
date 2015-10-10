<?php 
/*
This file is part of Ice Framework.

Ice Framework is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Ice Framework is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ice Framework. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */
define('CLIENT_PATH',dirname(__FILE__));
include ("config.base.php");
include ("include.common.php");


$modulePath = SessionUtils::getSessionObject("modulePath");
if(!defined('MODULE_PATH')){
	define('MODULE_PATH',$modulePath);
}

include("server.includes.inc.php");

if($_REQUEST['a'] != "rsp" && $_REQUEST['a'] != "rpc"){
	if(empty($user)){
		$ret['status'] = "ERROR";
		echo json_encode($ret);
		exit();
	}
}


$action = $_REQUEST['a'];
if($action == 'get'){
	$_REQUEST['sm'] = BaseService::getInstance()->fixJSON($_REQUEST['sm']);
	$_REQUEST['ft'] = BaseService::getInstance()->fixJSON($_REQUEST['ft']);
	$ret['object'] = BaseService::getInstance()->get($_REQUEST['t'],$_REQUEST['sm'],$_REQUEST['ft'],$_REQUEST['ob']);
	$ret['status'] = "SUCCESS";
	
}else if($action == 'getElement'){
	$_REQUEST['sm'] = BaseService::getInstance()->fixJSON($_REQUEST['sm']);
	$ret['object'] = BaseService::getInstance()->getElement($_REQUEST['t'],$_REQUEST['id'],$_REQUEST['sm']);
	if(!empty($ret['object'])){
		$ret['status'] = "SUCCESS";	
	}else{
		$ret['status'] = "ERROR";
	}
}else if($action == 'add'){
	if($_REQUEST['t'] == "Report"){
		$data = $reportHandler->handleReport($_REQUEST);
		$ret['status'] = $data[0];
		$ret['object'] = $data[1];
	}else{
		$resp = BaseService::getInstance()->addElement($_REQUEST['t'],$_REQUEST);
		$ret['object'] = $resp->getData();
		$ret['status'] = $resp->getStatus();
	}
		
	
}else if($action == 'delete'){
	$ret['object'] = BaseService::getInstance()->deleteElement($_REQUEST['t'],$_REQUEST['id']);
	if($ret['object'] == null){
		$ret['status'] = "SUCCESS";	
	}else{
		$ret['status'] = "ERROR";
	}
	
}else if($action == 'getFieldValues'){
	$ret['data'] = BaseService::getInstance()->getFieldValues($_REQUEST['t'], $_REQUEST['key'], $_REQUEST['value'],$_REQUEST['method'],$_REQUEST['methodParams']);
	if($ret['data'] != null){	
		$ret['status'] = "SUCCESS";	
	}else{
		$ret['status'] = "ERROR";
	}

}else if($action == 'setAdminEmp'){
	BaseService::getInstance()->setCurrentAdminProfile($_REQUEST['empid']);	
	$ret['status'] = "SUCCESS";	
	
}else if($action == 'ca'){
	if(isset($_REQUEST['req'])){
		$_REQUEST['req'] = BaseService::getInstance()->fixJSON($_REQUEST['req']);
	}
	$mod = $_REQUEST['mod'];
	$modPath = explode("=", $mod);
	$moduleCapsName = ucfirst($modPath[1]);
	
	$subAction = $_REQUEST['sa'];
	$apiFile = APP_BASE_PATH.$modPath[0]."/".$modPath[1]."/api/".$moduleCapsName."ActionManager.php";
	LogManager::getInstance()->info("Api File:".$apiFile);
	$emailSenderFile = APP_BASE_PATH.$modPath[0]."/".$modPath[1]."/api/".$moduleCapsName."EmailSender.php";
	if(file_exists($apiFile)){
		include ($apiFile);
		if(file_exists($emailSenderFile)){
			include ($emailSenderFile);	
		}
		
		$cls = $moduleCapsName."ActionManager";
		$apiClass = new $cls();
		$apiClass->setUser($user);
		$apiClass->setBaseService($baseService);
		$apiClass->setEmailSender($baseService->getEmailSender());
		
		if(isset($_REQUEST['req'])){
			$req = json_decode($_REQUEST['req']);
		}else{
			$req = new stdClass();
		}
		foreach ($_REQUEST as $k=>$v){
			if($k != 'mod' && $k != 'sa' && $k != 'a' && $k != 't' && $k != 'req'){
				if(!isset($req->$k)){
					$req->$k = $v;
				}
			}
		}
		$res = $apiClass->$subAction($req);
		$ret = $res->getJsonArray();
	}else{
		$ret = array("status"=>"ERROR");
		LogManager::getInstance()->info("File dosen't exist :".$apiFile);	
	}	
	
}else if($action == 'file'){
	$name = $_REQUEST['name'];
	$file = new File();
	$file->Load("name =?",array($name));
	$ret = array();
	if($file->name == $name){
		$ret['status'] = "SUCCESS";
		if(SettingsManager::getInstance()->getSetting("Files: Upload Files to S3") == '1'){
			$uploadFilesToS3Key = SettingsManager::getInstance()->getSetting("Files: Amazon S3 Key for File Upload");
			$uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting("Files: Amazone S3 Secret for File Upload");
			$s3FileSys = new S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
			$s3WebUrl = SettingsManager::getInstance()->getSetting("Files: S3 Web Url");
			$fileUrl = $s3WebUrl.CLIENT_NAME."/".$file->filename;
			$fileUrl = $s3FileSys->generateExpiringURL($fileUrl);
			$file->filename = $fileUrl;
		}
		$ret['data']=$file;
	}else{
		$ret['status'] = "ERROR";
	}
}else if($action == 'download'){
	$fileName = $_REQUEST['file'];
	$fileName = str_replace("..","",$fileName);
	$fileName = str_replace("/","",$fileName);
	$fileName = CLIENT_BASE_PATH.'data/'.$fileName;
	if(!file_exists($fileName)){
		exit;	
	}
	header('Content-Description: File Transfer');
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename='.basename($fileName));
	header('Content-Transfer-Encoding: binary');
	header('Expires: 0');
	header('Cache-Control: must-revalidate');
	header('Pragma: public');
	header('Content-Length: ' . filesize($fileName));
	ob_clean();
	flush();
	readfile($fileName);
	exit;
	
}else if($action == 'rsp'){ // linked clicked from password change email
	$user = new User();
	if(!empty($_REQUEST['key'])){
		$arr = explode("-", $_REQUEST['key']);
		$userId = $arr[0];
		$keyArr = array_shift($arr);
		if(count($keyArr) > 1){
			$key = implode("-", $arr);
		}else{
			$key = $arr[0];
		}
		
		$user->Load("id = ?",array($userId));
		if(!empty($user->id)){
			LogManager::getInstance()->info("Key : ".$key);
			$data = AesCtr::decrypt($key, $user->password, 256);
			if(empty($data)){
				$ret['status'] = "ERROR";
				$ret['message'] = "Invalid Key for changing password, error decrypting data";
			}else{
				$data = json_decode($data,true);
				if($data['CLIENT_NAME'] != CLIENT_NAME || $data['email'] != $user->email){
					$ret['status'] = "ERROR";
					$ret['message'] = "Invalid Key for changing password, keys do not match";
				}else{
					if(empty($_REQUEST['now'])){
						LogManager::getInstance()->info("now not defined");
						header("Location:".CLIENT_BASE_URL."login.php?cp=1&key=".$_REQUEST['key']);
					}else{
						if(!empty($_REQUEST['pwd'])){
							if(strlen($_REQUEST['pwd']) >= 6){
								$user->password = md5($_REQUEST['pwd']);
								$user->Save();
								LogManager::getInstance()->info("user password changed");
								$ret['status'] = "SUCCESS";
							}else{
								$ret['status'] = "ERROR";
								$ret['message'] = "Password may contain only letters, numbers and should be longer than 6 characters";
							}
						}
						
					}
					
				}	
			}
			
			
		}else{
			$ret['status'] = "ERROR";
				$ret['message'] = "Invalid Key for changing password, user not found";
		}
		
		
	}else{
		
	}
	
}else if($action == 'rpc'){	
	if($emailSender->sendResetPasswordEmail($_REQUEST['id'])){
		$ret['status'] = "SUCCESS";
		$ret['message'] = "An email has been sent to you with instructions for changing password";
	}else{
		$ret['status'] = "ERROR";
		$ret['message'] = "You have entered an incorrect email or user id";
	}
	
}else if($action == 'getNotifications'){	
	$ret['data'] = $notificationManager->getLatestNotificationsAndCounts($user->id);
	$ret['status'] = "SUCCESS";
	
}else if($action == 'clearNotifications'){	
	$notificationManager->clearNotifications($user->id);
	$ret['status'] = "SUCCESS";
	
}else if($action == 'verifyInstance'){	
	$key = $_POST['key'];
	if(empty($key)){
		$ret['status'] = "ERROR";
		$ret['message'] = "Instance key not found";
	}else{
		$baseService->setInstanceKey($key);
		if($baseService->validateInstance()){
			$ret['status'] = "SUCCESS";
		}else{
			$ret['status'] = "ERROR";
			$ret['message'] = "Error Verifying IceHrm Instance due to invalid key. If you are keep getting this, please contact us through ".CONTACT_EMAIL;
		}
	}
	
}

echo json_encode($ret);