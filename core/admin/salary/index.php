<?php
use Classes\PermissionManager;
use Salary\Common\Model\SalaryComponentType;
use Salary\Common\Model\SalaryComponent;
use Salary\Common\Model\EmployeeSalary;


$moduleName = 'salary';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

/*$moduleBuilder = new \Classes\ModuleBuilder\ModuleBuilder();

$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'SalaryComponentType','SalaryComponentType','Salary Component Types','SalaryComponentTypeAdapter','','',true
));
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'SalaryComponent','SalaryComponent','Salary Components','SalaryComponentAdapter','',''
));
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'EmployeeSalary','EmployeeSalary','Employee Salary Components','EmployeeSalaryAdapter','','',false,array("setRemoteTable"=>"true"))
);


echo \Classes\UIManager::getInstance()->renderModule($moduleBuilder);

include APP_BASE_PATH.'footer.php';*/

?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabSalaryComponentType" href="#tabSalaryComponentType"><?=t('Salary Component Types')?></a></li>
		<li><a id="tabSalaryComponent" href="#tabPageSalaryComponent"><?=t('Salary Components')?></a></li>
		<li><a id="tabEmployeeSalary" href="#tabPageEmployeeSalary"><?=t('Employee Salary Components')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageSalaryComponentType">
			<div id="SalaryComponentTypeTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="SalaryComponentTypeForm"></div>
            <div id="SalaryComponentTypeFilterForm"></div>
		</div>

		<div class="tab-pane" id="tabPageSalaryComponent">
			<div id="SalaryComponentTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="SalaryComponentForm"></div>
            <div id="SalaryComponentFilterForm"></div>
		</div>

		<div class="tab-pane" id="tabPageEmployeeSalary">
			<div id="EmployeeSalaryTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="EmployeeSalaryForm"></div>
            <div id="EmployeeSalaryFilterForm"></div>
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

    ]
];
?>

<script>
  initAdminSalary(<?=json_encode($moduleData)?>);
</script>


<?php include APP_BASE_PATH.'footer.php';?>
