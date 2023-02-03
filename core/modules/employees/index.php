<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Classes\BaseService;
use Classes\JwtTokenService;

$moduleName = 'employees';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
$fieldNameMap = BaseService::getInstance()->getFieldNameMappings("Employee");
$customFields = BaseService::getInstance()->getCustomFields("Employee");

$jwtService = new JwtTokenService();
$threeMonthAccessToken = $jwtService->create(3600 * 24 * 180);
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
		<li class="active"><a id="tabEmployee" href="#tabPageEmployee"><?=t('My Details')?></a></li>
		<li><a id="tabCompanyGraph" href="#tabPageCompanyGraph"><?=t('Company')?></a></li>
		<?php if (\Classes\SettingsManager::getInstance()->getSetting("Api: REST Api Enabled") == "1") { ?>
		<li><a id="tabApiAccess" href="#tabPageApiAccess"><?=t('Api Access')?></a></li>
		<?php } ?>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployee">
			<div id="Employee" class="container reviewBlock" data-content="List" style="padding:25px 0px 0px 0px; width:99%;"></div>
			<div id="EmployeeForm"></div>
			<div id="EmployeeFilterForm"></div>
		</div>
		<div class="tab-pane reviewBlock" id="tabPageCompanyGraph" style="overflow-x: scroll;">

		</div>
		<?php if (\Classes\SettingsManager::getInstance()->getSetting("Api: REST Api Enabled") == "1") { ?>
		<div class="tab-pane reviewBlock" id="tabPageApiAccess" style="overflow-x: scroll;">
			<div class="row">
				<div class="panel panel-default" style="width:97.5%;">
					<div class="panel-heading"><h4>Api Access Token</h4></div>
					<div class="panel-body wrap" id="apiToken"><?=$threeMonthAccessToken?></div>
			    </div>
                <div class="panel panel-default" style="width:97.5%;">
                    <div class="panel-heading"><h4>Mobile Authentication Code (Legacy Mobile Apps)</h4></div>
                    <div class="panel-body" id="apiToken">
                        <canvas id="apiQRcode"></canvas>
                    </div>
                </div>
                <div class="panel panel-default" style="width:97.5%;">
                    <div class="panel-heading"><h4>Mobile Authentication Code</h4></div>
                    <div class="panel-body" id="loginCode" style="font-size: 17px;">
                        <button class="btn btn-small btn-primary">Request One-time Login Code  <i class="fa fa-lock-open"></i></button>
                    </div>
                </div>
		    </div>
		<?php } ?>
	</div>

</div>
<script>
var modJsList = [];
modJsList['tabEmployee'] = new EmployeeAdapter('Employee');
modJsList['tabEmployee'].setFieldNameMap(<?=json_encode($fieldNameMap)?>);
modJsList['tabEmployee'].setCustomFields(<?=json_encode($customFields)?>);

modJsList['tabEmployee'].setObjectTypeName('Employee');
modJsList['tabEmployee'].setModalType(EmployeeAdapter.MODAL_TYPE_STEPS);
$(document).ready(() => modJsList['tabEmployee'].initForm());

modJsList['tabCompanyGraph'] = new CompanyGraphAdapter('CompanyStructure');
modJsList['tabApiAccess'] = new ApiAccessAdapter('ApiAccess');

modJsList['tabApiAccess'].setToken('<?=$threeMonthAccessToken?>');

var modJs = modJsList['tabEmployee'];

</script>
<div id="EmployeeFormReact"></div>
<div id="dataGroup"></div>
<?php include APP_BASE_PATH.'footer.php';?>
