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
	</div>

</div>
<script>
var modJsList = new Array();
modJsList['tabEmployee'] = new EmployeeAdapter('Employee');
modJsList['tabEmployee'].setFieldNameMap(<?=json_encode($fieldNameMap)?>);
modJsList['tabEmployee'].setCustomFields(<?=json_encode($customFields)?>);
modJsList['tabCompanyGraph'] = new CompanyGraphAdapter('CompanyStructure');

var modJs = modJsList['tabEmployee'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
