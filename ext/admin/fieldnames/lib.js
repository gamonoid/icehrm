/**
 * Author: Thilina Hasantha
 */


/**
 * FieldNameAdapter
 */

function FieldNameAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
}

FieldNameAdapter.inherits(AdapterBase);



FieldNameAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "textOrig",
	        "textMapped",
	        "display"
	];
});

FieldNameAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Original Text"},
			{ "sTitle": "Mapped Text"},
			{ "sTitle": "Display Status"}
	];
});

FieldNameAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "type", {"label":"Type","type":"placeholder","validation":""}],
	        [ "name", {"label":"Name","type":"placeholder","validation":""}],
	        [ "textOrig", {"label":"Original Text","type":"placeholder","validation":""}],
	        [ "textMapped", {"label":"Mapped Text","type":"text","validation":""}],
            [ "display", {"label":"Display Status","type":"select","source":[["Form","Form"],["Table and Form","Table and Form"],["Hidden","Hidden"]]}]
	];
});

/*
 *
 */

function CustomFieldAdapter(endPoint,tab,filter,orderBy) {
    this.initAdapter(endPoint,tab,filter,orderBy);
	this.tableType = "";
}

CustomFieldAdapter.inherits(AdapterBase);



CustomFieldAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "display",
	        "display_order"
	];
});

CustomFieldAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Display Status"},
			{ "sTitle": "Priority"}
	];
});

CustomFieldAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        //[ "type", {"label":"Type","type":"placeholder","validation":""}],
	        [ "name", {"label":"Name","type":"text","validation":""}],
            [ "display", {"label":"Display Status","type":"select","source":[["Form","Show"],["Hidden","Hidden"]]}],
            [ "field_type", {"label":"Field Type","type":"select","source":[["text","Text Field"],["textarea","Text Area"],["select","Select"],["select2","Select2"],["select2multi","Multi Select"],["fileupload","File Upload"],["date","Date"],["datetime","Date Time"],["time","Time"]]}],
            [ "field_label", {"label":"Field Label","type":"text","validation":""}],
			[ "field_validation", {"label":"Validation","type":"select","validation":"none","sort":"none","source":[["","Required"],["none","None"],["number","Number"],["numberOrEmpty","Number or Empty"],["float","Decimal"],["email","Email"],["emailOrEmpty","Email or Empty"]]}],
			[ "field_options", {"label":"Field Options","type":"datagroup",
				"form":[
					[ "label", {"label":"Label","type":"text","validation":""}],
					[ "value", {"label":"Value","type":"text","validation":"none"}]
				],
				"html":'<div id="#_id_#" class="panel panel-default"><div class="panel-body">#_delete_##_edit_#<span style="color:#999;font-size:13px;font-weight:bold">#_label_#</span>:#_value_#</div></div>',
				"validation":"none"
			}],
			[ "display_order", {"label":"Priority","type":"text","validation":"number"}],
			[ "display_section", {"label":"Display Section","type":"text","validation":""}]
	];
});

CustomFieldAdapter.method('setTableType', function(type) {
	this.tableType = type;
});

CustomFieldAdapter.method('doCustomValidation', function(params) {
	var validateName= function (str) {
		var name = /^[a-z][a-z0-9\._]+$/;
		return str != null && name.test(str);
	};

	if(!validateName(params.name)){
		return "Invalid name for custom field";
	}


	return null;
});

CustomFieldAdapter.method('forceInjectValuesBeforeSave', function(params) {


	//Build data field
	var data = [params.name], options = [], optionsData;
	data.push({});
	data[1]['label'] = params.field_label;
	data[1]['type'] = params.field_type;
	data[1]['validation'] = params.field_validation;
	if(["select","select2","select2multi"].indexOf(params.field_type) >= 0){
		optionsData = JSON.parse(params.field_options);
		for(index in optionsData){
			options.push([optionsData[index].value, optionsData[index].label]);
		}
		data[1]['source'] = options;
	}
	if(params.field_validation == null || params.field_validation == undefined){
		params.field_validation = "";
	}
	params.data = JSON.stringify(data);
	params.type = this.tableType;
	return params;
});



