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

function EmployeeTimeSheetAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
}

EmployeeTimeSheetAdapter.inherits(AdapterBase);

this.currentTimesheetId = null;
this.currentTimesheet = null;

EmployeeTimeSheetAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "date_start",
	        "date_end",
	        "status"
	];
});

EmployeeTimeSheetAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Start Date"},
			{ "sTitle": "End Date"},
			{ "sTitle": "Status"}
	];
});

EmployeeTimeSheetAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "date_start", {"label":"TimeSheet Start Date","type":"date","validation":""}],
	        [ "date_end", {"label":"TimeSheet End Date","type":"date","validation":""}],
	        [ "details", {"label":"Reason","type":"textarea","validation":"none"}]
	];
});

EmployeeTimeSheetAdapter.method('preProcessTableData', function(row) {
	row[1] =  Date.parse(row[1]).toString('MMM d, yyyy (dddd)');
	row[2] =  Date.parse(row[2]).toString('MMM d, yyyy (dddd)');
	return row;
});

EmployeeTimeSheetAdapter.method('renderForm', function(object) {
	var formHtml = this.templates['formTemplate'];
	var html = "";
	
	$("#"+this.getTableName()+'Form').html(formHtml);
	$("#"+this.getTableName()+'Form').show();
	$("#"+this.getTableName()).hide();
	
	$('#timesheet_start').html(Date.parse(object.date_start).toString('MMM d, yyyy (dddd)'));
	$('#timesheet_end').html(Date.parse(object.date_end).toString('MMM d, yyyy (dddd)'));
	
	this.currentTimesheet = object;
	
	this.getTimeEntries();
	
});


EmployeeTimeSheetAdapter.method('openTimeEntryDialog', function() {
	this.currentTimesheetId = this.currentId;
	var obj = modJsList['tabEmployeeTimeEntry'];
	$('#TimeEntryModel').modal({
		  backdrop: 'static',
		  keyboard: false
		});
	obj.currentTimesheet = this.currentTimesheet;
	obj.renderForm();
	obj.timesheetId = this.currentId;
	
});

EmployeeTimeSheetAdapter.method('closeTimeEntryDialog', function() {
	$('#TimeEntryModel').modal('hide');
});


EmployeeTimeSheetAdapter.method('getTimeEntries', function() {
	timesheetId = this.currentId;
	var sourceMappingJson = JSON.stringify(modJsList['tabEmployeeTimeEntry'].getSourceMapping());
	object = {"id":timesheetId,"sm":sourceMappingJson};
	
	var reqJson = JSON.stringify(object);
	
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getTimeEntriesSuccessCallBack';
	callBackData['callBackFail'] = 'getTimeEntriesFailCallBack';
	
	this.customAction('getTimeEntries','modules=time_sheets',reqJson,callBackData);
});

EmployeeTimeSheetAdapter.method('getTimeEntriesSuccessCallBack', function(callBackData) {
	var entries = callBackData;
	var html = "";
	var temp = '<tr><td><img class="tableActionButton" src="_BASE_images/delete.png" style="cursor:pointer;" rel="tooltip" title="Delete" onclick="modJsList[\'tabEmployeeTimeEntry\'].deleteRow(_id_);return false;"></img></td><td>_start_</td><td>_end_</td><td>_duration_</td><td>_project_</td><td>_details_</td>';
	
	for(var i=0;i<entries.length;i++){
		try{
			var t = temp;
			t = t.replace(/_start_/g,Date.parse(entries[i].date_start).toString('MMM d, yyyy [hh:mm tt]'));
			t = t.replace(/_end_/g,Date.parse(entries[i].date_end).toString('MMM d, yyyy [hh:mm tt]'));
			
			var mili = Date.parse(entries[i].date_end) - Date.parse(entries[i].date_start);
			var minutes = Math.round(mili/60000);
			var hourMinutes = (minutes % 60);
			var hours = (minutes-hourMinutes)/60;
			
			t = t.replace(/_duration_/g,"Hours ("+hours+") - Min ("+hourMinutes+")");
			if(entries[i].project == 'null' || entries[i].project == null || entries[i].project == undefined){
				t = t.replace(/_project_/g,"None");
			}else{
				t = t.replace(/_project_/g,entries[i].project);
			}
			t = t.replace(/_project_/g,entries[i].project);
			t = t.replace(/_details_/g,entries[i].details);
			t = t.replace(/_id_/g,entries[i].id);
			t = t.replace(/_BASE_/g,this.baseUrl);
		html += t;
		}catch(e){}
	}
	
	$('.timesheet_entries_table_body').html(html);
	if(modJs.getTableName() == 'SubEmployeeTimeSheetAll'){
		$('#submit_sheet').hide();
		$('#add_time_sheet_entry').hide();
	}else{
		if(this.currentElement.status == 'Approved'){
			$('#submit_sheet').hide();
			$('#add_time_sheet_entry').hide();
		}else{
			$('#submit_sheet').show();
			$('#add_time_sheet_entry').show();
		}
	}
});

EmployeeTimeSheetAdapter.method('getTimeEntriesFailCallBack', function(callBackData) {
	this.showMessage("Error", "Error occured while getting timesheet entries");
});



EmployeeTimeSheetAdapter.method('createPreviousTimesheet', function(id) {
	object = {"id":id};
	
	var reqJson = JSON.stringify(object);
	
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'createPreviousTimesheetSuccessCallBack';
	callBackData['callBackFail'] = 'createPreviousTimesheetFailCallBack';
	
	this.customAction('createPreviousTimesheet','modules=time_sheets',reqJson,callBackData);
});

EmployeeTimeSheetAdapter.method('createPreviousTimesheetSuccessCallBack', function(callBackData) {
	$('.tooltip').css("display","none");
	$('.tooltip').remove();
	//this.showMessage("Success", "Previous Timesheet created");
	this.get([]);
});

EmployeeTimeSheetAdapter.method('createPreviousTimesheetFailCallBack', function(callBackData) {
	this.showMessage("Error", callBackData);
});

EmployeeTimeSheetAdapter.method('changeTimeSheetStatusWithId', function(id, status) {
	
	if(status == "" || status ==null || status == undefined){
		this.showMessage("Status Error","Please select a status");
		return;
	}
	
	object = {"id":id,"status":status};
	
	var reqJson = JSON.stringify(object);
	
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'changeTimeSheetStatusSuccessCallBack';
	callBackData['callBackFail'] = 'changeTimeSheetStatusFailCallBack';
	
	this.customAction('changeTimeSheetStatus','modules=time_sheets',reqJson,callBackData);
});

EmployeeTimeSheetAdapter.method('changeTimeSheetStatusSuccessCallBack', function(callBackData) {
	this.showMessage("Successful", "Timesheet status changed successfully");
	this.get([]);
});

EmployeeTimeSheetAdapter.method('changeTimeSheetStatusFailCallBack', function(callBackData) {
	this.showMessage("Error", "Error occured while changing Timesheet status");
});


EmployeeTimeSheetAdapter.method('getActionButtonsHtml', function(id,data) {
	var html = '';
	if(this.getTableName() == "EmployeeTimeSheetAll"){
		html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.edit(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/redo.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Create previous time sheet" onclick="modJs.createPreviousTimesheet(_id_);return false;"></img></div>';
	}else{
		html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.edit(_id_);return false;"></img></div>';
	}
	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
});

EmployeeTimeSheetAdapter.method('getCustomTableParams', function() {
	var that = this;
	var dataTableParams = {
			"aoColumnDefs": [ 
			                 {
			                	 "fnRender": function(data, cell){
			                		 return that.preProcessRemoteTableData(data, cell, 1)
			                	 } ,
			                	 "aTargets": [1]
			                 },
			                 {
			                	 "fnRender": function(data, cell){
			                		 return that.preProcessRemoteTableData(data, cell, 2)
			                	 } ,
			                	 "aTargets": [2]
			                 },
			                 {
			      				"fnRender": that.getActionButtons,
			      				"aTargets": [that.getDataMapping().length]
			      			}
			                 ]
	};
	return dataTableParams;
});

EmployeeTimeSheetAdapter.method('preProcessRemoteTableData', function(data, cell, id) {
	return Date.parse(cell).toString('MMM d, yyyy (dddd)');
});


/*
 * Subordinate TimeSheets
 */

function SubEmployeeTimeSheetAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
}

this.timeSheetStatusChangeId = null;

SubEmployeeTimeSheetAdapter.inherits(EmployeeTimeSheetAdapter);

SubEmployeeTimeSheetAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "employee",
	        "date_start",
	        "date_end",
	        "status"
	];
});

SubEmployeeTimeSheetAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Employee","bSearchable":true},
			{ "sTitle": "Start Date","bSearchable":true},
			{ "sTitle": "End Date","bSearchable":true},
			{ "sTitle": "Status"}
	];
});

SubEmployeeTimeSheetAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "employee", {"label":"Employee","type":"select","allow-null":false,"remote-source":["Employee","id","first_name+last_name"]}],
	        [ "date_start", {"label":"TimeSheet Start Date","type":"date","validation":""}],
	        [ "date_end", {"label":"TimeSheet Start Date","type":"date","validation":""}],
	        [ "details", {"label":"Reason","type":"textarea","validation":"none"}]
	];
});

/*
SubEmployeeTimeSheetAdapter.method('get', function(callBackData) {
	var that = this;
	var sourceMappingJson = JSON.stringify(this.getSourceMapping());
	
	var filterJson = "";
	if(this.getFilter() != null){
		filterJson = JSON.stringify(this.getFilter());
	}
	
	var orderBy = "";
	if(this.getOrderBy() != null){
		orderBy = this.getOrderBy();
	}
	
	var object = {'sm':sourceMappingJson,'ft':filterJson,'ob':orderBy};
	var reqJson = JSON.stringify(object);
	
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getCustomSuccessCallBack';
	callBackData['callBackFail'] = 'getFailCallBack';
	
	this.customAction('getSubEmployeeTimeSheets','modules=time_sheets',reqJson,callBackData);
	
});
*/


SubEmployeeTimeSheetAdapter.method('isSubProfileTable', function() {
	return true;
});

SubEmployeeTimeSheetAdapter.method('getCustomSuccessCallBack', function(serverData) {
	var data = [];
	var mapping = this.getDataMapping();
	for(var i=0;i<serverData.length;i++){
		var row = [];
		for(var j=0;j<mapping.length;j++){
			row[j] = serverData[i][mapping[j]];
		}
		data.push(this.preProcessTableData(row));
	}
	
	this.tableData = data;
	
	this.createTable(this.getTableName());
	$("#"+this.getTableName()+'Form').hide();
	$("#"+this.getTableName()).show();
	
});

SubEmployeeTimeSheetAdapter.method('preProcessTableData', function(row) {
	row[2] =  Date.parse(row[2]).toString('MMM d, yyyy (dddd)');
	row[3] =  Date.parse(row[3]).toString('MMM d, yyyy (dddd)');
	return row;
});

SubEmployeeTimeSheetAdapter.method('openTimeSheetStatus', function(timeSheetId,status) {
	this.currentTimesheetId = timeSheetId;
	$('#TimeSheetStatusModel').modal('show');
	$('#timesheet_status').val(status);
	this.timeSheetStatusChangeId = timeSheetId;
});

SubEmployeeTimeSheetAdapter.method('closeTimeSheetStatus', function() {
	$('#TimeSheetStatusModel').modal('hide');
});

SubEmployeeTimeSheetAdapter.method('changeTimeSheetStatus', function() {
	var timeSheetStatus = $('#timesheet_status').val();
	
	this.changeTimeSheetStatusWithId(this.timeSheetStatusChangeId,timeSheetStatus);
	
	this.closeTimeSheetStatus();
	this.timeSheetStatusChangeId = null;
});




SubEmployeeTimeSheetAdapter.method('getActionButtonsHtml', function(id,data) {
	var html;
	html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.edit(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/run.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Change TimeSheet Status" onclick="modJs.openTimeSheetStatus(_id_,\'_status_\');return false;"></img></div>';
	
	
	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	html = html.replace(/_status_/g,data[3]);
	return html;
});


SubEmployeeTimeSheetAdapter.method('getCustomTableParams', function() {
	var that = this;
	var dataTableParams = {
			"aoColumnDefs": [ 
			                 {
			                	 "fnRender": function(data, cell){
			                		 return that.preProcessRemoteTableData(data, cell, 2)
			                	 } ,
			                	 "aTargets": [2]
			                 },
			                 {
			                	 "fnRender": function(data, cell){
			                		 return that.preProcessRemoteTableData(data, cell, 3)
			                	 } ,
			                	 "aTargets": [3]
			                 },
			                 {
			      				"fnRender": that.getActionButtons,
			      				"aTargets": [that.getDataMapping().length]
			      			}
			                 ]
	};
	return dataTableParams;
});


/**
 * EmployeeTimeEntryAdapter
 */

function EmployeeTimeEntryAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
}

EmployeeTimeEntryAdapter.inherits(AdapterBase);

this.timesheetId = null;
this.currentTimesheet = null;
this.allProjectsAllowed = 1;
this.employeeProjects = [];

EmployeeTimeEntryAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "project",
	        "date_start",
	        "time_start",
	        "date_end",
	        "time_end",
	        "details"
	];
});

EmployeeTimeEntryAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Project"},
			{ "sTitle": "Start Date"},
			{ "sTitle": "Start Time"},
			{ "sTitle": "End Date"},
			{ "sTitle": "End Time"},
			{ "sTitle": "Details"}
	];
});

EmployeeTimeEntryAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "project", {"label":"Project","type":"select2","allow-null":true,"remote-source":["Project","id","name"]}],
	        //[ "project", {"label":"Project","type":"select","source":[]}],
	        [ "date_select", {"label":"Date","type":"select","source":[]}],
	        [ "date_start", {"label":"Start Time","type":"time","validation":""}],
	        /*[ "time_start", {"label":"Start Time","type":"time"}],*/
	        [ "date_end", {"label":"End Time","type":"time","validation":""}],
	        /*[ "time_end", {"label":"End Time","type":"time"}],*/
	        [ "details", {"label":"Details","type":"textarea","validation":""}]
	];
});

/*
EmployeeTimeEntryAdapter.method('renderForm', function(object) {
	var formHtml = this.templates['orig_formTemplate'];
	formHtml = formHtml.replace(/modJs/g,"modJsList['tabEmployeeTimeEntry']");
	var html = "";
	var fields = this.getFormFields();
	for(var i=0;i<fields.length;i++){
		html += this.renderFormField(fields[i]);
	}
	formHtml = formHtml.replace(/_id_/g,this.getTableName()+"_submit");
	formHtml = formHtml.replace(/_fields_/g,html);
	$("#"+this.getTableName()+'Form').html(formHtml);
	$("#"+this.getTableName()+'Form').show();
	
	$("#"+this.getTableName()+'Form .datefield').datepicker({'viewMode':2});
	$("#"+this.getTableName()+'Form .timefield').timepicker({ 'step': 15 });
	
	if(object != undefined && object != null){
		this.fillForm(object);
	}
	
});
*/

EmployeeTimeEntryAdapter.method('getDates', function(startDate, stopDate) {
	
    var dateArray = new Array();
    var currentDate = startDate;
    while (currentDate <= stopDate) {
        dateArray.push( new Date (currentDate) );
        currentDate = currentDate.add({ days: 1 });
    }
    return dateArray;
});


EmployeeTimeEntryAdapter.method('renderForm', function(object) {
	var formHtml = this.getCustomTemplate('time_entry_form.html');
	formHtml = formHtml.replace(/modJs/g,"modJsList['tabEmployeeTimeEntry']");
	var html = "";
	var fields = this.getFormFields();
	
	for(var i=0;i<fields.length;i++){
		var metaField = this.getMetaFieldForRendering(fields[i][0]);
		if(metaField == "" || metaField == undefined){
			html += this.renderFormField(fields[i]);
		}else{
			var metaVal = object[metaField];
			if(metaVal != '' && metaVal != null && metaVal != undefined && metaVal.trim() != ''){
				html += this.renderFormField(JSON.parse(metaVal));
			}else{
				html += this.renderFormField(fields[i]);
			}
		}
		
	}
	
	
	//append dates
	
	var dateStart = new Date(this.currentTimesheet.date_start.replace(" ","T"));
	var dateStop = new Date(this.currentTimesheet.date_end.replace(" ","T"));
	
	var datesArray = this.getDates(dateStart, dateStop);
	
	var optionList = "";
	for(var i=0; i<datesArray.length; i++){
		optionList += '<option value="'+datesArray[i].toString("yyyy-MM-dd")+'">'+datesArray[i].toString("d-MMM-yyyy")+'</option>';
	}
	
	
	
	formHtml = formHtml.replace(/_id_/g,this.getTableName()+"_submit");
	formHtml = formHtml.replace(/_fields_/g,html);
	$("#"+this.getTableName()+'Form').html(formHtml);
	$("#"+this.getTableName()+'Form').show();
	$("#"+this.getTableName()).hide();
	
	$("#"+this.getTableName()+'Form .datefield').datepicker({'viewMode':2});
	$("#"+this.getTableName()+'Form .datetimefield').datetimepicker({
      language: 'en'
    });
	$("#"+this.getTableName()+'Form .timefield').datetimepicker({
	      language: 'en',
	      pickDate: false
	});
	
	$("#"+this.getTableName()+'Form .select2Field').select2();
	
	$("#date_select").html(optionList);
	
	var projectOptionList = "";
	projectOptionList += '<option value="NULL">None</option>';
	if(this.allProjectsAllowed == 0){
		for(var i=0;i<this.employeeProjects.length;i++){
			projectOptionList += '<option value="'+this.employeeProjects[i].project+'">'+this.employeeProjects[i].name+'</option>';
		}
	}else{
		for(var i=0;i<this.employeeProjects.length;i++){
			projectOptionList += '<option value="'+this.employeeProjects[i].id+'">'+this.employeeProjects[i].name+'</option>';
		}
	}
	
	$("#project").html(projectOptionList);
	
	if(object != undefined && object != null){
		this.fillForm(object);
	}
});


EmployeeTimeEntryAdapter.method('cancel', function() {
	$('#TimeEntryModel').modal('hide');
});

EmployeeTimeEntryAdapter.method('setAllProjectsAllowed', function(allProjectsAllowed) {
	this.allProjectsAllowed = allProjectsAllowed;
});

EmployeeTimeEntryAdapter.method('setEmployeeProjects', function(employeeProjects) {
	this.employeeProjects = employeeProjects;
});

/*
EmployeeTimeEntryAdapter.method('save', function() {
	var validator = new FormValidation(this.getTableName()+"_submit",true,{'ShowPopup':false,"LabelErrorClass":"error"});
	
	if(validator.checkValues()){
		var params = validator.getFormParameters();
		params.date_end = params.date_start;
		$(params).attr('timesheet',this.timesheetId);
		
		if(params.time_start.indexOf("am") != -1 && params.time_start.indexOf("12:") != -1){
			params.time_start = params.time_start.replace("12:","00:");
		}
		
		if(params.time_end.indexOf("am") != -1 && params.time_end.indexOf("12:") != -1){
			params.time_end = params.time_end.replace("12:","00:");
		}
		
		params.date_start = Date.parse(params.date_start+" "+params.time_start.replace('am',' AM').replace('pm',' PM')).toString("yyyy-MM-dd HH:mm:ss");
		params.date_end = Date.parse(params.date_end+" "+params.time_end.replace('am',' AM').replace('pm',' PM')).toString("yyyy-MM-dd HH:mm:ss");
		var msg = this.doCustomValidation(params);
		
		if(msg == null){
			var id = $('#'+this.getTableName()+"_submit #id").val();
			if(id != null && id != undefined && id != ""){
				$(params).attr('id',id);
			}
			this.add(params,[]);
			this.cancel();
		}else{
			$("#"+this.getTableName()+'Form .label').html(msg);
			$("#"+this.getTableName()+'Form .label').show();
		}
		
	}
});
*/

EmployeeTimeEntryAdapter.method('save', function() {
	var validator = new FormValidation(this.getTableName()+"_submit",true,{'ShowPopup':false,"LabelErrorClass":"error"});
	
	if(validator.checkValues()){
		var params = validator.getFormParameters();
		$(params).attr('timesheet',this.timesheetId);
		
		params.time_start = params.date_start;
		params.time_end = params.date_end;
		
		params.date_start = params.date_select+" "+params.date_start;
		params.date_end = params.date_select+" "+params.date_end;
		
		
		var msg = this.doCustomValidation(params);
		
		if(msg == null){
			var id = $('#'+this.getTableName()+"_submit #id").val();
			if(id != null && id != undefined && id != ""){
				$(params).attr('id',id);
			}
			this.add(params,[]);
			this.cancel();
		}else{
			$("#"+this.getTableName()+'Form .label').html(msg);
			$("#"+this.getTableName()+'Form .label').show();
		}
		
	}
});

EmployeeTimeEntryAdapter.method('doCustomValidation', function(params) {
	var st = Date.parse(params.date_start.replace(" ","T"));
	var et = Date.parse(params.date_end.replace(" ","T"));
	if(st.compareTo(et) != -1){
		return "Start time should be less than End time";
	}
	/*
	var sd = Date.parse(this.currentTimesheet.date_start);
	var ed = Date.parse(this.currentTimesheet.date_end).addDays(1);
	
	if(sd.compareTo(et) != -1 || sd.compareTo(st) > 0 || st.compareTo(ed) != -1 || et.compareTo(ed) != -1){
		return "Start time and end time shoud be with in " + sd.toString('MMM d, yyyy (dddd)') + " and " + ed.toString('MMM d, yyyy (dddd)');
	}
	*/
	return null;
});

EmployeeTimeEntryAdapter.method('addSuccessCallBack', function(callBackData,serverData) {
	this.get(callBackData);
	modJs.getTimeEntries();
});

EmployeeTimeEntryAdapter.method('deleteRow', function(id) {
	this.deleteObj(id,[]);
	
});

EmployeeTimeEntryAdapter.method('deleteSuccessCallBack', function(callBackData,serverData) {
	modJs.getTimeEntries();
});