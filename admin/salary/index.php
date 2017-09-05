<?php

$moduleName = 'salary';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$moduleBuilder = new \Classes\ModuleBuilder\ModuleBuilder();

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

include APP_BASE_PATH.'footer.php';
