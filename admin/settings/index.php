<?php
/*
This file is part of iCE Hrm.

iCE Hrm is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

iCE Hrm is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with iCE Hrm. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

$moduleName = 'settings';
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
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
    'EmailSetting','Setting','Email','SettingAdapter','{"name":["Email:"]}','name',false,$options1
));
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
