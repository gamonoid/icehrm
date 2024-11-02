<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\PermissionManager;
use Model\UserInvitation;
use Users\Common\Model\User;
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
		<li class=""><a id="tabUserInvitation" href="#tabPageUserInvitation"><?=t('Users Invitations')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageUser">
			<div id="UserTable" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="UserForm" data-content="Form" data-csrf="<?=$csrf?>">

			</div>
            <div id="UserPasswordChangeForm" data-content="Form" data-csrf="<?=$csrf?>">

			</div>
		</div>
        <div class="tab-pane" id="tabPageUserRole">
            <div id="UserRoleTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="UserRoleForm"></div>
            <div id="UserRoleFilterForm"></div>
        </div>
        <div class="tab-pane" id="tabPageUserInvitation">
            <div id="UserInvitationTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="UserInvitationForm" data-content="Form"></div>
        </div>
	</div>

</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'User' => PermissionManager::checkGeneralAccess(new User()),
        'UserRole' => PermissionManager::checkGeneralAccess(new UserRole()),
        'UserInvitation' => PermissionManager::checkGeneralAccess(new UserInvitation()),
    ]];
?>
<script>
var modJsList = [];
modJsList['tabUser'] = new UserAdapter('User');
modJsList['tabUser'].setCSRFRequired(true);
modJsList['tabUser'].setRemoteTable(true);
modJsList['tabUser'].setObjectTypeName('User');
modJsList['tabUser'].setDataPipe(new IceDataPipe(modJsList['tabUser']));
modJsList['tabUser'].setAccess(<?=json_encode($moduleData['permissions']['UserRole'])?>);

modJsList['tabUserRole'] = new UserRoleAdapter('UserRole');
modJsList['tabUserRole'].setTables(<?=json_encode($modelClasses)?>);
modJsList['tabUserRole'].setObjectTypeName('User Role');
modJsList['tabUserRole'].setDataPipe(new IceDataPipe(modJsList['tabUserRole']));
modJsList['tabUserRole'].setAccess(<?=json_encode($moduleData['permissions']['UserRole'])?>);

modJsList['tabUserInvitation'] = new UserInvitationAdapter('UserInvitation');
modJsList['tabUserInvitation'].setObjectTypeName('User Invitation');
modJsList['tabUserInvitation'].setRemoteTable(true);
modJsList['tabUserInvitation'].setDataPipe(new IceDataPipe(modJsList['tabUserInvitation']));
modJsList['tabUserInvitation'].setAccess(<?=json_encode($moduleData['permissions']['UserInvitation'])?>);
modJsList['tabUserInvitation'].setModalType(UserInvitationAdapter.MODAL_TYPE_STEPS);

var modJs = modJsList['tabUser'];

</script>
<div id="UserRoleFormReact"></div>
<div id="dataGroup"></div>
<?php include APP_BASE_PATH.'footer.php';?>
