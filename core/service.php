<?php

use Classes\BaseService;
use Classes\IceResponse;
use Classes\PasswordManager;
use Metadata\Common\Model\SupportedLanguage;
use Users\Common\Model\User;
use Utils\LogManager;
use Classes\Exception\IceHttpException;

define('CLIENT_PATH',dirname(__FILE__));
include ("config.base.php");
include ("include.common.php");

$modulePath = \Utils\SessionUtils::getSessionObject("modulePath");
if(!defined('MODULE_PATH')){
	define('MODULE_PATH',$modulePath);
}

include("server.includes.inc.php");

$userLevelArray = ['Admin', 'Manager', 'Employee', 'Restricted Admin', 'Restricted Manager', 'Restricted Employee', 'Anonymous'];

if($_REQUEST['a'] != "rsp" && $_REQUEST['a'] != "rpc"){
	if(empty($user) || empty($user->email) ||  empty($user->id) || !in_array($user->user_level, $userLevelArray)){
		$ret['status'] = "ERROR";
		echo json_encode($ret);
		exit();
	}
}

try {// Domain aware input cleanup
    $cleaner = new \Classes\DomainAwareInputCleaner();
    if (isset($_REQUEST['t'])) {
        $_REQUEST['t'] = $cleaner->cleanTableColumn($_REQUEST['t']);
    }
    if (isset($_REQUEST['ft'])) {
        $_REQUEST['ft'] = $cleaner->cleanFilters($_REQUEST['ft']);
    }
    if (isset($_REQUEST['ob'])) {
        $_REQUEST['ob'] = $cleaner->cleanOrderBy($_REQUEST['ob']);
    }
    if (isset($_REQUEST['sSearch'])) {
        $_REQUEST['sSearch'] = $cleaner->cleanSearch($_REQUEST['sSearch']);
    }
    if (isset($_REQUEST['cl'])) {
        $_REQUEST['cl'] = $cleaner->cleanColumns($_REQUEST['cl']);
    }
    $action = $_REQUEST['a'];
    if ($action == 'get') {
        $_REQUEST['sm'] = BaseService::getInstance()->fixJSON($_REQUEST['sm']);
        $_REQUEST['ft'] = BaseService::getInstance()->fixJSON($_REQUEST['ft']);
        $ret['object'] = BaseService::getInstance()->get(
            $_REQUEST['t'],
            $_REQUEST['sm'],
            $_REQUEST['ft'],
            $_REQUEST['ob']
        );
        $ret['status'] = "SUCCESS";

    } else if ($action == 'getElement') {
        $ret['object'] = BaseService::getInstance()->getElement(
            $_POST['t'],
            $_POST['id'],
            BaseService::getInstance()->fixJSON($_POST['sm'])
        );
        if (!empty($ret['object'])) {
            $ret['status'] = "SUCCESS";
        } else {
            $ret['status'] = "ERROR";
        }
    } else if ($action == 'add') {
        if ($_POST['t'] == "Report" || $_POST['t'] == "UserReport") {
            $data = $reportHandler->handleReport($_POST);
            $ret['status'] = $data[0];
            $ret['object'] = $data[1];
        } else {
            $resp = BaseService::getInstance()->addElement($_POST['t'], $_POST);
            $ret['object'] = $resp->getData();
            $ret['status'] = $resp->getStatus();
        }


    } else if ($action == 'delete') {
        /* @var IceResponse $response */
        $response = BaseService::getInstance()->deleteElement($_POST['t'], $_POST['id']);
        if ($response->getStatus() == IceResponse::SUCCESS) {
            $ret['status'] = IceResponse::SUCCESS;
        } else {
            $ret['status'] = IceResponse::ERROR;
        }

    } else if ($action == 'getFieldValues') {
        $ret['data'] = BaseService::getInstance()->getFieldValues(
            $_REQUEST['t'],
            $_REQUEST['key'],
            $_REQUEST['value'],
            $_REQUEST['method'],
            $_REQUEST['methodParams']
        );
        if ($ret['data'] !== null) {
            $ret['status'] = "SUCCESS";
        } else {
            $ret['status'] = "ERROR";
        }

    } else if ($action == 'setAdminEmp') {
        BaseService::getInstance()->setCurrentAdminProfile($_POST['empid']);
        $ret['status'] = "SUCCESS";

    } else if ($action == 'ca') {
        if (isset($_REQUEST['req'])) {
            $_REQUEST['req'] = BaseService::getInstance()->fixJSON($_REQUEST['req']);
        }
        $mod = $_REQUEST['mod'];
        $modPath = explode("=", $mod);
        $moduleCapsName = ucfirst($modPath[1]);
        /* @var \Classes\AbstractModuleManager $moduleManager */
        $moduleManager = BaseService::getInstance()->getModuleManager($modPath[0], $modPath[1]);

        if ($moduleManager === null) {
            exit();
        }

        $subAction = $_REQUEST['sa'];

        $apiClass = $moduleManager->getActionManager();
        $reflectionClass = null;
        try {
            $reflectionClass = new ReflectionClass($apiClass);
        } catch (ReflectionException $e) {
            exit();
        }
        $reflectionMethods = array_filter(
            $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC),
            function ($o) use ($reflectionClass) {
                return $o->class == $reflectionClass->getName();
            });

        $methods = [];
        foreach ($reflectionMethods as $method) {
            $methods[] = $method->name;
        }

        if (!in_array($subAction, $methods)) {
            exit();
        }

        $apiClass->setUser($user);
        $apiClass->setBaseService($baseService);
        $apiClass->setEmailSender($baseService->getEmailSender());

        if (isset($_REQUEST['req'])) {
            $req = json_decode($_REQUEST['req']);
        } else {
            $req = new stdClass();
        }
        foreach ($_REQUEST as $k => $v) {
            if ($k != 'mod' && $k != 'sa' && $k != 'a' && $k != 't' && $k != 'req') {
                if (!isset($req->$k)) {
                    $req->$k = $v;
                }
            }
        }
        $res = $apiClass->$subAction($req);
        $ret = $res->getJsonArray();

    } else if ($action == 'file') {
        $name = $_REQUEST['name'];
        $file = new \Model\File();
        $file->Load("name =?", array($name));
        $ret = array();
        $type = strtolower(substr($file->filename, strrpos($file->filename, ".") + 1));
        if ($file->name == $name) {
            $ret['status'] = "SUCCESS";
            if (\Classes\SettingsManager::getInstance()->getSetting("Files: Upload Files to S3") == '1') {
                $uploadFilesToS3Key = \Classes\SettingsManager::getInstance()->getSetting("Files: Amazon S3 Key for File Upload");
                $uploadFilesToS3Secret = \Classes\SettingsManager::getInstance()->getSetting("Files: Amazone S3 Secret for File Upload");
                $s3FileSys = new \Classes\S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
                $s3WebUrl = \Classes\SettingsManager::getInstance()->getSetting("Files: S3 Web Url");
                $fileUrl = $s3WebUrl . CLIENT_NAME . "/" . $file->filename;
                $fileUrl = $s3FileSys->generateExpiringURL($fileUrl);
                $file->filename = $fileUrl;

            } else {
                $file->filename = CLIENT_BASE_URL . 'data/' . $file->filename;
            }
            $ret['data'] = $file;
        } else {
            $ret['status'] = "ERROR";
        }
    } else if ($action == 'download') {
        $fileName = $_REQUEST['file'];
        $fileName = str_replace("..", "", $fileName);
        $fileName = str_replace("/", "", $fileName);
        $fileName = CLIENT_BASE_PATH . 'data/' . $fileName;
        if (!file_exists($fileName)) {
            exit;
        }
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($fileName));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fileName));
        ob_clean();
        flush();
        readfile($fileName);
        exit;

    } else if ($action == 'rsp') { // linked clicked from password change email
        $user = new User();
        if (!empty($_REQUEST['key'])) {
            $user = PasswordManager::verifyPasswordRestKey($_REQUEST['key']);
            if ($user !== false && $user instanceof User && !empty($user->id)) {
                if (empty($_REQUEST['now'])) {
                    header("Location:" . CLIENT_BASE_URL . "login.php?cp=1&key=" . $_REQUEST['key']);
                    exit();
                } else {
                    if (!empty($_REQUEST['pwd'])) {
                        $passwordCheck = PasswordManager::isQualifiedPassword($_REQUEST['pwd']);
                        if ($passwordCheck->getStatus() === IceResponse::SUCCESS) {
                            $user->password = PasswordManager::createPasswordHash($_REQUEST['pwd']);
                            $user->Save();
                            LogManager::getInstance()->info("User password changed [$user->id]");
                            $ret['status'] = "SUCCESS";
                        } else {
                            $ret['status'] = "ERROR";
                            $ret['message'] = $passwordCheck->getData();
                        }
                    }
                }

            } else {
                $ret['status'] = "ERROR";
                $ret['message'] = "Error verifying password reset request";
            }

        } else {
            $ret['status'] = "ERROR";
            $ret['message'] = "Invalid request";
        }

    } else if ($action == 'rpc') {
        try {
            $user = new User();
            $user->Load("email = ? or username = ?", [$_REQUEST['id'], $_REQUEST['id']]);
            if (empty($user->id)) {
                $ret['status'] = "SUCCESS";
                $ret['message'] = "If the user exists you should receive an email with instructions for changing the password";
            } else if (($passwordChangeWaitingMinutes = PasswordManager::passwordChangeWaitingTimeMinutes($user)) > 0) {
                $ret['status'] = "ERROR";
                $ret['message'] = "Wait another $passwordChangeWaitingMinutes minutes to request a password change again";
            } else if ($emailSender->sendResetPasswordEmail($_REQUEST['id'])) {
                $ret['status'] = "SUCCESS";
                $ret['message'] = "If the user exists you should receive an email with instructions for changing the password";
            } else {
                $ret['status'] = "SUCCESS";
                $ret['message'] = "If the user exists you should receive an email with instructions for changing the password";
            }
        } catch (Exception $e) {
            LogManager::getInstance()->error('Error occurred while changing password:' . $e->getMessage());
            LogManager::getInstance()->notifyException($e);
        }

    } else if ($action == 'getNotifications') {
        $ret['data'] = $notificationManager->getLatestNotificationsAndCounts($user->id);
        $ret['status'] = "SUCCESS";

    } else if ($action == 'clearNotifications') {
        $notificationManager->clearNotifications($user->id);
        $ret['status'] = "SUCCESS";

    } else if ($action == 'verifyInstance') {
        $key = $_POST['key'];
        if (empty($key)) {
            $ret['status'] = "ERROR";
            $ret['message'] = "Instance key not found";
        } else {
            $baseService->setInstanceKey($key);
            if ($baseService->validateInstance()) {
                $ret['status'] = "SUCCESS";
            } else {
                $ret['status'] = "ERROR";
                $ret['message'] = "Error Verifying IceHrm Instance due to invalid key. If you are keep getting this, please contact us through " . CONTACT_EMAIL;
            }
        }

    } else if ($action === 'updateLanguage') {
        $language = $_POST['language'];
        $supportedLanguage = new SupportedLanguage();
        $supportedLanguage->Load('name = ?', [$language]);
        $ret['status'] = "ERROR";
        if (!empty($supportedLanguage->id) && $supportedLanguage->name === $language) {
            $languageUser = new User();
            $languageUser->Load('id = ?', [$user->id]);
            if (!empty($languageUser->id)) {
                $languageUser->lang = $supportedLanguage->id;
                $languageUser->Save();
                $user->lang = $languageUser->lang;
                \Utils\SessionUtils::saveSessionObject('user', $user);
                $ret['status'] = "SUCCESS";
            }
        }
    }
    try {
        echo BaseService::getInstance()->safeJsonEncode($ret);
    } catch (Exception $e) {
        LogManager::getInstance()->error($e->getMessage());
        LogManager::getInstance()->notifyException($e);
        echo json_encode(['status' => 'Error']);
    }
} catch (IceHttpException $e) {
    http_response_code($e->getCode());
    echo json_encode(['message' => $e->getMessage()]);
}
