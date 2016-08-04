/**
 * Author: Thilina Hasantha
 */


/**
 * ImmigrationDocumentAdapter
 */

function ImmigrationDocumentAdapter(endPoint) {
	this.initAdapter(endPoint);
}

ImmigrationDocumentAdapter.inherits(AdapterBase);



ImmigrationDocumentAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "details",
	        "required",
	        "alert_on_missing",
	        "alert_before_expiry"
	];
});

ImmigrationDocumentAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Details"},
			{ "sTitle": "Compulsory"},
			{ "sTitle": "Alert If Not Found"},
			{ "sTitle": "Alert Before Expiry"}
	];
});

ImmigrationDocumentAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "name", {"label":"Name","type":"text","validation":""}],
	        [ "details", {"label":"Details","type":"textarea","validation":"none"}],
	        [ "required", {"label":"Compulsory","type":"select","source":[["No","No"],["Yes","Yes"]]}],
	        [ "alert_on_missing", {"label":"Alert If Not Found","type":"select","source":[["No","No"],["Yes","Yes"]]}],
	        [ "alert_before_expiry", {"label":"Alert Before Expiry","type":"select","source":[["No","No"],["Yes","Yes"]]}],
	        [ "alert_before_day_number", {"label":"Days for Expiry Alert","type":"text","validation":""}]
	];
});


/**
 * EmployeeImmigrationAdapter
 */


function EmployeeImmigrationAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeImmigrationAdapter.inherits(AdapterBase);



EmployeeImmigrationAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "employee",
	        "document",
	        "documentname",
	        "valid_until",
	        "status"
	];
});

EmployeeImmigrationAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Employee" },
			{ "sTitle": "Document" },
			{ "sTitle": "Document Id" },
			{ "sTitle": "Valid Until"},
			{ "sTitle": "Status"}
	];
});

EmployeeImmigrationAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "employee", {"label":"Employee","type":"select2","remote-source":["Employee","id","first_name+last_name"]}],
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


EmployeeImmigrationAdapter.method('getFilters', function() {
	return [
	        [ "employee", {"label":"Employee","type":"select2","remote-source":["Employee","id","first_name+last_name"]}]
	        
	];
});


/**
 * EmployeeTravelRecordAdminAdapter
 */


function EmployeeTravelRecordAdminAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
    this.itemName = 'TravelRequest';
    this.itemNameLower = 'travelrequest';
    this.modulePathName = 'travel';
}

EmployeeTravelRecordAdminAdapter.inherits(ApproveAdminAdapter);



EmployeeTravelRecordAdminAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "employee",
	        "type",
	        "purpose",
	        "travel_from",
	        "travel_to",
	        "travel_date",
            "status"
	];
});

EmployeeTravelRecordAdminAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Employee" },
			{ "sTitle": "Travel Type" },
			{ "sTitle": "Purpose" },
			{ "sTitle": "From"},
			{ "sTitle": "To"},
			{ "sTitle": "Travel Date"},
			{ "sTitle": "Status"}
	];
});

EmployeeTravelRecordAdminAdapter.method('getFormFields', function() {
    return [
        ["id", {"label": "ID", "type": "hidden"}],
        ["employee", {
            "label": "Employee",
            "type": "select2",
            "sort": "none",
            "allow-null": false,
            "remote-source": ["Employee", "id", "first_name+last_name", "getActiveSubordinateEmployees"]
        }],
        ["type", {
            "label": "Travel Type",
            "type": "select",
            "source": [["Local", "Local"], ["International", "International"]]
        }],
        ["purpose", {"label": "Purpose of Travel", "type": "textarea", "validation": ""}],
        ["travel_from", {"label": "Travel From", "type": "text", "validation": ""}],
        ["travel_to", {"label": "Travel To", "type": "text", "validation": ""}],
        ["travel_date", {"label": "Travel Date", "type": "datetime", "validation": ""}],
        ["return_date", {"label": "Return Date", "type": "datetime", "validation": ""}],
        ["details", {"label": "Notes", "type": "textarea", "validation": "none"}],
        ["currency", {"label": "Currency", "type": "select2", "allow-null":false, "remote-source": ["CurrencyType", "id", "code"]}],
        ["funding", {"label": "Total Funding Proposed", "type": "text", "validation": "float"}],
        ["attachment1", {"label": "Itinerary / Cab Receipt", "type": "fileupload", "validation": "none"}],
        ["attachment2", {"label": "Other Attachment 1", "type": "fileupload", "validation": "none"}],
        ["attachment3", {"label": "Other Attachment 2", "type": "fileupload", "validation": "none"}]
    ];
});

