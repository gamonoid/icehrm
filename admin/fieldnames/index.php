<?php 
/*
This file is part of Ice Framework.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

$moduleName = 'fieldnames';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
			  
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
        <li class="active"><a id="tabEmployeeFieldName" href="#tabPageEmployeeFieldName"><?=t('Employee Field Names')?></a></li>
        <li><a id="tabEmployeeCustomField" href="#tabPageEmployeeCustomField"><?=t('Employee Custom Fields')?></a></li>
	</ul>
	 
	<div class="tab-content">
        <div class="tab-pane active" id="tabPageEmployeeFieldName">
            <div id="EmployeeFieldName" class="reviewBlock" data-content="List" style="padding-left:5px;">

            </div>
            <div id="EmployeeFieldNameForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

            </div>
        </div>
        <div class="tab-pane" id="tabPageEmployeeCustomField">
            <div id="EmployeeCustomField" class="reviewBlock" data-content="List" style="padding-left:5px;">

            </div>
            <div id="EmployeeCustomFieldForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

            </div>
        </div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabEmployeeFieldName'] = new FieldNameAdapter('FieldNameMapping','EmployeeFieldName',{"type":"Employee"});
modJsList['tabEmployeeFieldName'].setRemoteTable(true);
modJsList['tabEmployeeFieldName'].setShowAddNew(false);

modJsList['tabEmployeeCustomField'] = new CustomFieldAdapter('CustomField','EmployeeCustomField',{"type":"Employee"},"display_order desc");
modJsList['tabEmployeeCustomField'].setRemoteTable(true);
modJsList['tabEmployeeCustomField'].setTableType("Employee");


var modJs = modJsList['tabEmployeeFieldName'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>      
