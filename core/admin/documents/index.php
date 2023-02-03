<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'documents';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
$activeStr = '';
if($user->user_level == "Manager"){
    $activeStr = 'active';
}

$moduleBuilder = new \Classes\ModuleBuilderV2\ModuleBuilder();
if($user->user_level == "Admin") {
    $moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilderV2\ModuleTab(
        '\Documents\Common\Model\CompanyDocument',
        'CompanyDocument',
        'CompanyDocument',
        'Company Documents',
        'CompanyDocumentAdapter',
        '',
        '',
        true
    ));
    $moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilderV2\ModuleTab(
        '\Documents\Common\Model\Document',
        'Document',
        'Document',
        'Document Types',
        'DocumentAdapter',
        '',
        '',
        false
    ));
    $options1 = array();
    $options1['setRemoteTable'] = 'true';
    $moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilderV2\ModuleTab(
        '\Documents\Common\Model\EmployeeDocument',
        'EmployeeDocument',
        'EmployeeDocument',
        'Employee Documents',
        'EmployeeDocumentAdapter',
        '',
        'date_added desc',
        false,
        $options1
    ));

    $options1 = array();
    $options1['setRemoteTable'] = 'true';
    $moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilderV2\ModuleTab(
        '\Documents\Common\Model\EmployeeDocument',
        'PayslipDocument',
        'PayslipDocument',
        'Employee Payslip',
        'EmployeePayslipDocumentAdapter',
        '',
        'date_added desc',
        false,
        $options1
    ));
}else{
    $options1 = array();
    $options1['setRemoteTable'] = 'true';
    $moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilderV2\ModuleTab(
        '\Documents\Common\Model\EmployeeDocument',
        'EmployeeDocument',
        'EmployeeDocument',
        'Employee Documents',
        'EmployeeDocumentAdapter',
        '',
        '',
        ($user->user_level != "Admin"),
        $options1
    ));
}

echo \Classes\UIManager::getInstance()->renderModule($moduleBuilder);

include APP_BASE_PATH.'footer.php';
