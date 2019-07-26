<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

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
			<div id="CompanyStructure" class="reviewBlock" data-content="List" style="padding-left:5px;">

			</div>
			<div id="CompanyStructureForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane reviewBlock" id="tabPageCompanyGraph" style="overflow-x: scroll;">

		</div>
	</div>

</div>
<script>
var modJsList = new Array();
modJsList['tabCompanyStructure'] 	= new CompanyStructureAdapter('CompanyStructure');

<?php if(isset($modulePermissions['perm']['Add Company Structure']) && $modulePermissions['perm']['Add Company Structure'] == "No"){?>
modJsList['tabCompanyStructure'].setShowAddNew(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Delete Company Structure']) && $modulePermissions['perm']['Delete Company Structure'] == "No"){?>
modJsList['tabCompanyStructure'].setShowDelete(false);
<?php }?>
<?php if(isset($modulePermissions['perm']['Edit Company Structure']) && $modulePermissions['perm']['Edit Company Structure'] == "No"){?>
modJsList['tabCompanyStructure'].setShowEdit(false);
<?php }?>


modJsList['tabCompanyGraph'] = new CompanyGraphAdapter('CompanyStructure');

var modJs = modJsList['tabCompanyStructure'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
