<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'users';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
$csrf = \Classes\BaseService::getInstance()->generateCsrf('User');
?><div class="span9">
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabUser" href="#tabPageUser"><?=t('Users')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageUser">
			<div id="User" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="UserForm" class="reviewBlock" data-content="Form" data-csrf="<?=$csrf?>" style="padding-left:5px;display:none;">

			</div>
		</div>
<!--        <div class="tab-pane" id="tabPageUserRole">-->
<!--            <div id="UserRole" class="reviewBlock" data-content="List" style="padding-left:5px;">-->
<!---->
<!--            </div>-->
<!--            <div id="UserRoleForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">-->
<!---->
<!--            </div>-->
<!--        </div>-->
	</div>

</div>
<script>
var modJsList = new Array();
modJsList['tabUser'] = new UserAdapter('User');
modJsList['tabUser'].setCSRFRequired(true);
<?php if(isset($_GET['action']) && $_GET['action'] == "new" && isset($_GET['object'])){?>
modJsList['tabUser'].newInitObject = JSON.parse(Base64.decode('<?=$_GET['object']?>'));
<?php }?>
modJsList['tabUserRole'] = new UserRoleAdapter('UserRole');
var modJs = modJsList['tabUser'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
