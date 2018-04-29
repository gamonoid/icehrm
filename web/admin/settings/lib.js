/**
 * Author: Thilina Hasantha
 */


/**
 * SettingAdapter
 */

function SettingAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
}

SettingAdapter.inherits(AdapterBase);



SettingAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "value",
	        "description"
	];
});

SettingAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Value"},
			{ "sTitle": "Details"}
	];
});

SettingAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "value", {"label":"Value","type":"text","validation":"none"}]
	];
});

SettingAdapter.method('getActionButtonsHtml', function(id,data) {
	var html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img></div>';
	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
});


SettingAdapter.method('getMetaFieldForRendering', function(fieldName) {
	if(fieldName == "value"){
		return "meta";
	}
	return "";
});

SettingAdapter.method('edit', function(id) {
    this.loadRemoteDataForSettings();
    this.uber('edit',id);
});


SettingAdapter.method('fillForm', function(object) {

	var metaField = this.getMetaFieldForRendering('value');
	var metaVal = object[metaField];
	var formFields = null;

	if(metaVal != "" && metaVal != undefined){
		var formFields = [
			[ "id", {"label":"ID","type":"hidden"}],
			JSON.parse(metaVal)
		];
	}


	this.uber('fillForm',object, null, formFields);
	$("#helptext").html(object.description);
});


SettingAdapter.method('loadRemoteDataForSettings', function () {
	var fields = [];
	var field = null;
	fields.push(["country", {"label": "Country", "type": "select2multi", "remote-source": ["Country", "id", "name"]}]);
	fields.push(["currency", {"label": "Currency", "type": "select2multi", "remote-source": ["CurrencyType","id","code+name"]}]);
	fields.push(["nationality", {"label": "Nationality", "type": "select2multi", "remote-source": ["Nationality","id","name"]}]);
	fields.push(["supportedLanguage", {"label":"Value","type":"select2","allow-null":false,"remote-source":["SupportedLanguage","name","description"]}]);

	for(index in fields){
		field = fields[index];
		if (field[1]['remote-source'] != undefined && field[1]['remote-source'] != null) {
			var key = field[1]['remote-source'][0] + "_" + field[1]['remote-source'][1] + "_" + field[1]['remote-source'][2];
			this.fieldMasterDataKeys[key] = false;
			this.sourceMapping[field[0]] = field[1]['remote-source'];

			var callBackData = {};
			callBackData['callBack'] = 'initFieldMasterDataResponse';
			callBackData['callBackData'] = [key];

			this.getFieldValues(field[1]['remote-source'], callBackData);
		}
	}

});


SettingAdapter.method('getHelpLink', function () {
	return 'http://blog.icehrm.com/docs/settings/';
});