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


/**
 * ModuleAdapter
 */

function ModuleAdapter(endPoint) {
	this.initAdapter(endPoint);
}

ModuleAdapter.inherits(AdapterBase);



ModuleAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "label",
	        "menu",
	        "mod_group",
	        "mod_order",
	        "status",
	        "version",
	        "update_path"
	];
});

ModuleAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Menu" ,"bVisible":false},
			{ "sTitle": "Group"},
			{ "sTitle": "Order"},
			{ "sTitle": "Status"},
			{ "sTitle": "Version","bVisible":false},
			{ "sTitle": "Path" ,"bVisible":false}
	];
});

ModuleAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "label", {"label":"Label","type":"text","validation":""}],
	        [ "status", {"label":"Status","type":"select","source":[["Enabled","Enabled"],["Disabled","Disabled"]]}],
            [ "user_levels", {"label":"User Levels","type":"select2multi","source":[["Admin","Admin"],["Manager","Manager"],["Employee","Employee"],["Other","Other"]]}],
            [ "user_roles", {"label":"User Roles","type":"select2multi","remote-source":["UserRole","id","name"]}]
	];
});


ModuleAdapter.method('getActionButtonsHtml', function(id,data) {


	var nonEditableFields = {};
	nonEditableFields["admin_Company Structure"] = 1;
	nonEditableFields["admin_Employees"] = 1;
	nonEditableFields["admin_Job Details Setup"] = 1;
	nonEditableFields["admin_Leaves"] = 1;
	nonEditableFields["admin_Manage Modules"] = 1;
	nonEditableFields["admin_Projects"] = 1;
	nonEditableFields["admin_Qualifications"] = 1;
	nonEditableFields["admin_Settings"] = 1;
	nonEditableFields["admin_Users"] = 1;
	nonEditableFields["admin_Upgrade"] = 1;
	nonEditableFields["admin_Dashboard"] = 1;

	nonEditableFields["user_Basic Information"] = 1;
	nonEditableFields["user_Dashboard"] = 1;

	if(nonEditableFields[data[3]+"_"+data[1]] == 1){
		return "";
	}
	var html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"/></div>';
	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
});



function UsageAdapter(endPoint) {
	this.initAdapter(endPoint);
}

UsageAdapter.inherits(AdapterBase);



UsageAdapter.method('getDataMapping', function() {
	return [];
});

UsageAdapter.method('getHeaders', function() {
	return [];
});

UsageAdapter.method('getFormFields', function() {
	return [];
});


UsageAdapter.method('get', function(callBackData) {
});


UsageAdapter.method('saveUsage', function() {
	var that = this;
	var object = {};
	var arr = [];
	$('.module-check').each(function(){
		if($(this).is(":checked")) {
			arr.push($(this).val());
		}

	});

	if(arr.length == 0){
		alert("Please select one or more module groups");
		return;
	}

	object['groups'] = arr.join(",");

	var reqJson = JSON.stringify(object);
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getInitDataSuccessCallBack';
	callBackData['callBackFail'] = 'getInitDataFailCallBack';

	this.customAction('saveUsage','admin=modules',reqJson,callBackData);
});



UsageAdapter.method('saveUsageSuccessCallBack', function(data) {


});

UsageAdapter.method('saveUsageFailCallBack', function(callBackData) {

});
