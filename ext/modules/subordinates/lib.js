/**
 * Author: Thilina Hasantha
 */

function SubordinateAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
}

SubordinateAdapter.inherits(AdapterBase);



SubordinateAdapter.method('getDataMapping', function() {
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

SubordinateAdapter.method('getHeaders', function() {
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

SubordinateAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden","validation":""}],
	        [ "employee_id", {"label":"Employee Number","type":"text","validation":""}],
	        [ "first_name", {"label":"First Name","type":"text","validation":""}],
	        [ "middle_name", {"label":"Middle Name","type":"text","validation":"none"}],
	        [ "last_name", {"label":"Last Name","type":"text","validation":""}],
	        [ "nationality", {"label":"Nationality","type":"select2","remote-source":["Nationality","id","name"]}],
	        [ "birthday", {"label":"Date of Birth","type":"date","validation":""}],
	        [ "gender", {"label":"Gender","type":"select","source":[["Male","Male"],["Female","Female"]]}],
	        [ "marital_status", {"label":"Marital Status","type":"select","source":[["Married","Married"],["Single","Single"],["Divorced","Divorced"],["Widowed","Widowed"],["Other","Other"]]}],
	        [ "ssn_num", {"label":"SSN/NRIC","type":"text","validation":"none"}],
	        [ "nic_num", {"label":"NIC","type":"text","validation":"none"}],
	        [ "other_id", {"label":"Other ID","type":"text","validation":"none"}],
	        [ "driving_license", {"label":"Driving License No","type":"text","validation":"none"}],
	        /*[ "driving_license_exp_date", {"label":"License Exp Date","type":"date","validation":"none"}],*/
	        [ "employment_status", {"label":"Employment Status","type":"select2","remote-source":["EmploymentStatus","id","name"]}],
	        [ "job_title", {"label":"Job Title","type":"select2","remote-source":["JobTitle","id","name"]}],
	        [ "pay_grade", {"label":"Pay Grade","type":"select2","allow-null":true,"remote-source":["PayGrade","id","name"]}],
	        [ "work_station_id", {"label":"Work Station Id","type":"text","validation":"none"}],
	        [ "address1", {"label":"Address Line 1","type":"text","validation":"none"}],
	        [ "address2", {"label":"Address Line 2","type":"text","validation":"none"}],
	        [ "city", {"label":"City","type":"text","validation":"none"}],
	        [ "country", {"label":"Country","type":"select2","remote-source":["Country","code","name"]}],
	        [ "province", {"label":"Province","type":"select2","allow-null":true,"remote-source":["Province","id","name"]}],
	        [ "postal_code", {"label":"Postal/Zip Code","type":"text","validation":"none"}],
	        [ "home_phone", {"label":"Home Phone","type":"text","validation":"none"}],
	        [ "mobile_phone", {"label":"Mobile Phone","type":"text","validation":"none"}],
	        [ "work_phone", {"label":"Work Phone","type":"text","validation":"none"}],
	        [ "work_email", {"label":"Work Email","type":"text","validation":"email"}],
	        [ "private_email", {"label":"Private Email","type":"text","validation":"email"}],
	        [ "joined_date", {"label":"Joined Date","type":"date","validation":"none"}],
	        [ "confirmation_date", {"label":"Confirmation Date","type":"date","validation":"none"}],
	        [ "department", {"label":"Department","type":"select2","remote-source":["CompanyStructure","id","title"]}],
	        [ "supervisor", {"label":"Supervisor","type":"select2","allow-null":true,"remote-source":["Employee","id","first_name+last_name"]}]
	];
});

SubordinateAdapter.method('getActionButtonsHtml', function(id) {
	var html = '<div style="width:110px;"><img class="tableActionButton" src="_BASE_images/user.png" style="cursor:pointer;" rel="tooltip" title="Login as this Employee" onclick="modJs.setAdminEmployee(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img></div>';
	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
});

