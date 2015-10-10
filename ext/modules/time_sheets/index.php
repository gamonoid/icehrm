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

$moduleName = 'employee_TimeSheet';
define('MODULE_PATH',dirname(__FILE__));
include APP_BASE_PATH.'header.php';
include APP_BASE_PATH.'modulejslibs.inc.php';


//custom code
$employeeProjects = array();
$allowAllProjects = $settingsManager->getSetting("Projects: Make All Projects Available to Employees");
if($allowAllProjects == 0){
	$employeeProjects = array();
	$employeeProjectsTemp = $baseService->get("EmployeeProject");
	foreach($employeeProjectsTemp as $p){
		$project = new Project();
		$project->Load("id = ?",$p->project);
		$p->name = $project->name;
		$employeeProjects[] = $p;
	}
}else{
	$employeeProjects = $baseService->get("Project");
}


?><div class="span9">
			  
	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="active"><a id="tabEmployeeTimeSheetAll" href="#tabPageEmployeeTimeSheetAll">All My TimeSheets</a></li>
		<li class=""><a id="tabEmployeeTimeSheetApproved" href="#tabPageEmployeeTimeSheetApproved">Approved TimeSheets</a></li>
		<li class=""><a id="tabEmployeeTimeSheetPending" href="#tabPageEmployeeTimeSheetPending">Pending TimeSheets</a></li>
		<li class=""><a id="tabSubEmployeeTimeSheetAll" href="#tabPageSubEmployeeTimeSheetAll">Subordinate TimeSheets</a></li>
	</ul>
	 
	<div class="tab-content">
		<div class="tab-pane active" id="tabPageEmployeeTimeSheetAll">
			<div id="EmployeeTimeSheetAll" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
			<div id="EmployeeTimeSheetAllForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">
		
			</div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeTimeSheetApproved">
			<div id="EmployeeTimeSheetApproved" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
			<div id="EmployeeTimeSheetApprovedForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">
		
			</div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeTimeSheetPending">
			<div id="EmployeeTimeSheetPending" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
			<div id="EmployeeTimeSheetPendingForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">
		
			</div>
		</div>
		<div class="tab-pane" id="tabPageSubEmployeeTimeSheetAll">
			<div id="SubEmployeeTimeSheetAll" class="reviewBlock" data-content="List" style="padding-left:5px;">
		
			</div>
			<div id="SubEmployeeTimeSheetAllForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">
		
			</div>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabEmployeeTimeSheetAll'] = new EmployeeTimeSheetAdapter('EmployeeTimeSheet','EmployeeTimeSheetAll','','date_start desc');
modJsList['tabEmployeeTimeSheetAll'].setShowAddNew(false);
modJsList['tabEmployeeTimeSheetAll'].setRemoteTable(true);

modJsList['tabEmployeeTimeSheetApproved'] = new EmployeeTimeSheetAdapter('EmployeeTimeSheet','EmployeeTimeSheetApproved',{"status":"Approved"});
modJsList['tabEmployeeTimeSheetApproved'].setShowAddNew(false);
modJsList['tabEmployeeTimeSheetApproved'].setRemoteTable(true);

modJsList['tabEmployeeTimeSheetPending'] = new EmployeeTimeSheetAdapter('EmployeeTimeSheet','EmployeeTimeSheetPending',{"status":"Pending"});
modJsList['tabEmployeeTimeSheetPending'].setShowAddNew(false);
modJsList['tabEmployeeTimeSheetPending'].setRemoteTable(true);

modJsList['tabSubEmployeeTimeSheetAll'] = new SubEmployeeTimeSheetAdapter('EmployeeTimeSheet','SubEmployeeTimeSheetAll','','date_start desc');
modJsList['tabSubEmployeeTimeSheetAll'].setShowAddNew(false);
modJsList['tabSubEmployeeTimeSheetAll'].setRemoteTable(true);

modJsList['tabEmployeeTimeEntry'] = new EmployeeTimeEntryAdapter('EmployeeTimeEntry','EmployeeTimeEntry','','');
modJsList['tabEmployeeTimeEntry'].setShowAddNew(false);
modJsList['tabEmployeeTimeEntry'].setAllProjectsAllowed(<?=$allowAllProjects?>);
modJsList['tabEmployeeTimeEntry'].setEmployeeProjects(<?=json_encode($employeeProjects)?>);

var modJs = modJsList['tabEmployeeTimeSheetAll'];

</script>
<div class="modal" id="TimeSheetStatusModel" tabindex="-1" role="dialog" aria-labelledby="messageModelLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">	
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
		<h3 style="font-size: 17px;">Change Timesheet Status</h3>
	</div>
	<div class="modal-body">
		<form id="TimeSheetStatusForm">
		<div class="control-group">
			<label class="control-label" for="timesheet_status">Timesheet Status</label>
			<div class="controls">
			  	<select class="" type="text" id="timesheet_status" name="timesheet_status">
				  	<option value="Approved">Approved</option>
				  	<option value="Pending">Pending</option>
				  	<option value="Rejected">Rejected</option>
				  	<option value="Submitted">Submitted</option>
			  	</select>
			</div>
		</div>
		</form>
	</div>
	<div class="modal-footer">
 		<button class="btn btn-primary" onclick="modJs.changeTimeSheetStatus();">Change Status</button>
 		<button class="btn" onclick="modJs.closeTimeSheetStatus();">Not Now</button>
	</div>
</div>
</div>
</div>

<div class="modal" id="TimeEntryModel" tabindex="-1" role="dialog" aria-labelledby="messageModelLabel" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true"><li class="fa fa-times"/></button>
		<h3 style="font-size: 17px;">Time Entry</h3>
	</div>
	<div class="modal-body" style="max-height:530px;" id="EmployeeTimeEntryForm">
		
	</div>
</div>
</div>
</div>

<?php include APP_BASE_PATH.'footer.php';?>      