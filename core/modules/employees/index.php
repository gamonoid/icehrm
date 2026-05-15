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
$csrf = \Classes\BaseService::getInstance()->generateCsrf('password');
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
        <li><a id="tabMobileApp" href="#tabPageMobileApp"><?=t('Mobile App')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployee">
			<div id="Employee" class="container reviewBlock" data-content="List" style="padding:25px 0px 0px 0px; width:99%;"></div>
			<div id="EmployeeForm"></div>
			<div id="EmployeeFilterForm"></div>
		</div>
		<div class="tab-pane reviewBlock" id="tabPageCompanyGraph" style="overflow-x: scroll;">

		</div>
		<div class="tab-pane reviewBlock" id="tabPageMobileApp" style="overflow-x: scroll;">
			<div class="row">
                <div class="panel panel-default" style="width:97.5%;">
                    <div class="panel-heading"><h4><?=t('Download Mobile App')?></h4></div>
                    <div class="panel-body">
                        <p style="margin-bottom: 15px; color: #666;"><?=t('Access IceHrm on the go. Download our mobile app for iOS or Android.')?></p>
                        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <a href="https://apps.apple.com/gb/app/icehrm/id1624346692" target="_blank" rel="noopener noreferrer" style="display: inline-block;">
                                <img src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg" alt="Download on the App Store" style="height: 50px;">
                            </a>
                            <a href="https://play.google.com/store/apps/details?id=com.icehrm.m3&hl=en" target="_blank" rel="noopener noreferrer" style="display: inline-block;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" alt="Get it on Google Play" style="height: 50px;">
                            </a>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default" style="width:97.5%;">
                    <div class="panel-heading"><h4><?=t('Mobile Authentication Code')?></h4></div>
                    <div class="panel-body" id="loginCode" style="font-size: 17px;">
                        <p style="margin-bottom: 15px; color: #666;"><?=t('Use this one-time code to securely log in to the mobile app.')?></p>
                        <button class="btn btn-small btn-primary">Request One-time Login Code  <i class="fa fa-lock-open"></i></button>
                    </div>
                </div>
                <?php if (\Classes\SettingsManager::getInstance()->getSetting("Api: REST Api Enabled") == "1") { ?>
                <div class="panel panel-default" style="width:97.5%;">
                    <div class="panel-heading"><h4>Api Access Token</h4></div>
                    <div class="panel-body wrap" id="apiToken"><?=$threeMonthAccessToken?></div>
                </div>
                <?php } ?>
		    </div>
		</div>
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
modJsList['tabMobileApp'] = new MobileAppAdapter('MobileApp');
modJsList['tabMobileApp'].setToken('<?=$threeMonthAccessToken?>');

var modJs = modJsList['tabEmployee'];
window.passwordCSRF = '<?=$csrf?>';

</script>
<div id="EmployeeFormReact"></div>
<div id="dataGroup"></div>
<?php include APP_BASE_PATH.'footer.php';?>
