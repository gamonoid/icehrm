<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'travel';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$customFields = \Classes\BaseService::getInstance()->getCustomFields("EmployeeTravelRecord");

$travelRequestOptions = [];
$travelRequestOptions['setRemoteTable'] = 'true';
$travelRequestOptions['setCustomFields'] = json_encode($customFields);



$moduleBuilder = new \Classes\ModuleBuilder\ModuleBuilder();

$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'EmployeeTravelRecord',
	'EmployeeTravelRecord',
	'Travel Requests',
	'EmployeeTravelRecordAdminAdapter',
	'',
	'',
	true,
    $travelRequestOptions
));


echo \Classes\UIManager::getInstance()->renderModule($moduleBuilder);


$itemName = 'TravelRequest';
$moduleName = 'Travel Management';
$itemNameLower = strtolower($itemName);

include APP_BASE_PATH.'footer.php';
