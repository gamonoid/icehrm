<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

use Attendance\Admin\Api\AttendanceUtil;
use Classes\BaseService;
use Classes\SettingsManager;

$moduleName = 'attendance';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';

$useServerTime = SettingsManager::getInstance()->getSetting('Attendance: Use Department Time Zone');
$currentEmployeeTimeZone = BaseService::getInstance()->getCurrentEmployeeTimeZone();
$attendanceUtils = new AttendanceUtil();
$employeeId = BaseService::getInstance()->getCurrentProfileId();
$hasOpenPunch = $attendanceUtils->isEmployeeHasOpenPunch(date('Y-m-d'), $employeeId)?1:0;
$punchedOutToday = $attendanceUtils->isEmployeePunchedOut(date('Y-m-d'), $employeeId)?1:0;

if(empty($currentEmployeeTimeZone)){
    $useServerTime = 0;
}
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabAttendance" href="#tabPageAttendance"><?=t('Attendance')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageAttendance">
			<div id="AttendanceTable" class="reviewBlock" data-content="List" style="padding-left:5px;"></div>
			<div id="AttendanceForm"></div>
		</div>
	</div>

</div>
<style>
#Attendance .dataTables_filter label{
	float:left;
}
</style>
<script>
var modJsList = [];
modJsList['tabAttendance'] = new AttendanceAdapter('Attendance','Attendance','','in_time desc');
modJsList['tabAttendance'].setUseServerTime(<?=$useServerTime?>);
modJsList['tabAttendance'].setHasOpenPunch(<?=$hasOpenPunch?>);
modJsList['tabAttendance'].setPunchedOutToday(<?=$punchedOutToday?>);
modJsList['tabAttendance'].setObjectTypeName('Attendance');
modJsList['tabAttendance'].setAccess([]);
modJsList['tabAttendance'].setDataPipe(new IceDataPipe(modJsList['tabAttendance']));


var modJs = modJsList['tabAttendance'];

</script>
<?php include APP_BASE_PATH.'footer.php';?>
