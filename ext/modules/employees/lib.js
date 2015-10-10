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

function EmployeeAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeAdapter.inherits(AdapterBase);

this.currentUserId = null;

EmployeeAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "employee_id",
	        "first_name",
	        "last_name",
	        "mobile_phone",
	        "department",
	        "gender",
	        "supervisor"
	];
});

EmployeeAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" },
			{ "sTitle": "Employee Number" },
			{ "sTitle": "First Name" },
			{ "sTitle": "Last Name"},
			{ "sTitle": "Mobile"},
			{ "sTitle": "Department"},
			{ "sTitle": "Gender"},
			{ "sTitle": "Supervisor"}
	];
});

EmployeeAdapter.method('getFormFields', function() {
	
	var employee_id, ssn_num, employment_status, job_title, pay_grade, joined_date, department, work_email, country;
	
	if(this.checkPermission("Edit Employee Number") == "Yes"){
		employee_id = [ "employee_id", {"label":"Employee Number","type":"text","validation":""}];
	}else{
		employee_id = [ "employee_id", {"label":"Employee Number","type":"placeholder","validation":""}];
	}
	
	if(this.checkPermission("Edit EPF/CPF Number") == "Yes"){
		ssn_num = [ "ssn_num", {"label":"EPF/CPF/SS No","type":"text","validation":"none"}];
	}else{
		ssn_num = [ "ssn_num", {"label":"EPF/CPF/SS No","type":"placeholder","validation":"none"}];
	}
	
	if(this.checkPermission("Edit Employment Status") == "Yes"){
		employment_status = [ "employment_status", {"label":"Employment Status","type":"select2","remote-source":["EmploymentStatus","id","name"]}];
	}else{
		employment_status = [ "employment_status", {"label":"Employment Status","type":"placeholder","remote-source":["EmploymentStatus","id","name"]}];
	}
	
	if(this.checkPermission("Edit Job Title") == "Yes"){
		job_title = [ "job_title", {"label":"Job Title","type":"select2","remote-source":["JobTitle","id","name"]}];
	}else{
		job_title = [ "job_title", {"label":"Job Title","type":"placeholder","remote-source":["JobTitle","id","name"]}];
	}
	
	if(this.checkPermission("Edit Pay Grade") == "Yes"){
		pay_grade = [ "pay_grade", {"label":"Pay Grade","type":"select2","allow-null":true,"remote-source":["PayGrade","id","name"]}];
	}else{
		pay_grade = [ "pay_grade", {"label":"Pay Grade","type":"placeholder","allow-null":true,"remote-source":["PayGrade","id","name"]}];
	}
	
	if(this.checkPermission("Edit Joined Date") == "Yes"){
		joined_date = [ "joined_date", {"label":"Joined Date","type":"date","validation":""}];
	}else{
		joined_date = [ "joined_date", {"label":"Joined Date","type":"placeholder","validation":""}];
	}
	
	if(this.checkPermission("Edit Department") == "Yes"){
		department = [ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"]}];
	}else{
		department = [ "department", {"label":"Department","type":"placeholder","remote-source":["CompanyStructure","id","title"]}];
	}
	
	if(this.checkPermission("Edit Work Email") == "Yes"){
		work_email = [ "work_email", {"label":"Work Email","type":"text","validation":"email"}];
	}else{
		work_email = [ "work_email", {"label":"Work Email","type":"placeholder","validation":"emailOrEmpty"}];
	}
	
	if(this.checkPermission("Edit Country") == "Yes"){
		country = [ "country", {"label":"Country","type":"select2","remote-source":["Country","code","name"]}];
	}else{
		country = [ "country", {"label":"Country","type":"placeholder","remote-source":["Country","code","name"]}];
	}
	
	return [
	        [ "id", {"label":"ID","type":"hidden","validation":""}],
	        employee_id,
	        [ "first_name", {"label":"First Name","type":"text","validation":""}],
	        [ "middle_name", {"label":"Middle Name","type":"text","validation":"none"}],
	        [ "last_name", {"label":"Last Name","type":"text","validation":""}],
	        [ "nationality", {"label":"Nationality","type":"select2","remote-source":["Nationality","id","name"]}],
	        [ "birthday", {"label":"Date of Birth","type":"date","validation":""}],
	        [ "gender", {"label":"Gender","type":"select","source":[["Male","Male"],["Female","Female"]]}],
	        [ "marital_status", {"label":"Marital Status","type":"select","source":[["Married","Married"],["Single","Single"],["Divorced","Divorced"],["Widowed","Widowed"],["Other","Other"]]}],
	        ssn_num,
	        [ "nic_num", {"label":"NIC","type":"text","validation":"none"}],
	        [ "other_id", {"label":"Other ID","type":"text","validation":"none"}],
	        [ "driving_license", {"label":"Driving License No","type":"text","validation":"none"}],
	        employment_status,
	        job_title,
	        pay_grade,
	        [ "work_station_id", {"label":"Work Station Id","type":"text","validation":"none"}],
	        [ "address1", {"label":"Address Line 1","type":"text","validation":"none"}],
	        [ "address2", {"label":"Address Line 2","type":"text","validation":"none"}],
	        [ "city", {"label":"City","type":"text","validation":"none"}],
	        country,
	        [ "province", {"label":"Province","type":"select2","allow-null":true,"remote-source":["Province","id","name"]}],
	        [ "postal_code", {"label":"Postal/Zip Code","type":"text","validation":"none"}],
	        [ "home_phone", {"label":"Home Phone","type":"text","validation":"none"}],
	        [ "mobile_phone", {"label":"Mobile Phone","type":"text","validation":"none"}],
	        [ "work_phone", {"label":"Work Phone","type":"text","validation":"none"}],
	        work_email,
	        [ "private_email", {"label":"Private Email","type":"text","validation":"emailOrEmpty"}],
	        joined_date,
	        department
	];
});

EmployeeAdapter.method('getSourceMapping' , function() {
	var k = this.sourceMapping ;
	k['supervisor'] = ["Employee","id","first_name+last_name"];
	return k;
});


EmployeeAdapter.method('get', function() {
	var that = this;
	var sourceMappingJson = JSON.stringify(this.getSourceMapping());
	
	var req = {"map":sourceMappingJson};
	var reqJson = JSON.stringify(req);
	
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'modEmployeeGetSuccessCallBack';
	callBackData['callBackFail'] = 'modEmployeeGetFailCallBack';
	
	this.customAction('get','modules=employees',reqJson,callBackData);
});

EmployeeAdapter.method('deleteProfileImage', function(empId) {
	var that = this;
	
	var req = {"id":empId};
	var reqJson = JSON.stringify(req);
	
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'modEmployeeDeleteProfileImageCallBack';
	callBackData['callBackFail'] = 'modEmployeeDeleteProfileImageCallBack';
	
	this.customAction('deleteProfileImage','modules=employees',reqJson,callBackData);
});

EmployeeAdapter.method('modEmployeeDeleteProfileImageCallBack', function(data) {
	top.location.href = top.location.href;
});

EmployeeAdapter.method('modEmployeeGetSuccessCallBack' , function(data) {
	var currentEmpId = data[1];
	var userEmpId = data[2];
	data = data[0];
	var html = this.getCustomTemplate('myDetails.html');
	
	html = html.replace(/_id_/g,data.id);
	
	$("#"+this.getTableName()).html(html);
	var fields = this.getFormFields();
	for(var i=0;i<fields.length;i++) {
		$("#"+this.getTableName()+" #" + fields[i][0]).html(data[fields[i][0]]);
	}
	
	var subordinates = "";
	for(var i=0;i<data.subordinates.length;i++){
		if(data.subordinates[i].first_name != undefined && data.subordinates[i].first_name != null){
			subordinates += data.subordinates[i].first_name+" ";
		}
		+data.subordinates[i].middle_name
		if(data.subordinates[i].middle_name != undefined && data.subordinates[i].middle_name != null && data.subordinates[i].middle_name != ""){
			subordinates += data.subordinates[i].middle_name+" ";
		}
		
		if(data.subordinates[i].last_name != undefined && data.subordinates[i].last_name != null && data.subordinates[i].last_name != ""){
			subordinates += data.subordinates[i].last_name;
		}
		subordinates += "<br/>";
	}
	
	$("#"+this.getTableName()+" #subordinates").html(subordinates);
	
	$("#"+this.getTableName()+" #nationality_Name").html(data.nationality_Name);
	$("#"+this.getTableName()+" #employment_status_Name").html(data.employment_status_Name);
	$("#"+this.getTableName()+" #job_title_Name").html(data.job_title_Name);
	$("#"+this.getTableName()+" #country_Name").html(data.country_Name);
	$("#"+this.getTableName()+" #province_Name").html(data.province_Name);
	$("#"+this.getTableName()+" #supervisor_Name").html(data.supervisor_Name);
	$("#"+this.getTableName()+" #department_Name").html(data.department_Name);
	
	$("#"+this.getTableName()+" #name").html(data.first_name + " " + data.last_name);
	this.currentUserId = data.id;
	
	$("#"+this.getTableName()+" #profile_image_"+data.id).attr('src',data.image);
	
	if(this.checkPermission("Upload/Delete Profile Image") == "No"){
		$("#employeeUploadProfileImage").remove();
		$("#employeeDeleteProfileImage").remove();
	}
	
	if(this.checkPermission("Edit Employee Details") == "No"){
		$("#employeeProfileEditInfo").remove();
	}
	
	if(currentEmpId != userEmpId){
		$("#employeeUpdatePassword").remove();
	}
	
	this.cancel();
});

EmployeeAdapter.method('modEmployeeGetFailCallBack' , function(data) {
	
});

EmployeeAdapter.method('editEmployee' , function() {
	this.edit(this.currentUserId);
});

EmployeeAdapter.method('changePassword', function() {
	$('#adminUsersModel').modal('show');
	$('#adminUsersChangePwd #newpwd').val('');
	$('#adminUsersChangePwd #conpwd').val('');
});

EmployeeAdapter.method('changePasswordConfirm', function() {
	$('#adminUsersChangePwd_error').hide();
	
	var passwordValidation =  function (str) {  
		var val = /^[a-zA-Z0-9]\w{6,}$/;  
		return str != null && val.test(str);  
	};
	
	var password = $('#adminUsersChangePwd #newpwd').val();
	
	if(!passwordValidation(password)){
		$('#adminUsersChangePwd_error').html("Password may contain only letters, numbers and should be longer than 6 characters");
		$('#adminUsersChangePwd_error').show();
		return;
	}
	
	var conPassword = $('#adminUsersChangePwd #conpwd').val();
	
	if(conPassword != password){
		$('#adminUsersChangePwd_error').html("Passwords don't match");
		$('#adminUsersChangePwd_error').show();
		return;
	}
	
	var req = {"pwd":conPassword};
	var reqJson = JSON.stringify(req);
	
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'changePasswordSuccessCallBack';
	callBackData['callBackFail'] = 'changePasswordFailCallBack';
	
	this.customAction('changePassword','modules=employees',reqJson,callBackData);
	
});

EmployeeAdapter.method('closeChangePassword', function() {
	$('#adminUsersModel').modal('hide');
});

EmployeeAdapter.method('changePasswordSuccessCallBack', function(callBackData,serverData) {
	this.closeChangePassword();
	this.showMessage("Password Change","Password changed successfully");
});

EmployeeAdapter.method('changePasswordFailCallBack', function(callBackData,serverData) {
	this.closeChangePassword();
	this.showMessage("Error",callBackData);
});




/*
 * Company Graph
 */


function CompanyGraphAdapter(endPoint) {
	this.initAdapter(endPoint);
}

CompanyGraphAdapter.inherits(AdapterBase);



CompanyGraphAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "title",
	        "address",
	        "type",
	        "country",
	        "parent"
	];
});

CompanyGraphAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID","bVisible":false },
			{ "sTitle": "Name" },
			{ "sTitle": "Address"},
			{ "sTitle": "Type"},
			{ "sTitle": "Country", "sClass": "center" },
			{ "sTitle": "Parent Structure"}
	];
});

CompanyGraphAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden","validation":""}],
	        [ "title", {"label":"Name","type":"text","validation":""}],
	        [ "description", {"label":"Details","type":"textarea","validation":""}],
	        [ "address", {"label":"Address","type":"textarea","validation":"none"}],
	        [ "type", {"label":"Type","type":"select","source":[["Company","Company"],["Head Office","Head Office"],["Regional Office","Regional Office"],["Department","Department"],["Unit","Unit"],["Sub Unit","Sub Unit"],["Other","Other"]]}],
			[ "country", {"label":"Country","type":"select","remote-source":["Country","code","name"]}],
			[ "parent", {"label":"Parent Structure","type":"select","allow-null":true,"remote-source":["CompanyStructure","id","title"]}]
	];
});

CompanyGraphAdapter.method('createTable', function(elementId) {

	var sourceData = this.sourceData;

	if(modJs['r'] == undefined || modJs['r'] == null){
		modJs['r'] = Raphael("CompanyGraph", 800, 1000);
	}else{
		return;
	}

	var r = modJs['r'];

	for(var i=0; i< sourceData.length; i++){
		sourceData[i].parent = sourceData[i]._original[6];
	}
	
	var hierarchy = new HierarchyJs();
	var nodes = hierarchy.createNodes(sourceData);
	hierarchy.createHierarchy(nodes, r);
	
	
});
