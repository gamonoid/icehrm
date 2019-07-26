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
			<div id="EmergencyContact" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmergencyContactForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabEmergencyContact'] = new EmergencyContactAdapter('EmergencyContact');

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
