<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'settings';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$moduleBuilder = new \Classes\ModuleBuilder\ModuleBuilder();

$options1 = array();
$options1['setShowAddNew'] = 'false';

$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
    'CompanySetting','Setting','Company','SettingAdapter','{"name":["Company:"]}','name',true,$options1
));
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
    'SystemSetting','Setting','System','SettingAdapter','{"name":["System:"]}','name',false,$options1
));
if (!defined('CLOUD_INSTALLATION')) {
    $moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
        'EmailSetting', 'Setting', 'Email', 'SettingAdapter', '{"name":["Email:"]}', 'name', false, $options1
    ));
}
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
    'LeaveSetting','Setting','Leave / PTO','SettingAdapter','{"name":["Leave:"]}','name',false,$options1
));
if(!defined('LDAP_ENABLED') || LDAP_ENABLED == true){
    $moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
        'LDAPSetting','Setting','LDAP','SettingAdapter','{"name":["LDAP:"]}','name',false,$options1
    ));
}
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
    'AttendanceSetting','Setting','Attendance','SettingAdapter','{"name":["Attendance:"]}','name',false,$options1
));
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
    'OtherSetting','Setting','Other','SettingAdapter','{"name":["Projects:","Recruitment:","Notifications:","Expense:","Travel:","Api:","Overtime:"]}','name',false,$options1
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
