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

$moduleName = 'dashboard';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
<div class="row">
    <?php
    $moduleManagers = BaseService::getInstance()->getModuleManagers();
    $dashBoardList = array();
    foreach($moduleManagers as $moduleManagerObj){

        //Check if this is not an admin module
        if($moduleManagerObj->getModuleType() != 'admin'){
            continue;
        }

        $allowed = BaseService::getInstance()->isModuleAllowedForUser($moduleManagerObj);

        if(!$allowed){
            continue;
        }

        $item = $moduleManagerObj->getDashboardItem();
        if(!empty($item)) {
            $index = $moduleManagerObj->getDashboardItemIndex();
            $dashBoardList[$index] = $item;
        }
    }

    ksort($dashBoardList);

    foreach($dashBoardList as $k=>$v){
        echo LanguageManager::translateTnrText($v);
    }
    ?>
</div>
	

</div>
<script>
var modJsList = new Array();

modJsList['tabDashboard'] = new DashboardAdapter('Dashboard','Dashboard');

var modJs = modJsList['tabDashboard'];

/*
$("#employeeLink").attr("href",modJs.getCustomUrl('?g=admin&n=employees&m=admin_Admin'));
$("#companyLink").attr("href",modJs.getCustomUrl('?g=admin&n=company_structure&m=admin_Admin'));
$("#usersLink").attr("href",modJs.getCustomUrl('?g=admin&n=users&m=admin_System'));
$("#projectsLink").attr("href",modJs.getCustomUrl('?g=admin&n=projects&m=admin_Admin'));
$("#attendanceLink").attr("href",modJs.getCustomUrl('?g=admin&n=attendance&m=admin_Admin'));
$("#leaveLink").attr("href",modJs.getCustomUrl('?g=admin&n=leaves&m=admin_Admin'));
$("#reportsLink").attr("href",modJs.getCustomUrl('?g=admin&n=reports&m=admin_Reports'));
$("#settingsLink").attr("href",modJs.getCustomUrl('?g=admin&n=settings&m=admin_System'));
$("#jobsLink").attr("href",modJs.getCustomUrl('?g=admin&n=jobpositions&m=admin_Recruitment'));
$("#candidatesLink").attr("href",modJs.getCustomUrl('?g=admin&n=candidates&m=admin_Recruitment'));
$("#trainingLink").attr("href",modJs.getCustomUrl('?g=admin&n=training&m=admin_Admin'));
$("#travelLink").attr("href",modJs.getCustomUrl('?g=admin&n=travel&m=admin_Employees'));
$("#documentLink").attr("href",modJs.getCustomUrl('?g=admin&n=documents&m=admin_Employees'));
$("#expenseLink").attr("href",modJs.getCustomUrl('?g=admin&n=expenses&m=admin_Employees'));
$("#permissionLink").attr("href",modJs.getCustomUrl('?g=admin&n=permissions&m=admin_System'));
$("#upgradeLink").attr("href",modJs.getCustomUrl('?g=admin&n=billing&m=admin_System'));

modJs.getInitData();
*/

</script>
<?php include APP_BASE_PATH.'footer.php';?>      