<?php

use Classes\BaseService;
use Classes\CustomFieldManager;
use Classes\JwtTokenService;
use Classes\MemoryCacheService;
use Classes\Migration\MigrationManager;
use Classes\NotificationManager;
use Classes\RedisCacheService;
use Classes\ReportHandler;
use Classes\SettingsManager;
use Model\Audit;
use Model\BaseModel;
use Model\DataEntryBackup;
use Model\File;
use Model\Notification;
use Model\Report;
use Model\RestAccessToken;
use Model\Setting;
use Utils\LogManager;

//include(APP_BASE_PATH.'lib/adodb512/adodb.inc.php');
//include(APP_BASE_PATH.'lib/adodb512/adodb-active-record.inc.php');
//$ADODB_ASSOC_CASE = 2;

include(APP_BASE_PATH.'lib/fpdf/fpdf.php');

//detect admin and user modules
if (defined("MODULE_PATH")) {
    $tArr = explode("/", MODULE_PATH);
    if (count($tArr) == 1) {
        $tArr = explode("\\", MODULE_PATH);
    }
    if (!defined('MODULE_TYPE')) {
        if (count($tArr) >= 2) {
            define('MODULE_TYPE', $tArr[count($tArr)-2]);
        } else {
            define('MODULE_TYPE', "");
        }
    }
}

//$dbLocal = NewADOConnection('mysqli');
//$res = $dbLocal->Connect(APP_HOST, APP_USERNAME, APP_PASSWORD, APP_DB);

$dbLocal = new \MyORM\MySqlActiveRecord();
$res = $dbLocal->Connect(APP_HOST, APP_USERNAME, APP_PASSWORD, APP_DB);

//File::SetDatabaseAdapter($dbLocal);
//Setting::SetDatabaseAdapter($dbLocal);
//Report::SetDatabaseAdapter($dbLocal);
//DataEntryBackup::SetDatabaseAdapter($dbLocal);
//Audit::SetDatabaseAdapter($dbLocal);
//Notification::SetDatabaseAdapter($dbLocal);
//RestAccessToken::SetDatabaseAdapter($dbLocal);

$user = \Utils\SessionUtils::getSessionObject('user');


$baseService = BaseService::getInstance();
BaseService::getInstance()->setNonDeletables("User", "id", 1);
BaseService::getInstance()->setCurrentUser($user);
BaseService::getInstance()->setCustomFieldManager(new CustomFieldManager());
BaseService::getInstance()->setDB($dbLocal);

$reportHandler = new ReportHandler();
$settingsManager = SettingsManager::getInstance();
$notificationManager = new NotificationManager();

BaseService::getInstance()->setNotificationManager($notificationManager);
BaseService::getInstance()->setSettingsManager($settingsManager);
BaseService::getInstance()->setCustomFieldManager(new CustomFieldManager());
$migrationManager = new MigrationManager();
$migrationManager->setMigrationPath(APP_BASE_PATH .'/migrations/');
BaseService::getInstance()->setMigrationManager($migrationManager);

$notificationManager->setBaseService($baseService);

if (defined('REDIS_SERVER_URI')
    && !empty(REDIS_SERVER_URI)
    && defined('QUERY_CACHE_TYPE')
    && QUERY_CACHE_TYPE === 'redis'
) {
    BaseService::getInstance()->setCacheService(
        new RedisCacheService(REDIS_SERVER_URI, CLIENT_NAME)
    );
} else {
    BaseService::getInstance()->setCacheService(
        new MemoryCacheService(CLIENT_NAME)
    );
}

$awsRegion = SettingsManager::getInstance()->getSetting("System: AWS Region");
if (!defined("AWS_REGION")) {
    define('AWS_REGION', empty($awsRegion) ? 'us-east-1' : $awsRegion);
}

$samlEnabled = SettingsManager::getInstance()->getSetting("SAML: Enabled");
if ($samlEnabled === '1') {
    include APP_BASE_PATH . 'lib/saml2/Utilities.php';
    include APP_BASE_PATH . 'lib/saml2/Response.php';
    include APP_BASE_PATH . 'lib/saml2/encryption.php';
    include APP_BASE_PATH . 'lib/saml2/mo-saml-options-enum.php';
}

$instanceId = SettingsManager::getInstance()->getSetting("Instance : ID");
$instanceKey = SettingsManager::getInstance()->getSetting("Instance: Key");
if(!defined('APP_SEC')){define('APP_SEC',sha1($instanceId.$instanceKey));}

$noJSONRequests = SettingsManager::getInstance()->getSetting("System: Do not pass JSON in request");

$debugMode = SettingsManager::getInstance()->getSetting("System: Debug Mode");
if ($debugMode == "1") {
    if (!defined('LOG_LEVEL')) {
        define('LOG_LEVEL', Monolog\Logger::DEBUG);
    }
    error_reporting(E_ALL);
} else {
    if (!defined('LOG_LEVEL')) {
        define('LOG_LEVEL', Monolog\Logger::INFO);
    }
    error_reporting(E_ERROR);
}

LogManager::getInstance();

include("includes.com.php");

$userTables = array();
$fileFields = array();
$mysqlErrors = array();
//============ Start - Initializing Modules ==========
if (defined('CLIENT_PATH')) {
    include APP_BASE_PATH.'modules.php';

    $moduleManagers = BaseService::getInstance()->getModuleManagers();

    /* @var \Classes\AbstractModuleManager $moduleManagerObj */
    foreach ($moduleManagers as $moduleManagerObj) {
        $moduleManagerObj->setupModuleClassDefinitions();
        $moduleManagerObj->initializeUserClasses();
        $moduleManagerObj->initializeFieldMappings();
        $moduleManagerObj->initializeDatabaseErrorMappings();

        $moduleManagerObj->setupUserClasses($userTables);
        $moduleManagerObj->setupFileFieldMappings($fileFields);
        $moduleManagerObj->setupErrorMappings($mysqlErrors);
        //$moduleManagerObj->setupRestEndPoints();
        $moduleManagerObj->initCalculationHooks();
        $moduleManagerObj->initialize();

        $modelClassList = $moduleManagerObj->getModelClasses();
        $metaData = $moduleManagerObj->getModuleObject();
        /** @var BaseModel $modelClass */
        foreach ($modelClassList as $modelClass) {
            $modelClassWithNameSpace = $metaData['model_namespace']."\\".$modelClass;
            //$modelClassWithNameSpace::SetDatabaseAdapter($dbLocal);
            $baseService->addModelClass($modelClass, $modelClassWithNameSpace);
            $modelClassObject = new $modelClassWithNameSpace();
        }
    }
}
//============= End - Initializing Modules ============

BaseService::getInstance()->setFileFields($fileFields);

BaseService::getInstance()->setUserTables($userTables);

BaseService::getInstance()->setSqlErrors($mysqlErrors);


if (class_exists('\\Audit\\Admin\\Api\\AuditActionManager')) {
    $auditManager = new \Audit\Admin\Api\AuditActionManager();
    $auditManager->setBaseService($baseService);
    $auditManager->setUser($user);
    BaseService::getInstance()->setAuditManager($auditManager);
}

$emailEnabled = SettingsManager::getInstance()->getSetting("Email: Enable");
$emailMode = SettingsManager::getInstance()->getSetting("Email: Mode");
$uploadS3 = SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");

if ($emailMode == "SES" || $uploadS3 == '1') {
    include(APP_BASE_PATH.'lib/aws.phar');
}

$emailSender = null;
if ($emailEnabled == "1") {
    if ($emailMode == "SMTP") {
        $emailSender = new \Classes\Email\SMTPEmailSender($settingsManager);
    } elseif ($emailMode == "SES") {
        $emailSender = new \Classes\Email\SNSEmailSender($settingsManager);
    } elseif ($emailMode == "PHP Mailer") {
        $emailSender = new \Classes\Email\PHPMailer($settingsManager);
    } elseif ($emailMode == "Swift SMTP") {
        $emailSender = new \Classes\Email\SwiftMailer($settingsManager);
    }
}

BaseService::getInstance()->setEmailSender($emailSender);

$jwtService = new JwtTokenService();

function shutdown()
{
    session_write_close();
    $error = error_get_last();

    if (!empty($error) && isset($error['type']) && in_array($error['type'], [E_ERROR, E_PARSE])) {
        LogManager::getInstance()->error(json_encode($error));
        LogManager::getInstance()->notifyException(new ErrorException(
            $error['message'],
            0,
            1,
            $error['file'],
            $error['line']
        ));
    }
}

register_shutdown_function('shutdown');
