<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'modules';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabModule" href="#tabPageModule"><?=t('Modules')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageModule">
			<div id="Module" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="ModuleForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
	</div>

</div>
<script>
var modJsList = [];

modJsList['tabModule'] = new ModuleAdapter('Module','Module');
modJsList['tabModule'].setShowAddNew(false);
var modJs = modJsList['tabModule'];
</script>
<?php include APP_BASE_PATH.'footer.php';?>
