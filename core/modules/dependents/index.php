<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'dependents';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmployeeDependent" href="#tabPageEmployeeDependent"><?=t('Dependents')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployeeDependent">
			<div id="EmployeeDependentTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmployeeDependentForm" data-content="Form"></div>
		</div>
	</div>

</div>
<?php
$permissions = ['get', 'element', 'save', 'delete'];
if(isset($modulePermissions['perm']['Add Dependents']) && $modulePermissions['perm']['Add Dependents'] == "No"){
	$permissions = array_diff($permissions, ['save']);
}
if(isset($modulePermissions['perm']['Delete Dependents']) && $modulePermissions['perm']['Delete Dependents'] == "No"){
	$permissions = array_diff($permissions, ['delete']);
}
if(isset($modulePermissions['perm']['Edit Dependents']) && $modulePermissions['perm']['Edit Dependents'] == "No"){
	$permissions = array_diff($permissions, ['save']);
}
$permissions = array_values($permissions); // Re-index array
?>
<script>
var modJsList = [];

modJsList['tabEmployeeDependent'] = new EmployeeDependentAdapter('EmployeeDependent', 'EmployeeDependent', '', '');
modJsList['tabEmployeeDependent'].setObjectTypeName('Employee Dependent');
modJsList['tabEmployeeDependent'].setDataPipe(new IceDataPipe(modJsList['tabEmployeeDependent']));
modJsList['tabEmployeeDependent'].setAccess(<?=json_encode($permissions)?>);
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
