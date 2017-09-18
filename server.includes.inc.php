<?php
if (!defined("AWS_REGION")) {
    define('AWS_REGION', 'us-east-1');
}
include(APP_BASE_PATH.'lib/Mail.php');
include(APP_BASE_PATH.'lib/adodb512/adodb.inc.php');
include(APP_BASE_PATH.'lib/adodb512/adodb-active-record.inc.php');
$ADODB_ASSOC_CASE = 2;

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

$user = \Utils\SessionUtils::getSessionObject('user');

$dbLocal = NewADOConnection(APP_CON_STR);

\Model\File::SetDatabaseAdapter($dbLocal);
\Model\Setting::SetDatabaseAdapter($dbLocal);
\Model\Report::SetDatabaseAdapter($dbLocal);
\Model\DataEntryBackup::SetDatabaseAdapter($dbLocal);
\Model\Audit::SetDatabaseAdapter($dbLocal);
\Model\Notification::SetDatabaseAdapter($dbLocal);
\Model\RestAccessToken::SetDatabaseAdapter($dbLocal);


$baseService = \Classes\BaseService::getInstance();
\Classes\BaseService::getInstance()->setNonDeletables("User", "id", 1);
\Classes\BaseService::getInstance()->setCurrentUser($user);
\Classes\BaseService::getInstance()->setCustomFieldManager(new \Classes\CustomFieldManager());
\Classes\BaseService::getInstance()->setDB($dbLocal);

$reportHandler = new \Classes\ReportHandler();
$settingsManager = \Classes\SettingsManager::getInstance();
$notificationManager = new \Classes\NotificationManager();

\Classes\BaseService::getInstance()->setNotificationManager($notificationManager);
\Classes\BaseService::getInstance()->setSettingsManager($settingsManager);
\Classes\BaseService::getInstance()->setCustomFieldManager(new \Classes\CustomFieldManager());
$migrationManager = new \Classes\Migration\MigrationManager();
$migrationManager->setMigrationPath(APP_BASE_PATH .'/migrations/');
\Classes\BaseService::getInstance()->setMigrationManager($migrationManager);

$notificationManager->setBaseService($baseService);

$noJSONRequests = \Classes\SettingsManager::getInstance()->getSetting("System: Do not pass JSON in request");

$debugMode = \Classes\SettingsManager::getInstance()->getSetting("System: Debug Mode");
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

\Utils\LogManager::getInstance();

$userTables = array();
$fileFields = array();
$mysqlErrors = array();
//============ Start - Initializing Modules ==========
if (defined('CLIENT_PATH')) {
    include APP_BASE_PATH.'modules.php';

    $moduleManagers = \Classes\BaseService::getInstance()->getModuleManagers();

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

        $modelClassList = $moduleManagerObj->getModelClasses();
        $metaData = $moduleManagerObj->getModuleObject();
        foreach ($modelClassList as $modelClass) {
            $modelClassWithNameSpace = $metaData['model_namespace']."\\".$modelClass;
            $modelClassWithNameSpace::SetDatabaseAdapter($dbLocal);
            $baseService->addModelClass($modelClass, $modelClassWithNameSpace);
        }
    }
}
//============= End - Initializing Modules ============

\Classes\BaseService::getInstance()->setFileFields($fileFields);

\Classes\BaseService::getInstance()->setUserTables($userTables);

\Classes\BaseService::getInstance()->setSqlErrors($mysqlErrors);

include("includes.com.php");

if (class_exists('\\Audit\\Admin\\Api\\AuditActionManager')) {
    $auditManager = new \Audit\Admin\Api\AuditActionManager();
    $auditManager->setBaseService($baseService);
    $auditManager->setUser($user);
    \Classes\BaseService::getInstance()->setAuditManager($auditManager);
}

$emailEnabled = \Classes\SettingsManager::getInstance()->getSetting("Email: Enable");
$emailMode = \Classes\SettingsManager::getInstance()->getSetting("Email: Mode");
$uploadS3 = \Classes\SettingsManager::getInstance()->getSetting("Files: Upload Files to S3");

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
    }
}

\Classes\BaseService::getInstance()->setEmailSender($emailSender);
