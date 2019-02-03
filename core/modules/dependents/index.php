<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'emergency_contact';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmployeeDependent" href="#tabPageEmployeeDependent"><?=t('Dependents')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployeeDependent">
			<div id="EmployeeDependent" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeDependentForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabEmployeeDependent'] = new EmployeeDependentAdapter('EmployeeDependent');
<?php if(isset($modulePermissions['perm']['Add Dependents']) && $modulePermissions['perm']['Add Dependents'] == "No"){?>
modJsList['tabEmployeeDependent'].setShowAddNew(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Delete Dependents']) && $modulePermissions['perm']['Delete Dependents'] == "No"){?>
modJsList['tabEmployeeDependent'].setShowDelete(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Edit Dependents']) && $modulePermissions['perm']['Edit Dependents'] == "No"){?>
modJsList['tabEmployeeDependent'].setShowEdit(false);
<?php }?>

var modJs = modJsList['tabEmployeeDependent'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
