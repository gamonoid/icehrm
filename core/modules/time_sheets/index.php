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
$startEndTimeNeeded = \Classes\SettingsManager::getInstance()->getSetting(
	'System: Time-sheet Entry Start and End time Required'
);
?><script type="text/javascript" src="<?=BASE_URL?>js/mindmup-editabletable.js?v=<?=$jsVersion?>"></script>
<div class="span9">

	<ul class="nav nav-tabs" id="modTab" style="margin-bottom:0px;margin-left:5px;border-bottom: none;">
		<li class="modTabPage active"><a id="tabEmployeeTimeSheetAll" href="#tabPageEmployeeTimeSheetAll"><?=t('All My TimeSheets')?></a></li>
		<li class="modTabPage"><a id="tabEmployeeTimeSheetApproved" href="#tabPageEmployeeTimeSheetApproved"><?=t('Approved TimeSheets')?></a></li>
		<li class="modTabPage"><a id="tabEmployeeTimeSheetPending" href="#tabPageEmployeeTimeSheetPending"><?=t('Pending TimeSheets')?></a></li>
		<li class="modTabPage"><a id="tabSubEmployeeTimeSheetAll" href="#tabPageSubEmployeeTimeSheetAll"><?=t('Subordinate TimeSheets')?></a></li>
	</ul>

	<div class="tab-content" id="timesheetTabs">
		<div class="tab-pane active" id="tabPageEmployeeTimeSheetAll">
			<div id="EmployeeTimeSheetAll" class="reviewBlock reviewBlockTable" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeTimeSheetAllForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeTimeSheetApproved">
			<div id="EmployeeTimeSheetApproved" class="reviewBlock reviewBlockTable" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeTimeSheetApprovedForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane" id="tabPageEmployeeTimeSheetPending">
			<div id="EmployeeTimeSheetPending" class="reviewBlock reviewBlockTable" data-content="List" style="padding-left:5px;">

			</div>
			<div id="EmployeeTimeSheetPendingForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div class="tab-pane" id="tabPageSubEmployeeTimeSheetAll">
			<div id="SubEmployeeTimeSheetAll" class="reviewBlock reviewBlockTable" data-content="List" style="padding-left:5px;">

			</div>
			<div id="SubEmployeeTimeSheetAllForm" class="reviewBlock" data-content="Form" style="padding-left:5px;display:none;">

			</div>
		</div>
		<div id="QtsheetHeader" class="reviewBlock" style="text-align: right;display:none;padding: 12px 19px 14px;">
			<span style="font-size:17px;font-weight:bold;color:#999;margin-left:10px;">
				Timesheet From <span class="timesheet_start"></span> to <span class="timesheet_end"></span>
			</span>
		</div>
		<div id="Qtsheet" class="reviewBlock" data-content="List" style="padding-left:5px;display:none;overflow-x: auto;">

		</div>
		<div id="QtsheetDataButtons" style="text-align: right;margin-top: 10px;">
			<button class="cancelBtnTable btn" style="margin-right:5px;"><i class="fa fa-times-circle-o"></i> Cancel</button>
			<button class="saveBtnTable btn btn-primary" style="margin-right:5px;"><i class="fa fa-save"></i> Save</button>
			<button class="downloadBtnTable btn btn-primary" style="margin-right:5px;"><i class="fa fa-check"></i> Download</button>
			<button class="completeBtnTable btn btn-primary" style="margin-right:5px;"><i class="fa fa-check-square-o"></i> Submit</button>
		</div>
	</div>

</div>
<script>
var modJsList = new Array();

modJsList['tabEmployeeTimeSheetAll'] = new EmployeeTimeSheetAdapter('EmployeeTimeSheet','EmployeeTimeSheetAll','','date_start desc');
modJsList['tabEmployeeTimeSheetAll'].setShowAddNew(false);
modJsList['tabEmployeeTimeSheetAll'].setRemoteTable(true);
modJsList['tabEmployeeTimeSheetAll'].setNeedStartEndTime(<?=$startEndTimeNeeded?>);

modJsList['tabEmployeeTimeSheetApproved'] = new EmployeeTimeSheetAdapter('EmployeeTimeSheet','EmployeeTimeSheetApproved',{"status":"Approved"});
modJsList['tabEmployeeTimeSheetApproved'].setShowAddNew(false);
modJsList['tabEmployeeTimeSheetApproved'].setRemoteTable(true);
modJsList['tabEmployeeTimeSheetApproved'].setNeedStartEndTime(<?=$startEndTimeNeeded?>);

modJsList['tabEmployeeTimeSheetPending'] = new EmployeeTimeSheetAdapter('EmployeeTimeSheet','EmployeeTimeSheetPending',{"status":"Pending"});
modJsList['tabEmployeeTimeSheetPending'].setShowAddNew(false);
modJsList['tabEmployeeTimeSheetPending'].setRemoteTable(true);
modJsList['tabEmployeeTimeSheetPending'].setNeedStartEndTime(<?=$startEndTimeNeeded?>);

modJsList['tabSubEmployeeTimeSheetAll'] = new SubEmployeeTimeSheetAdapter('EmployeeTimeSheet','SubEmployeeTimeSheetAll','','date_start desc');
modJsList['tabSubEmployeeTimeSheetAll'].setShowAddNew(false);
modJsList['tabSubEmployeeTimeSheetAll'].setRemoteTable(true);
modJsList['tabSubEmployeeTimeSheetAll'].setNeedStartEndTime(<?=$startEndTimeNeeded?>);

modJsList['tabEmployeeTimeEntry'] = new EmployeeTimeEntryAdapter('EmployeeTimeEntry','EmployeeTimeEntry','','');
modJsList['tabEmployeeTimeEntry'].setShowAddNew(false);

modJsList['tabQtsheet'] = new QtsheetAdapter('Qtsheet','Qtsheet');
modJsList['tabQtsheet'].setRemoteTable(false);
modJsList['tabQtsheet'].setShowAddNew(false);
modJsList['tabQtsheet'].setModulePath('modules=time_sheets');
modJsList['tabQtsheet'].setRowFieldName('project');
modJsList['tabQtsheet'].setColumnFieldName('date');
modJsList['tabQtsheet'].setTables('Project','QTDays','EmployeeTimeEntry');

$(".saveBtnTable").off().on('click',function(){
	modJsList['tabQtsheet'].sendCellDataUpdates();
});

$(".completeBtnTable").off().on('click',function(){
	modJsList['tabQtsheet'].sendAllCellDataUpdates();
	$(".completeBtnTable").hide();
	$(".saveBtnTable").hide();
});

$(".downloadBtnTable").off().on('click',function(){
	modJsList['tabQtsheet'].downloadTimesheet();
});

$(".cancelBtnTable").off().on('click',function(){
	var lastTabName = $('#Qtsheet').data('lastActiveTab');
	modJs = modJsList['tab'+lastTabName];
	modJs.get([]);
	$('#QtsheetHeader').hide();
	$('#Qtsheet').hide();
	$('#QtsheetDataButtons').hide();
});

$(".modTabPage").on('click',function(){
	$('#QtsheetHeader').hide();
	$('#Qtsheet').hide();
	$('#QtsheetDataButtons').hide();
});

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
