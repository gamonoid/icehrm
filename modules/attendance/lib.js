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

function AttendanceAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
	this.punch = null;
    this.useServerTime = 0;
	this.photoTaken = 0;
	this.photoAttendance = 0;
}

AttendanceAdapter.inherits(AdapterBase);

AttendanceAdapter.method('updatePunchButton', function() {
	this.getPunch('changePunchButtonSuccessCallBack');
});

AttendanceAdapter.method('setUseServerTime', function(val) {
    this.useServerTime = val;
});

AttendanceAdapter.method('setPhotoAttendance', function(val) {
	this.photoAttendance = val;
});


AttendanceAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "in_time",
	        "out_time",
	        "note"
	];
});

AttendanceAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Time-In" },
			{ "sTitle": "Time-Out"},
			{ "sTitle": "Note"}
	];
});

AttendanceAdapter.method('getFormFields', function() {
    if(this.useServerTime == 0){
        return [
            [ "id", {"label":"ID","type":"hidden"}],
            [ "time", {"label":"Time","type":"datetime"}],
            [ "note", {"label":"Note","type":"textarea","validation":"none"}]
        ];
    }else{
        return [
            [ "id", {"label":"ID","type":"hidden"}],
            [ "note", {"label":"Note","type":"textarea","validation":"none"}]
        ];
    }

});


AttendanceAdapter.method('getCustomTableParams', function() {
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

AttendanceAdapter.method('preProcessRemoteTableData', function(data, cell, id) {
	if(id == 1){
		if(cell == '0000-00-00 00:00:00' || cell == "" || cell == undefined || cell == null){
			return "";
		}
		return Date.parse(cell).toString('yyyy MMM d  <b>HH:mm</b>');
	}else if(id == 2){
		if(cell == '0000-00-00 00:00:00' || cell == "" || cell == undefined || cell == null){
			return "";
		}
		return Date.parse(cell).toString('MMM d  <b>HH:mm</b>');
	}else if(id == 3){
		if(cell != undefined && cell != null){
			if(cell.length > 20){
				return cell.substring(0,20)+"..";
			}
		}
		return cell;
	}

});


AttendanceAdapter.method('getActionButtonsHtml', function(id,data) {
	/*
	var html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/download.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Download Document" onclick="download(\'_attachment_\');return false;"></img><img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img></div>';
	html = html.replace(/_id_/g,id);
	html = html.replace(/_attachment_/g,data[5]);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
	*/
	return "";
});

AttendanceAdapter.method('getTableTopButtonHtml', function() {
	if(this.punch == null || this.punch == undefined){
		return '<button id="punchButton" style="float:right;" onclick="modJs.showPunchDialog();return false;" class="btn btn-small">Punch-in <span class="icon-time"></span></button>';
	}else{
		return '<button id="punchButton" style="float:right;" onclick="modJs.showPunchDialog();return false;" class="btn btn-small">Punch-out <span class="icon-time"></span></button>';
	}

});


AttendanceAdapter.method('save', function() {
	var that = this;
	var validator = new FormValidation(this.getTableName()+"_submit",true,{'ShowPopup':false,"LabelErrorClass":"error"});
	if(validator.checkValues()){

		var msg = this.doCustomValidation(params);
		if(msg == null){
			var params = validator.getFormParameters();
			params = this.forceInjectValuesBeforeSave(params);
			params['cdate'] = this.getClientDate(new Date()).toISOString().slice(0, 19).replace('T', ' ');
			var reqJson = JSON.stringify(params);
			var callBackData = [];
			callBackData['callBackData'] = [];
			callBackData['callBackSuccess'] = 'saveSuccessCallback';
			callBackData['callBackFail'] = 'getPunchFailCallBack';

			this.customAction('savePunch','modules=attendance',reqJson,callBackData, true);
		}else{
			$("#"+this.getTableName()+'Form .label').html(msg);
			$("#"+this.getTableName()+'Form .label').show();
		}
	}
});

AttendanceAdapter.method('saveSuccessCallback', function(callBackData) {
	this.punch = callBackData;
	this.getPunch('changePunchButtonSuccessCallBack');
	$('#PunchModel').modal('hide');
	this.get([]);
});


AttendanceAdapter.method('cancel', function() {
	$('#PunchModel').modal('hide');
});

AttendanceAdapter.method('showPunchDialog', function() {
	this.getPunch('showPunchDialogShowPunchSuccessCallBack');
});

AttendanceAdapter.method('getPunch', function(successCallBack) {
	var that = this;
	var object = {};

	object['date'] = this.getClientDate(new Date()).toISOString().slice(0, 19).replace('T', ' ');
	object['offset'] = this.getClientGMTOffset();
	var reqJson = JSON.stringify(object);
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = successCallBack;
	callBackData['callBackFail'] = 'getPunchFailCallBack';

	this.customAction('getPunch','modules=attendance',reqJson,callBackData);
});

AttendanceAdapter.method('postRenderForm', function(object, $tempDomObj) {
    $("#Attendance").show();
});


AttendanceAdapter.method('showPunchDialogShowPunchSuccessCallBack', function(callBackData) {
	this.punch = callBackData;
	$('#PunchModel').modal('show');
	if(this.punch == null){
		$('#PunchModel').find("h3").html("Punch Time-in");
		modJs.renderForm();
	}else{
		$('#PunchModel').find("h3").html("Punch Time-out");
		modJs.renderForm(this.punch);
	}
	$('#Attendance').show();
	var picker = $('#time_datetime').data('datetimepicker');
	picker.setLocalDate(new Date());
});

AttendanceAdapter.method('changePunchButtonSuccessCallBack', function(callBackData) {
	this.punch = callBackData;
	if(this.punch == null){
		$("#punchButton").html('Punch-in <span class="icon-time"></span>');
	}else{
		$("#punchButton").html('Punch-out <span class="icon-time"></span>');
	}
});

AttendanceAdapter.method('getPunchFailCallBack', function(callBackData) {
	this.showMessage("Error Occured while Time Punch", callBackData);
});

AttendanceAdapter.method('getClientDate', function (date) {

	var offset = this.getClientGMTOffset();
    var tzDate = date.addMinutes(offset*60);
    return tzDate;

});

AttendanceAdapter.method('getClientGMTOffset', function () {

	var rightNow = new Date();
	var jan1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);
	var temp = jan1.toGMTString();
	var jan2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
	var std_time_offset = (jan1 - jan2) / (1000 * 60 * 60);

	return std_time_offset;

});

AttendanceAdapter.method('doCustomValidation', function(params) {
	if (this.photoAttendance  && !this.photoTaken) {
		return "Please attach a photo before submitting";
	}
	return null;
});

AttendanceAdapter.method('forceInjectValuesBeforeSave', function(params) {
	if (this.photoAttendance) {
		var canvas = document.getElementById('attendnaceCanvas');
		params.image = canvas.toDataURL();
	}
	return params;
});

AttendanceAdapter.method('postRenderForm', function() {
	if (this.photoAttendance == '1') {
		$('.photoAttendance').show();
		var video = document.getElementById('attendnaceVideo');

		// Get access to the camera!
		if(navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
			navigator.mediaDevices.getUserMedia({ video: true }).then(function(stream) {
				video.src = window.URL.createObjectURL(stream);
				video.play();
			});
		}
		this.photoTaken = false;
		this.configureEvents();
	} else {
		$('.photoAttendance').remove();
	}

});

AttendanceAdapter.method('configureEvents', function() {
	var that = this;
	var canvas = document.getElementById('attendnaceCanvas');
	var context = canvas.getContext('2d');
	var video = document.getElementById('attendnaceVideo');
	$('.attendnaceSnap').click(function() {
		context.drawImage(video, 0, 0, 208, 156);
		that.photoTaken = true;
		return false;
	});
});





function EmployeeAttendanceSheetAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
}

EmployeeAttendanceSheetAdapter.inherits(AdapterBase);

this.currentTimesheetId = null;
this.currentTimesheet = null;

EmployeeAttendanceSheetAdapter.method('getDataMapping', function() {
	return [
		"id",
		"date_start",
		"date_end",
		"total_time",
		"status"
	];
});

EmployeeAttendanceSheetAdapter.method('getHeaders', function() {
	return [
		{ "sTitle": "ID" ,"bVisible":false},
		{ "sTitle": "Start Date"},
		{ "sTitle": "End Date"},
		{ "sTitle": "Total Time"},
		{ "sTitle": "Status"}
	];
});

EmployeeAttendanceSheetAdapter.method('getFormFields', function() {
	return [
		[ "id", {"label":"ID","type":"hidden"}],
		[ "date_start", {"label":"TimeSheet Start Date","type":"date","validation":""}],
		[ "date_end", {"label":"TimeSheet End Date","type":"date","validation":""}],
		[ "details", {"label":"Reason","type":"textarea","validation":"none"}]
	];
});

EmployeeAttendanceSheetAdapter.method('preProcessTableData', function(row) {
	row[1] =  Date.parse(row[1]).toString('MMM d, yyyy (dddd)');
	row[2] =  Date.parse(row[2]).toString('MMM d, yyyy (dddd)');
	return row;
});

EmployeeAttendanceSheetAdapter.method('renderForm', function(object) {
	var formHtml = this.templates['formTemplate'];
	var html = "";

	$("#"+this.getTableName()+'Form').html(formHtml);
	$("#"+this.getTableName()+'Form').show();
	$("#"+this.getTableName()).hide();

	$('#attendnacesheet_start').html(Date.parse(object.date_start).toString('MMM d, yyyy (dddd)'));
	$('#attendnacesheet_end').html(Date.parse(object.date_end).toString('MMM d, yyyy (dddd)'));

	this.currentTimesheet = object;

	this.getTimeEntries();

});



EmployeeAttendanceSheetAdapter.method('getTimeEntries', function() {
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

EmployeeAttendanceSheetAdapter.method('getTimeEntriesSuccessCallBack', function(callBackData) {
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

EmployeeAttendanceSheetAdapter.method('getTimeEntriesFailCallBack', function(callBackData) {
	this.showMessage("Error", "Error occured while getting timesheet entries");
});



EmployeeAttendanceSheetAdapter.method('createPreviousAttendnacesheet', function(id) {
	object = {"id":id};

	var reqJson = JSON.stringify(object);

	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'createPreviousAttendnacesheetSuccessCallBack';
	callBackData['callBackFail'] = 'createPreviousAttendnacesheetFailCallBack';

	this.customAction('createPreviousAttendnaceSheet','modules=attendnace',reqJson,callBackData);
});

EmployeeAttendanceSheetAdapter.method('createPreviousAttendnacesheetSuccessCallBack', function(callBackData) {
	$('.tooltip').css("display","none");
	$('.tooltip').remove();
	//this.showMessage("Success", "Previous Timesheet created");
	this.get([]);
});

EmployeeAttendanceSheetAdapter.method('createPreviousAttendnacesheetFailCallBack', function(callBackData) {
	this.showMessage("Error", callBackData);
});




EmployeeAttendanceSheetAdapter.method('getActionButtonsHtml', function(id,data) {
	var html = '';
	if(this.getTableName() == "EmployeeTimeSheetAll"){
		html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.edit(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/redo.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Create previous time sheet" onclick="modJs.createPreviousAttendnacesheet(_id_);return false;"></img></div>';
	}else{
		html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.edit(_id_);return false;"></img></div>';
	}
	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
});

EmployeeAttendanceSheetAdapter.method('getCustomTableParams', function() {
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

EmployeeAttendanceSheetAdapter.method('preProcessRemoteTableData', function(data, cell, id) {
	return Date.parse(cell).toString('MMM d, yyyy (dddd)');
});



