/**
 * Author: Thilina Hasantha
 */


/**
 * JobTitleAdapter
 */

function JobTitleAdapter(endPoint) {
	this.initAdapter(endPoint);
}

JobTitleAdapter.inherits(AdapterBase);



JobTitleAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "code",
	        "name"
	];
});

JobTitleAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Code" },
			{ "sTitle": "Name" }
	];
});

JobTitleAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "code", {"label":"Job Title Code","type":"text"}],
	        [ "name", {"label":"Job Title","type":"text"}],
	        [ "description", {"label":"Description","type":"textarea"}],
	        [ "specification", {"label":"Specification","type":"textarea"}]
	];
});

JobTitleAdapter.method('getHelpLink', function () {
	return 'http://blog.icehrm.com/?page_id=80';
});


/**
 * PayGradeAdapter
 */

function PayGradeAdapter(endPoint) {
	this.initAdapter(endPoint);
}

PayGradeAdapter.inherits(AdapterBase);



PayGradeAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "currency",
	        "min_salary",
	        "max_salary"
	];
});

PayGradeAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Currency"},
			{ "sTitle": "Min Salary" },
			{ "sTitle": "Max Salary"}
	];
});

PayGradeAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "name", {"label":"Pay Grade Name","type":"text"}],
	        [ "currency", {"label":"Currency","type":"select2","remote-source":["CurrencyType","code","name"]}],
	        [ "min_salary", {"label":"Min Salary","type":"text","validation":"float"}],
	        [ "max_salary", {"label":"Max Salary","type":"text","validation":"float"}]
	];
});

PayGradeAdapter.method('doCustomValidation', function(params) {
	try{
		if(parseFloat(params.min_salary)>parseFloat(params.max_salary)){
			return "Min Salary should be smaller than Max Salary";
		}
	}catch(e){
		
	}
	return null;
});



/**
 * EmploymentStatusAdapter
 */

function EmploymentStatusAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmploymentStatusAdapter.inherits(AdapterBase);



EmploymentStatusAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "description"
	];
});

EmploymentStatusAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" },
			{ "sTitle": "Name" },
			{ "sTitle": "Description"}
	];
});

EmploymentStatusAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "name", {"label":"Employment Status","type":"text"}],
	        [ "description",  {"label":"Description","type":"textarea","validation":""}]
	];
});

