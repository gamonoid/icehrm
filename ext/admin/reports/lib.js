/**
 * Author: Thilina Hasantha
 */


/**
 * ReportAdapter
 */

function ReportAdapter(endPoint) {
	this.initAdapter(endPoint);
	this._formFileds =  [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "name", {"label":"Name","type":"label","validation":""}],
	        [ "parameters", {"label":"Parameters","type":"fieldset","validation":"none"}]
	];
}

ReportAdapter.inherits(AdapterBase);

ReportAdapter.method('_initLocalFormFields', function() {
	this._formFileds =  [
 	        [ "id", {"label":"ID","type":"hidden"}],
 	        [ "name", {"label":"Name","type":"label","validation":""}],
 	        [ "parameters", {"label":"Parameters","type":"fieldset","validation":"none"}]
 	];
});

ReportAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "details",
	        "parameters"
	];
});

ReportAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Details"},
			{ "sTitle": "Parameters","bVisible":false},
	];
});

ReportAdapter.method('getFormFields', function() {
	return this._formFileds;
});

ReportAdapter.method('processFormFieldsWithObject', function(object) {
	var that = this;
	this._initLocalFormFields();
	var len = this._formFileds.length;
	var fieldIDsToDelete = [];
	var fieldsToDelete = [];
	for(var i=0;i<len;i++){
		if(this._formFileds[i][1]['type']=="fieldset"){
			var newFields = JSON.parse(object[this._formFileds[i][0]]);
			fieldsToDelete.push(this._formFileds[i][0]);
			newFields.forEach(function(entry) {
				that._formFileds.push(entry);
			});
			
		}
	}
	
	var tempArray = [];
	that._formFileds.forEach(function(entry) {
		if(jQuery.inArray(entry[0], fieldsToDelete) < 0){
			tempArray.push(entry);
		}
	});
	
	that._formFileds = tempArray;
});

ReportAdapter.method('renderForm', function(object) {
	var that = this;
	this.processFormFieldsWithObject(object);
	var cb = function(){
		that.uber('renderForm',object);
	};
	this.initFieldMasterData(cb);
	
});

ReportAdapter.method('getActionButtonsHtml', function(id,data) {
	var html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/download.png" style="cursor:pointer;" rel="tooltip" title="Download" onclick="modJs.edit(_id_);return false;"></img></div>';
	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
});

ReportAdapter.method('addSuccessCallBack', function(callBackData,serverData) {
	//var link = '<a href="'+this.getCustomActionUrl("download",{'file':serverData})+'" target="_blank">Download Report <i class="icon-download-alt"></i> </a>';
	//this.showMessage("Download Report",link);
	
	var fileName = serverData[0];
	var link;
	
	if(fileName.indexOf("https:") == 0){
		link = '<a href="'+fileName+'" target="_blank" style="font-size:14px;font-weight:bold;">Download Report <img src="_BASE_images/download.png"></img> </a>';	
	}else{
		link = '<a href="'+modJs.getCustomActionUrl("download",{'file':fileName})+'" target="_blank" style="font-size:14px;font-weight:bold;">Download Report <img src="_BASE_images/download.png"></img> </a>';
	}
	link = link.replace(/_BASE_/g,this.baseUrl);
	
	var tableHtml = link+'<br/><br/><div class="box-body table-responsive" style="overflow-x:scroll;padding: 5px;border: solid 1px #DDD;"><table id="tempReportTable" cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped"></table></div>';
	
	//Delete existing temp report table
	$("#tempReportTable").remove();
	
	//this.showMessage("Report",tableHtml);
	
	$("#Report").html(tableHtml);
	$("#Report").show();
	$("#ReportForm").hide();
	
	//Prepare headers
	var headers = [];
	for(title in serverData[1]){
		headers.push({ "sTitle":  serverData[1][title]});
	}
	
	var data = serverData[2];
	
	
	var dataTableParams = {
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			},
			"aaData": data,
			"aoColumns": headers,
			"bSort": false,
			"iDisplayLength": 15,
			"iDisplayStart": 0
		};
	
	$("#tempReportTable").dataTable( dataTableParams );
	
	$(".dataTables_paginate ul").addClass("pagination");
	$(".dataTables_length").hide();
	$(".dataTables_filter input").addClass("form-control");
	$(".dataTables_filter input").attr("placeholder","Search");
	$(".dataTables_filter label").contents().filter(function(){
	    return (this.nodeType == 3);
	}).remove();
	$('.tableActionButton').tooltip();
});


ReportAdapter.method('fillForm', function(object) {
	var fields = this.getFormFields();
	for(var i=0;i<fields.length;i++) {
		if(fields[i][1].type == 'label'){
			$("#"+this.getTableName()+'Form #'+fields[i][0]).html(object[fields[i][0]]);
		}else{
			$("#"+this.getTableName()+'Form #'+fields[i][0]).val(object[fields[i][0]]);
		}
	    
	}
});

ReportAdapter.method('getHelpLink', function () {
	return 'http://blog.icehrm.com/?page_id=118';
});
