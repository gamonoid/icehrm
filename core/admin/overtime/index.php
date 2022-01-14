<?php
use Overtime\Common\Model\EmployeeOvertime;
use Overtime\Common\Model\OvertimeCategory;
use Classes\PermissionManager;

$moduleName = 'overtime';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
/**
 * This is needed for multi level approvals
 */
$itemName = 'OvertimeRequest';
$moduleName = 'Time Management';
$itemNameLower = strtolower($itemName);
?><div class="span9">

    <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tabOvertimeCategory" href="#tabPageOvertimeCategory"><?=t('Overtime Categories')?></a></li>
        <li><a id="tabEmployeeOvertime" href="#tabPageEmployeeOvertime"><?=t('Overtime Requests')?></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tabPageOvertimeCategory">
            <div id="OvertimeCategoryTable" class="reviewBlock" data-content="List"></div>
            <div id="OvertimeCategoryForm"></div>
            <div id="OvertimeCategoryFilterForm"></div>
        </div>
        <div class="tab-pane" id="tabPageEmployeeOvertime">
            <div id="EmployeeOvertimeTable" class="reviewBlock" data-content="List"></div>
            <div id="EmployeeOvertimeForm"></div>
            <div id="EmployeeOvertimeFilterForm"></div>
        </div>
    </div>

</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'OvertimeCategory' => PermissionManager::checkGeneralAccess(new OvertimeCategory()),
        'EmployeeOvertime' => PermissionManager::checkGeneralAccess(new EmployeeOvertime()),
    ]
];
?>
<script>
  initAdminOvertime(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>


