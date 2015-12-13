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
}

AttendanceAdapter.inherits(AdapterBase);

AttendanceAdapter.method('updatePunchButton', function() {
	this.getPunch('changePunchButtonSuccessCallBack');
});

AttendanceAdapter.method('setUseServerTime', function(val) {
    this.useServerTime = val;
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
		var params = validator.getFormParameters();
		params['cdate'] = this.getClientDate(new Date()).toISOString().slice(0, 19).replace('T', ' ');
		var reqJson = JSON.stringify(params);
		var callBackData = [];
		callBackData['callBackData'] = [];
		callBackData['callBackSuccess'] = 'saveSuccessCallback';
		callBackData['callBackFail'] = 'getPunchFailCallBack';
		
		this.customAction('savePunch','modules=attendance',reqJson,callBackData);
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
