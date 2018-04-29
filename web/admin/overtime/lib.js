/**
 * Author: Thilina Hasantha
 */


/**
 * OvertimeCategoryAdapter
 */

function OvertimeCategoryAdapter(endPoint) {
	this.initAdapter(endPoint);
}

OvertimeCategoryAdapter.inherits(AdapterBase);



OvertimeCategoryAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name"
	];
});

OvertimeCategoryAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" }
	];
});

OvertimeCategoryAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "name", {"label":"Name","type":"text","validation":""}]
	];
});




/**
 * EmployeeOvertimeAdminAdapter
 */


function EmployeeOvertimeAdminAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
    this.itemName = 'OvertimeRequest';
    this.itemNameLower = 'overtimerequest';
    this.modulePathName = 'overtime';
}

EmployeeOvertimeAdminAdapter.inherits(ApproveAdminAdapter);



EmployeeOvertimeAdminAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "employee",
	        "category",
	        "start_time",
	        "end_time",
	        "project",
            "status"
	];
});

EmployeeOvertimeAdminAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Employee" },
			{ "sTitle": "Category" },
			{ "sTitle": "Start Time" },
			{ "sTitle": "End Time"},
			{ "sTitle": "Project"},
			{ "sTitle": "Status"}
	];
});

EmployeeOvertimeAdminAdapter.method('getFormFields', function() {
    return [
        ["id", {"label": "ID", "type": "hidden"}],
        ["employee", {
            "label": "Employee",
            "type": "select2",
            "sort": "none",
            "allow-null": false,
            "remote-source": ["Employee", "id", "first_name+last_name", "getActiveSubordinateEmployees"]
        }],
		["category", {"label": "Category", "type": "select2", "allow-null":false, "remote-source": ["OvertimeCategory", "id", "name"]}],
		["start_time", {"label": "Start Time", "type": "datetime", "validation": ""}],
		["end_time", {"label": "End Time", "type": "datetime", "validation": ""}],
		["project", {"label": "Project", "type": "select2", "allow-null":true,"null=label":"none","remote-source": ["Project", "id", "name"]}],
		["notes", {"label": "Notes", "type": "textarea", "validation": "none"}]
    ];
});

