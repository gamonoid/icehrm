<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\PermissionManager;
use Company\Common\Model\CompanyStructure;

$moduleName = 'company_structure';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?>
<script type="text/javascript" src="<?=BASE_URL.'js/d3js/d3.js?v='.$jsVersion?>"></script>
<script type="text/javascript" src="<?=BASE_URL.'js/d3js/d3.layout.js?v='.$jsVersion?>"></script>

<style type="text/css">


.node circle {
  cursor: pointer;
  fill: #fff;
  stroke: steelblue;
  stroke-width: 1.5px;
}

.node text {
  font-size: 11px;
}

path.link {
  fill: none;
  stroke: #ccc;
  stroke-width: 1.5px;
}

    </style>

<div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabCompanyStructure" href="#tabPageCompanyStructure"><?=t('Company Structure')?></a></li>
		<li><a id="tabCompanyGraph" href="#tabPageCompanyGraph"><?=t('Company Graph')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageCompanyStructure">
			<div id="CompanyStructureTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="CompanyStructureForm"></div>
            <div id="CompanyStructureFilterForm"></div>
		</div>
		<div class="tab-pane reviewBlock" id="tabPageCompanyGraph" style="overflow-x: scroll;">

		</div>
	</div>

</div>
<?php
$moduleData = [
    'user_level' => $user->user_level,
    'permissions' => [
        'CompanyStructure' => PermissionManager::checkGeneralAccess(new CompanyStructure()),
    ]
];
?>
<script>
  initAdminCompanyStructure(<?=json_encode($moduleData)?>);
</script>
<?php include APP_BASE_PATH.'footer.php';?>
