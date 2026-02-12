<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'emergency_contact';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmergencyContact" href="#tabPageEmergencyContact"><?=t('Emergency Contacts')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmergencyContact">
			<div id="EmergencyContactTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmergencyContactForm" data-content="Form"></div>
		</div>
	</div>

</div>
<?php
$permissions = ['get', 'element', 'save', 'delete'];
if(isset($modulePermissions['perm']['Add Emergency Contacts']) && $modulePermissions['perm']['Add Emergency Contacts'] == "No"){
	$permissions = array_diff($permissions, ['save']);
}
if(isset($modulePermissions['perm']['Delete Emergency Contacts']) && $modulePermissions['perm']['Delete Emergency Contacts'] == "No"){
	$permissions = array_diff($permissions, ['delete']);
}
if(isset($modulePermissions['perm']['Edit Emergency Contacts']) && $modulePermissions['perm']['Edit Emergency Contacts'] == "No"){
	$permissions = array_diff($permissions, ['save']);
}
$permissions = array_values($permissions); // Re-index array
?>
<script>
var modJsList = [];

modJsList['tabEmergencyContact'] = new EmergencyContactAdapter('EmergencyContact', 'EmergencyContact', '', '');
modJsList['tabEmergencyContact'].setObjectTypeName('Emergency Contact');
modJsList['tabEmergencyContact'].setDataPipe(new IceDataPipe(modJsList['tabEmergencyContact']));
modJsList['tabEmergencyContact'].setAccess(<?=json_encode($permissions)?>);
<?php if(isset($modulePermissions['perm']['Add Emergency Contacts']) && $modulePermissions['perm']['Add Emergency Contacts'] == "No"){?>
modJsList['tabEmergencyContact'].setShowAddNew(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Delete Emergency Contacts']) && $modulePermissions['perm']['Delete Emergency Contacts'] == "No"){?>
modJsList['tabEmergencyContact'].setShowDelete(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Edit Emergency Contacts']) && $modulePermissions['perm']['Edit Emergency Contacts'] == "No"){?>
modJsList['tabEmergencyContact'].setShowEdit(false);
<?php }?>

var modJs = modJsList['tabEmergencyContact'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
