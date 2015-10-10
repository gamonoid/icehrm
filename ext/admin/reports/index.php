<?php 

$moduleName = 'Reports';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
			  
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabReport" href="#tabPageReport">Reports</a></li>
	</ul>
	 
	<div class="tab-content">
		<div class="tab-pane active" id="tabPageReport">
			<div id="Report" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
			<div id="ReportForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">
		
			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabReport'] = new ReportAdapter('Report','Report');
modJsList['tabReport'].setShowAddNew(false);

var modJs = modJsList['tabReport'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>      