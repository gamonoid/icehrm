<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\PermissionManager;
use FieldNames\Common\Model\FieldNameMapping;

$moduleName = 'fieldnames';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tabEmployeeFieldName" href="#tabPageEmployeeFieldName"><?=t('Employee Field Names')?></a></li>
    </ul>

	<div class="tab-content">
        <div class="tab-pane active" id="tabPageEmployeeFieldName">
            <div id="EmployeeFieldNameTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="EmployeeFieldNameForm"></div>
            <div id="EmployeeFieldNameFilterForm"></div>
            </div>
        </div>
	</div>

</div>
<div id="dataGroup"></div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'FieldNameMapping' => PermissionManager::checkGeneralAccess(new FieldNameMapping()),
    ]
];
?>
<script>
  initAdminEmployeeFieldName(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>
