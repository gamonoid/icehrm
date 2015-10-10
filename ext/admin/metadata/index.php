<?php 
/*
This file is part of Ice Framework.

Ice Framework is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Ice Framework is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ice Framework. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

$moduleName = 'metadata';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$moduleBuilder = new ModuleBuilder();

$moduleBuilder->addModuleOrGroup(new ModuleTab('Country','Country','Countries','CountryAdapter','','',true));
$moduleBuilder->addModuleOrGroup(new ModuleTab('Province','Province','Provinces','ProvinceAdapter','',''));
$moduleBuilder->addModuleOrGroup(new ModuleTab('CurrencyType','CurrencyType','Currency Types','CurrencyTypeAdapter','',''));
$moduleBuilder->addModuleOrGroup(new ModuleTab('Nationality','Nationality','Nationality','NationalityAdapter','',''));
$moduleBuilder->addModuleOrGroup(new ModuleTab('Nationality','Nationality','Nationality','NationalityAdapter','',''));
$moduleBuilder->addModuleOrGroup(new ModuleTab('Ethnicity','Ethnicity','Ethnicity','EthnicityAdapter','',''));
$moduleBuilder->addModuleOrGroup(new ModuleTab('ImmigrationStatus','ImmigrationStatus','Immigration Status','ImmigrationStatusAdapter','',''));


echo UIManager::getInstance()->renderModule($moduleBuilder);

include APP_BASE_PATH.'footer.php';