/*
This file is part of iCE Hrm.

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */


function EmployeeOvertimeAdapter(endPoint) {
	this.initAdapter(endPoint);
	this.itemName = 'Overtime';
	this.itemNameLower = 'employeeovertime';
	this.modulePathName = 'overtime';
}

EmployeeOvertimeAdapter.inherits(ApproveModuleAdapter);



EmployeeOvertimeAdapter.method('getDataMapping', function() {
	return [
		"id",
		"category",
		"start_time",
		"end_time",
		"project",
		"status"
	];
});

EmployeeOvertimeAdapter.method('getHeaders', function() {
	return [
		{ "sTitle": "ID" ,"bVisible":false},
		{ "sTitle": "Category" },
		{ "sTitle": "Start Time" },
		{ "sTitle": "End Time"},
		{ "sTitle": "Project"},
		{ "sTitle": "Status"}
	];
});

EmployeeOvertimeAdapter.method('getFormFields', function() {
	return [
		["id", {"label": "ID", "type": "hidden"}],
		["category", {"label": "Category", "type": "select2", "allow-null":false, "remote-source": ["OvertimeCategory", "id", "name"]}],
		["start_time", {"label": "Start Time", "type": "datetime", "validation": ""}],
		["end_time", {"label": "End Time", "type": "datetime", "validation": ""}],
		["project", {"label": "Project", "type": "select2", "allow-null":true,"null=label":"none","remote-source": ["Project", "id", "name"]}],
		["notes", {"label": "Notes", "type": "textarea", "validation": "none"}]
	];
});


/*
 EmployeeOvertimeApproverAdapter
 */

function EmployeeOvertimeApproverAdapter(endPoint) {
	this.initAdapter(endPoint);
	this.itemName = 'Overtime';
	this.itemNameLower = 'employeeovertime';
	this.modulePathName = 'overtime';
}

EmployeeOvertimeApproverAdapter.inherits(EmployeeOvertimeAdminAdapter);

EmployeeOvertimeApproverAdapter.method('getActionButtonsHtml', function(id,data) {
	var statusChangeButton = '<img class="tableActionButton" src="_BASE_images/run.png" style="cursor:pointer;" rel="tooltip" title="Change Status" onclick="modJs.openStatus(_id_, \'_cstatus_\');return false;"></img>';
	var viewLogsButton = '<img class="tableActionButton" src="_BASE_images/log.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="View Logs" onclick="modJs.getLogs(_id_);return false;"></img>';

	var html = '<div style="width:80px;">_status__logs_</div>';


	html = html.replace('_logs_',viewLogsButton);


	if(data[this.getStatusFieldPosition()] == 'Processing'){
		html = html.replace('_status_',statusChangeButton);

	}else{
		html = html.replace('_status_','');
	}

	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	html = html.replace(/_cstatus_/g,data[this.getStatusFieldPosition()]);
	return html;
});

EmployeeOvertimeApproverAdapter.method('getStatusOptionsData', function(currentStatus) {
	var data = {};
	if(currentStatus != 'Processing'){

	}else{
		data["Approved"] = "Approved";
		data["Rejected"] = "Rejected";

	}

	return data;
});

EmployeeOvertimeApproverAdapter.method('getStatusOptions', function(currentStatus) {
	return this.generateOptions(this.getStatusOptionsData(currentStatus));
});


/*
 EmployeeOvertimeAdapter
 */

function SubordinateEmployeeOvertimeAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
	this.itemName = 'Overtime';
	this.itemNameLower = 'employeeovertime';
	this.modulePathName = 'overtime';
}

SubordinateEmployeeOvertimeAdapter.inherits(EmployeeOvertimeAdminAdapter);

