<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'overtime';
$moduleGroup = 'modules';
$moduleMainName = "EmployeeOvertime"; // for creating module js lib
$subModuleMainName = "SubordinateEmployeeOvertime";
$moduleItemName = "Overtime Request"; // For permissions

$itemName = $moduleItemName; // for status change popup
$itemNameLower = strtolower($moduleMainName);  // for status change popup
$appModName = $moduleMainName.'Approval';

define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
$additionalJs = array();
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

    <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tab<?=$moduleMainName?>" href="#tabPage<?=$moduleMainName?>"><?=t('Overtime Requests')?></a></li>
        <li class=""><a id="tab<?=$subModuleMainName?>" href="#tabPage<?=$subModuleMainName?>"><?=t('Subordinate Overtime Requests')?></a></li>
        <li class=""><a id="tab<?=$appModName?>" href="#tabPage<?=$appModName?>"><?=t('Overtime Request Approval')?></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tabPage<?=$moduleMainName?>">
            <div id="<?=$moduleMainName?>" class="reviewBlock" data-content="List" style="padding-left:5px;">

            </div>
            <div id="<?=$moduleMainName?>Form" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

            </div>
        </div>
        <div class="tab-pane" id="tabPage<?=$subModuleMainName?>">
            <div id="<?=$subModuleMainName?>" class="reviewBlock" data-content="List" style="padding-left:5px;">

            </div>
            <div id="<?=$subModuleMainName?>Form" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

            </div>
        </div>
        <div class="tab-pane" id="tabPage<?=$appModName?>">
            <div id="<?=$appModName?>" class="reviewBlock" data-content="List" style="padding-left:5px;">

            </div>
            <div id="<?=$appModName?>Form" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

            </div>
        </div>
    </div>

</div>
<script>
    var modJsList = new Array();

    modJsList['tab<?=$moduleMainName?>'] = new <?=$moduleMainName?>Adapter('<?=$moduleMainName?>','<?=$moduleMainName?>');


    modJsList['tab<?=$appModName?>'] = new <?=$moduleMainName?>ApproverAdapter('<?=$appModName?>', '<?=$appModName?>');
    modJsList['tab<?=$appModName?>'].setShowAddNew(false);
    modJsList['tab<?=$appModName?>'].setShowDelete(false);
    modJsList['tab<?=$appModName?>'].setShowEdit(false);

    modJsList['tab<?=$subModuleMainName?>'] = new <?=$subModuleMainName?>Adapter('<?=$moduleMainName?>','<?=$subModuleMainName?>');
    modJsList['tab<?=$subModuleMainName?>'].setRemoteTable(true);
    modJsList['tab<?=$subModuleMainName?>'].setShowAddNew(false);
    modJsList['tab<?=$subModuleMainName?>'].setShowDelete(false);
    modJsList['tab<?=$subModuleMainName?>'].setShowEdit(true);

    var modJs = modJsList['tab<?=$moduleMainName?>'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
