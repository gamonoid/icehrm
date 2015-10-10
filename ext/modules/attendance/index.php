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
 
$moduleName = 'attendance';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
			  
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabAttendance" href="#tabPageAttendance">Attendance</a></li>
	</ul>
	 
	<div class="tab-content">
		<div class="tab-pane active" id="tabPageAttendance">
			<div id="Attendance" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
		</div>
	</div>

</div>
<style>
#Attendance .dataTables_filter label{
	float:left;
}
</style>
<script>
var modJsList = new Array();
modJsList['tabAttendance'] = new AttendanceAdapter('Attendance','Attendance','','in_time desc');
modJsList['tabAttendance'].setRemoteTable(true);
modJsList['tabAttendance'].updatePunchButton(true);

var modJs = modJsList['tabAttendance'];

</script>
<div class="modal" id="PunchModel" tabindex="-1" role="dialog" aria-labelledby="messageModelLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
		<h3 style="font-size: 17px;">Punch Time</h3>
	</div>
	<div class="modal-body" style="max-height:530px;" id="AttendanceForm">
		
	</div>
</div>
</div>
</div>
<?php include APP_BASE_PATH.'footer.php';?>      