<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\ModuleBuilder\ModuleBuilder;
use Classes\ModuleBuilder\ModuleTab;
use Classes\SettingsManager;
use Classes\UIManager;
use Model\Setting;

$moduleName = 'settings';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

// Helper function to check if a category has visible settings
function hasVisibleSettings($categories) {
    if (!is_array($categories)) {
        $categories = [$categories];
    }

    $hiddenSettings = SettingsManager::getInstance()->getHiddenSettings();
    $deprecatedSettings = SettingsManager::getInstance()->getDeprecatedSettings();

    $setting = new Setting();
    foreach ($categories as $category) {
        $settings = $setting->Find('category = ?', [$category]);
        foreach ($settings as $s) {
            if (!in_array($s->name, $hiddenSettings) && !in_array($s->name, $deprecatedSettings)) {
                return true;
            }
        }
    }
    return false;
}

$moduleBuilder = new ModuleBuilder();

$options1 = array();
$options1['setShowAddNew'] = 'false';
$options1['setRemoteTable'] = 'true';

$notCloud = !defined('IS_CLOUD') || IS_CLOUD == false;
$isFirstTab = true;

if (hasVisibleSettings('Company')) {
    $moduleBuilder->addModuleOrGroup(new ModuleTab(
        'CompanySetting','Setting','Company','SettingAdapter','{"category":"Company"}','name',$isFirstTab,$options1
    ));
    $isFirstTab = false;
}

if (hasVisibleSettings('System')) {
    $moduleBuilder->addModuleOrGroup(new ModuleTab(
        'SystemSetting','Setting','System','SettingAdapter','{"category":"System"}','name',$isFirstTab,$options1
    ));
    $isFirstTab = false;
}

if ($notCloud && hasVisibleSettings('Email')) {
    $moduleBuilder->addModuleOrGroup(new ModuleTab(
        'EmailSetting', 'Setting', 'Email', 'SettingAdapter', '{"category":"Email"}', 'name', $isFirstTab, $options1
    ));
    $isFirstTab = false;
}

if (defined('LEAVE_ENABLED') && LEAVE_ENABLED == true && hasVisibleSettings('Leave')) {
    $moduleBuilder->addModuleOrGroup(new ModuleTab(
        'LeaveSetting', 'Setting', 'Leave', 'SettingAdapter', '{"category":"Leave"}', 'name', $isFirstTab, $options1
    ));
    $isFirstTab = false;
}

if (hasVisibleSettings('Attendance')) {
    $moduleBuilder->addModuleOrGroup(new ModuleTab(
        'AttendanceSetting','Setting','Attendance','SettingAdapter','{"category":"Attendance"}','name',$isFirstTab,$options1
    ));
    $isFirstTab = false;
}

if (hasVisibleSettings('Files')) {
    $moduleBuilder->addModuleOrGroup(new ModuleTab(
        'FilesSetting','Setting','Files','SettingAdapter','{"category":"Files"}','name',$isFirstTab,$options1
    ));
    $isFirstTab = false;
}

if ((!defined('LDAP_ENABLED') || LDAP_ENABLED == true) && hasVisibleSettings('LDAP')) {
    $moduleBuilder->addModuleOrGroup(new ModuleTab(
        'LDAPSetting','Setting','LDAP','SettingAdapter','{"category":"LDAP"}','name',$isFirstTab,$options1
    ));
    $isFirstTab = false;
}

if ((!defined('SAML_ENABLED') || SAML_ENABLED == true) && hasVisibleSettings('SAML')) {
    $moduleBuilder->addModuleOrGroup(new ModuleTab(
        'SAMLSetting','Setting','SAML','SettingAdapter','{"category":"SAML"}','name',$isFirstTab,$options1
    ));
    $isFirstTab = false;
}

$otherCategories = ['Projects', 'Recruitment', 'Notifications', 'Expense', 'Travel', 'Api', 'Overtime', 'Microsoft', 'Google'];
if (hasVisibleSettings($otherCategories)) {
    $moduleBuilder->addModuleOrGroup(new ModuleTab(
        'OtherSetting','Setting','Other','SettingAdapter','{"category":["Projects","Recruitment","Notifications","Expense","Travel","Api","Overtime","Microsoft","Google"]}','name',$isFirstTab,$options1
    ));
}
echo UIManager::getInstance()->renderModule($moduleBuilder);
?>
</div>
<script>

$(window).load(function() {
    // Set dataPipe for all settings tabs
    if (typeof modJsList !== 'undefined') {
        var tabNames = ['CompanySetting', 'SystemSetting', 'EmailSetting', 'LeaveSetting', 
                        'AttendanceSetting', 'FilesSetting', 'LDAPSetting', 'SAMLSetting', 'OtherSetting'];
        for (var i = 0; i < tabNames.length; i++) {
            var tabName = tabNames[i];
            if (modJsList['tab' + tabName] && typeof IceDataPipe !== 'undefined') {
                modJsList['tab' + tabName].setDataPipe(new IceDataPipe(modJsList['tab' + tabName]));
            }
        }
    }
    
    // Load remote data and initialize table for active tab
    if (modJs) {
        if (typeof modJs.loadRemoteDataForSettings === 'function') {
            modJs.loadRemoteDataForSettings();
        }
        // Initialize table and load data
        if (typeof modJs.get === 'function') {
            modJs.get([]);
        }
    }
});

// Handle tab switching - initialize table when tab is shown
$(document).on('shown.bs.tab', 'a[data-toggle="tab"]', function (e) {
    var target = $(e.target).attr('href'); // e.g., #tabPageCompanySetting
    var tabName = target.replace('#tabPage', ''); // e.g., CompanySetting
    if (modJsList && modJsList['tab' + tabName]) {
        var adapter = modJsList['tab' + tabName];
        if (!adapter.tableInitialized && typeof adapter.get === 'function') {
            adapter.get([]);
        }
    }
});

</script>
<?php include APP_BASE_PATH.'footer.php';?>
