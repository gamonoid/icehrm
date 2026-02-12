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

        if($_REQUEST['username'] != "admin") {
            if (SettingsManager::getInstance()->getSetting("LDAP: Enabled") === "1") {
                $ldapResp = LDAPManager::getInstance()->checkLDAPLogin($_REQUEST['username'], $_REQUEST['password']);
                if ($ldapResp->getStatus() == \Classes\IceResponse::ERROR) {
                    header("Location:" . CLIENT_BASE_URL . "login.php?f=1");
                    exit();
                } else {
                    $suser = new \Users\Common\Model\User();
                    $suser->Load("username = ?", array($_REQUEST['username']));
                    if (empty($suser)) {
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
            LogManager::getInstance()->info('SSO SAML User Email:'.$ssoUserEmail);
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

                LogManager::getInstance()->info('SSO SAML User:'.print_r($suser->email, true));
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

            if (!\Classes\PasswordManager::verifyPassword($_REQUEST['password'], $suser->password)) {
                // Check if this is a login code
                if (!empty($suser->login_hash) && !empty($suser->last_password_requested_at)) {
                    // Check if code is still valid (within 15 minutes)
                    $codeRequestedAt = strtotime($suser->last_password_requested_at);
                    $codeExpiry = $codeRequestedAt + (15 * 60);

                    if (time() < $codeExpiry && password_verify($_REQUEST['password'], $suser->login_hash)) {
                        // Valid login code - clear it after use
                        $suser->login_hash = null;
                        $suser->Save();
                    } else {
                        $suser = null;
                    }
                } else {
                    $suser = null;
                }
            }
        }

        if (empty($suser)) {
            $next = !empty($_REQUEST['next'])?'&next='.$_REQUEST['next']:'';
            header("Location:".CLIENT_BASE_URL."login.php?f=1".$next);
            exit();
        }

        $loginCsrf = \Utils\SessionUtils::getSessionObject('csrf-login');

        if (!$SAMLUserLoaded && ($_REQUEST['csrf'] != $loginCsrf || empty($_REQUEST['csrf']))) {
            $next = !empty($_REQUEST['next'])?'&next='.$_REQUEST['next']:'';
            header("Location:".CLIENT_BASE_URL."login.php?f=1".$next);
            exit();
        }
    }

    if (!empty($suser)) {
        $user = $suser;
        \Utils\SessionUtils::saveSessionObject('user', $user);
        $suser->last_login = date("Y-m-d H:i:s");
        $suser->Save();

        if (!$ssoUserLoaded && !empty(\Classes\BaseService::getInstance()->auditManager)) {
            \Classes\BaseService::getInstance()->auditManager->user = $user;
            \Classes\BaseService::getInstance()->audit(\Classes\IceConstants::AUDIT_AUTHENTICATION, "User Login");
        }

        if (!empty($_REQUEST['next']) && !empty(($loginRedirect = \Base64Url\Base64Url::decode($_REQUEST['next'])))) {
            header("Location:" . CLIENT_BASE_URL.$loginRedirect);
            exit();
        } else {
            if ($user->user_level == "Admin") {
                if (\Utils\SessionUtils::getSessionObject('account_locked') == "1") {
                    header("Location:".CLIENT_BASE_URL."?g=admin&n=billing&m=admin_System");
                    exit();
                } else {
                    header("Location:".HOME_LINK_ADMIN);
                    exit();
                }
            } else {
                if (empty($user->default_module)) {
                    header("Location:".HOME_LINK_OTHERS);
                    exit();
                } else {
                    $defaultModule = new \Modules\Common\Model\Module();
                    $defaultModule->Load("id = ?", array($user->default_module));
                    if ($defaultModule->mod_group == "user") {
                        $defaultModule->mod_group = "modules";
                    }
                    $homeLink = CLIENT_BASE_URL."?g=".$defaultModule->mod_group."&&n=".$defaultModule->name.
                        "&m=".$defaultModule->mod_group."_".str_replace(" ", "_", $defaultModule->menu);
                    header("Location:".$homeLink);
                    exit();
                }
            }
        }
    }
} else {
    if ($user->user_level == "Admin") {
        header("Location:".HOME_LINK_ADMIN);
        exit();
    } else {
        if (empty($user->default_module)) {
            header("Location:".HOME_LINK_OTHERS);
            exit();
        } else {
            $defaultModule = new \Modules\Common\Model\Module();
            $defaultModule->Load("id = ?", array($user->default_module));
            if ($defaultModule->mod_group == "user") {
                $defaultModule->mod_group = "modules";
            }
            $homeLink = CLIENT_BASE_URL."?g=".$defaultModule->mod_group."&n=".$defaultModule->name.
                "&m=".$defaultModule->mod_group."_".str_replace(" ", "_", $defaultModule->menu);
            header("Location:".$homeLink);
            exit();
        }
    }
}

$tuser = \Utils\SessionUtils::getSessionObject('user');
$logoFileUrl = UIManager::getInstance()->getCompanyLogoUrl();

$csrfToken = sha1(rand(4500, 100000) . time(). CLIENT_BASE_URL);
\Utils\SessionUtils::saveSessionObject('csrf-login', $csrfToken);
?><!DOCTYPE html>
<html lang="en" style="
    width: 97.5%;
    /*height: 100%;*/
    /*display: table;*/
    background-color: #0c5460;
"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>IceHrm Login</title>
    <link rel="shortcut icon" href="https://icehrm.s3.amazonaws.com/images/icon16.png">

    <link href="<?=BASE_URL?>dist/login.css?v=<?=$cssVersion?>" rel="stylesheet">
    <script src="<?=BASE_URL?>dist/login.js"></script>
</head>

<body data-aos-easing="ease" data-aos-duration="400" data-aos-delay="0" class="" style="
    height: 100%;
">
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

<div style="
    height: 100%;
">
    <div class="row no-gutters" style="height: 80%">
        <div class="col-lg-12 col-md-12 col-xs-12" style="padding-left: 25%; padding-right: 25%;padding-top: 50px; background-color: #0c5460;">
            <div class="row">
                <div class="col-lg-2 col-md-2 col-xs-0">
                </div>
                <div class="col-lg-8 col-md-8 col-xs-12" style="background-color: #FFF;">
                    <div class="row" style="padding-top:3%;">
                        <div class="col-lg-12 col-md-12 col-xs-12">
                            <div class="bg-white-2 h-100 px-11 pt-11 pb-7">
                                <div class="row d-flex justify-content-center">
                                    <a href="https://icehrm.com" target="_blank">
                                        <img style="max-width: 100%;padding-bottom: 30px;max-height:120px;" src="<?=$logoFileUrl?>" alt="IceHrm for Managing Employees Data, Vacation, Attendance and Recruitment. A complete HR solution for your company"/>
                                    </a>
                                </div>
                                <?php if ($googleAuthEnabled) {?>
                                    <div class="row">
                                        <div class="col-4 col-xs-12">

                                        </div>
                                        <div class="col-4 col-xs-12" style="cursor: pointer;">
                                            <a onclick="authGoogle(); return false;" class="font-size-4 font-weight-semibold position-relative text-white bg-poppy h-px-48 flex-all-center w-100 px-6 mb-4"><i class="fab fa-google pos-xs-abs-cl font-size-7 ml-xs-4"></i> <span class="d-none d-xs-block">Continue with Google</span></a>
                                        </div>
                                        <div class="col-4 col-xs-12">

                                        </div>
                                    </div>
                                <?php }?>
                                <?php if ($microsoftAuthEnabled) {?>
                                    <div class="row">
                                        <div class="col-4 col-xs-12">

                                        </div>
                                        <div class="col-4 col-xs-12" style="cursor: pointer;">
                                            <a onclick="authMicrosoft(); return false;" class="font-size-4 font-weight-semibold position-relative text-white bg-allports h-px-48 flex-all-center w-100 px-6 mb-4"><i class="fab fa-microsoft pos-xs-abs-cl font-size-7 ml-xs-4"></i> <span class="d-none d-xs-block">Continue with Microsoft</span></a>
                                        </div>
                                        <div class="col-4 col-xs-12">

                                        </div>
                                    </div>
                                <?php }?>
                                <?php if (!isset($_REQUEST['cp'])) {?>
                                    <?php if ($googleAuthEnabled || $microsoftAuthEnabled) {?>
                                        <div class="or-devider">
                                            <span class="font-size-3 line-height-reset ">Or</span>
                                        </div>
                                    <?php }?>
                                    <form id="loginForm" action="login.php" method="POST" style1="display: block; box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 1px 2px; border: none; margin-bottom: 20px; padding: 20px;">
                                        <input type="hidden" id="next" name="next" value="<?=$_REQUEST['next']?>"/>
                                        <input type="hidden" id="csrf" name="csrf" value="<?=$csrfToken?>"/>
                                        <div class="form-group">
                                            <label for="username" class="font-size-4 text-black-2 font-weight-semibold line-height-reset">Email or Username</label>
                                            <input class="form-control" placeholder="Enter username or email" id="username" name="username" style="border-radius: 0;">
                                        </div>
                                        <div class="form-group">
                                            <label for="password" class="font-size-4 text-black-2 font-weight-semibold line-height-reset">Password</label>
                                            <div class="position-relative">
                                                <input type="password" id="password" name="password" class="form-control" id="password" placeholder="Enter password" style="border-radius: 0;">
                                                <a href="#" class="show-password pos-abs-cr fas mr-6 text-black-2" data-show-pass="password"></a>
                                            </div>
                                        </div>
                                        <?php if (isset($_REQUEST['f'])) {?>
                                            <div class="alert alert-danger" role="alert">
                                                <i class="fa fa-theater-masks" style="padding-right: 10px;"></i> Login failed
                                                <?php if (isset($_REQUEST['fm'])) {
                                                    echo $_REQUEST['fm'];
                                                }?>
                                            </div>
                                        <?php } ?>
                                        <?php if (isset($_REQUEST['c'])) {?>
                                            <div class="alert alert-info" role="alert">
                                                Password changed successfully
                                            </div>
                                        <?php } ?>
                                        <div class="form-group d-flex flex-wrap justify-content-between">
                                            <!--                                <label for="terms-check" class="gr-check-input d-flex  mr-3">-->
                                            <!--                                    <input class="d-none" type="checkbox" id="terms-check">-->
                                            <!--                                    <span class="checkbox mr-5"></span>-->
                                            <!--                                    <span class="font-size-3 mb-0 line-height-reset mb-1 d-block">Remember password</span>-->
                                            <!--                                </label>-->
                                        </div>
                                        <div class="form-group mb-8">
                                            <button class="btn btn-info btn-medium w-100 text-uppercase" type="button" onclick="submitLogin();return false;">Log in </button>
                                        </div>
                                        <div class="or-devider">
                                            <span class="font-size-3 line-height-reset">Or</span>
                                        </div>
                                        <div class="form-group mb-8">
                                            <button class="btn btn-outline-info btn-medium w-100 text-uppercase" type="button" onclick="showLoginWithCode();return false;"><i class="fas fa-envelope" style="margin-right: 8px;"></i>Login with Email Code</button>
                                        </div>
                                        <p class="font-size-4 text-center heading-default-color">Can't remember your password? <a href="" class="text-info" onclick="showForgotPassword();return false;">Reset Password</a></p>
                                    </form>
                                    <form id="loginWithCodeForm" style="display:none;" action="">
                                        <div class="form-group">
                                            <label for="codeEmail" class="font-size-4 text-black-2 font-weight-semibold line-height-reset">Email</label>
                                            <input type="email" class="form-control" placeholder="Enter your email address" id="codeEmail" name="codeEmail" style="border-radius: 0;">
                                        </div>
                                        <div id="loginCodeFormAlert" class="alert alert-info" role="alert" style="display: none;"></div>
                                        <div class="form-group mb-8" id="requestCodeBtn">
                                            <button class="btn btn-info btn-medium w-100 text-uppercase" type="button" onclick="requestLoginCode();return false;">Send Login Code to Email</button>
                                        </div>
                                        <div id="enterCodeSection" style="display:none;">
                                            <div class="form-group">
                                                <label for="loginCode" class="font-size-4 text-black-2 font-weight-semibold line-height-reset">Login Code</label>
                                                <input class="form-control" placeholder="Enter the code from email" id="loginCode" name="loginCode" style="border-radius: 0;">
                                            </div>
                                            <div class="form-group mb-8">
                                                <button class="btn btn-info btn-medium w-100 text-uppercase" type="button" onclick="submitLoginWithCode();return false;">Log in with Code</button>
                                            </div>
                                        </div>
                                        <div class="form-group mb-8">
                                            <button class="btn btn-outline-info btn-small w-100 text-uppercase" type="button" onclick="window.location = '<?=CLIENT_BASE_URL?>login.php'">Back</button>
                                        </div>
                                    </form>
									<?php if (defined('DEMO_MODE')) {?>
                                        <br />
                                        <br />
                                        <div class="col-md-12">
                                            <a href="#" class="media bg-white rounded-4 pl-8 pt-9 pb-9 pr-7 hover-shadow-1 mb-9 shadow-8" style="border: dashed;">
                                                <div class="text-pink bg-pink-opacity-1 circle-56 font-size-6 mr-7">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                                <!-- Category Content -->
                                                <div class="">
                                                    <h5 class="font-size-5 font-weight-semibold text-black-2 line-height-reset font-weight-bold mb-1">Demo Logins</h5>
                                                    <p class="font-size-3 font-weight-normal text-gray mb-0"> Admin: admin / admin </p>
                                                    <p class="font-size-3 font-weight-normal text-gray mb-0">Manager: manager / demouserpwd</p>
                                                    <p class="font-size-3 font-weight-normal text-gray mb-0">User: user1 / demouserpwd</p>
                                                    <p class="font-size-3 font-weight-normal text-gray mb-0">User: user2 / demouserpwd</p>
                                                </div>
                                            </a>
                                        </div>
									<?php }?>
                                    <form id="requestPasswordChangeForm" style="display:none;" action="">
                                        <div class="form-group">
                                            <label for="username" class="font-size-4 text-black-2 font-weight-semibold line-height-reset">Email or Username</label>
                                            <input class="form-control" placeholder="Enter username or email" id="usernameChange" name="usernameChange">
                                        </div>
                                        <div id="requestPasswordChangeFormAlert" class="alert alert-warning" role="alert" style="display: none;">

                                        </div>
                                        <div class="form-group mb-8">
                                            <button class="btn btn-info btn-medium w-100 text-uppercase" type="button" onclick="requestPasswordChange();return false;">Request Password Change&nbsp;&nbsp;<span class="icon-arrow-right"></span></button>
                                        </div>
                                        <div class="form-group mb-8">
                                            <button class="btn btn-outline-info btn-small w-100 text-uppercase" type="button" onclick="window.location = '<?=CLIENT_BASE_URL?>login.php'">Back&nbsp;&nbsp;<span class="icon-arrow-right"></span></button>
                                        </div>
                                    </form>
                                <?php } else {?>
                                    <form id="newPasswordForm" action="">
                                        <div class="form-group">
                                            <label for="password" class="font-size-4 text-black-2 font-weight-semibold line-height-reset">Password</label>
                                            <div class="position-relative">
                                                <input type="password" id="password" name="password" class="form-control" id="password" placeholder="Enter new password">
                                                <a href="#" class="show-password pos-abs-cr fas mr-6 text-black-2" data-show-pass="password"></a>
                                            </div>
                                        </div>
                                        <div id="newPasswordFormAlert" class="alert alert-warning" role="alert" style="display: none;">

                                        </div>
                                        <div class="form-group mb-8">
                                            <button class="btn btn-info btn-medium w-100 text-uppercase" type="button" onclick="changePassword(key);return false;">Change Password <span class="icon-arrow-right"></span></button>
                                        </div>
                                    </form>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-2 col-xs-0">
                </div>
            </div>
        </div>

    </div>
</div>


</body></html>
