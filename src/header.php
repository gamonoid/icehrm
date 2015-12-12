<?php
/*
This file is part of Ice Framework.

Ice Framework is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Ice Framework is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ice Framework. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

include 'includes.inc.php';
if(empty($user)){
    $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    SessionUtils::saveSessionObject('loginRedirect',$actual_link);
	header("Location:".CLIENT_BASE_URL."login.php");
}

if(empty($user->default_module)){
    if($user->user_level == "Admin"){
        $homeLink = HOME_LINK_ADMIN;
    }else{
        $homeLink = HOME_LINK_OTHERS;
    }
}else{
    $defaultModule = new Module();
    $defaultModule->Load("id = ?",array($user->default_module));
    $homeLink = CLIENT_BASE_URL."?g=".$defaultModule->mod_group."&&n=".$defaultModule->name.
        "&m=".$defaultModule->mod_group."_".str_replace(" ","_",$defaultModule->menu);
}


//Check Module Permissions
$modulePermissions = BaseService::getInstance()->loadModulePermissions($_REQUEST['g'], $_REQUEST['n'],$user->user_level);


if(!in_array($user->user_level, $modulePermissions['user'])){

    if(!empty($user->user_roles)){
        $userRoles = json_decode($user->user_roles,true);
    }else{
        $userRoles = array();
    }
    $commonRoles = array_intersect($modulePermissions['user_roles'], $userRoles);
    if(empty($commonRoles)){
        echo "You are not allowed to access this page";
        header("Location:".CLIENT_BASE_URL."logout.php");
        exit();
    }

}


$logoFileName = CLIENT_BASE_PATH."data/logo.png";
$logoFileUrl = CLIENT_BASE_URL."data/logo.png";
if(!file_exists($logoFileName)){
	$logoFileUrl = BASE_URL."images/logo.png";	
}

$companyName = SettingsManager::getInstance()->getSetting('Company: Name');

//Load meta info
$meta = json_decode(file_get_contents(MODULE_PATH."/meta.json"),true);

include('configureUIManager.php');

?><!DOCTYPE html>
<html>
    <head>
	    <meta charset="utf-8">
	    <title><?=APP_NAME?></title>
	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <meta name="description" content="">
	    <meta name="author" content="">
	
	    <link href="<?=BASE_URL?>themecss/bootstrap.min.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>themecss/font-awesome.min.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>themecss/ionicons.min.css" rel="stylesheet">
	    
	    
	    
		
		<script type="text/javascript" src="<?=BASE_URL?>js/jquery2.0.2.min.js"></script>
		
	    <script src="<?=BASE_URL?>themejs/bootstrap.js"></script>
		<script src="<?=BASE_URL?>js/jquery.placeholder.js"></script>
		<script src="<?=BASE_URL?>js/base64.js"></script>

		
		<script src="<?=BASE_URL?>js/bootstrap-datepicker.js"></script>
		<script src="<?=BASE_URL?>js/jquery.timepicker.js"></script>
		<script src="<?=BASE_URL?>js/bootstrap-datetimepicker.js"></script>
		<script src="<?=BASE_URL?>js/select2/select2.min.js"></script>
		<script src="<?=BASE_URL?>js/bootstrap-colorpicker-2.1.1/js/bootstrap-colorpicker.min.js"></script>

        <!--fullcaledar-->

        <link href="<?=BASE_URL?>js/fullcaledar/fullcalendar.css" rel="stylesheet">
        <link href="<?=BASE_URL?>js/fullcaledar/fullcalendar.print.css" rel="stylesheet" media="print">
        <script src="<?=BASE_URL?>js/fullcaledar/lib/moment.min.js"></script>
        <script src="<?=BASE_URL?>js/fullcaledar/fullcalendar.min.js"></script>

	    <link href="<?=BASE_URL?>themecss/datatables/dataTables.bootstrap.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>css/jquery.timepicker.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>css/datepicker.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>css/bootstrap-datetimepicker.min.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>js/select2/select2.css" rel="stylesheet">
	    <link href="<?=BASE_URL?>js/bootstrap-colorpicker-2.1.1/css/bootstrap-colorpicker.min.css" rel="stylesheet">
	    
	    <link href="<?=BASE_URL?>themecss/AdminLTE.css" rel="stylesheet">
	    
	    <script src="<?=BASE_URL?>themejs/plugins/datatables/jquery.dataTables.js?v=<?=$jsVersion?>"></script>
		<script src="<?=BASE_URL?>themejs/plugins/datatables/dataTables.bootstrap.js?v=<?=$jsVersion?>"></script>
		<script src="<?=BASE_URL?>themejs/AdminLTE/app.js"></script>
	    
	    
	    <link href="<?=BASE_URL?>css/style.css?v=<?=$cssVersion?>" rel="stylesheet">
	    
	    
	    <script type="text/javascript" src="<?=BASE_URL?>js/date.js"></script>
		<script type="text/javascript" src="<?=BASE_URL?>js/json2.js"></script>
		<script type="text/javascript" src="<?=BASE_URL?>js/CrockfordInheritance.v0.1.js"></script>
	
		<script type="text/javascript" src="<?=BASE_URL?>api/Base.js?v=<?=$jsVersion?>"></script>
		<script type="text/javascript" src="<?=BASE_URL?>api/AdapterBase.js?v=<?=$jsVersion?>"></script>
		<script type="text/javascript" src="<?=BASE_URL?>api/FormValidation.js?v=<?=$jsVersion?>"></script>
		<script type="text/javascript" src="<?=BASE_URL?>api/Notifications.js?v=<?=$jsVersion?>"></script>
		<script type="text/javascript" src="<?=BASE_URL?>api/TimeUtils.js?v=<?=$jsVersion?>"></script>
		<script type="text/javascript" src="<?=BASE_URL?>api/AesCrypt.js?v=<?=$jsVersion?>"></script>
		<script type="text/javascript" src="<?=BASE_URL?>api/SocialShare.js?v=<?=$jsVersion?>"></script>
		<?php include 'modulejslibs.inc.php';?>
	
	
	    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	    <!--[if lt IE 9]>
	    	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	    	<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	    <![endif]-->
		<script>
				var baseUrl = '<?=CLIENT_BASE_URL?>service.php';
				var CLIENT_BASE_URL = '<?=CLIENT_BASE_URL?>';
		</script>
		<script type="text/javascript" src="<?=BASE_URL?>js/app-global.js"></script>
		
		
	
  	</head>
    <body class="skin-blue">
    	<script>
		  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
		  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
		  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
		  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
		
		  ga('create', '<?=BaseService::getInstance()->getGAKey()?>', 'gamonoid.com');
		  ga('send', 'pageview');
	
	  	</script>
	  	<script type="text/javascript">
	  			
			
		</script>
		
        <header id="delegationDiv" class="header">
            <a href="<?=$homeLink?>" class="logo" style="font-family: 'Source Sans Pro', sans-serif;">
             <?=APP_NAME?>   
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
                <div class="navbar-right">
                    <ul class="nav navbar-nav">
                    	<?=UIManager::getInstance()->getMenuItemsHTML();?>
                    </ul>
                </div>
            </nav>
        </header>
        <div class="wrapper row-offcanvas row-offcanvas-left">
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="left-side sidebar-offcanvas">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <!-- Sidebar user panel -->
                    <?=UIManager::getInstance()->getProfileBlocks();?>
                  
                    <ul class="sidebar-menu">
                    	
                        
                        <?php if($user->user_level == 'Admin' || $user->user_level == 'Manager' || $user->user_level == 'Other'){?>
			            
			            <?php foreach($adminModules as $menu){?>
			            	<?php if(count($menu['menu']) == 0){continue;}?>
			            	<li  class="treeview" ref="<?="admin_".str_replace(" ", "_", $menu['name'])?>">			       
			            		<a href="#">
                                	<i class="fa <?=!isset($mainIcons[$menu['name']])?"fa-th":$mainIcons[$menu['name']];?>"></i></i> <span><?=$menu['name']?></span>
                                	<i class="fa fa-angle-left pull-right"></i>
                            	</a>
			            	
				            	<ul class="treeview-menu" id="<?="admin_".str_replace(" ", "_", $menu['name'])?>">
				            	<?php foreach ($menu['menu'] as $item){?>
					            		<li>
					            			<a href="<?=CLIENT_BASE_URL?>?g=admin&n=<?=$item['name']?>&m=<?="admin_".str_replace(" ", "_", $menu['name'])?>">
					            			<i class="fa <?=!isset($item['icon'])?"fa-angle-double-right":$item['icon']?>"></i> <?=$item['label']?>
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
                                	<i class="fa <?=!isset($mainIcons[$menu['name']])?"fa-th":$mainIcons[$menu['name']];?>"></i></i> <span><?=$menu['name']?></span>
                                	<i class="fa fa-angle-left pull-right"></i>
                            	</a>
			            	
				            	<ul class="treeview-menu" id="<?="module_".str_replace(" ", "_", $menu['name'])?>">
				            	<?php foreach ($menu['menu'] as $item){?>
				            		<li>
				            			<a href="<?=CLIENT_BASE_URL?>?g=modules&n=<?=$item['name']?>&m=<?="module_".str_replace(" ", "_", $menu['name'])?>">
				            			<i class="fa <?=!isset($item['icon'])?"fa-angle-double-right":$item['icon']?>"></i> <?=$item['label']?>
				            			</a>
				            		</li>
				            	<?php }?>
				            	</ul>
			            	</li>
			            <?php }?>
			            
			            <?php }?>
                        
                        
                        
                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">                
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1>
                        <?=$meta['label']?>
                        <small>
                        	<?=$meta['menu']?>&nbsp;&nbsp;
                        	<a href="#" class="helpLink" target="_blank" style="display:none;color:#fff;"><i class="glyphicon glyphicon-question-sign"></i></a>
                        </small>
                    </h1>
                </section>

                <!-- Main content -->
                <section class="content">
                 

                