<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\ModuleBuilder\ModuleBuilder;
use Classes\ModuleBuilder\ModuleTab;

$moduleName = 'settings';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$moduleBuilder = new ModuleBuilder();

$options1 = array();
$options1['setShowAddNew'] = 'false';
$options1['setRemoteTable'] = 'true';

$moduleBuilder->addModuleOrGroup(new ModuleTab(
    'CompanySetting','Setting','Company','SettingAdapter','{"category":"Company"}','name',true,$options1
));
$moduleBuilder->addModuleOrGroup(new ModuleTab(
    'SystemSetting','Setting','System','SettingAdapter','{"category":"System"}','name',false,$options1
));
if (!defined('CLOUD_INSTALLATION')) {
    $moduleBuilder->addModuleOrGroup(new ModuleTab(
        'EmailSetting', 'Setting', 'Email', 'SettingAdapter', '{"category":"Email"}', 'name', false, $options1
    ));
}
$moduleBuilder->addModuleOrGroup(new ModuleTab(
    'LeaveSetting','Setting','Leave / PTO','SettingAdapter','{"category":"Leave"}','name',false,$options1
));
if(!defined('LDAP_ENABLED') || LDAP_ENABLED == true){
    $moduleBuilder->addModuleOrGroup(new ModuleTab(
        'LDAPSetting','Setting','LDAP','SettingAdapter','{"category":"LDAP"}','name',false,$options1
    ));
}
$moduleBuilder->addModuleOrGroup(new ModuleTab(
    'AttendanceSetting','Setting','Attendance','SettingAdapter','{"category":"Attendance"}','name',false,$options1
));
$moduleBuilder->addModuleOrGroup(new ModuleTab(
    'OtherSetting','Setting','Other','SettingAdapter','{"category":["Projects","Recruitment","Notifications","Expense","Travel","Api","Overtime"]}','name',false,$options1
));
echo \Classes\UIManager::getInstance()->renderModule($moduleBuilder);
?>
</div>
<script>

$(window).load(function() {
    modJs.loadRemoteDataForSettings();
});

</script>
<?php include APP_BASE_PATH.'footer.php';?>
