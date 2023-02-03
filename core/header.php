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

if(empty($companyName) || $companyName == "Sample Company Pvt Ltd"){
    $companyName = APP_NAME;
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

?><!DOCTYPE html>
<html>
<head>
    <?php if (!empty(\Classes\BaseService::getInstance()->getGAKey())) { ?>
        <!-- Google Analytics -->
        <script>
            window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
            ga('create', '<?=\Classes\BaseService::getInstance()->getGAKey()?>', 'auto');
            ga('send', 'pageview');
        </script>
        <script async src='https://www.google-analytics.com/analytics.js'></script>
        <!-- End Google Analytics -->
    <?php } else { ?>
        <script>window.ga = [];</script>
    <?php } ?>

    <meta charset="utf-8">
    <title><?=$companyName?></title>
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
            <?=$companyName?>
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
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- Sidebar user panel -->
            <?=\Classes\UIManager::getInstance()->getProfileBlocks();?>

            <ul class="sidebar-menu">


                <?php if($user->user_level == 'Admin' || $user->user_level == 'Manager' || $user->user_level == 'Restricted Admin' || $user->user_level == 'Restricted Manager'){?>

                    <?php foreach($adminModules as $menu){?>
                        <?php if(count($menu['menu']) == 0){continue;}?>
                        <li  class="treeview" ref="<?="admin_".str_replace(" ", "_", $menu['name'])?>">
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
                        <li  class="treeview" ref="<?="module_".str_replace(" ", "_", $menu['name'])?>">
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
                        <li  class="treeview" ref="<?="admin_".str_replace(" ", "_", $menu['name'])?>">
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
                    <li  class="treeview" ref="<?="module_".str_replace(" ", "_", $menu['name'])?>">
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


