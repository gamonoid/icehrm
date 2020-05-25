<?php
/*
 Copyright (c) 2020 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'clients';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabClient" href="#tabPageClient"><?=t('Clients')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageClient">
			<div id="Client" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="ClientForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = [];

modJsList['tabClient'] = new ClientAdapter('Client','Client');

<?php if(isset($modulePermissions['perm']['Add Clients']) && $modulePermissions['perm']['Add Clients'] == "No"){?>
modJsList['tabClient'].setShowAddNew(false);
<?php }?>

<?php if(isset($modulePermissions['perm']['Delete Clients']) && $modulePermissions['perm']['Delete Clients'] == "No"){?>
modJsList['tabClient'].setShowDelete(false);
<?php }?>

<?php if(isset($modulePermissions['perm']['Edit Clients']) && $modulePermissions['perm']['Edit Clients'] == "No"){?>
modJsList['tabClient'].setShowSave(false);
<?php }?>


var modJs = modJsList['tabClient'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
