/**
 * Author: Thilina Hasantha
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
            [ "user_levels", {"label":"User Levels","type":"select2multi","source":[["Admin","Admin"],["Manager","Manager"],["Employee","Employee"],["Other","Other"]]}]
	];
});


ModuleAdapter.method('getActionButtonsHtml', function(id,data) {
	
	
	var nonEditableFields = {};
	nonEditableFields["admin_Company Structure"] = 1;
	nonEditableFields["admin_Employees"] = 1;
	nonEditableFields["admin_Jobs"] = 1;
	nonEditableFields["admin_Leaves"] = 1;
	nonEditableFields["admin_Manage Modules"] = 1;
	nonEditableFields["admin_Projects"] = 1;
	nonEditableFields["admin_Qualifications"] = 1;
	nonEditableFields["admin_Settings"] = 1;
	nonEditableFields["admin_Users"] = 1;
	nonEditableFields["admin_Upgrade"] = 1;
	
	nonEditableFields["user_Basic Information"] = 1;
	
	if(nonEditableFields[data[3]+"_"+data[1]] == 1){
		return "";
	}
	var html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img></div>';
	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
});
