<?php 

$moduleName = 'Reports';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
$additionalJs = array();
$additionalJs[] = BASE_URL.'admin/reports/lib.js?v='.$jsVersion;
include APP_BASE_PATH.'modulejslibs.inc.php';
?>
<div class="span9">
			  
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabUserReport" href="#tabPageUserReport"><?=t('Reports')?></a></li>
	</ul>
	 
	<div class="tab-content">
		<div class="tab-pane active" id="tabPageUserReport">
			<div id="UserReport" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
			<div id="UserReportForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">
		
			</div>
		</div>

	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabUserReport'] = new UserReportAdapter('UserReport','UserReport','','report_group');
modJsList['tabUserReport'].setShowAddNew(false);
modJsList['tabUserReport'].setRemoteTable(true);

/*
modJsList['tabReport'] = new ReportGenAdapter('File','File','{"file_group":"Report"}','group');
modJsList['tabReport'].setShowAddNew(false);
*/

var modJs = modJsList['tabUserReport'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>      
