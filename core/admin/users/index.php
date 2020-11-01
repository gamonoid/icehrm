<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\PermissionManager;
use Users\Common\Model\UserRole;

$moduleName = 'users';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
$csrf = \Classes\BaseService::getInstance()->generateCsrf('User');
$modelClasses = array_keys(\Classes\BaseService::getInstance()->getModelClassMap());
$modelClasses = array_map(function($item) {
    return [ $item, $item ];
}, $modelClasses);

?><div class="span9">
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabUser" href="#tabPageUser"><?=t('Users')?></a></li>
		<li class=""><a id="tabUserRole" href="#tabPageUserRole"><?=t('Users Roles')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageUser">
			<div id="User" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="UserForm" class="reviewBlock" data-content="Form" data-csrf="<?=$csrf?>" style="padding-left:5px;display:none;">

			</div>
		</div>
        <div class="tab-pane" id="tabPageUserRole">
            <div id="UserRoleTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="UserRoleForm"></div>
            <div id="UserRoleFilterForm"></div>
        </div>
	</div>

</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'UserRole' => PermissionManager::checkGeneralAccess(new UserRole()),
    ]];
?>
<script>
var modJsList = [];
modJsList['tabUser'] = new UserAdapter('User');
modJsList['tabUser'].setCSRFRequired(true);
modJsList['tabUser'].setRemoteTable(true);;
<?php if(isset($_GET['action']) && $_GET['action'] == "new" && isset($_GET['object'])){?>
modJsList['tabUser'].newInitObject = JSON.parse(Base64.decode('<?=$_GET['object']?>'));
<?php }?>
modJsList['tabUserRole'] = new UserRoleAdapter('UserRole');
modJsList['tabUserRole'].setTables(<?=json_encode($modelClasses)?>);
modJsList['tabUserRole'].setObjectTypeName('User Role');
modJsList['tabUserRole'].setDataPipe(new IceDataPipe(modJsList['tabUserRole']));
modJsList['tabUserRole'].setAccess(<?=json_encode($moduleData['permissions']['UserRole'])?>);
var modJs = modJsList['tabUser'];

</script>
<div id="UserRoleFormReact"></div>
<div id="dataGroup"></div>
<?php include APP_BASE_PATH.'footer.php';?>
