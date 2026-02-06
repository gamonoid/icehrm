<?php

use Classes\PermissionManager;
use Model\Audit;
use Model\EmailLogEntry;

$moduleName = 'audit';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
$moduleData = [
	'user_level' => $user->user_level,
	'permissions' => [
		'Audit' => PermissionManager::checkGeneralAccess(new Audit()),
		'EmailLog' => PermissionManager::checkGeneralAccess(new EmailLogEntry()),
	]];
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabAudit" href="#tabPageAudit"><?=t('Audit Log')?></a></li>
		<li class=""><a id="tabEmailLog" href="#tabPageEmailLog"><?=t('Email Log')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageAudit">
			<div id="AuditTable" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="AuditForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
            <div id="AuditFilterForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

            </div>
		</div>
        <div class="tab-pane" id="tabPageEmailLog">
            <div id="EmailLogTable" class="reviewBlock" data-content="List" style="padding-left:5px;">

            </div>
            <div id="EmailLogForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

            </div>
        </div>
	</div>

</div>
<script>
var modJsList = [];

modJsList['tabAudit'] = new AuditAdapter('Audit','Audit','','id desc');
modJsList['tabAudit'].setObjectTypeName('Audit Log Entry');
modJsList['tabAudit'].setDataPipe(new IceDataPipe(modJsList['tabAudit']));
modJsList['tabAudit'].setAccess(<?=json_encode($moduleData['permissions']['Audit'])?>);
modJsList['tabAudit'].setShowDelete(false);
modJsList['tabAudit'].setShowAddNew(false);
modJsList['tabAudit'].setShowSave(false);

modJsList['tabEmailLog'] = new EmailLogAdapter('EmailLogEntry','EmailLog','','id desc');
modJsList['tabEmailLog'].setObjectTypeName('Email Log Entry');
modJsList['tabEmailLog'].setDataPipe(new IceDataPipe(modJsList['tabEmailLog']));
modJsList['tabEmailLog'].setAccess(<?=json_encode($moduleData['permissions']['EmailLog'])?>);
modJsList['tabEmailLog'].setShowAddNew(false);
modJsList['tabEmailLog'].setShowSave(false);
modJsList['tabEmailLog'].setShowEdit(false);

var modJs = modJsList['tabAudit'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
