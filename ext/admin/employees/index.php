<?php 

$moduleName = 'employees';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
			  
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmployee" href="#tabPageEmployee">Employees</a></li>
	</ul>
	 
	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployee">
			<div id="Employee" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
			<div id="EmployeeForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">
		
			</div>
		</div>
		
	</div>

</div>
<script>
var modJsList = new Array();
modJsList['tabEmployee'] = new EmployeeAdapter('Employee');
modJsList['tabEmployee'].setRemoteTable(true);

var modJs = modJsList['tabEmployee'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>      
