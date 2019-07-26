<?php
$moduleName = 'metadata';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$moduleBuilder = new \Classes\ModuleBuilder\ModuleBuilder();

$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'Country','Country','Countries','CountryAdapter','','',true
));
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'Province','Province','Provinces','ProvinceAdapter','',''
));
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'CurrencyType','CurrencyType','Currency Types','CurrencyTypeAdapter','',''
));
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'Nationality','Nationality','Nationality','NationalityAdapter','',''
));
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'Ethnicity','Ethnicity','Ethnicity','EthnicityAdapter','',''
));
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'ImmigrationStatus','ImmigrationStatus','Immigration Status','ImmigrationStatusAdapter','',''
));


echo \Classes\UIManager::getInstance()->renderModule($moduleBuilder);

include APP_BASE_PATH.'footer.php';
