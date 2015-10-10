/**
 * Author: Thilina Hasantha
 */


/**
 * DocumentAdapter
 */

function DocumentAdapter(endPoint) {
	this.initAdapter(endPoint);
}

DocumentAdapter.inherits(AdapterBase);



DocumentAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "details"
	];
});

DocumentAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Details"}
	];
});

DocumentAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "name", {"label":"Name","type":"text","validation":""}],
	        [ "details", {"label":"Details","type":"textarea","validation":"none"}]
	];
});

DocumentAdapter.method('getHelpLink', function () {
	return 'http://blog.icehrm.com/?page_id=88';
});


function EmployeeDocumentAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeDocumentAdapter.inherits(AdapterBase);



EmployeeDocumentAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "employee",
	        "document",
	        "details",
	        "date_added",
	        "status",
	        "attachment"
	];
});

EmployeeDocumentAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Employee" },
			{ "sTitle": "Document" },
			{ "sTitle": "Details" },
			{ "sTitle": "Date Added"},
			{ "sTitle": "Status"},
			{ "sTitle": "Attachment","bVisible":false}
	];
});

EmployeeDocumentAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "employee", {"label":"Employee","type":"select2","remote-source":["Employee","id","first_name+last_name"]}],
	        [ "document", {"label":"Document","type":"select2","remote-source":["Document","id","name"]}],
	        [ "date_added", {"label":"Date Added","type":"date","validation":""}],
	        [ "valid_until", {"label":"Valid Until","type":"date","validation":"none"}],
	        [ "status", {"label":"Status","type":"select","source":[["Active","Active"],["Inactive","Inactive"],["Draft","Draft"]]}],
	        [ "details", {"label":"Details","type":"textarea","validation":"none"}],
	        [ "attachment", {"label":"Attachment","type":"fileupload","validation":"none"}]
	];
});


EmployeeDocumentAdapter.method('getFilters', function() {
	return [
	        [ "employee", {"label":"Employee","type":"select2","remote-source":["Employee","id","first_name+last_name"]}]
	        
	];
});


EmployeeDocumentAdapter.method('getActionButtonsHtml', function(id,data) {	
	var html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/download.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Download Document" onclick="download(\'_attachment_\');return false;"></img><img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img></div>';
	html = html.replace(/_id_/g,id);
	html = html.replace(/_attachment_/g,data[6]);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
});
