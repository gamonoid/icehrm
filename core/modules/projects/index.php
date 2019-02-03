<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'projects';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmployeeProject" href="#tabPageEmployeeProject"><?=t('My Projects')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployeeProject">
			<div id="EmployeeProject" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeProjectForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabEmployeeProject'] = new EmployeeProjectAdapter('EmployeeProject','EmployeeProject');

<?php if(isset($modulePermissions['perm']['Add Projects']) && $modulePermissions['perm']['Add Projects'] == "No"){?>
modJsList['tabEmployeeProject'].setShowAddNew(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Delete Projects']) && $modulePermissions['perm']['Delete Projects'] == "No"){?>
modJsList['tabEmployeeProject'].setShowDelete(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Edit Projects']) && $modulePermissions['perm']['Edit Projects'] == "No"){?>
modJsList['tabEmployeeProject'].setShowEdit(false);
<?php }?>

var modJs = modJsList['tabEmployeeProject'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
