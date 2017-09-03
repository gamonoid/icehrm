/**
 * Author: Thilina Hasantha
 */


/**
 * PermissionAdapter
 */

function PermissionAdapter(endPoint) {
	this.initAdapter(endPoint);
}

PermissionAdapter.inherits(AdapterBase);



PermissionAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "user_level",
	        "module_id",
	        "permission",
	        "value"
	];
});

PermissionAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "User Level" },
			{ "sTitle": "Module"},
			{ "sTitle": "Permission"},
			{ "sTitle": "Value"}
	];
});

PermissionAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "user_level", {"label":"User Level","type":"placeholder","validation":"none"}],
	        [ "module_id", {"label":"Module","type":"placeholder","remote-source":["Module","id","menu+name"]}],
	        [ "permission", {"label":"Permission","type":"placeholder","validation":"none"}],
	        [ "value", {"label":"Value","type":"text","validation":"none"}]
	];
});

PermissionAdapter.method('getFilters', function() {
	return [
	        [ "module_id", {"label":"Module","type":"select2","allow-null":true,"null-label":"All Modules","remote-source":["Module","id","menu+name"]}] 
	];
});

PermissionAdapter.method('getActionButtonsHtml', function(id,data) {
	var html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img></div>';
	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
});


PermissionAdapter.method('getMetaFieldForRendering', function(fieldName) {
	if(fieldName == "value"){
		return "meta";
	}
	return "";
});


PermissionAdapter.method('fillForm', function(object) {
	this.uber('fillForm',object);
	$("#helptext").html(object.description);
});
