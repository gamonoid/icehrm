<?php
/*
This file is part of iCE Hrm.

iCE Hrm is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

iCE Hrm is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with iCE Hrm. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

$moduleName = 'employees';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
$fieldNameMap = \Classes\BaseService::getInstance()->getFieldNameMappings("Employee");
$customFields = \Classes\BaseService::getInstance()->getCustomFields("Employee");

if (\Classes\SettingsManager::getInstance()->getSetting("Api: REST Api Enabled") == "1") {
	$user = \Classes\BaseService::getInstance()->getCurrentUser();
	if (empty($user)) {
		return;
	}
	$dbUser = new \Users\Common\Model\User();
	$dbUser->Load("id = ?", array($user->id));
	$resp = \Classes\RestApiManager::getInstance()->getAccessTokenForUser($dbUser);
	if ($resp->getStatus() != \Classes\IceResponse::SUCCESS) {
		\Utils\LogManager::getInstance()->error(
			"Error occurred while creating REST Api access token for ".$user->username
		);
	}
}
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
			<div id="Employee" class="container reviewBlock" data-content="List" style="padding:25px 0px 0px 0px; width:99%;">

			</div>
			<div id="EmployeeForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane reviewBlock" id="tabPageCompanyGraph" style="overflow-x: scroll;">

		</div>
		<?php if (\Classes\SettingsManager::getInstance()->getSetting("Api: REST Api Enabled") == "1") { ?>
		<div class="tab-pane reviewBlock" id="tabPageApiAccess" style="overflow-x: scroll;">
			<div class="row">
				<div class="panel panel-default" style="width:97.5%;">
					<div class="panel-heading"><h4>Api Access Token</h4></div>
					<div class="panel-body">
						<?=$resp->getData()?>
					</div>
			</div>
		</div>
		<?php } ?>
	</div>

</div>
<script>
var modJsList = new Array();
modJsList['tabEmployee'] = new EmployeeAdapter('Employee');
modJsList['tabEmployee'].setFieldNameMap(<?=json_encode($fieldNameMap)?>);
modJsList['tabEmployee'].setCustomFields(<?=json_encode($customFields)?>);
modJsList['tabCompanyGraph'] = new CompanyGraphAdapter('CompanyStructure');
modJsList['tabApiAccess'] = new ApiAccessAdapter('ApiAccess');

var modJs = modJsList['tabEmployee'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
