<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'Permissions';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabPermission" href="#tabPagePermission"><?=t('Permissions')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPagePermission">
			<div id="Permission" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="PermissionForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabPermission'] = new PermissionAdapter('Permission','Permission');
modJsList['tabPermission'].setShowAddNew(false);
var modJs = modJsList['tabPermission'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
