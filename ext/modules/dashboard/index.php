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

$moduleName = 'dashboard';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';
?><div class="span9">
			  
	<div class="row">
		<div class="col-lg-3 col-xs-6">
			<!-- small box -->
			<div class="small-box bg-aqua">
				<div class="inner">
					<h3 id="lastPunchTime">
					..
					</h3>
					<p id="punchTimeText">
					Waiting for Response..
					</p>
				</div>
				<div class="icon">
					<i class="ion ion-ios7-alarm-outline"></i>
				</div>
				<a href="#" class="small-box-footer" id="atteandanceLink">
					Record Attendance <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div><!-- ./col -->						                        
		<div class="col-lg-3 col-xs-6">
			<!-- small box -->
			<div class="small-box bg-green">
				<div class="inner">
					<h3 id="pendingLeaveCount">..</h3>
					<p>
						Pending Leaves
					</p>
				</div>
				<div class="icon">
					<i class="ion ion-calendar"></i>
				</div>
				<a href="#" class="small-box-footer" id="leavesLink">
					Check Leave Status <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div><!-- ./col -->
		<div class="col-lg-3 col-xs-6">
			<!-- small box -->
			<div class="small-box bg-yellow">
				<div class="inner">
					<h3 id="timeSheetHoursWorked">..</h3>
					<p>
						Hours worked Last Week
					</p>
				</div>
				<div class="icon">
					<i class="ion ion-clock"></i>
				</div>
				<a href="#" class="small-box-footer" id="timesheetLink">
					Update Time Sheet <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div><!-- ./col -->
		<div class="col-lg-3 col-xs-6">
			<!-- small box -->
			<div class="small-box bg-red">
				<div class="inner">
					<h3 id="numberOfProjects">..</h3>
					<p>
						Active Projects
					</p>
				</div>
				<div class="icon">
					<i class="ion ion-pie-graph"></i>
				</div>
				<a href="#" class="small-box-footer" id="projectsLink">
					More info <i class="fa fa-arrow-circle-right"></i>
				</a>
			</div>
		</div><!-- ./col -->                        
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabDashboard'] = new DashboardAdapter('Dashboard','Dashboard');

var modJs = modJsList['tabDashboard'];

$("#atteandanceLink").attr("href",modJs.getCustomUrl('?g=modules&n=attendance&m=module_Time_Management'));
$("#leavesLink").attr("href",modJs.getCustomUrl('?g=modules&n=leaves&m=module_Leaves'));
$("#timesheetLink").attr("href",modJs.getCustomUrl('?g=modules&n=time_sheets&m=module_Time_Management'));
$("#projectsLink").attr("href",modJs.getCustomUrl('?g=modules&n=projects&m=module_Personal_Information'));

modJs.getPunch();
modJs.getPendingLeaves();
modJs.getLastTimeSheetHours();
modJs.getEmployeeActiveProjects();

</script>
<?php include APP_BASE_PATH.'footer.php';?>      