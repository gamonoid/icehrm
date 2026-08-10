<?php

use Classes\BaseService;
use Classes\IceResponse;
use Classes\PasswordManager;
use Classes\Pdf\PDFRegister;
use Classes\SettingsManager;
use Metadata\Common\Model\SupportedLanguage;
use Model\File;
use Users\Common\Model\User;
use Utils\LogManager;
use Classes\Exception\IceHttpException;

define('CLIENT_PATH',dirname(__FILE__));
include ("config.base.php");
include ("include.common.php");

$modulePath = \Utils\SessionUtils::getSessionObject("modulePath");
// New SPA UI: explicit per-request module (mg/mn) overrides the ambient session
// modulePath for correct data scope (see docs/DATA_SCOPE_ISSUE.md). Authorization
// validated after server.includes. Legacy requests omit mg/mn (unchanged).
$reqModGroup = isset($_REQUEST['mg']) ? $_REQUEST['mg'] : null;
$reqModName = isset($_REQUEST['mn']) ? $_REQUEST['mn'] : null;
if (!empty($reqModGroup) && !empty($reqModName)) {
	$resolvedModulePath = \Classes\ModuleScopeResolver::pathFor($reqModGroup, $reqModName);
	if ($resolvedModulePath !== null) {
		$modulePath = $resolvedModulePath;
	}
}
if(!defined('MODULE_PATH')){
	define('MODULE_PATH',$modulePath);
}

include("server.includes.inc.php");

$userLevelArray = ['Admin', 'Manager', 'Employee', 'Restricted Admin', 'Restricted Manager', 'Restricted Employee', 'Anonymous'];
$isFileDownloadWithSignature = $_REQUEST['a'] === "download" && isset($_REQUEST['signature']);
if($_REQUEST['a'] != "rsp" && $_REQUEST['a'] != "rpc" && $_REQUEST['a'] != "rlc" && !$isFileDownloadWithSignature){
	if(empty($user) || empty($user->email) ||  empty($user->id) || !in_array($user->user_level, $userLevelArray)){
		$ret['status'] = "ERROR";
        $ret['code'] = "NO_USER_FOUND";
		echo json_encode($ret);
		exit();
	}
	// Reject a forged/unauthorized explicit module before any action runs.
	if (!empty($reqModGroup) && !empty($reqModName)
		&& !\Classes\ModuleScopeResolver::isAuthorized($reqModGroup, $reqModName, $user)
	) {
		http_response_code(403);
		$ret['status'] = "ERROR";
		$ret['code'] = "MODULE_ACCESS_DENIED";
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

    // CSRF gate for state-changing actions. service.php authenticates from the session
    // cookie and reads the action from $_REQUEST, so a=delete/add/ca/setAdminEmp are
    // triggerable over GET as well as POST; SameSite=Lax alone does not stop a top-level
    // GET navigation. The SPA only ever calls these via same-origin XHR (which carries an
    // Origin/Referer), so require a same-origin request for every mutation. Reads are not
    // gated (a cross-site read cannot be seen by the attacker and changes no state).
    // Forced password reset: while the session is flagged, the ONLY action allowed is the
    // reset itself. Without this the shell gate would be cosmetic — a client could keep
    // driving the app over XHR with the existing session cookie.
    // 'fpc' is the reset itself; rsp/rpc/rlc are the email-based recovery paths, which a
    // flagged user must still be able to reach (e.g. they cannot recall the old password).
    $passwordResetAllowedActions = array('fpc', 'rsp', 'rpc', 'rlc');
    if (\Classes\PasswordManager::userNeedsPasswordReset($user)
        && !in_array($action, $passwordResetAllowedActions, true)
    ) {
        http_response_code(403);
        $ret['status'] = "ERROR";
        $ret['code'] = "PASSWORD_RESET_REQUIRED";
        $ret['message'] = "You must update your password before continuing";
        echo json_encode($ret);
        exit();
    }

    // 'clearNotifications' writes (marks the caller's unread notifications Read), so it
    // belongs here too — a forged cross-site request could otherwise hide a victim's
    // alerts. It is scoped to the session user, so the impact is suppression, not
    // disclosure, but the gate should cover every action that writes.
    $stateChangingActions = array('save', 'add', 'delete', 'setAdminEmp', 'ca', 'fpc', 'clearNotifications');
    if (in_array($action, $stateChangingActions, true)
        && !BaseService::getInstance()->isSameOriginRequest()
    ) {
        http_response_code(403);
        $ret['status'] = "ERROR";
        $ret['code'] = "CSRF_ORIGIN_DENIED";
        echo json_encode($ret);
        exit();
    }

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
        $resp = BaseService::getInstance()->addElement($_POST['t'], $_POST);
        $ret['object'] = $resp->getData();
        $ret['status'] = $resp->getStatus();
    } else if ($action == 'delete') {
        /* @var IceResponse $response */
        $response = BaseService::getInstance()->deleteElement($_POST['t'], $_POST['id']);
        if ($response->getStatus() == IceResponse::SUCCESS) {
            $ret['status'] = IceResponse::SUCCESS;
        } else {
            $ret['status'] = IceResponse::ERROR;
            $ret['data'] = $response->getData();
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

        // AUTHORIZATION. The custom-action path reaches a module's action manager
        // from any logged-in session; the module's meta.json user_levels only pruned
        // the MENU, never this dispatch. Without this check an Employee could invoke
        // any admin module's actions directly (e.g.
        // service.php?a=ca&mod=admin=leaves&sa=getSubEmployeeLeaves dumped every
        // employee's leave; deleteEmployee / saveUsage / payroll writes were reachable
        // the same way). Enforce the module's own declared user_levels/user_roles here,
        // the same rule the menu applies — read off the resolved manager, so the check
        // is independent of any name mapping between the request and the DB module.
        $caModuleObject = $moduleManager->getModuleObject();
        $caAllowed = false;
        if (is_array($caModuleObject)) {
            $caAllowed = \Classes\ModuleAccessService::getInstance()->userMayAccessModuleLevels(
                isset($caModuleObject['user_levels']) ? $caModuleObject['user_levels'] : null,
                isset($caModuleObject['user_roles']) ? $caModuleObject['user_roles'] : null,
                $user
            );
        }
        // Approval actions are a deliberate exception. The SPA routes changeStatus and
        // getLogs to admin=<module> even when the user is on the USER module's Approvals
        // tab, because ReactLogViewAdapter::getActionModuleRef() hardcodes the admin
        // prefix. That tab is shown to Employees (modules/overtime, expenses/user — both
        // declare Employee in user_levels and gate the tab on the module's multi-level
        // setting), so an Employee named as approver1/2/3 would be refused a workflow
        // their own module legitimately offers them, leaving the request stuck at
        // Processing with nobody able to advance it.
        //
        // Fall back to the sibling USER module's user_levels for those two actions only.
        // This grants no new data access: both actions load the record and run it past
        // ApproveCommonActionManager::currentUserCanReviewRecord (owner's manager, or a
        // named approver on that specific record), and ApprovalStatus::updateApprovalStatus
        // still refuses anyone who is not the currently-active level. Every other
        // sub-action — getSubEmployeeLeaves, deleteEmployee, payroll writes — stays behind
        // the strict admin-module check.
        if (!$caAllowed
            && $modPath[0] === 'admin'
            && in_array($subAction, array('changeStatus', 'getLogs'), true)
        ) {
            // Core user modules register under 'modules', extension user modules under
            // 'user'; try both rather than assuming which kind this module is.
            foreach (array('modules', 'user') as $siblingType) {
                $siblingManager = BaseService::getInstance()->getModuleManager($siblingType, $modPath[1]);
                if ($siblingManager === null) {
                    continue;
                }
                $siblingObject = $siblingManager->getModuleObject();
                if (!is_array($siblingObject)) {
                    continue;
                }
                if (\Classes\ModuleAccessService::getInstance()->userMayAccessModuleLevels(
                    isset($siblingObject['user_levels']) ? $siblingObject['user_levels'] : null,
                    isset($siblingObject['user_roles']) ? $siblingObject['user_roles'] : null,
                    $user
                )) {
                    $caAllowed = true;
                    break;
                }
            }
        }

        if (!$caAllowed) {
            \Utils\LogManager::getInstance()->info(sprintf(
                'CA_ACCESS_DENIED: user=%s level=%s attempted mod=%s sa=%s',
                isset($user->id) ? $user->id : '?',
                isset($user->user_level) ? $user->user_level : '?',
                $mod,
                isset($_REQUEST['sa']) ? $_REQUEST['sa'] : '?'
            ));
            http_response_code(403);
            $ret['status'] = "ERROR";
            $ret['code'] = "MODULE_ACCESS_DENIED";
            echo json_encode($ret);
            exit();
        }

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
        // Ownership gate. Without this any logged-in employee could mint a signed
        // download link for ANY file by name — other employees' HR documents, and
        // the full employee-export reports (SSN, salary, address), whose names are
        // guessable. Admin/owner/manager-over-owner only; owner-less files (reports)
        // are admin-only.
        if (!empty($file->id) && !BaseService::getInstance()->currentUserCanAccessFile($file)) {
            \Utils\LogManager::getInstance()->info(sprintf(
                'FILE_ACCESS_DENIED: user=%s level=%s requested file name=%s owner=%s',
                isset($user->id) ? $user->id : '?',
                isset($user->user_level) ? $user->user_level : '?',
                $name,
                isset($file->employee) ? $file->employee : ''
            ));
            http_response_code(403);
            $ret['status'] = "ERROR";
            $ret['code'] = "FILE_ACCESS_DENIED";
            echo json_encode($ret);
            exit();
        }
        $type = strtolower(substr($file->filename, strrpos($file->filename, ".") + 1));
        if ($file->name == $name) {
            $ret['status'] = "SUCCESS";
            $file->ext = explode('.', $file->filename)[1];
            if (SettingsManager::getInstance()->getSetting("Files: Upload Files to S3") == '1') {
                $uploadFilesToS3Key = SettingsManager::getInstance()->getSetting("Files: Amazon S3 Key for File Upload");
                $uploadFilesToS3Secret = SettingsManager::getInstance()->getSetting("Files: Amazon S3 Secret for File Upload");
                $s3FileSys = new \Classes\S3FileSystem($uploadFilesToS3Key, $uploadFilesToS3Secret);
                $s3WebUrl = SettingsManager::getInstance()->getSetting("Files: S3 Web Url");
                $fileUrl = $s3WebUrl . CLIENT_NAME . "/" . $file->filename;
                $fileUrl = $s3FileSys->generateExpiringURL($fileUrl);
                $file->filename = $fileUrl;

            } else {
                $file->filename = \Classes\FileService::getInstance()->getLocalSecureUrl($file->filename);
            }
            $ret['data'] = BaseService::getInstance()->cleanUpAll($file);
        } else {
            $ret['status'] = "ERROR";
        }
    } else if ($action == 'download') {
        $fileName = $_REQUEST['file'];

		if (!isset($_REQUEST['signature'])) {
			exit;
		}
		$downloadExpires = isset($_REQUEST['expires']) ? $_REQUEST['expires'] : null;
		if (!\Classes\FileService::getInstance()->verifyDownloadSignature(
			$fileName,
			$downloadExpires,
			$_REQUEST['signature']
		)) {
			exit;
		}

		$file = new File();
		$file->Load('name = ?', array($fileName));

        $fileName = str_replace("..", "", $fileName);
        $fileName = str_replace("/", "", $fileName);

        if ($fileName !== $file->name) {
            $file->Load('filename = ?', array($fileName));
        }

        if (empty($file->id)) {
            exit;
        }

        if (!file_exists(BaseService::getInstance()->getDataDirectory() . $file->filename)) {
            exit;
        }

        $extension = explode('.', $file->filename)[1];
        $seconds_to_cache = 3600;
        $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
        header('Content-Description: File Transfer');
        if ('png' === $extension) {
            header("Expires: $ts");
            header("Pragma: cache");
            header("Cache-Control: max-age=$seconds_to_cache");
            header('Content-Type: image/png');
        } elseif ('gif' === $extension) {
            header("Expires: $ts");
            header("Pragma: cache");
            header("Cache-Control: max-age=$seconds_to_cache");
            header('Content-Type: image/png');
        } elseif ('jpg' === $extension || 'jpeg' === $extension) {
            header("Expires: $ts");
            header("Pragma: cache");
            header("Cache-Control: max-age=$seconds_to_cache");
            header('Content-Type: image/jpeg');
        } elseif ('pdf' === $extension) {
            header('Content-Type: application/pdf');
        } elseif ('xml' === $extension) {
            header('Content-Type: application/xml');
        } else {
            header('Content-Disposition: attachment; filename=' . basename($file->filename));
            header('Content-Type: application/octet-stream');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
        }

        header('Content-Length: ' . filesize(BaseService::getInstance()->getDataDirectory() . $file->filename));
        ob_clean();
        flush();
        readfile(BaseService::getInstance()->getDataDirectory() . $file->filename);
        exit;

    } else if ($action == 'fpc') {
        // Forced password change for a legacy MD5 account (see core/password-reset-required.php).
        // Reachable only by a logged-in session that is actually flagged; it upgrades the
        // stored hash to bcrypt and then ends the session so the user signs in again.
        try {
            if (!\Classes\PasswordManager::userNeedsPasswordReset($user)) {
                $ret['status'] = "ERROR";
                $ret['message'] = "No password update is required";
            } else {
                $fpcCsrf = \Utils\SessionUtils::getSessionObject('csrf-fpc');
                if (empty($_REQUEST['csrf']) || !is_string($_REQUEST['csrf'])
                    || !hash_equals((string) $fpcCsrf, $_REQUEST['csrf'])
                ) {
                    $ret['status'] = "ERROR";
                    $ret['message'] = "Error validating CSRF token";
                } else {
                    $fpcUser = new User();
                    $fpcUser->Load("id = ?", array($user->id));

                    if (empty($fpcUser->id)) {
                        $ret['status'] = "ERROR";
                        $ret['message'] = "Error occurred while changing password";
                    } elseif (!PasswordManager::verifyPassword($_REQUEST['current'], $fpcUser->password)) {
                        $ret['status'] = "ERROR";
                        $ret['message'] = "Current password is incorrect";
                    } else {
                        $fpcStrength = PasswordManager::isQualifiedPassword($_REQUEST['pwd']);
                        if ($fpcStrength->getStatus() === IceResponse::ERROR) {
                            $ret['status'] = "ERROR";
                            $ret['message'] = $fpcStrength->getData();
                        } else {
                            $fpcUser->password = PasswordManager::createPasswordHash($_REQUEST['pwd']);
                            if (!$fpcUser->Save()) {
                                $ret['status'] = "ERROR";
                                $ret['message'] = "Error occurred while changing password";
                            } else {
                                PasswordManager::resetFailedLogins($fpcUser);
                                PasswordManager::clearPasswordResetRequired();

                                // Revoke the SPA session token and drop the session: the
                                // user must sign in again with the new password, which also
                                // refreshes the User object cached in the session (it still
                                // holds the old MD5 hash).
                                \Classes\RestApiManager::getInstance()
                                    ->deleteAccessTokenForUser($fpcUser, 'Web');
                                \Utils\SessionUtils::unsetClientSession();

                                $ret['status'] = "SUCCESS";
                                $ret['message'] = "Password updated";
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            LogManager::getInstance()->error('Error in forced password change:' . $e->getMessage());
            $ret['status'] = "ERROR";
            $ret['message'] = "An error occurred. Please try again.";
        }

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
                            // A password reset clears any failed-attempt lock.
                            PasswordManager::resetFailedLogins($user);
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

    } else if ($action == 'rlc') {
        // Request Login Code (email only)
        try {
            $email = trim($_REQUEST['email'] ?? '');
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $ret['status'] = "ERROR";
                $ret['message'] = "Please enter a valid email address";
            } else {
                $user = new User();
                $user->Load("email = ?", [$email]);

                // Uniform response regardless of whether the email is registered, is
                // rate-limited, or the send succeeds. Previously each case returned a
                // distinct status/message ("A login code has been sent" vs "Wait N
                // minutes" vs the neutral unknown-email string), which enumerated
                // registered addresses. All work happens internally; its outcome is
                // logged, never reflected to the caller.
                if (!empty($user->id)
                    && PasswordManager::passwordChangeWaitingTimeMinutes($user) <= 0
                ) {
                    // Generate 6-digit code
                    $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $hashedCode = password_hash($code, PASSWORD_BCRYPT);

                    // Store hash in login_hash (60 chars fits in varchar(64))
                    // Expiration is tracked via last_password_requested_at (already updated by rate limiter)
                    $user->login_hash = $hashedCode;
                    $user->Save();

                    // Send email
                    $companyName = SettingsManager::getInstance()->getSetting('Company: Name');
                    $subject = "Your IceHrm Login Code $code";
                    $body = "Your login code is: <strong>$code</strong><br><br>";
                    $body .= "This code will expire in 15 minutes.<br><br>";
                    $body .= "If you did not request this code, please ignore this email.";

                    if (!$emailSender->sendEmail($subject, $user->email, $body, [])) {
                        LogManager::getInstance()->error('Failed to send login code email to a registered address');
                    }
                } else {
                    // Unknown or rate-limited: run one matching bcrypt so the hashing
                    // portion of the response time does not set these apart from the
                    // send path.
                    password_hash('rlc-timing-equalizer', PASSWORD_BCRYPT);
                }

                $ret['status'] = "SUCCESS";
                $ret['message'] = "If the email is registered, a login code has been sent. Enter the code below to login.";
            }
        } catch (Exception $e) {
            LogManager::getInstance()->error('Error occurred while sending login code:' . $e->getMessage());
            LogManager::getInstance()->notifyException($e);
            $ret['status'] = "ERROR";
            $ret['message'] = "An error occurred. Please try again.";
        }

    } else if ($action == 'getNotifications') {
        $ret['data'] = $notificationManager->getLatestNotificationsAndCounts($user->id);
        $ret['status'] = "SUCCESS";

    } else if ($action == 'clearNotifications') {
        $notificationManager->clearNotifications($user->id);
        $ret['status'] = "SUCCESS";

    // The 'verifyInstance' action was removed. Instance verification is no longer part
    // of the product, and the action let *any* authenticated user (down to Employee —
    // the block at the top of this file only checks that someone is logged in) overwrite
    // the 'Instance: Key' setting with a value of their choosing. It persisted the key
    // before validating it, so even a rejected key stuck. The key is now generated
    // server-side on first use (BaseService::getInstanceKey) and has no setter, so
    // there is no request path that can influence it.

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

    if ($action == 'pdf') {
        $data = $_REQUEST['data'];
        $hash = $_REQUEST['h'];
        PDFRegister::init();
        $callback = PDFRegister::get($hash);
        if (empty($callback) || !$callback($data)->granted()) {
            $ret['status'] = "ERROR";
            $ret['message'] = "Invalid request";
        } else {
            $pdfBuilder = $callback($data);
            $pdf = $pdfBuilder->createPdf();
            $pdf->SetAuthor(SettingsManager::getInstance()->getSetting('Company: Name'));
            $pdf->Output();
        }
    } else {
        try {
            echo BaseService::getInstance()->safeJsonEncode($ret);
        } catch (Exception $e) {
            LogManager::getInstance()->error($e->getMessage());
            LogManager::getInstance()->notifyException($e);
            echo json_encode(['status' => 'Error']);
        }
    }

} catch (IceHttpException $e) {
    http_response_code($e->getCode());
    echo json_encode(['message' => $e->getMessage()]);
}
