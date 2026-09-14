<?php
use Classes\LDAPManager;
use Classes\SAMLManager;
use Classes\SettingsManager;
use Classes\UIManager;
use Utils\LogManager;
define('CLIENT_PATH', dirname(__FILE__));
include("config.base.php");
include("include.common.php");
include("server.includes.inc.php");

$googleAuthEnabled = SettingsManager::getInstance()->getSetting('System: G Suite Enabled');
$microsoftAuthEnabled = SettingsManager::getInstance()->getSetting('Microsoft: Authentication Enabled');
$companyName = SettingsManager::getInstance()->getSetting('Company: Name');
$SAMLAutoLogin = SettingsManager::getInstance()->getSetting('SAML: Auto Login') === "1";
$SAMLEnabled = SettingsManager::getInstance()->getSetting("SAML: Enabled") == "1";
$SAMLUserLoaded = false;

if (isset($_REQUEST['logout'])) {
    // Revoke the web SPA session token so a captured Bearer JWT stops working
    // after logout. The long-lived 'FullAPI' token (mobile/API) is left intact.
    $logoutUser = \Utils\SessionUtils::getSessionObject('user');
    if (!empty($logoutUser) && !empty($logoutUser->id)) {
        \Classes\RestApiManager::getInstance()->deleteAccessTokenForUser($logoutUser, 'Web');
    }
    \Utils\SessionUtils::unsetClientSession();
    $user = null;
}

if (empty($user) || empty($user->email)) {
    if (!isset($_REQUEST['logout']) && !isset($_POST['SAMLResponse']) && $SAMLAutoLogin && $SAMLEnabled && !empty(SettingsManager::getInstance()->getSetting("SAML: IDP SSO Url"))) {
        header("Location:" . SettingsManager::getInstance()->getSetting("SAML: IDP SSO Url"));
        exit();
    }

    if ($googleAuthEnabled == '1'
        && (isset($_REQUEST['google']) || (isset($_REQUEST['code']) && $_SESSION['auth_type'] == 'google'))
    ) {
        include 'google.login.php';
    }

    if ($microsoftAuthEnabled == '1'
        && (isset($_REQUEST['microsoft']) || (isset($_REQUEST['code']) && $_SESSION['auth_type'] == 'microsoft'))
    ) {
        include 'microsoft.login.php';
    }

    if ((!empty($_REQUEST['username']) && !empty($_REQUEST['password']))
        || isset($_POST['SAMLResponse'])
    ) {
        $suser = null;
        $ssoUserLoaded = false;
        $accountLocked = false;

        // A SAML assertion is POSTed by the IdP, is cross-site by design and carries no CSRF
        // token; every other credential path must present one. This runs before any credential
        // is examined so a forged cross-site POST cannot consume an email login code, clear
        // login_hash, or drive the failed-attempt counter into a lockout.
        $isSamlResponsePost = $SAMLEnabled && isset($_POST['SAMLResponse']);
        $loginCsrf = \Utils\SessionUtils::getSessionObject('csrf-login');
        if (!$isSamlResponsePost
            && (empty($_REQUEST['csrf'])
                || !is_string($_REQUEST['csrf'])
                || !hash_equals((string) $loginCsrf, $_REQUEST['csrf']))
        ) {
            $next = !empty($_REQUEST['next']) ? '&next='.$_REQUEST['next'] : '';
            header("Location:".CLIENT_BASE_URL."login.php?f=1".$next);
            exit();
        }

        if($_REQUEST['username'] != "admin") {
            if (SettingsManager::getInstance()->getSetting("LDAP: Enabled") === "1") {
                $ldapResp = LDAPManager::getInstance()->checkLDAPLogin($_REQUEST['username'], $_REQUEST['password']);
                if ($ldapResp->getStatus() == \Classes\IceResponse::ERROR) {
                    header("Location:" . CLIENT_BASE_URL . "login.php?f=1");
                    exit();
                } else {
                    $suser = new \Users\Common\Model\User();
                    $suser->Load("username = ?", array($_REQUEST['username']));
                    // Load() returns a populated object even when no row matched, so
                    // empty($suser) is never true — check the id, as the SAML branch does.
                    if (empty($suser) || empty($suser->id)) {
                        header("Location:" . CLIENT_BASE_URL . "login.php?f=1");
                        exit();
                    }
                    $ssoUserLoaded = true;
                }
            }
        }

        if ($SAMLEnabled && isset($_POST['SAMLResponse'])) {
            $samlData = $_POST['SAMLResponse'];

            if(array_key_exists('RelayState', $_POST) && !empty( $_POST['RelayState'] ) && $_POST['RelayState'] !== '/') {
                $relayState = htmlspecialchars($_POST['RelayState']);
            } else {
                $relayState = '';
            }

            $ssoUserEmail = (new SAMLManager())->getSSOEmail($samlData, $relayState);
            // DEBUG, not INFO: an email address is PII and this ran on every SSO login.
            LogManager::getInstance()->debug('SSO SAML User Email:'.$ssoUserEmail);
            if (false === $ssoUserEmail) {
                header("Location:" . CLIENT_BASE_URL . "login.php?f=1");
                exit();
            } else {
                $mapping = SettingsManager::getInstance()->getSetting('SAML: Name ID Mapping');
                $suser = new \Users\Common\Model\User();
                if ($mapping === 'username') {
                    $suser->Load("username = ?", array($ssoUserEmail));
                } else {
                    $suser->Load("email = ?", array($ssoUserEmail));
                }

                LogManager::getInstance()->debug('SSO SAML User:'.print_r($suser->email, true));
                if (empty($suser) || empty($suser->id)) {
                    header("Location:" . CLIENT_BASE_URL . "logout.php");
                    exit();
                }
                $ssoUserLoaded = true;
                $SAMLUserLoaded = true;
            }
        }

        if (empty($suser)) {
            $suser = new \Users\Common\Model\User();
            $suser->Load(
                "username = ? or email = ?",
                [
                    $_REQUEST['username'],
                    $_REQUEST['username'],
                ]
            );

            // Is a valid, unexpired email login code being presented? This is the
            // recovery path and is allowed even when the account is locked.
            $validLoginCode = false;
            if (!empty($suser->id)
                && !empty($suser->login_hash)
                && !empty($suser->last_password_requested_at)
            ) {
                $codeExpiry = strtotime($suser->last_password_requested_at) + (15 * 60);
                if (time() < $codeExpiry && password_verify($_REQUEST['password'], $suser->login_hash)) {
                    $validLoginCode = true;
                }
            }

            if ($validLoginCode) {
                // Valid login code — consume it. The failed-attempt counter is reset
                // on the success path below, which unlocks the account.
                $suser->login_hash = null;
                $suser->Save();
            } elseif (\Classes\PasswordManager::isAccountLocked($suser)) {
                // Too many failed attempts: refuse password login and force the user
                // to log in with an email code (which then resets the counter).
                //
                // Still record this failure. Previously the locked branch returned
                // without counting, so once locked an attacker had UNLIMITED free
                // guesses — including against the 6-digit email code, which is checked
                // above and otherwise has no attempt limit. Counting here keeps the
                // lock window fresh and, past the kill threshold, invalidates any
                // active login code so it cannot be brute-forced.
                \Classes\PasswordManager::recordFailedLogin($suser);
                if (!empty($suser->login_hash)
                    && \Classes\PasswordManager::shouldInvalidateLoginCode($suser)
                ) {
                    $suser->login_hash = null;
                    $suser->Save();
                }
                $suser = null;
                $accountLocked = true;
            } elseif (\Classes\PasswordManager::verifyPassword($_REQUEST['password'], $suser->password)) {
                // Correct password on an unlocked account — success.
            } else {
                // Wrong password on an unlocked account — count it (locks at threshold).
                \Classes\PasswordManager::recordFailedLogin($suser);
                $suser = null;
            }
        }

        if (empty($suser)) {
            $next = !empty($_REQUEST['next'])?'&next='.$_REQUEST['next']:'';
            $lockedFlag = $accountLocked ? '&locked=1' : '';
            header("Location:".CLIENT_BASE_URL."login.php?f=1".$lockedFlag.$next);
            exit();
        }

    }

    if (!empty($suser)) {
        $user = $suser;
        // Successful login (password or email code) — clear any failed-attempt lock.
        $suser->wrong_password_count = 0;
        $suser->last_wrong_attempt_at = null;
        // Prevent session fixation: issue a fresh session ID now that the user is
        // authenticated, discarding any pre-login (or attacker-supplied) session ID.
        \Utils\SessionUtils::regenerateSession();
        \Utils\SessionUtils::saveSessionObject('user', $user);
        $suser->last_login = date("Y-m-d H:i:s");
        $suser->Save();

        // Apply any pending schema migrations now that someone is authenticated —
        // core, extension and pro alike, for EVERY user level rather than only for an
        // Admin whose request happened to initialise the settings module.
        //
        // This is the moment that matters: after a file-level update the new code is
        // live but the schema is not, and whoever logs in first meets the mismatch. If
        // that is a Manager or an Employee, they used to get "Unknown column ..." with
        // nothing pointing at a pending upgrade. Runs once per request, takes an
        // advisory lock so a rush of post-upgrade logins cannot migrate concurrently,
        // and swallows its own errors — a migration problem must never stop people
        // logging in.
        \Classes\Migration\MigrationRunner::runAll(true);

        if (!$ssoUserLoaded && !empty(\Classes\BaseService::getInstance()->auditManager)) {
            \Classes\BaseService::getInstance()->auditManager->user = $user;
            \Classes\BaseService::getInstance()->audit(\Classes\IceConstants::AUDIT_AUTHENTICATION, "User Login");
        }

        if (!empty($_REQUEST['next']) && !empty(($loginRedirect = \Base64Url\Base64Url::decode($_REQUEST['next'])))) {
            header("Location:" . CLIENT_BASE_URL.$loginRedirect);
            exit();
        } else {
            // The legacy UI has been retired: everyone lands on the new React SPA.
            // Persist ui_mode='new' so the choice is durable across sessions.
            \Utils\SessionUtils::saveSessionString('uiMode', 'new');
            if ($user->ui_mode !== 'new') {
                $user->ui_mode = 'new';
                $user->Save();
                \Utils\SessionUtils::saveSessionObject('user', $user);
            }
            header("Location:".CLIENT_BASE_URL."ui/");
            exit();
        }
    }
} else {
    // Already authenticated and hitting login.php: the legacy UI has been retired,
    // so always continue to the new React SPA.
    \Utils\SessionUtils::saveSessionString('uiMode', 'new');
    header("Location:".CLIENT_BASE_URL."ui/");
    exit();
}

$tuser = \Utils\SessionUtils::getSessionObject('user');
$logoFileUrl = UIManager::getInstance()->getCompanyLogoUrl();

// CSPRNG: this token gates the unauthenticated login POST, so it must not be derivable
// from rand()+time().
$csrfToken = bin2hex(random_bytes(32));
\Utils\SessionUtils::saveSessionObject('csrf-login', $csrfToken);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>IceHrm Login</title>
    <link rel="shortcut icon" href="https://icehrm.s3.amazonaws.com/images/icon16.png">
    <script src="<?=BASE_URL?>dist/login.js"></script>
    <style>
        /* Modern login — matches the SPA shell theme (web/shell/src/theme.js):
           navy chrome #1a2233, primary #1976d2, Roboto, 8px radii, MUI shadows. */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: "Roboto", "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: rgba(0, 0, 0, 0.87);
            background: #1a2233;
            background-image: radial-gradient(1200px 600px at 80% -10%, rgba(25, 118, 210, 0.28), transparent 60%),
                              radial-gradient(900px 500px at -10% 110%, rgba(25, 118, 210, 0.18), transparent 55%);
        }
        .login-wrap {
            min-height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            background: #ffffff;
            border-radius: 12px;
            padding: 40px 36px 28px;
            box-shadow: 0 3px 5px -1px rgba(0,0,0,0.2), 0 6px 10px 0 rgba(0,0,0,0.14), 0 1px 18px 0 rgba(0,0,0,0.12);
        }
        .login-logo {
            display: block;
            margin: 0 auto 12px;
            max-width: 70%;
            max-height: 96px;
        }
        .login-subtitle {
            text-align: center;
            color: rgba(0, 0, 0, 0.6);
            margin-bottom: 24px;
        }
        .field { margin-bottom: 16px; }
        .field label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: rgba(0, 0, 0, 0.6);
            margin-bottom: 6px;
        }
        .field input {
            width: 100%;
            height: 40px;
            padding: 8px 12px;
            font-size: 14px;
            font-family: inherit;
            color: rgba(0, 0, 0, 0.87);
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.23);
            border-radius: 8px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field input:hover { border-color: rgba(0, 0, 0, 0.5); }
        .field input:focus {
            border-color: #1976d2;
            box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.18);
        }
        .pw-wrap { position: relative; }
        .pw-wrap input { padding-right: 42px; }
        .pw-toggle {
            position: absolute;
            top: 50%;
            right: 6px;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            border-radius: 50%;
            color: rgba(0, 0, 0, 0.45);
            cursor: pointer;
        }
        .pw-toggle:hover { background: rgba(0, 0, 0, 0.05); color: rgba(0, 0, 0, 0.7); }
        .btn, .btn-outline {
            width: 100%;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            border-radius: 8px;
            cursor: pointer;
            transition: background .15s, box-shadow .15s, border-color .15s;
        }
        .btn {
            border: 1px solid #1976d2;
            background: #1976d2;
            color: #fff;
            box-shadow: 0 2px 1px -1px rgba(0,0,0,0.08), 0 1px 3px 0 rgba(0,0,0,0.12);
        }
        .btn:hover { background: #1565c0; border-color: #1565c0; }
        .btn:disabled { background: rgba(0,0,0,0.12); border-color: transparent; color: rgba(0,0,0,0.38); cursor: default; }
        .btn-outline {
            border: 1px solid rgba(25, 118, 210, 0.6);
            background: #fff;
            color: #1976d2;
        }
        .btn-outline:hover { background: rgba(25, 118, 210, 0.06); border-color: #1976d2; }
        .social-btn {
            width: 100%;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            font-family: inherit;
            color: rgba(0, 0, 0, 0.75);
            background: #fff;
            border: 1px solid rgba(0, 0, 0, 0.23);
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            margin-bottom: 10px;
            transition: background .15s, border-color .15s;
        }
        .social-btn:hover { background: rgba(0, 0, 0, 0.03); border-color: rgba(0, 0, 0, 0.4); }
        .social-btn svg { flex: 0 0 auto; }
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 18px 0;
            color: rgba(0, 0, 0, 0.45);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .divider::before, .divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: rgba(0, 0, 0, 0.12);
        }
        .alert {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            line-height: 1.5;
        }
        .alert-danger { background: #fdeded; color: #5f2120; border: 1px solid #f5c6c6; }
        .alert-info { background: #e5f6fd; color: #014361; border: 1px solid #b8e7fb; }
        .alert-warning { background: #fff4e5; color: #663c00; border: 1px solid #ffe0b2; }
        .login-links {
            text-align: center;
            color: rgba(0, 0, 0, 0.6);
            margin-top: 4px;
        }
        .login-links a { color: #1976d2; text-decoration: none; font-weight: 500; }
        .login-links a:hover { text-decoration: underline; }
        .demo-box {
            margin-top: 20px;
            padding: 14px 16px;
            border: 1px dashed rgba(0, 0, 0, 0.25);
            border-radius: 8px;
            background: #fafafa;
            font-size: 13px;
            color: rgba(0, 0, 0, 0.6);
        }
        .demo-box h5 { font-size: 14px; color: rgba(0, 0, 0, 0.87); margin-bottom: 6px; }
        .demo-box p { margin: 2px 0; }
        .mb-8 { margin-bottom: 16px; }
    </style>
</head>
<body>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
    (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
    m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '<?=\Classes\BaseService::getInstance()->getGAKey()?>', 'gamonoid.com');
  ga('send', 'pageview');
</script>
<script type="text/javascript">
  var key = "";
  <?php if (isset($_REQUEST['key'])) {?>
  key = '<?=htmlentities($_REQUEST['key'], ENT_QUOTES, 'UTF-8')?>';
  key = key.replace(/ /g,"+");
  <?php }?>
</script>
<script type="text/javascript">
  // Show/hide the adjacent password input.
  window.icehrmTogglePassword = function (btn) {
    var input = btn.parentNode.querySelector('input');
    if (!input) { return; }
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.setAttribute('aria-label', input.type === 'password' ? 'Show password' : 'Hide password');
  };
</script>

<div class="login-wrap">
    <div class="login-card">
        <img class="login-logo" src="<?=$logoFileUrl?>" alt="IceHrm for Managing Employees Data, Vacation, Attendance and Recruitment. A complete HR solution for your company"/>
        <p class="login-subtitle">Sign in to your account</p>

        <?php if ($googleAuthEnabled) { ?>
            <a class="social-btn" onclick="authGoogle(); return false;" href="#">
                <svg width="18" height="18" viewBox="0 0 48 48" aria-hidden="true"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/><path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/></svg>
                Log in with Google
            </a>
        <?php }?>
        <?php if ($microsoftAuthEnabled) {?>
            <a class="social-btn" onclick="authMicrosoft(); return false;" href="#">
                <svg width="18" height="18" viewBox="0 0 23 23" aria-hidden="true"><rect x="1" y="1" width="10" height="10" fill="#f25022"/><rect x="12" y="1" width="10" height="10" fill="#7fba00"/><rect x="1" y="12" width="10" height="10" fill="#00a4ef"/><rect x="12" y="12" width="10" height="10" fill="#ffb900"/></svg>
                Continue with Microsoft
            </a>
        <?php }?>

        <?php if (!isset($_REQUEST['cp'])) {?>
            <?php if ($googleAuthEnabled || $microsoftAuthEnabled) {?>
                <div class="divider">Or</div>
            <?php }?>

            <form id="loginForm" action="login.php" method="POST">
                <input type="hidden" id="next" name="next" value="<?=htmlentities(isset($_REQUEST['next']) ? $_REQUEST['next'] : '', ENT_QUOTES, 'UTF-8')?>"/>
                <input type="hidden" id="csrf" name="csrf" value="<?=$csrfToken?>"/>
                <div class="field">
                    <label for="username">Email or Username</label>
                    <input placeholder="Enter username or email" id="username" name="username" autocomplete="username">
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="password" name="password" placeholder="Enter password" autocomplete="current-password">
                        <button type="button" class="pw-toggle" onclick="icehrmTogglePassword(this)" aria-label="Show password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <?php if (isset($_REQUEST['f'])) {?>
                    <div class="alert alert-danger" role="alert">
                        Login failed<?php if (isset($_REQUEST['fm'])) {
                            echo htmlspecialchars($_REQUEST['fm'], ENT_QUOTES, 'UTF-8');
                        }?>
                    </div>
                <?php } ?>
                <?php if (isset($_REQUEST['locked'])) {?>
                    <div class="alert alert-warning" role="alert">
                        Your account is locked after too many failed login attempts.
                        Please <a href="#" onclick="showLoginWithCode();return false;">log in with an email code</a> to continue.
                    </div>
                <?php } ?>
                <?php if (isset($_REQUEST['c'])) {?>
                    <div class="alert alert-info" role="alert">
                        Password changed successfully
                    </div>
                <?php } ?>
                <div class="field mb-8">
                    <button class="btn" type="button" onclick="submitLogin();return false;">Log in</button>
                </div>
                <div class="divider">Or</div>
                <div class="field mb-8">
                    <button class="btn-outline" type="button" onclick="showLoginWithCode();return false;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        Login with Email Code
                    </button>
                </div>
                <p class="login-links">Can't remember your password? <a href="" onclick="showForgotPassword();return false;">Reset Password</a></p>
            </form>

            <form id="loginWithCodeForm" style="display:none;" action="">
                <div class="field">
                    <label for="codeEmail">Email</label>
                    <input type="email" placeholder="Enter your email address" id="codeEmail" name="codeEmail" autocomplete="email">
                </div>
                <div id="loginCodeFormAlert" class="alert alert-info" role="alert" style="display: none;"></div>
                <div class="field mb-8" id="requestCodeBtn">
                    <button class="btn" type="button" onclick="requestLoginCode();return false;">Send Login Code to Email</button>
                </div>
                <div id="enterCodeSection" style="display:none;">
                    <div class="field">
                        <label for="loginCode">Login Code</label>
                        <input placeholder="Enter the code from email" id="loginCode" name="loginCode" autocomplete="one-time-code">
                    </div>
                    <div class="field mb-8">
                        <button class="btn" type="button" onclick="submitLoginWithCode();return false;">Log in with Code</button>
                    </div>
                </div>
                <div class="field mb-8">
                    <button class="btn-outline" type="button" onclick="window.location = '<?=CLIENT_BASE_URL?>login.php'">Back</button>
                </div>
            </form>

            <?php if (defined('DEMO_MODE')) {?>
                <div class="demo-box">
                    <h5>Demo Logins</h5>
                    <p>Admin: admin / admin</p>
                    <p>Manager: manager / demouserpwd</p>
                    <p>User: user1 / demouserpwd</p>
                    <p>User: user2 / demouserpwd</p>
                </div>
            <?php }?>

            <form id="requestPasswordChangeForm" style="display:none;" action="">
                <div class="field">
                    <label for="usernameChange">Email or Username</label>
                    <input placeholder="Enter username or email" id="usernameChange" name="usernameChange">
                </div>
                <div id="requestPasswordChangeFormAlert" class="alert alert-warning" role="alert" style="display: none;"></div>
                <div class="field mb-8">
                    <button class="btn" type="button" onclick="requestPasswordChange();return false;">Request Password Change</button>
                </div>
                <div class="field mb-8">
                    <button class="btn-outline" type="button" onclick="window.location = '<?=CLIENT_BASE_URL?>login.php'">Back</button>
                </div>
            </form>
        <?php } else {?>
            <form id="newPasswordForm" action="">
                <div class="field">
                    <label for="password">Password</label>
                    <div class="pw-wrap">
                        <input type="password" id="password" name="password" placeholder="Enter new password" autocomplete="new-password">
                        <button type="button" class="pw-toggle" onclick="icehrmTogglePassword(this)" aria-label="Show password">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div id="newPasswordFormAlert" class="alert alert-warning" role="alert" style="display: none;"></div>
                <div class="field mb-8">
                    <button class="btn" type="button" onclick="changePassword(key);return false;">Change Password</button>
                </div>
            </form>
        <?php }?>
    </div>
</div>
</body>
</html>
