<?php
use Classes\SAMLManager;use Classes\SettingsManager;use Utils\LogManager;define('CLIENT_PATH', dirname(__FILE__));
include("config.base.php");
include("include.common.php");
include("server.includes.inc.php");

$gsuiteEnabled = SettingsManager::getInstance()->getSetting('System: G Suite Enabled');
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

    if ((!empty($_REQUEST['username']) && !empty($_REQUEST['password']))
        || isset($_POST['SAMLResponse'])
    ) {
        $suser = null;
        $ssoUserLoaded = false;

        if($_REQUEST['username'] != "admin") {
            if (SettingsManager::getInstance()->getSetting("LDAP: Enabled") === "1") {
                $ldapResp = \Classes\LDAPManager::getInstance()->checkLDAPLogin($_REQUEST['username'], $_REQUEST['password']);
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
                $suser = null;
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
        } else {
            $next = !empty($_REQUEST['next'])?'&next='.$_REQUEST['next']:'';
            header("Location:".CLIENT_BASE_URL."login.php?f=1".$next);
            exit();
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
$logoFileUrl = \Classes\UIManager::getInstance()->getCompanyLogoUrl();

$csrfToken = sha1(rand(4500, 100000) . time(). CLIENT_BASE_URL);
\Utils\SessionUtils::saveSessionObject('csrf-login', $csrfToken);
?><!DOCTYPE html>
<html lang="en" style="
    width: 97.5%;
    height: 100%;
    display: table;
"><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>IceHrm Login</title>
    <link rel="shortcut icon" href="<?=BASE_URL?>image/favicon.ico" type="image/x-icon">

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
    <div class="row no-gutters" style="
    height: 100%;
">
        <div class="col-lg-5 col-md-5">
            <div class="pt-10 pb-6 pl-11 pr-12 bg-black-2 h-100 d-flex flex-column dark-mode-texts">
                <div class="pb-9">
                    <h3 class="font-size-7 text-white line-height-reset pb-4 line-height-1p4">
                        <?=empty($companyName) || $companyName === 'Sample Company Pvt Ltd' ? 'IceHrm Login' : $companyName?>
                    </h3>
                    <p class="mb-0 font-size-4 text-white">Log in to continue to your IceHrm account</p>
                    <?php if (false || defined('DEMO_MODE')) {?>
                        <br />
                        <br />
                        <div class="col-md-12">
                            <a href="#" class="media bg-white rounded-4 pl-8 pt-9 pb-9 pr-7 hover-shadow-1 mb-9 shadow-8">
                                <div class="text-pink bg-pink-opacity-1 circle-56 font-size-6 mr-7">
                                    <i class="fas fa-user"></i>
                                </div>
                                <!-- Category Content -->
                                <div class="">
                                    <h5 class="font-size-5 font-weight-semibold text-black-2 line-height-reset font-weight-bold mb-1">Demo Logins</h5>
                                    <p class="font-size-3 font-weight-normal text-gray mb-0">Admin: (Username = admin/ Password = admin)</p>
                                    <p class="font-size-3 font-weight-normal text-gray mb-0">Manager: (Username = manager/ Password = demouserpwd)</p>
                                    <p class="font-size-3 font-weight-normal text-gray mb-0">User: (Username = user1/ Password = demouserpwd)</p>
                                    <p class="font-size-3 font-weight-normal text-gray mb-0">User: (Username = user2/ Password = demouserpwd)</p>
                                </div>
                            </a>
                        </div>
                    <?php } else {?>
                        <img src="<?=BASE_URL?>images/icehrm-login.png" style="width:80%;margin-top: 20%;"/>
                    <?php }?>
                </div>
                <div class="border-top border-default-color-2 mt-auto">
                    <div class="d-flex mx-n9 pt-6 flex-xs-row flex-column">
                        <div class="pt-5 px-3">
                            <a href="https://www.linkedin.com/company/ice-hrm---human-resource-management" target="_blank">
                                <p class="bg-white circle-56 font-size-6 mr-7">
                                    <i class="fab fa-linkedin"></i>
                                </p>
                            </a>
                        </div>
                        <div class="pt-5 px-3">
                            <a href="https://www.facebook.com/icehrm" target="_blank">
                            <p class="bg-white circle-56 font-size-6 mr-7">
                                <i class="fab fa-facebook-square"></i>
                            </p>
                            </a>
                        </div>
                        <div class="pt-5 px-3">
                            <a href="https://twitter.com/icehrmapp" target="_blank">
                                <p class="bg-white circle-56 font-size-6 mr-7">
                                    <i class="fab fa-twitter-square"></i>
                                </p>
                            </a>
                        </div>
                        <div class="pt-5 px-3">
                            <a href="https://github.com/gamonoid/icehrm" target="_blank">
                                <p class="bg-white circle-56 font-size-6 mr-7">
                                    <i class="fab fa-github-square"></i>
                                </p>
                            </a>
                        </div>
                        <div class="pt-5 px-3">
                            <a href="https://icehrm.com" target="_blank">
                                <p class="bg-white circle-56 font-size-6 mr-7">
                                    <i class="fas fa-blog"></i>
                                </p>
                            </a>
                        </div>
                        <div class="pt-5 px-3">
                            <a href="https://icehrm.gitbook.io/icehrm/" target="_blank">
                                <p class="bg-white circle-56 font-size-6 mr-7">
                                    <i class="fas fa-question-circle"></i>
                                </p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7 col-md-7">
            <div class="row" style="padding-top:12%;">
                <div class="col-lg-3 col-md-2 col-xs-1"></div>
                <div class="col-lg-6 col-md-8 col-xs-10">
                    <div class="bg-white-2 h-100 px-11 pt-11 pb-7">
                        <div class="row d-flex justify-content-center">
                            <img style="max-width: 100%;" src="<?=$logoFileUrl?>"/>
                        </div>
                        <hr/>
                        <?php if ($gsuiteEnabled) {?>
                            <div class="row">
                                <div class="col-4 col-xs-12">

                                </div>
                                <div class="col-4 col-xs-12">
                                    <a onclick="authGoogle(); return false;" class="font-size-4 font-weight-semibold position-relative text-white bg-poppy h-px-48 flex-all-center w-100 px-6 rounded-5 mb-4"><i class="fab fa-google pos-xs-abs-cl font-size-7 ml-xs-4"></i> <span class="d-none d-xs-block">Log in with Google</span></a>
                                </div>
                                <div class="col-4 col-xs-12">

                                </div>
                            </div>
                            <div class="or-devider">
                                <span class="font-size-3 line-height-reset ">Or</span>
                            </div>
                        <?php }?>
                        <?php if (!isset($_REQUEST['cp'])) {?>
                        <form id="loginForm" action="login.php" method="POST">
                            <input type="hidden" id="next" name="next" value="<?=$_REQUEST['next']?>"/>
                            <input type="hidden" id="csrf" name="csrf" value="<?=$csrfToken?>"/>
                            <div class="form-group">
                                <label for="username" class="font-size-4 text-black-2 font-weight-semibold line-height-reset">E-mail or Username</label>
                                <input class="form-control" placeholder="Enter username or email" id="username" name="username">
                            </div>
                            <div class="form-group">
                                <label for="password" class="font-size-4 text-black-2 font-weight-semibold line-height-reset">Password</label>
                                <div class="position-relative">
                                    <input type="password" id="password" name="password" class="form-control" id="password" placeholder="Enter password">
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
                                <button class="btn btn-info btn-medium w-100 rounded-5 text-uppercase" type="button" onclick="submitLogin();return false;">Log in </button>
                            </div>
                            <p class="font-size-4 text-center heading-default-color">Can't remember your password? <a href="" class="text-info" onclick="showForgotPassword();return false;">Reset Password</a></p>
                        </form>
                        <form id="requestPasswordChangeForm" style="display:none;" action="">
                            <div class="form-group">
                                <label for="username" class="font-size-4 text-black-2 font-weight-semibold line-height-reset">E-mail or Username</label>
                                <input class="form-control" placeholder="Enter username or email" id="usernameChange" name="usernameChange">
                            </div>
                            <div id="requestPasswordChangeFormAlert" class="alert alert-warning" role="alert" style="display: none;">

                            </div>
                            <div class="form-group mb-8">
                                <button class="btn btn-info btn-medium w-100 rounded-5 text-uppercase" type="button" onclick="requestPasswordChange();return false;">Request Password Change&nbsp;&nbsp;<span class="icon-arrow-right"></span></button>
                            </div>
                            <div class="form-group mb-8">
                                <button class="btn btn-outline-info btn-small w-100 rounded-5 text-uppercase" type="button" onclick="window.location = '<?=CLIENT_BASE_URL?>/login.php'">Back&nbsp;&nbsp;<span class="icon-arrow-right"></span></button>
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
                                    <button class="btn btn-info btn-medium w-100 rounded-5 text-uppercase" type="button" onclick="changePassword(key);return false;">Change Password <span class="icon-arrow-right"></span></button>
                                </div>
                            </form>
                        <?php }?>
                    </div>
                </div>
                <div class="col-lg-3 col-md-2 col-xs-1"></div>
            </div>
        </div>
    </div>
</div>


</body></html>
