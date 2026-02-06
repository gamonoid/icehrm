<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\PermissionManager;
use Modules\Common\Model\Module;

$moduleName = 'modules';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
$moduleData = [
	'user_level' => $user->user_level,
	'permissions' => [
		'Module' => PermissionManager::checkGeneralAccess(new Module()),
	]];
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabModule" href="#tabPageModule"><?=t('Modules')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageModule">
			<div id="ModuleTable" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="ModuleForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
            <div id="ModuleFilterForm"></div>
		</div>
	</div>

</div>
<script>
var modJsList = [];

modJsList['tabModule'] = new ModuleAdapter('Module','Module');
modJsList['tabModule'].setShowAddNew(false);
modJsList['tabModule'].setObjectTypeName('Module');
modJsList['tabModule'].setDataPipe(new IceDataPipe(modJsList['tabModule']));
modJsList['tabModule'].setAccess(<?=json_encode($moduleData['permissions']['Module'])?>);
var modJs = modJsList['tabModule'];
</script>
<?php include APP_BASE_PATH.'footer.php';?>
