<?php

use Classes\PermissionManager;
use Salary\Common\Model\EmployeeSalary;
use Salary\Common\Model\SalaryComponent;
use Salary\Common\Model\SalaryComponentType;

$moduleName = 'salary';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabSalaryComponentType" href="#tabPageSalaryComponentType"><?=t('Salary Component Types')?></a></li>
		<li class=""><a id="tabSalaryComponent" href="#tabPageSalaryComponent"><?=t('Salary Components')?></a></li>
		<li class=""><a id="tabEmployeeSalary" href="#tabPageEmployeeSalary"><?=t('Salary')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageSalaryComponentType">
			<div id="SalaryComponentTypeTable" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="SalaryComponentTypeForm" data-content="Form">

			</div>
		</div>
		<div class="tab-pane" id="tabPageSalaryComponent">
			<div id="SalaryComponentTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="SalaryComponentForm"></div>
			<div id="SalaryComponentFilterForm"></div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeSalary">
			<div id="EmployeeSalaryTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmployeeSalaryForm" data-content="Form"></div>
			<div id="EmployeeSalaryFilterForm" data-content="Form"></div>
		</div>
	</div>

</div>
<?php
$moduleData = [
	'user_level' => $user->user_level,
	'permissions' => [
		'SalaryComponentType' => PermissionManager::checkGeneralAccess(new SalaryComponentType()),
		'SalaryComponent' => PermissionManager::checkGeneralAccess(new SalaryComponent()),
		'EmployeeSalary' => PermissionManager::checkGeneralAccess(new EmployeeSalary()),
	]];
?>
<script>
    var modJsList = [];
    modJsList['tabSalaryComponentType'] = new SalaryComponentTypeAdapter('SalaryComponentType');
    modJsList['tabSalaryComponentType'].setRemoteTable(true);
    modJsList['tabSalaryComponentType'].setObjectTypeName('Salary Component Type');
    modJsList['tabSalaryComponentType'].setDataPipe(new IceDataPipe(modJsList['tabSalaryComponentType']));
    modJsList['tabSalaryComponentType'].setAccess(<?=json_encode($moduleData['permissions']['SalaryComponentType'])?>);

    modJsList['tabSalaryComponent'] = new SalaryComponentAdapter('SalaryComponent');
    modJsList['tabSalaryComponent'].setObjectTypeName('Salary Component');
    modJsList['tabSalaryComponent'].setDataPipe(new IceDataPipe(modJsList['tabSalaryComponent']));
    modJsList['tabSalaryComponent'].setAccess(<?=json_encode($moduleData['permissions']['SalaryComponent'])?>);

    modJsList['tabEmployeeSalary'] = new EmployeeSalaryAdapter('EmployeeSalary');
    modJsList['tabEmployeeSalary'].setObjectTypeName('Employee Salary');
    modJsList['tabEmployeeSalary'].setRemoteTable(true);
    modJsList['tabEmployeeSalary'].setDataPipe(new IceDataPipe(modJsList['tabEmployeeSalary']));
    modJsList['tabEmployeeSalary'].setAccess(<?=json_encode($moduleData['permissions']['EmployeeSalary'])?>);

    var modJs = modJsList['tabSalaryComponentType'];

</script>
<div id="dataGroup"></div>
<?php include APP_BASE_PATH.'footer.php';?>
