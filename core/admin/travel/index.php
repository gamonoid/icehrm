<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'travel';
$moduleGroup = 'admin';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$customFields = \Classes\BaseService::getInstance()->getCustomFields("EmployeeTravelRecord");
?>
<div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tabTravelProject" href="#tabPageTravelProject">Travel Projects</a></li>
		<li><a id="tabEmployeeTravelRecord" href="#tabPageEmployeeTravelRecord">Travel Requests</a></li>
	</ul>

	<div class="tab-content">
        <div class="tab-pane active" id="tabPageTravelProject">
            <div id="TravelProjectTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
            <div id="TravelProjectForm" data-content="Form"></div>
            <div id="TravelProjectFilterForm" data-content="Filter"></div>
        </div>
		<div class="tab-pane" id="tabPageEmployeeTravelRecord">
			<div id="EmployeeTravelRecordTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="EmployeeTravelRecordForm" data-content="Form"></div>
			<div id="EmployeeTravelRecordFilterForm" data-content="Filter"></div>
		</div>
	</div>

</div>
<?php
$permissions = ['get', 'element', 'save', 'delete'];
$permissions = array_values($permissions); // Re-index array
?>
<script>
var modJsList = [];

modJsList['tabEmployeeTravelRecord'] = new EmployeeTravelRecordAdminAdapter('EmployeeTravelRecord', 'EmployeeTravelRecord', '', '');
modJsList['tabEmployeeTravelRecord'].setObjectTypeName('Travel Request');
modJsList['tabEmployeeTravelRecord'].setDataPipe(new IceDataPipe(modJsList['tabEmployeeTravelRecord']));
modJsList['tabEmployeeTravelRecord'].setAccess(<?=json_encode($permissions)?>);
modJsList['tabEmployeeTravelRecord'].setRemoteTable(true);
modJsList['tabEmployeeTravelRecord'].setCustomFields(<?=json_encode($customFields)?>);
modJsList['tabEmployeeTravelRecord'].setModalType('Steps');

modJsList['tabTravelProject'] = new TravelProjectAdapter('TravelProject', 'TravelProject', '', '');
modJsList['tabTravelProject'].setObjectTypeName('Travel Project');
modJsList['tabTravelProject'].setDataPipe(new IceDataPipe(modJsList['tabTravelProject']));
modJsList['tabTravelProject'].setAccess(<?=json_encode($permissions)?>);
modJsList['tabTravelProject'].setRemoteTable(true);

var modJs = modJsList['tabTravelProject'];
</script>
<?php


$itemName = 'TravelRequest';
$moduleName = 'Travel Management';
$itemNameLower = strtolower($itemName);

include APP_BASE_PATH.'footer.php';
