<?php
if(!defined("AWS_REGION")){define('AWS_REGION','us-east-1');}
include(APP_BASE_PATH.'lib/Mail.php');
include(APP_BASE_PATH.'adodb512/adodb.inc.php');
include(APP_BASE_PATH.'adodb512/adodb-active-record.inc.php');
$ADODB_ASSOC_CASE = 2;

//detect admin and user modules
if(defined("MODULE_PATH")){
	$tArr = explode("/", MODULE_PATH);
	if(count($tArr) == 1){
		$tArr = explode("\\", MODULE_PATH);
	}
	if(!defined('MODULE_TYPE')){
		if(count($tArr) >= 2){
			define('MODULE_TYPE',$tArr[count($tArr)-2]);
		}else{
			define('MODULE_TYPE',"");
		}
	
	}	
}


$user = SessionUtils::getSessionObject('user');

include (APP_BASE_PATH."classes/BaseService.php");
include (APP_BASE_PATH."classes/CronUtils.php");
include (APP_BASE_PATH."classes/FileService.php");
include (APP_BASE_PATH."classes/SubActionManager.php");
include (APP_BASE_PATH."classes/AbstractInitialize.php");
include (APP_BASE_PATH."classes/AbstractModuleManager.php");
include (APP_BASE_PATH."classes/SettingsManager.php");
include (APP_BASE_PATH."classes/EmailSender.php");
include (APP_BASE_PATH."classes/ReportHandler.php");
include (APP_BASE_PATH."classes/NotificationManager.php");
include (APP_BASE_PATH."classes/S3FileSystem.php");
include (APP_BASE_PATH."classes/UIManager.php");
include (APP_BASE_PATH."classes/RestApiManager.php");
include (APP_BASE_PATH."classes/ModuleBuilder.php");
include (APP_BASE_PATH."classes/SimpleImage.php");
include (APP_BASE_PATH."classes/Macaw.php");
include (APP_BASE_PATH."classes/crypt/Aes.php");
include (APP_BASE_PATH."classes/crypt/AesCtr.php");

include (APP_BASE_PATH."model/models.base.php");
include (APP_BASE_PATH."model/models.inc.php");

include APP_BASE_PATH.'admin/users/api/UsersAdminManager.php';
include APP_BASE_PATH.'admin/modules/api/ModulesAdminManager.php';
include APP_BASE_PATH.'admin/permissions/api/PermissionsAdminManager.php';

include (APP_BASE_PATH."classes/ApproveActionManager.php");

$dbLocal = NewADOConnection(APP_CON_STR);

File::SetDatabaseAdapter($dbLocal);
Setting::SetDatabaseAdapter($dbLocal);
Report::SetDatabaseAdapter($dbLocal);
DataEntryBackup::SetDatabaseAdapter($dbLocal);
Audit::SetDatabaseAdapter($dbLocal);
Notification::SetDatabaseAdapter($dbLocal);
RestAccessToken::SetDatabaseAdapter($dbLocal);

include (APP_BASE_PATH."model/custom.models.inc.php");

$baseService = BaseService::getInstance();
BaseService::getInstance()->setNonDeletables("User", "id", 1);
BaseService::getInstance()->setCurrentUser($user);
BaseService::getInstance()->setDB($dbLocal);

$reportHandler = new ReportHandler();
$settingsManager = SettingsManager::getInstance();
$notificationManager = new NotificationManager();

BaseService::getInstance()->setNotificationManager($notificationManager);
BaseService::getInstance()->setSettingsManager($settingsManager);

$notificationManager->setBaseService($baseService);



$noJSONRequests = SettingsManager::getInstance()->getSetting("System: Do not pass JSON in request");

$debugMode = SettingsManager::getInstance()->getSetting("System: Debug Mode");
if($debugMode == "1"){
	if(!defined('LOG_LEVEL')){define('LOG_LEVEL',Monolog\Logger::DEBUG);}
    error_reporting(E_ALL);
}else{
	if(!defined('LOG_LEVEL')){define('LOG_LEVEL',Monolog\Logger::INFO);}
    error_reporting(E_ERROR);
}

LogManager::getInstance();

$userTables = array();
$fileFields = array();
$mysqlErrors = array();
//============ Start - Initializing Modules ==========
if(defined('CLIENT_PATH')){
	include 'modules.php';
	
	
	$moduleManagers = BaseService::getInstance()->getModuleManagers();
	
	foreach($moduleManagers as $moduleManagerObj){
		
		$moduleManagerObj->setupModuleClassDefinitions();
		$moduleManagerObj->initializeUserClasses();
		$moduleManagerObj->initializeFieldMappings();
		$moduleManagerObj->initializeDatabaseErrorMappings();
		
		$moduleManagerObj->setupUserClasses($userTables);
		$moduleManagerObj->setupFileFieldMappings($fileFields);
		$moduleManagerObj->setupErrorMappings($mysqlErrors);
		//$moduleManagerObj->setupRestEndPoints();
		$moduleManagerObj->initCalculationHooks();
		
		$modelClassList = $moduleManagerObj->getModelClasses();
		
		foreach($modelClassList as $modelClass){
			$modelClass::SetDatabaseAdapter($dbLocal);
		}
	}
}
//============= End - Initializing Modules ============




BaseService::getInstance()->setFileFields($fileFields);

BaseService::getInstance()->setUserTables($userTables);

BaseService::getInstance()->setSqlErrors($mysqlErrors);

include ("includes.com.php");

if(file_exists(APP_BASE_PATH.'admin/audit/api/AuditActionManager.php')){
	include APP_BASE_PATH.'admin/audit/api/AuditActionManager.php';
	$auditManager = new AuditActionManager();
	$auditManager->setBaseService($baseService);
	$auditManager->setUser($user);
	BaseService::getInstance()->setAuditManager($auditManager);
}

$emailEnabled = SettingsManager::getInstance()->getSetting("Email: Enable");
$emailMode = SettingsManager::getInstance()->getSetting("Email: Mode");
$uploadS3 = SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");

if($emailMode == "SES" || $uploadS3 == '1'){
    include(APP_BASE_PATH.'lib/aws.phar');
}

$emailSender = null;
if($emailEnabled == "1"){
	if($emailMode == "SMTP"){
		$emailSender = new SMTPEmailSender($settingsManager);
	}else if($emailMode == "SES"){
		$emailSender = new SNSEmailSender($settingsManager);
	}else if($emailMode == "PHP Mailer"){
		$emailSender = new PHPMailer($settingsManager);	
	}
}

BaseService::getInstance()->setEmailSender($emailSender);

include ('common.cron.tasks.php');

?>
