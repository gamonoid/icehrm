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
            [ "display", {"label":"Display Status","type":"select","source":[["Form","Show"],["Hidden","Hidden"]]}]
	];
});


