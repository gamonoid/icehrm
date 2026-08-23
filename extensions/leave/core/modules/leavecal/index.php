<?php
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

$moduleName = 'leavecal';
$moduleGroup = 'modules';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmployeeLeaveCalendar" href="#tabPageEmployeeLeaveCalendar"><?=t('Leave Calendar')?></a></li>
	</ul>

	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployeeLeaveCalendar">
			<div id="EmployeeLeaveCalendar" class="reviewBlock" data-content="List" style="padding-left:5px;">
				<div id="loadingLeaveCalendarBlock" style="display:none;position: absolute;top: 10px;left: 10px;font-weight: bold">Loading...</div>
				<div id="EmployeeLeaveCalendarTable" style="width:100%;margin-left:5px;"></div>
			</div>

		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabEmployeeLeaveCalendar'] = new EmployeeLeaveCalendarAdapter('EmployeeLeaveCalendar','EmployeeLeaveCalendar');

var modJs = modJsList['tabEmployeeLeaveCalendar'];

// $(document).ready(function() {
//
// 	$('#leaveCalendarBlock').fullCalendar({
//         header: {
//             left: 'prev,next today',
//             center: 'title',
//             right: 'month,agendaWeek,agendaDay'
//         },
// 		height:500,
// 		firstDay:1,
// 		editable: false,
//
// 		events: modJs.getLeaveJsonUrl(),
//
// 		loading: function(bool) {
// 			if (bool) $('#loadingLeaveCalendarBlock').show();
// 			else $('#loadingLeaveCalendarBlock').hide();
// 		}
//
// 	});
//
// });

</script>
<?php include APP_BASE_PATH.'footer.php';?>
