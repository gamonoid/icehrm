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

$moduleName = 'travel';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$options = array();
$options['setRemoteTable'] = 'true';

$moduleBuilder = new \Classes\ModuleBuilder\ModuleBuilder();
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'OvertimeCategory','OvertimeCategory','Overtime Categories','OvertimeCategoryAdapter','','',true,$options
));
$moduleBuilder->addModuleOrGroup(new \Classes\ModuleBuilder\ModuleTab(
	'EmployeeOvertime','EmployeeOvertime','Overtime Requests','EmployeeOvertimeAdminAdapter','','',false,$options
));
echo \Classes\UIManager::getInstance()->renderModule($moduleBuilder);


$itemName = 'OvertimeRequest';
$moduleName = 'Time Management';
$itemNameLower = strtolower($itemName);

include APP_BASE_PATH.'footer.php';
