/*
This file is part of iCE Hrm.

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

function EmployeeImmigrationAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeImmigrationAdapter.inherits(AdapterBase);



EmployeeImmigrationAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "document",
	        "documentname",
	        "valid_until",
	        "status"
	];
});

EmployeeImmigrationAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Document" },
			{ "sTitle": "Document Id" },
			{ "sTitle": "Valid Until"},
			{ "sTitle": "Status"}
	];
});

EmployeeImmigrationAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "document", {"label":"Document","type":"select2","remote-source":["ImmigrationDocument","id","name"]}],
	        [ "documentname", {"label":"Document Id","type":"text","validation":""}],
	        [ "valid_until", {"label":"Valid Until","type":"date","validation":"none"}],
	        [ "status", {"label":"Status","type":"select","source":[["Active","Active"],["Inactive","Inactive"],["Draft","Draft"]]}],
	        [ "details", {"label":"Details","type":"textarea","validation":"none"}],
	        [ "attachment1", {"label":"Attachment 1","type":"fileupload","validation":"none"}],
	        [ "attachment2", {"label":"Attachment 2","type":"fileupload","validation":"none"}],
	        [ "attachment3", {"label":"Attachment 3","type":"fileupload","validation":"none"}]
	];
});





function EmployeeTravelRecordAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
	this.itemName = 'Travel';
	this.itemNameLower = 'employeetravelrecord';
	this.modulePathName = 'travel';
}

EmployeeTravelRecordAdapter.inherits(ApproveModuleAdapter);



EmployeeTravelRecordAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "type",
	        "purpose",
	        "travel_from",
	        "travel_to",
	        "travel_date",
	        "return_date",
            "status"
	];
});

EmployeeTravelRecordAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Travel Type" },
			{ "sTitle": "Purpose" },
			{ "sTitle": "From"},
			{ "sTitle": "To"},
			{ "sTitle": "Travel Date"},
			{ "sTitle": "Return Date"},
			{ "sTitle": "Status"}
	];
});

EmployeeTravelRecordAdapter.method('getFormFields', function() {
	return [
        [ "id", {"label":"ID","type":"hidden"}],
        [ "type", {"label":"Travel Type","type":"select","source":[["Local","Local"],["International","International"]]}],
        [ "purpose", {"label":"Purpose of Travel","type":"textarea","validation":""}],
        [ "travel_from", {"label":"Travel From","type":"text","validation":""}],
        [ "travel_to", {"label":"Travel To","type":"text","validation":""}],
        [ "travel_date", {"label":"Travel Date","type":"datetime","validation":""}],
        [ "return_date", {"label":"Return Date","type":"datetime","validation":""}],
        [ "details", {"label":"Notes","type":"textarea","validation":"none"}],
        [ "currency", {"label":"Currency","type":"select2","allow-null":false,"remote-source":["CurrencyType","id","code"]}],
        [ "funding", {"label":"Total Funding Proposed","type":"text","validation":"float"}],
        [ "attachment1", {"label":"Itinerary / Cab Receipt","type":"fileupload","validation":"none"}],
        [ "attachment2", {"label":"Other Attachment 1","type":"fileupload","validation":"none"}],
        [ "attachment3", {"label":"Other Attachment 2","type":"fileupload","validation":"none"}]
	];
});

/*
EmployeeTravelRecordAdapter.method('getActionButtonsHtml', function(id,data) {
    var editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
    var deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
    var requestCancellationButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Cancel Travel Request" onclick="modJs.cancelTravel(_id_);return false;"></img>';

    var html = '<div style="width:80px;">_edit__delete_</div>';

    if(this.showDelete){
        if(data[7] == "Approved"){
            html = html.replace('_delete_',requestCancellationButton);
        }else{
            html = html.replace('_delete_',deleteButton);
        }

    }else{
        html = html.replace('_delete_','');
    }

    if(this.showEdit){
        html = html.replace('_edit_',editButton);
    }else{
        html = html.replace('_edit_','');
    }

    html = html.replace(/_id_/g,id);
    html = html.replace(/_BASE_/g,this.baseUrl);
    return html;
});

EmployeeTravelRecordAdapter.method('cancelTravel', function(id) {
    var that = this;
    var object = {};
    object['id'] = id;

    var reqJson = JSON.stringify(object);

    var callBackData = [];
    callBackData['callBackData'] = [];
    callBackData['callBackSuccess'] = 'cancelSuccessCallBack';
    callBackData['callBackFail'] = 'cancelFailCallBack';

    this.customAction('cancelTravel','modules=travel',reqJson,callBackData);
});

EmployeeTravelRecordAdapter.method('cancelSuccessCallBack', function(callBackData) {
    this.showMessage("Successful", "Travel request cancellation request sent");
    this.get([]);
});

EmployeeTravelRecordAdapter.method('cancelFailCallBack', function(callBackData) {
    this.showMessage("Error Occurred while cancelling Travel request", callBackData);
});
*/

/*
 EmployeeTravelRecordApproverAdapter
 */

function EmployeeTravelRecordApproverAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
	this.itemName = 'Travel';
	this.itemNameLower = 'employeetravelrecord';
	this.modulePathName = 'travel';
}

EmployeeTravelRecordApproverAdapter.inherits(EmployeeTravelRecordAdminAdapter);

EmployeeTravelRecordApproverAdapter.method('getActionButtonsHtml', function(id,data) {
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

EmployeeTravelRecordApproverAdapter.method('getStatusOptionsData', function(currentStatus) {
	var data = {};
	if(currentStatus != 'Processing'){

	}else{
		data["Approved"] = "Approved";
		data["Rejected"] = "Rejected";

	}

	return data;
});

EmployeeTravelRecordApproverAdapter.method('getStatusOptions', function(currentStatus) {
	return this.generateOptions(this.getStatusOptionsData(currentStatus));
});


/*
 SubordinateExpenseModuleAdapter
 */

function SubordinateEmployeeTravelRecordAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
	this.itemName = 'Travel';
	this.itemNameLower = 'employeetravelrecord';
	this.modulePathName = 'travel';
}

SubordinateEmployeeTravelRecordAdapter.inherits(EmployeeTravelRecordAdminAdapter);

