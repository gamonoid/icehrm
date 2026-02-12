<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Travel\Common\Model\EmployeeTravelRecord;

$moduleName = 'travel';
$moduleGroup = 'modules';
$moduleMainName = "EmployeeTravelRecord"; // for creating module js lib
$subModuleMainName = "SubordinateEmployeeTravelRecord";
$moduleItemName = "Travel Request"; // For permissions

$itemName = $moduleItemName; // for status change popup
$itemNameLower = strtolower($moduleMainName);  // for status change popup
$appModName = $moduleMainName.'Approval';

define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';

$customFields = \Classes\BaseService::getInstance()->getCustomFields("EmployeeTravelRecord");
$isExpenseApprovalsNeeded = (new EmployeeTravelRecord())->isMultiLevelApprovalsEnabled();
$additionalJs = array();
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

    <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tab<?=$moduleMainName?>" href="#tabPage<?=$moduleMainName?>"><?=t('Travel Requests')?></a></li>
        <li class=""><a id="tab<?=$subModuleMainName?>" href="#tabPage<?=$subModuleMainName?>"><?=t('Travel Requests (Direct Reports)')?></a></li>
        <?php if ($isExpenseApprovalsNeeded) {?>
        <li class=""><a id="tab<?=$appModName?>" href="#tabPage<?=$appModName?>"><?=t('Travel Requests for Approval')?></a></li>
        <?php } ?>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tabPage<?=$moduleMainName?>">
            <div id="<?=$moduleMainName?>Table" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="<?=$moduleMainName?>Form" data-content="Form"></div>
            <div id="<?=$moduleMainName?>FilterForm" data-content="Filter"></div>
        </div>
        <div class="tab-pane" id="tabPage<?=$subModuleMainName?>">
            <div id="<?=$subModuleMainName?>Table" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="<?=$subModuleMainName?>Form" data-content="Form"></div>
            <div id="<?=$subModuleMainName?>FilterForm" data-content="Filter"></div>
        </div>
        <?php if ($isExpenseApprovalsNeeded) {?>
        <div class="tab-pane" id="tabPage<?=$appModName?>">
            <div id="<?=$appModName?>Table" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="<?=$appModName?>Form" data-content="Form"></div>
            <div id="<?=$appModName?>FilterForm" data-content="Filter"></div>
        </div>
        <?php } ?>
    </div>

</div>
<?php
$permissions = ['get', 'element', 'save', 'delete'];
// Apply module-specific permission checks
if(isset($modulePermissions['perm']['Add '.$moduleItemName]) && $modulePermissions['perm']['Add '.$moduleItemName] == "No"){
    $permissions = array_diff($permissions, ['save']);
}
if(isset($modulePermissions['perm']['Delete '.$moduleItemName]) && $modulePermissions['perm']['Delete '.$moduleItemName] == "No"){
    $permissions = array_diff($permissions, ['delete']);
}
if(isset($modulePermissions['perm']['Edit '.$moduleItemName]) && $modulePermissions['perm']['Edit '.$moduleItemName] == "No"){
    $permissions = array_diff($permissions, ['save']);
}
$permissions = array_values($permissions); // Re-index array
?>
<script>
var modJsList = [];

// Tab 1: Employee Travel Record (User's own requests)
modJsList['tab<?=$moduleMainName?>'] = new <?=$moduleMainName?>Adapter('<?=$moduleMainName?>', '<?=$moduleMainName?>', '', '');
modJsList['tab<?=$moduleMainName?>'].setObjectTypeName('<?=$moduleItemName?>');
modJsList['tab<?=$moduleMainName?>'].setDataPipe(new IceDataPipe(modJsList['tab<?=$moduleMainName?>']));
modJsList['tab<?=$moduleMainName?>'].setAccess(<?=json_encode($permissions)?>);
modJsList['tab<?=$moduleMainName?>'].setCustomFields(<?=json_encode($customFields)?>);
modJsList['tab<?=$moduleMainName?>'].setModalType('Steps');
<?php if(isset($modulePermissions['perm']['Add '.$moduleItemName]) && $modulePermissions['perm']['Add '.$moduleItemName] == "No"){?>
modJsList['tab<?=$moduleMainName?>'].setShowAddNew(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Delete '.$moduleItemName]) && $modulePermissions['perm']['Delete '.$moduleItemName] == "No"){?>
modJsList['tab<?=$moduleMainName?>'].setShowDelete(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Edit '.$moduleItemName]) && $modulePermissions['perm']['Edit '.$moduleItemName] == "No"){?>
modJsList['tab<?=$moduleMainName?>'].setShowEdit(false);
<?php }?>

// Tab 2: Subordinate Travel Records
modJsList['tab<?=$subModuleMainName?>'] = new <?=$subModuleMainName?>Adapter('<?=$moduleMainName?>', '<?=$subModuleMainName?>', '', '');
modJsList['tab<?=$subModuleMainName?>'].setObjectTypeName('<?=$moduleItemName?>');
modJsList['tab<?=$subModuleMainName?>'].setDataPipe(new IceDataPipe(modJsList['tab<?=$subModuleMainName?>']));
modJsList['tab<?=$subModuleMainName?>'].setAccess(['get', 'element']);
modJsList['tab<?=$subModuleMainName?>'].setRemoteTable(true);
modJsList['tab<?=$subModuleMainName?>'].setShowAddNew(false);
modJsList['tab<?=$subModuleMainName?>'].setShowDelete(false);
modJsList['tab<?=$subModuleMainName?>'].setShowEdit(true);

// Tab 3: Travel Records for Approval (if multi-level approvals enabled)
modJsList['tab<?=$appModName?>'] = new <?=$moduleMainName?>ApproverAdapter('<?=$appModName?>', '<?=$appModName?>', '', '');
modJsList['tab<?=$appModName?>'].setObjectTypeName('<?=$moduleItemName?>');
modJsList['tab<?=$appModName?>'].setDataPipe(new IceDataPipe(modJsList['tab<?=$appModName?>']));
modJsList['tab<?=$appModName?>'].setAccess(['get', 'element']);
modJsList['tab<?=$appModName?>'].setShowAddNew(false);
modJsList['tab<?=$appModName?>'].setShowDelete(false);
modJsList['tab<?=$appModName?>'].setShowEdit(false);

var modJs = modJsList['tab<?=$moduleMainName?>'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
