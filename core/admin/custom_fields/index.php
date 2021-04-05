<?php

use Classes\BaseService;
use Classes\PermissionManager;
use FieldNames\Common\Model\CustomField;

$moduleName = 'assets';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
$modelClasses = BaseService::getInstance()->getCustomFieldClassMap();
?><div class="span9">

    <ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tabCustomField" href="#tabPageCustomField"><?=t('Custom Fields')?></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane active" id="tabPageCustomField">
            <div id="CustomFieldTable" class="reviewBlock" data-content="List"></div>
            <div id="CustomFieldForm"></div>
            <div id="CustomFieldFilterForm"></div>
        </div>
    </div>

</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'types' => $modelClasses,
    'permissions' => [
        'CustomField' => PermissionManager::checkGeneralAccess(new CustomField()),
    ]
];
?>
<script>
  initAdminCustomFields(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>

