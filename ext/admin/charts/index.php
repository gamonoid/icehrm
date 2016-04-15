<?php 

$moduleName = 'company_structure';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?>

<link href="<?=BASE_URL.'js/nvd3/src/nv.d3.css?v='.$jsVersion?>" rel="stylesheet" type="text/css">

<script src="<?=BASE_URL.'js/nvd3/lib/d3.v3.js?v='.$jsVersion?>"></script>
<script src="<?=BASE_URL.'js/nvd3/nv.d3.js?v='.$jsVersion?>"></script>
<script src="<?=BASE_URL.'js/nvd3/src/tooltip.js?v='.$jsVersion?>"></script>
<script src="<?=BASE_URL.'js/nvd3/src/utils.js?v='.$jsVersion?>"></script>
<script src="<?=BASE_URL.'js/nvd3/src/models/legend.js?v='.$jsVersion?>"></script>
<script src="<?=BASE_URL.'js/nvd3/src/models/axis.js?v='.$jsVersion?>"></script>
<script src="<?=BASE_URL.'js/nvd3/src/models/multiBar.js?v='.$jsVersion?>"></script>
<script src="<?=BASE_URL.'js/nvd3/src/models/discreteBar.js?v='.$jsVersion?>"></script>
<script src="<?=BASE_URL.'js/nvd3/src/models/discreteBarChart.js?v='.$jsVersion?>"></script>
<script src="<?=BASE_URL.'js/nvd3/stream_layers.js?v='.$jsVersion?>"></script>

<style type="text/css">
svg .tooltip { opacity: 1; }
</style>

<div class="span9">
	
  		  
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabAttendanceGraph" href="#tabPageAttendanceGraph">Attendance Graph</a></li>
		<li><a id="tabTimeUtilizationGraph" href="#tabPageTimeUtilizationGraph">Hours in Office vs Hours Worked Graph</a></li>
	</ul>
	 
	<div class="tab-content">
		<div class="tab-pane active reviewBlock with-3d-shadow with-transitions" id="tabPageAttendanceGraph" style="height: 500px;position: relative;">
			<svg></svg>
		</div>
		<div class="tab-pane reviewBlock with-3d-shadow with-transitions" id="tabPageTimeUtilizationGraph" style="height: 500px;position: relative;">
			<svg></svg>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();
modJsList['tabAttendanceGraph'] = new AttendanceGraphAdapter('AttendanceGraph');
modJsList['tabAttendanceGraph'].setShowAddNew(false);

modJsList['tabTimeUtilizationGraph'] = new TimeUtilizationGraphAdapter('TimeUtilizationGraph');
modJsList['tabTimeUtilizationGraph'].setShowAddNew(false);

var modJs = modJsList['tabAttendanceGraph'];

</script>

<?php include APP_BASE_PATH.'footer.php';?>      