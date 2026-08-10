<?php
if (!defined('MODULE_NAME')) {
    define('MODULE_NAME', $moduleName);
}
include 'includes.inc.php';

if(empty($user) || empty($user->email)){
    $actualLinkArray = explode('/',$_SERVER['REQUEST_URI']);
    header("Location:".CLIENT_BASE_URL."login.php?next=".\Base64Url\Base64Url::encode($actualLinkArray[count($actualLinkArray) - 1]));
    exit();
}

// The standalone legacy UI has been retired. Legacy module pages now render ONLY
// as iframe embeds inside the new React SPA (Sec-Fetch-Dest=iframe). Any TOP-LEVEL
// visit to a legacy page link (bookmark, notification e-mail, etc.) bounces to the
// new UI. We gate on Sec-Fetch-Dest=document so the SPA's own iframe-embedded
// modules are NOT redirected.
$iceTopLevelVisit = isset($_SERVER['HTTP_SEC_FETCH_DEST'])
    && $_SERVER['HTTP_SEC_FETCH_DEST'] === 'document';
if ($iceTopLevelVisit) {
    header("Location:".CLIENT_BASE_URL."ui/");
    exit();
}

if(empty($user->default_module)){
    if($user->user_level == "Admin"){
        $homeLink = HOME_LINK_ADMIN;
    }else{
        $homeLink = HOME_LINK_OTHERS;
    }
}else{
    $defaultModule = new \Modules\Common\Model\Module();
    $defaultModule->Load("id = ?",array($user->default_module));
    if($defaultModule->mod_group == "user"){
        $defaultModule->mod_group = "modules";
    }
    $homeLink = CLIENT_BASE_URL."?g=".$defaultModule->mod_group."&n=".$defaultModule->name.
        "&m=".$defaultModule->mod_group."_".str_replace(" ","_",$defaultModule->menu);
}

if (!\Classes\BaseService::getInstance()->isModuleMenuEnabled($_REQUEST['g'].'>'.$_REQUEST['n'])) {
	header("Location:".CLIENT_BASE_URL."login.php");
	exit();
}

//Check Module Permissions
$modulePermissions = \Classes\BaseService::getInstance()->loadModulePermissions(
    $_REQUEST['g'].'>'.$_REQUEST['n'],
    $user->user_level
);


if(!in_array($user->user_level, $modulePermissions['user'])){

    if(!empty($user->user_roles)){
        $userRoles = json_decode($user->user_roles,true);
    }else{
        $userRoles = array();
    }
    $commonRoles = array_intersect($modulePermissions['user_roles'], $userRoles);
    if(empty($commonRoles)){
        session_start();
        $_SESSION['user'] = null;
        session_destroy();
        session_write_close();
        $user = null;
        header("Location:".CLIENT_BASE_URL."login.php");
        exit();
    }

}

$logoFileUrl = \Classes\UIManager::getInstance()->getCompanyLogoUrl();

$companyName = \Classes\SettingsManager::getInstance()->getSetting('Company: Name');
$companyName = substr($companyName,0,40);
if(empty($companyName) || $companyName == "Sample Company Pvt Ltd"){
    $companyName = 'IceHrm';
}

//Load meta info
$meta = json_decode(file_get_contents(MODULE_PATH."/meta.json"),true);

include('configureUIManager.php');

$chatUserProfile = \Classes\UIManager::getInstance()->getCurrentProfile();

if (defined('SYM_CLIENT')) {
    $restApiBase = WEB_APP_BASE_URL.'/api/'.SYM_CLIENT.'/';
} else if (defined('REST_API_BASE')){
    $restApiBase = REST_API_BASE;
} else {
    $restApiBase = CLIENT_BASE_URL.'api/';
}

// Check IceHrm.com connection status for Admin users
$isConnectedToIceHrm = false;
if ($user->user_level == 'Admin') {
    $connectionService = \Classes\ConnectionService::getInstance();
    $isConnectedToIceHrm = $connectionService->isConnected();
}

?><!DOCTYPE html>
<html>
<head>
    <script>
        // The standalone legacy UI has been retired; legacy pages render only as
        // iframe embeds inside the new SPA. Fallback for browsers that do not send
        // the Sec-Fetch-Dest header (the PHP guard above covers modern browsers):
        // any TOP-LEVEL visit (i.e. NOT inside the SPA's iframe, where
        // window.top !== window.self) is bounced to the new UI.
        (function () {
            try {
                if (window.top === window.self) {
                    window.location.replace('<?=CLIENT_BASE_URL?>ui/');
                }
            } catch (e) { /* framed cross-origin: leave it */ }
        })();
    </script>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo GA4_MEASUREMENT_ID; ?>"></script>
    <script>
        window.ga = [];
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', '<?php echo GA4_MEASUREMENT_ID; ?>');
    </script>

    <meta charset="utf-8">
    <title><?=htmlspecialchars($companyName, ENT_QUOTES)?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="https://icehrm.s3.amazonaws.com/images/icon16.png">
    <meta name="description" content="">
    <meta name="author" content="">

    <link href="<?=BASE_URL?>dist/third-party.css?v=<?=$cssVersion?>" rel="stylesheet">
    <script type="text/javascript" src="<?=BASE_URL?>dist/third-party.js?v=<?=$jsVersion?>"></script>
    <script type="text/javascript" src="<?=BASE_URL?>dist/common.js?v=<?=$jsVersion?>"></script>

    <!-- Can not bundle - Start-->
    <script src="<?=BASE_URL?>js/jquery.timepicker.js"></script>
    <script src="<?=BASE_URL?>js/bootstrap-datetimepicker.js"></script>
    <link href="<?=BASE_URL?>bower_components/flag-icon-css/css/flag-icon.min.css" rel="stylesheet">
    <!-- Can not bundle - End-->

    <script>
        var baseUrl = '<?=CLIENT_BASE_URL?>service.php';
        var CLIENT_BASE_URL = '<?=CLIENT_BASE_URL?>';
        var BASE_URL = '<?=BASE_URL?>';
    </script>
</head>
<body class="skin-blue" data-turbolinks="false">
<header id="delegationDiv" class="header">
    <a href="<?=$homeLink?>" class="logo" style="overflow: hidden;font-family: 'Source Sans Pro', sans-serif;">
        <?=\Classes\LanguageManager::tran('Home')?>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="navbar-btn sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </a>
        <div class="logo logoResponsive">
            <?=htmlspecialchars($companyName, ENT_QUOTES)?>
        </div>
        <div class="navbar-right">
            <ul class="nav navbar-nav">
                <?=\Classes\UIManager::getInstance()->getMenuItemsHTML();?>
            </ul>
        </div>
    </nav>
</header>
<?php if(\Classes\UIManager::getInstance()->getCurrentLanguageCode() === 'ar') {?>
    <link href="<?=BASE_URL?>css/rtl.css" rel="stylesheet">
<?php } ?>
<script>
    var isConnectedToIceHrm = <?=json_encode($isConnectedToIceHrm)?>;
    var currentUserLevel = '<?=$user->user_level?>';
    var isCloudInstance = <?=json_encode(defined('IS_CLOUD') && IS_CLOUD)?>;
</script>
<div class="wrapper row-offcanvas row-offcanvas-left">
    <div id="iceloader" style="
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, 0);
    background-image: url('<?=BASE_URL?>images/icehrm-loader.gif');
    background-repeat: no-repeat;
    background-position: center;display:none;"></div>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="left-side sidebar-offcanvas">
        <div class="skeletonSideMenu">
            <div class="skeleton-menu">
                <div class="skeleton-profile">
                    <div class="skeleton-avatar"></div>
                    <div class="skeleton-name"></div>
                </div>
                <div class="skeleton-item" style="margin-top:50px"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
                <div class="skeleton-item"></div>
            </div>
        </div>
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar" style="display: none;">
            <!-- Sidebar user panel -->
            <?=\Classes\UIManager::getInstance()->getProfileBlocks();?>
            <ul class="sidebar-menu">
                <div id="UserViewSwitch"></div>

                <?php if($user->user_level == 'Admin' || $user->user_level == 'Manager' || $user->user_level == 'Restricted Admin' || $user->user_level == 'Restricted Manager'){?>

                    <?php foreach($adminModules as $menu){?>
                        <?php if(count($menu['menu']) == 0){continue;}?>
                        <li  class="treeview" ref="<?="admin_".str_replace(" ", "_", $menu['name'])?>" id="<?="menu_admin_".str_replace(" ", "_", $menu['name'])?>">
                            <a href="#">
                                <i class="fa <?=!isset($mainIcons[$menu['name']])?"fa-th":$mainIcons[$menu['name']];?>"></i></i> <span><?=\Classes\LanguageManager::tran($menu['name'])?></span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>

                            <ul class="treeview-menu" id="<?="admin_".str_replace(" ", "_", $menu['name'])?>">
                                <?php foreach ($menu['menu'] as $item){?>
                                    <li>
                                        <a data-turbolinks="true" href="<?=CLIENT_BASE_URL?>?g=<?=$item['link_group']??'admin'?>&n=<?=$item['link_name']??$item['name']?>&m=<?="admin_".str_replace(" ", "_", $menu['name'])?>">
                                            <i class="fa <?=!isset($item['icon'])?"fa-angle-double-right":$item['icon']?>"></i> <?=\Classes\LanguageManager::tran($item['label'])?>
                                        </a>
                                    </li>
                                <?php }?>
                            </ul>
                        </li>
                    <?php }?>

                <?php }?>

                <?php if(!empty($profileCurrent) || !empty($profileSwitched)){?>

                    <?php foreach($userModules as $menu){?>

                        <?php if(count($menu['menu']) == 0){continue;}?>
                        <li  class="treeview" ref="<?="module_".str_replace(" ", "_", $menu['name'])?>" id="<?="menu_module_".str_replace(" ", "_", $menu['name'])?>">
                            <a href="#">
                                <i class="fa <?=!isset($mainIcons[$menu['name']])?"fa-th":$mainIcons[$menu['name']];?>"></i></i> <span><?=\Classes\LanguageManager::tran($menu['name'])?></span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>

                            <ul class="treeview-menu" id="<?="module_".str_replace(" ", "_", $menu['name'])?>">
                                <?php foreach ($menu['menu'] as $item){?>
                                    <li>
                                        <a data-turbolinks="true" href="<?=CLIENT_BASE_URL?>?g=<?=$item['link_group']??'modules'?>&n=<?=$item['link_name']??$item['name']?>&m=<?="module_".str_replace(" ", "_", $menu['name'])?>">
                                            <i class="fa <?=!isset($item['icon'])?"fa-angle-double-right":$item['icon']?>"></i> <?=\Classes\LanguageManager::tran($item['label'])?>
                                        </a>
                                    </li>
                                <?php }?>
                            </ul>
                        </li>
                    <?php }?>

                <?php }?>

                <?php if($user->user_level == 'Employee'){?>

                    <?php foreach($adminModules as $menu){?>
                        <?php if(count($menu['menu']) == 0){continue;}?>
                        <li  class="treeview" ref="<?="admin_".str_replace(" ", "_", $menu['name'])?>" id="<?="menu_admin_".str_replace(" ", "_", $menu['name'])?>">
                            <a href="#">
                                <i class="fa <?=!isset($mainIcons[$menu['name']])?"fa-th":$mainIcons[$menu['name']];?>"></i></i> <span><?=\Classes\LanguageManager::tran($menu['name'])?></span>
                                <i class="fa fa-angle-left pull-right"></i>
                            </a>

                            <ul class="treeview-menu" id="<?="admin_".str_replace(" ", "_", $menu['name'])?>">
                                <?php foreach ($menu['menu'] as $item){?>
                                    <li>
                                        <a data-turbolinks="true" href="<?=CLIENT_BASE_URL?>?g=<?=$item['link_group']??'admin'?>&n=<?=$item['link_name']??$item['name']?>&m=<?="admin_".str_replace(" ", "_", $menu['name'])?>">
                                            <i class="fa <?=!isset($item['icon'])?"fa-angle-double-right":$item['icon']?>"></i> <?=\Classes\LanguageManager::tran($item['label'])?>
                                        </a>
                                    </li>
                                <?php }?>
                            </ul>
                        </li>
                    <?php }?>

                <?php }?>

                <?php
                if(file_exists(CLIENT_PATH.'/third_party_meta.json')){
                    $tpModules = json_decode(file_get_contents(CLIENT_PATH.'/third_party_meta.json'),true);
                    foreach($tpModules as $menu){?>

                    <?php if(count($menu['menu']) == 0){continue;}?>
                    <li  class="treeview" ref="<?="module_".str_replace(" ", "_", $menu['name'])?>" id="<?="menu_module_".str_replace(" ", "_", $menu['name'])?>">
                        <a href="#">
                            <i class="fa <?=$menu['icon']?>"></i></i> <span><?=\Classes\LanguageManager::tran($menu['name'])?></span>
                            <i class="fa fa-angle-left pull-right"></i>
                        </a>

                        <ul class="treeview-menu" id="<?="module_".str_replace(" ", "_", $menu['name'])?>">
                            <?php foreach ($menu['menu'] as $item){?>
                                <li>
                                    <a data-turbolinks="true" href="<?=$item['link']?>" target="_blank">
                                        <i class="fa <?=!isset($item['icon'])?"fa-angle-double-right":$item['icon']?>"></i> <?=\Classes\LanguageManager::tran($item['label'])?>
                                    </a>
                                </li>
                            <?php }?>
                        </ul>
                    </li>
                <?php }
                }?>


                <li>
                    <div class="user-panel">
                        <div class="info">
                            <p></p>
                        </div>
                    </div>
                </li>


            </ul>
        </section>
        <!-- /.sidebar -->
    </aside>
    <!-- Right side column. Contains the navbar and content of the page -->
    <aside class="right-side">
        <!-- Main content -->
        <section class="content">
            <?php
            // Check if demo mode prompt should be shown (only for fresh installs with default employee)
            $showDemoPrompt = false;
            $demoDebug = [];
            $isDemoModePage = (isset($_REQUEST['n']) && strpos($_REQUEST['n'], 'demo-mode') !== false);
            $demoDebug['isDemoModePage'] = $isDemoModePage;
            $demoDebug['userLevel'] = $user->user_level;
            $demoExtPath = APP_BASE_PATH . '../extensions/demo-mode/admin/demo-mode.php';
            $demoDebug['extPath'] = $demoExtPath;
            $demoDebug['extExists'] = file_exists($demoExtPath);

            if ($user->user_level == 'Admin' && !$isDemoModePage && file_exists($demoExtPath)) {
                try {
                    $db = \Classes\BaseService::getInstance()->getDB();
                    $empResult = $db->Execute("SELECT COUNT(*) as cnt, MIN(first_name) as fname, MIN(last_name) as lname FROM Employees WHERE status = 'Active'");
                    $row = null;
                    if ($empResult) {
                        if (is_array($empResult)) {
                            $row = isset($empResult[0]) ? $empResult[0] : null;
                        } elseif (is_object($empResult) && method_exists($empResult, 'FetchRow')) {
                            $row = $empResult->FetchRow();
                        }
                    }
                    if ($row) {
                        $demoDebug['empCount'] = (int)$row['cnt'];
                        $demoDebug['empFname'] = $row['fname'];
                        $demoDebug['empLname'] = $row['lname'];
                        if ((int)$row['cnt'] === 1 && $row['fname'] === 'IceHrm' && $row['lname'] === 'Employee') {
                            $tableCheck = $db->Execute("SHOW TABLES LIKE 'DemoDataEntries'");
                            $tableExists = false;
                            if (is_array($tableCheck)) {
                                $tableExists = !empty($tableCheck);
                            } elseif (is_object($tableCheck) && method_exists($tableCheck, 'RecordCount')) {
                                $tableExists = ($tableCheck->RecordCount() > 0);
                            }
                            $demoDebug['tableExists'] = $tableExists;
                            $hasDemoData = false;
                            if ($tableExists) {
                                $demoDataResult = $db->Execute("SELECT COUNT(*) as cnt FROM DemoDataEntries");
                                $demoRow = null;
                                if (is_array($demoDataResult)) {
                                    $demoRow = isset($demoDataResult[0]) ? $demoDataResult[0] : null;
                                } elseif (is_object($demoDataResult) && method_exists($demoDataResult, 'FetchRow')) {
                                    $demoRow = $demoDataResult->FetchRow();
                                }
                                if ($demoRow) {
                                    $hasDemoData = ((int)$demoRow['cnt'] > 0);
                                    $demoDebug['demoDataCount'] = (int)$demoRow['cnt'];
                                }
                            }
                            $showDemoPrompt = !$hasDemoData;
                            $demoDebug['hasDemoData'] = $hasDemoData;
                        }
                    }
                } catch (\Exception $e) {
                    $showDemoPrompt = false;
                    $demoDebug['error'] = $e->getMessage();
                }
            }
            $demoDebug['showDemoPrompt'] = $showDemoPrompt;
            ?>
            <!-- Demo Debug: <?php echo json_encode($demoDebug); ?> -->
            <?php if($showDemoPrompt) { ?>
            <style>
                @keyframes demoPulse {
                    0%, 100% { box-shadow: 0 4px 15px rgba(82, 196, 26, 0.3); }
                    50% { box-shadow: 0 4px 25px rgba(82, 196, 26, 0.6), 0 0 40px rgba(82, 196, 26, 0.4); }
                }
                @keyframes iconPulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
                #DemoModeNotice { animation: demoPulse 2s ease-in-out infinite; }
                #DemoModeNotice .demo-icon { animation: iconPulse 2s ease-in-out infinite; }
            </style>
            <div id="DemoModeNotice" style="
                background: linear-gradient(135deg, #52c41a 0%, #389e0d 100%);
                border-radius: 12px;
                padding: 20px 24px;
                margin-bottom: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 4px 15px rgba(82, 196, 26, 0.3);
                border: 2px solid rgba(255,255,255,0.3);
            ">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div class="demo-icon" style="
                        background: rgba(255,255,255,0.25);
                        border-radius: 50%;
                        width: 48px;
                        height: 48px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <i class="fa fa-rocket" style="color: #fff; font-size: 22px;"></i>
                    </div>
                    <div>
                        <div style="color: #fff; font-weight: 600; font-size: 16px; margin-bottom: 4px;">
                            Welcome to IceHrm! Want to see how it works?
                        </div>
                        <div style="color: rgba(255,255,255,0.9); font-size: 14px;">
                            Add sample employees, projects, attendance, and more to explore all features.
                            <br><strong>You can clear all sample data with one click</strong> when you're ready to go live.
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <a href="<?=CLIENT_BASE_URL?>?g=extension&n=demo-mode|admin&m=admin_System" class="demo-btn" style="
                        background: #fff;
                        color: #389e0d;
                        padding: 12px 28px;
                        border-radius: 8px;
                        text-decoration: none;
                        font-size: 15px;
                        font-weight: 700;
                        transition: all 0.2s ease;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        display: flex;
                        align-items: center;
                        gap: 8px;
                    ">
                        <i class="fa fa-plus-circle"></i> Manage Sample Data
                    </a>
                    <a href="javascript:void(0)" onclick="document.getElementById('DemoModeNotice').style.display='none'" style="
                        background: rgba(255,255,255,0.2);
                        color: #fff;
                        padding: 10px 16px;
                        border-radius: 8px;
                        text-decoration: none;
                        font-size: 14px;
                        font-weight: 500;
                    ">
                        Dismiss
                    </a>
                </div>
            </div>
            <?php } ?>
            <?php if($user->user_level == 'Admin' && !$isConnectedToIceHrm && !(defined('IS_CLOUD') && IS_CLOUD)) { ?>
            <div id="IceHrmConnectionNotice" style="
                background: linear-gradient(135deg, #346CB0 0%, #2a5a9a 100%);
                border-radius: 12px;
                padding: 20px 24px;
                margin-bottom: 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                box-shadow: 0 4px 15px rgba(52, 108, 176, 0.3);
            ">
                <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="
                        background: rgba(255,255,255,0.2);
                        border-radius: 50%;
                        width: 48px;
                        height: 48px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <i class="fa fa-link" style="color: #fff; font-size: 20px;"></i>
                    </div>
                    <div>
                        <div style="color: #fff; font-weight: 600; font-size: 16px; margin-bottom: 4px;">
                            Connect to IceHrm.com
                        </div>
                        <div style="color: rgba(255,255,255,0.85); font-size: 14px;">
                            Access marketplace extensions, updates, and your purchased modules
                        </div>
                    </div>
                </div>
                <a href="<?=CLIENT_BASE_URL?>?g=extension&n=marketplace|admin&m=admin_System#my-extensions" style="
                    background: #fff;
                    color: #346CB0;
                    padding: 10px 24px;
                    border-radius: 8px;
                    text-decoration: none;
                    font-size: 14px;
                    font-weight: 600;
                    transition: all 0.2s ease;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                ">
                    Connect Now
                </a>
            </div>
            <?php } ?>
            <div class="skeletonTabs skeleton-tabs">
                <div class="skeleton-tab selected"></div>
                <div class="skeleton-tab"></div>
                <div class="skeleton-tab"></div>
            </div>
            <div class="skeletonContent skeleton-table" style="margin-top: 50px;">
                <div class="skeleton-row">
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                </div>
                <div class="skeleton-row">
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                </div>
                <div class="skeleton-row">
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                </div>
                <div class="skeleton-row">
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                </div>
                <div class="skeleton-row">
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                </div>
                <div class="skeleton-row">
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                    <div class="skeleton-cell"></div>
                </div>
            </div>

