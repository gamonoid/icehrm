/*
This file is part of Ice Framework.

Ice Framework is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Ice Framework is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Ice Framework. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */



/**
 * The base class for providing core functions to all module classes.
 * @class Base.js
 */
function IceHRMBase() {
	this.deleteParams = {};
	this.createRemoteTable = false;
	this.instanceId = "None";
	this.ga = [];
	this.showEdit = true;
	this.showDelete = true;
	this.showSave = true;
	this.showCancel = true;
	this.showFormOnPopup = false;
	this.filtersAlreadySet = false;
	this.currentFilterString = "";
    this.sorting = 0;
	this.settings = {};
	this.translations = {};
}

this.fieldTemplates = null;
this.templates = null;
this.customTemplates = null;
this.emailTemplates = null;
this.fieldMasterData = null;
this.fieldMasterDataKeys = null;
this.fieldMasterDataCallback = null;
this.sourceMapping = null;
this.currentId = null;
this.currentElement = null;
this.user = null;
this.currentProfile = null;
this.permissions = {};



this.baseUrl = null;

IceHRMBase.method('init' , function(appName, currentView, dataUrl, permissions) {

});

/**
 * Some browsers do not support sending JSON in get parameters. Set this to true to avoid sending JSON
 * @method setNoJSONRequests
 * @param val {Boolean}
 */
IceHRMBase.method('setNoJSONRequests' , function(val) {
	this.noJSONRequests = val;
});


IceHRMBase.method('setPermissions' , function(permissions) {
	this.permissions = permissions;
});

IceHRMBase.method('sortingStarted' , function(val) {
	this.sorting = val;
});

/**
 * Check if the current user has a permission
 * @method checkPermission
 * @param permission {String}
 * @example
 * 	this.checkPermission("Upload/Delete Profile Image")
 */
IceHRMBase.method('checkPermission' , function(permission) {
	if(this.permissions[permission] == undefined || this.permissions[permission] == null || this.permissions[permission] == "Yes"){
		return "Yes";
	}else{
		return this.permissions[permission];
	}
});

IceHRMBase.method('setBaseUrl' , function(url) {
	this.baseUrl = url;
});

IceHRMBase.method('setUser' , function(user) {
	this.user = user;
});

IceHRMBase.method('getUser' , function() {
	return this.user;
});

IceHRMBase.method('setInstanceId' , function(id) {
	this.instanceId = id;
});

IceHRMBase.method('setGoogleAnalytics' , function(ga) {
	this.ga = ga;
});

IceHRMBase.method('scrollToTop' , function() {
	$("html, body").animate({ scrollTop: 0 }, "fast");
});

IceHRMBase.method('scrollToBottom' , function() {
	$("html, body").animate({ scrollTop: $(document).height() }, "slow");
});

IceHRMBase.method('scrollToElement' , function(element) {
	if($(window).height() <= element.offset().top){
		$("html, body").animate({ scrollTop: element.offset().top }, "slow");
	}

});

IceHRMBase.method('scrollToElementBottom' , function(element) {
	if($(window).height() <= element.offset().top + element.height()){
		$("html, body").animate({ scrollTop: element.offset().top + element.height() }, "slow");
	}

});


IceHRMBase.method('setTranslations' , function(txt) {
	this.translations = txt['messages'][''];
});

IceHRMBase.method('gt' , function(key) {
	if(this.translations[key] == undefined){
		return key;
	}
	return this.translations[key][0];
});

IceHRMBase.method('addToLangTerms' , function(key) {
	var termsArr;
	var terms = localStorage.getItem("terms");
	if(terms == undefined){
		termsArr = {};
	}else{
		try{
			termsArr = JSON.parse(terms);
		}catch(e){
			termsArr = {};
		}

	}

	if(this.translations[key] == undefined){
		termsArr[key] = key;
		localStorage.setItem("terms", JSON.stringify(termsArr));
	}
});

/**
 * If this method returned false the action buttons in data table for modules will not be displayed.
 * Override this method in module lib.js to hide action buttons
 * @method showActionButtons
 * @param permission {String}
 * @example
 * 	EmployeeLeaveEntitlementAdapter.method('showActionButtons' , function() {
 *  	return false;
 *	});
 */
IceHRMBase.method('showActionButtons' , function() {
	return true;
});

IceHRMBase.method('trackEvent' , function(action, label, value) {
	try{
		if(label == undefined || label == null){
			this.ga.push(['_trackEvent', this.instanceId, action]);
		}else if(value == undefined || value == null){
			this.ga.push(['_trackEvent', this.instanceId, action, label]);
		}else{
			this.ga.push(['_trackEvent', this.instanceId, action, label, value]);
		}
	}catch(e){

	}


});


IceHRMBase.method('setCurrentProfile' , function(currentProfile) {
	this.currentProfile = currentProfile;
});

/**
 * Get the current profile
 * @method getCurrentProfile
 * @returns Profile of the current user if the profile is not switched if not switched profile
 */

IceHRMBase.method('getCurrentProfile' , function() {
	return this.currentProfile;
});

/**
 * Retrive data required to create select boxes for add new /edit forms for a given module. This is called when loading the module
 * @method initFieldMasterData
 * @param callback {Function} call this once loading completed
 * @param callback {Function} call this once all field loading completed. This indicate that the form can be displayed saftly
 * @example
 * 	ReportAdapter.method('renderForm', function(object) {
 *		var that = this;
 *		this.processFormFieldsWithObject(object);
 *		var cb = function(){
 *			that.uber('renderForm',object);
 *		};
 *		this.initFieldMasterData(cb);
 *      });
 */
IceHRMBase.method('initFieldMasterData' , function(callback, loadAllCallback, loadAllCallbackData) {
	var values;
	if(this.showAddNew == undefined || this.showAddNew == null){
		this.showAddNew = true;
	}
	this.fieldMasterData = {};
	this.fieldMasterDataKeys = {};
	this.fieldMasterDataCallback = loadAllCallback;
	this.fieldMasterDataCallbackData = loadAllCallbackData;
	this.sourceMapping = {};
	var fields = this.getFormFields();
	var filterFields = this.getFilters();

	if(filterFields != null){
		for(var j=0;j<filterFields.length;j++){
			values = this.getMetaFieldValues(filterFields[j][0],fields);
			if(values == null || (values['type']!= "select" && values['type']!= "select2" && values['type']!= "select2multi")){
				fields.push(filterFields[j]);
			}
		}
	}


	var remoteSourceFields = [];
	var remoteSourceFieldKeys = [];
	var field = null;
	var fieldSub = null;
	for(var i=0;i<fields.length;i++){
		field = fields[i];
		if(field[1]['remote-source'] != undefined && field[1]['remote-source'] != null){
			var key = field[1]['remote-source'][0]+"_"+field[1]['remote-source'][1]+"_"+field[1]['remote-source'][2];
			if(remoteSourceFieldKeys.indexOf(key) < 0){
				remoteSourceFields.push(field);
				remoteSourceFieldKeys.push(key);
			}

		}else if(field[1]['form'] != undefined && field[1]['form'] != null){
			for(var j=0;j<field[1]['form'].length;j++){
				fieldSub = field[1]['form'][j];
				if(fieldSub[1]['remote-source'] != undefined && fieldSub[1]['remote-source'] != null){
					var key = fieldSub[1]['remote-source'][0]+"_"+fieldSub[1]['remote-source'][1]+"_"+fieldSub[1]['remote-source'][2];
					if(remoteSourceFieldKeys.indexOf(key) < 0){
						remoteSourceFields.push(fieldSub);
						remoteSourceFieldKeys.push(key);
					}

				}
			}
		}
	}

	for(var i=0;i<remoteSourceFields.length;i++){
		var field = remoteSourceFields[i];
		if(field[1]['remote-source'] != undefined && field[1]['remote-source'] != null){
			var key = field[1]['remote-source'][0]+"_"+field[1]['remote-source'][1]+"_"+field[1]['remote-source'][2];
			this.fieldMasterDataKeys[key] = false;
			this.sourceMapping[field[0]] = field[1]['remote-source'];

			var callBackData = {};
			callBackData['callBack'] = 'initFieldMasterDataResponse';
			callBackData['callBackData'] = [key];
			if(callback != null && callback != undefined){
				callBackData['callBackSuccess'] = callback;
			}
			this.getFieldValues(field[1]['remote-source'],callBackData);
		}
	}
});

/**
 * Pass true to this method after creating module JS object to open new/edit entry form for the module on a popup.
 * @method setShowFormOnPopup
 * @param val {Boolean}
 * @example
 * 	modJs.subModJsList['tabCandidateApplication'] = new CandidateApplicationAdapter('Application','CandidateApplication',{"candidate":data.id});
 *	modJs.subModJsList['tabCandidateApplication'].setShowFormOnPopup(true);
 */

IceHRMBase.method('setShowFormOnPopup' , function(val) {
	this.showFormOnPopup = val;
});

/**
 * Set this to true to if you need the datatable to load data page by page instead of loading all data at once.
 * @method setRemoteTable
 * @param val {Boolean}
 * @example
 * 	modJs.subModJsList['tabCandidateApplication'] = new CandidateApplicationAdapter('Application','CandidateApplication',{"candidate":data.id});
 *	modJs.subModJsList['tabCandidateApplication'].setRemoteTable(true);
 */

IceHRMBase.method('setRemoteTable' , function(val) {
	this.createRemoteTable = val;
});

IceHRMBase.method('setSettings' , function(val) {
	this.settings = val;
});

IceHRMBase.method('getRemoteTable' , function() {
	return this.createRemoteTable;
});

IceHRMBase.method('isAllLoaded' , function(fieldMasterDataKeys) {

	for(key in fieldMasterDataKeys){
		if(fieldMasterDataKeys[key] == false){
			return false;
		}
	}
	return true;
});

IceHRMBase.method('initFieldMasterDataResponse' , function(key,data, callback, loadAllCallbackData) {
	this.fieldMasterData[key] = data;
	this.fieldMasterDataKeys[key] = true;

	if(callback != undefined && callback != null){
		callback();
	}

	if(this.fieldMasterDataCallback != null && this.fieldMasterDataCallback != undefined && this.isAllLoaded(this.fieldMasterDataKeys)){
        if(this.fieldMasterDataCallbackData == null || this.fieldMasterDataCallbackData == undefined){
            this.fieldMasterDataCallback();
        }else{
            this.fieldMasterDataCallback(this.fieldMasterDataCallbackData);
        }

	}

});

IceHRMBase.method('getMetaFieldValues' , function(key, fields) {
	for(var i=0;i<fields.length;i++){
		if(key == fields[i][0]){
			return fields[i][1];
		}
	}
	return null;
});

IceHRMBase.method('getThemeColors' , function() {


	var colors = ["red","yellow","aqua","blue",
		"light-blue","green","navy","teal","olive","orange",
		"fuchsia","purple"];

	return colors;

});

IceHRMBase.method('getColorByRandomString' , function(string) {
	var colors = this.getThemeColors();
	var k = string.charCodeAt(0);
	return colors[k % colors.length];
});

IceHRMBase.method('getColorByFileType' , function(type) {
	type = type.toLowerCase();

	var colorMap = {};
	colorMap['pdf'] = 'red';
	colorMap['csv'] = 'yellow';
	colorMap['xls'] = 'green';
	colorMap['xlsx'] = 'green';
	colorMap['doc'] = 'light-blue';
	colorMap['docx'] = 'light-blue';
	colorMap['docx'] = 'blue';
	colorMap['ppt'] = 'orange';
	colorMap['pptx'] = 'orange';
	colorMap['jpg'] = 'teal';
	colorMap['jpeg'] = 'teal';
	colorMap['gif'] = 'green';
	colorMap['png'] = 'yellow';
	colorMap['bmp'] = 'fuchsia';


	if(colorMap[type] != undefined || colorMap[type] != null){
		return colorMap[type];
	}else{
		return getColorByRandomString(type);
	}

});

IceHRMBase.method('getIconByFileType' , function(type) {
	type = type.toLowerCase();

	var iconMap = {};
	iconMap['pdf'] = 'fa fa-file-pdf-o';
	iconMap['csv'] = 'fa fa fa-file-code-o';
	iconMap['xls'] = 'fa fa-file-excel-o';
	iconMap['xlsx'] = 'fa fa-file-excel-o';
	iconMap['doc'] = 'fa fa-file-word-o';
	iconMap['docx'] = 'fa fa-file-word-o';
	iconMap['ppt'] = 'fa fa-file-powerpoint-o';
	iconMap['pptx'] = 'fa fa-file-powerpoint-o';
	iconMap['jpg'] = 'fa fa-file-image-o';
	iconMap['jpeg'] = 'fa fa-file-image-o';
	iconMap['gif'] = 'fa fa-file-image-o';
	iconMap['png'] = 'fa fa-file-image-o';
	iconMap['bmp'] = 'fa fa-file-image-o';
	iconMap['txt'] = 'fa fa-file-text-o';
	iconMap['rtf'] = 'fa fa-file-text-o';


	if(iconMap[type] != undefined || iconMap[type] != null){
		return iconMap[type];
	}else{
		return 'fa fa-file-o';
	}

});

IceHRMBase.method('getSourceMapping' , function() {
	return this.sourceMapping ;
});

IceHRMBase.method('setTesting' , function(testing) {
	this.testing = testing;
});

IceHRMBase.method('consoleLog' , function(message) {
	if(this.testing) {
		console.log(message);
	}
});

IceHRMBase.method('setClientMessages', function(msgList) {
	this.msgList = msgList;
});

IceHRMBase.method('setTemplates', function(templates) {
	this.templates = templates;
});


IceHRMBase.method('getWSProperty', function(array, key) {
	if(array.hasOwnProperty(key)) {
		return array[key];
	}
	return null;
});


IceHRMBase.method('getClientMessage', function(key) {
	return this.getWSProperty(this.msgList,key);
});



IceHRMBase.method('getTemplate', function(key) {
	return this.getWSProperty(this.templates, key);
});

IceHRMBase.method('setGoogleAnalytics', function (gaq) {
	this.gaq = gaq;
});


IceHRMBase.method('showView', function(view) {
	if(this.currentView != null) {
		this.previousView = this.currentView;
		$("#" + this.currentView).hide();
	}
	$('#' + view).show();
	this.currentView = view;
	this.moveToTop();
});

IceHRMBase.method('showPreviousView', function() {
	this.showView(this.previousView);
});


IceHRMBase.method('moveToTop', function () {

});


IceHRMBase.method('callFunction', function (callback, cbParams,thisParam) {
	if($.isFunction(callback)) {
		try{
			if(thisParam == undefined || thisParam == null){
				callback.apply(document, cbParams);
			}else{
				callback.apply(thisParam, cbParams);
			}

		} catch(e) {
		}
	} else {
		f = this[callback];
		if($.isFunction(f)) {
			try{
				f.apply(this, cbParams);
			} catch(e) {
			}
		}
	}
	return ;
});

IceHRMBase.method('getTableTopButtonHtml', function() {
	var html = "";
	if(this.getShowAddNew()){
		html = '<button onclick="modJs.renderForm();return false;" class="btn btn-small btn-primary">'+this.gt(this.getAddNewLabel())+' <i class="fa fa-plus"></i></button>';
	}

	if(this.getFilters() != null){
		if(html != ""){
			html += "&nbsp;&nbsp;";
		}
		html+='<button onclick="modJs.showFilters();return false;" class="btn btn-small btn-primary">'+this.gt('Filter')+' <i class="fa fa-filter"></i></button>';
		html += "&nbsp;&nbsp;";
		if(this.filtersAlreadySet){
			html+='<button id="__id___resetFilters" onclick="modJs.resetFilters();return false;" class="btn btn-small btn-default">__filterString__ <i class="fa fa-times"></i></button>';
		}else{
			html+='<button id="__id___resetFilters" onclick="modJs.resetFilters();return false;" class="btn btn-small btn-default" style="display:none;">__filterString__ <i class="fa fa-times"></i></button>';
		}

	}

	html = html.replace(/__id__/g, this.getTableName());

	if(this.currentFilterString != "" && this.currentFilterString != null){
		html = html.replace(/__filterString__/g, this.currentFilterString);
	}else{
		html = html.replace(/__filterString__/g, 'Reset Filters');
	}

	if(html != ""){
		html = '<div class="row"><div class="col-xs-12">'+html+'</div></div>';
	}

	return html;
});


IceHRMBase.method('getActionButtonHeader', function() {
    return { "sTitle": "", "sClass": "center" };
});

IceHRMBase.method('getTableHTMLTemplate', function() {
    return '<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="grid"></table></div>';
});

IceHRMBase.method('isSortable', function() {
    return true;
});

/**
 * Create the data table on provided element id
 * @method createTable
 * @param val {Boolean}
 */

IceHRMBase.method('createTable', function(elementId) {


    var that = this;

	if(this.getRemoteTable()){
		this.createTableServer(elementId);
		return;
	}


	var headers = this.getHeaders();

	//add translations
	for(index in headers){
		headers[index].sTitle = this.gt(headers[index].sTitle);
	}

	var data = this.getTableData();

	if(this.showActionButtons()){
        headers.push(this.getActionButtonHeader());
	}


	if(this.showActionButtons()){
		for(var i=0;i<data.length;i++){
			data[i].push(this.getActionButtonsHtml(data[i][0],data[i]));
		}
	}

	var html = "";
	html = this.getTableTopButtonHtml() + this.getTableHTMLTemplate();
	/*
	if(this.getShowAddNew()){
		html = this.getTableTopButtonHtml()+'<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="grid"></table></div>';
	}else{
		html = '<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="grid"></table></div>';
	}
	*/
	//Find current page
	var activePage = $('#'+elementId +" .dataTables_paginate .active a").html();
	var start = 0;
	if(activePage != undefined && activePage != null){
		start = parseInt(activePage, 10)*15 - 15;
	}

	$('#'+elementId).html(html);

	var dataTableParams = {
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			},
			"aaData": data,
			"aoColumns": headers,
			"bSort": that.isSortable(),
			"iDisplayLength": 15,
			"iDisplayStart": start
		};


	var customTableParams = this.getCustomTableParams();

	$.extend(dataTableParams, customTableParams);

	$('#'+elementId+' #grid').dataTable( dataTableParams );

	$(".dataTables_paginate ul").addClass("pagination");
	$(".dataTables_length").hide();
	$(".dataTables_filter input").addClass("form-control");
	$(".dataTables_filter input").attr("placeholder","Search");
	$(".dataTables_filter label").contents().filter(function(){
	    return (this.nodeType == 3);
	}).remove();
	$('.tableActionButton').tooltip();
});

/**
 * Create a data table on provided element id which loads data page by page
 * @method createTableServer
 * @param val {Boolean}
 */

IceHRMBase.method('createTableServer', function(elementId) {
	var that = this;
	var headers = this.getHeaders();

	headers.push({ "sTitle": "", "sClass": "center" });

	//add translations
	for(index in headers){
		headers[index].sTitle = this.gt(headers[index].sTitle);
	}

	var html = "";
	html = this.getTableTopButtonHtml() + this.getTableHTMLTemplate();
	/*
	if(this.getShowAddNew()){
		html = this.getTableTopButtonHtml()+'<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="grid"></table></div>';
	}else{
		html = '<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="grid"></table></div>';
	}
	*/

	//Find current page
	var activePage = $('#'+elementId +" .dataTables_paginate .active a").html();
	var start = 0;
	if(activePage != undefined && activePage != null){
		start = parseInt(activePage, 10)*15 - 15;
	}


	$('#'+elementId).html(html);

	var dataTableParams = {
			"oLanguage": {
				"sLengthMenu": "_MENU_ records per page"
			},
			"bProcessing": true,
		    "bServerSide": true,
		    "sAjaxSource": that.getDataUrl(that.getDataMapping()),
			"aoColumns": headers,
			"bSort": that.isSortable(),
			"parent":that,
			"iDisplayLength": 15,
			"iDisplayStart": start
		};

	if(this.showActionButtons()){
		dataTableParams["aoColumnDefs"] = [
		                        			{
		                         				"fnRender": that.getActionButtons,
		                         				"aTargets": [that.getDataMapping().length]
		                         			}
		                    			];
	}

	var customTableParams = this.getCustomTableParams();

	$.extend(dataTableParams, customTableParams);

	$('#'+elementId+' #grid').dataTable( dataTableParams );

	$(".dataTables_paginate ul").addClass("pagination");
	$(".dataTables_length").hide();
	$(".dataTables_filter input").addClass("form-control");
	$(".dataTables_filter input").attr("placeholder","Search");
	$(".dataTables_filter label").contents().filter(function(){
	    return (this.nodeType == 3);
	}).remove();

	$('.tableActionButton').tooltip();

});

/**
 * This should be overridden in module lib.js classes to return module headers which are used to create the data table.
 * @method getHeaders
 * @example
	SettingAdapter.method('getHeaders', function() {
  		return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Value"},
			{ "sTitle": "Details"}
		];
		});
 */
IceHRMBase.method('getHeaders', function() {

});


/**
 * This should be overridden in module lib.js classes to return module field values which are used to create the data table.
 * @method getDataMapping
 * @example
	SettingAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "value",
	        "description"
	];
	});
 */

IceHRMBase.method('getDataMapping', function() {

});

/**
 * This should be overridden in module lib.js classes to return module from fields which are used to create the add/edit form and also used for initializing select box values in form.
 * @method getFormFields
 * @example
	SettingAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "value", {"label":"Value","type":"text","validation":"none"}]
	];
	});
 */
IceHRMBase.method('getFormFields', function() {

});

IceHRMBase.method('getTableData', function() {

});

/**
 * This can be overridden in module lib.js classes inorder to show a filter form
 * @method getFilters
 * @example
	EmployeeAdapter.method('getFilters', function() {
		return [
		        [ "job_title", {"label":"Job Title","type":"select2","allow-null":true,"null-label":"All Job Titles","remote-source":["JobTitle","id","name"]}],
		        [ "department", {"label":"Department","type":"select2","allow-null":true,"null-label":"All Departments","remote-source":["CompanyStructure","id","title"]}],
		        [ "supervisor", {"label":"Supervisor","type":"select2","allow-null":true,"null-label":"Anyone","remote-source":["Employee","id","first_name+last_name"]}]
		];
	});
 */
IceHRMBase.method('getFilters', function() {
	return null;
});

/**
 * Show the edit form for an item
 * @method edit
 * @param id {int} id of the item to edit
 */
IceHRMBase.method('edit', function(id) {
	this.currentId = id;
	this.getElement(id,[]);
});

IceHRMBase.method('copyRow', function(id) {
	this.getElement(id,[],true);
});

IceHRMBase.method('renderModel', function(id,header,body) {
	$('#'+id+'ModelBody').html("");

	if(body == undefined || body == null){
		body = "";
	}

	$('#'+id+'ModelLabel').html(header);
	$('#'+id+'ModelBody').html(body);
});


IceHRMBase.method('renderYesNoModel', function(header,body,yesBtnName,noBtnName,callback, callbackParams) {
    var that = this;
    var modelId = "#yesnoModel";

    if(body == undefined || body == null){
        body = "";
    }

    $(modelId+'Label').html(header);
    $(modelId+'Body').html(body);
    if(yesBtnName != null){
        $(modelId+'YesBtn').html(yesBtnName);
    }
    if(noBtnName != null){
        $(modelId+'NoBtn').html(noBtnName);
    }

    $(modelId+'YesBtn').off().on('click',function(){
        if(callback != undefined && callback != null){
            callback.apply(that,callbackParams);
            that.cancelYesno();
        }
    });

    $(modelId).modal({
        backdrop: 'static'
    });


});

IceHRMBase.method('renderModelFromDom', function(id,header,element) {
	$('#'+id+'ModelBody').html("");

	if(element == undefined || element == null){
		element = $("<div></div>");
	}

	$('#'+id+'ModelLabel').html(header);
	$('#'+id+'ModelBody').html("");
	$('#'+id+'ModelBody').append(element);
});

/**
 * Delete an item
 * @method deleteRow
 * @param id {int} id of the item to edit
 */

IceHRMBase.method('deleteRow', function(id) {
	this.deleteParams['id'] = id;
	this.renderModel('delete',"Confirm Deletion","Are you sure you want to delete this item ?");
	$('#deleteModel').modal('show');

});

/**
 * Show a popup with message
 * @method showMessage
 * @param title {String} title of the message box
 * @param message {String} message
 * @param closeCallback {Function} this will be called once the dialog is closed (optional)
 * @param closeCallback {Function} data to pass to close callback (optional)
 * @param isPlain {Boolean} if true buttons are not shown (optional / default = true)
 * @example
 * 	this.showMessage("Error Occured while Applying Leave", callBackData);
 */
IceHRMBase.method('showMessage', function(title,message,closeCallback,closeCallbackData, isPlain) {
	var that = this;
	var modelId = "";
	if(isPlain){
		modelId = "#plainMessageModel";
		this.renderModel('plainMessage',title,message);
	}else{
		modelId = "#messageModel";
		this.renderModel('message',title,message);
	}

	$(modelId).unbind('hide');
	if(closeCallback != null && closeCallback != undefined){
		$(modelId).on('hidden.bs.modal',function(){
			closeCallback.apply(that,closeCallbackData);
			$(modelId).unbind('hidden.bs.modal');
		});
	}
	$(modelId).modal({
		  backdrop: 'static'
	});
});

IceHRMBase.method('showDomElement', function(title,element,closeCallback,closeCallbackData, isPlain) {
	var that = this;
	var modelId = "";
	if(isPlain){
		modelId = "#dataMessageModel";
		this.renderModelFromDom('dataMessage',title,element);
	}else{
		modelId = "#messageModel";
		this.renderModelFromDom('message',title,element);
	}

	$(modelId).unbind('hide');
	if(closeCallback != null && closeCallback != undefined){
		$(modelId).on('hidden.bs.modal',function(){
			closeCallback.apply(that,closeCallbackData);
			$(modelId).unbind('hidden.bs.modal');
		});
	}
	$(modelId).modal({
		  backdrop: 'static'
	});
});

IceHRMBase.method('confirmDelete', function() {
	if(this.deleteParams['id'] != undefined || this.deleteParams['id'] != null){
		this.deleteObj(this.deleteParams['id'],[]);
	}
	$('#deleteModel').modal('hide');
});

IceHRMBase.method('cancelDelete', function() {
	$('#deleteModel').modal('hide');
	this.deleteParams['id'] = null;
});

IceHRMBase.method('closeMessage', function() {
	$('#messageModel').modal('hide');
});

IceHRMBase.method('cancelYesno', function() {
	$('#yesnoModel').modal('hide');
});

IceHRMBase.method('closePlainMessage', function() {
	$('#plainMessageModel').modal('hide');
	$('#dataMessageModel').modal('hide');
});

IceHRMBase.method('closeDataMessage', function() {
    $('#dataMessageModel').modal('hide');
});


/**
 * Create or edit an element
 * @method save
 * @param getFunctionCallBackData {Array} once a success is returned call get() function for this module with these parameters
 * @param successCallback {Function} this will get called after success response
 */

IceHRMBase.method('save', function(callGetFunction, successCallback) {
	var validator = new FormValidation(this.getTableName()+"_submit",true,{'ShowPopup':false,"LabelErrorClass":"error"});
	if(validator.checkValues()){
		var params = validator.getFormParameters();
		params = this.forceInjectValuesBeforeSave(params);
		var msg = this.doCustomValidation(params);
		if(msg == null){
			var id = $('#'+this.getTableName()+"_submit #id").val();
			if(id != null && id != undefined && id != ""){
				$(params).attr('id',id);
			}
			params = this.makeEmptyDateFieldsNull(params);
			this.add(params,[],callGetFunction, successCallback);
		}else{
			$("#"+this.getTableName()+'Form .label').html(msg);
			$("#"+this.getTableName()+'Form .label').show();
		}

	}
});


IceHRMBase.method('makeEmptyDateFieldsNull', function(params) {
	var fields = this.getFormFields();
	fields.forEach(function(field) {
		if((field[1].type == 'date' || field[1].type == 'datetime')
			&& (params[field[0]] === '' || params[field[0]] === '0000-00-00' || params[field[0]] === '0000-00-00 00:00:00')){
			delete params[field[0]];
		}
	});
	return params;
});

/**
 * Override this method to inject attitional parameters or modify existing parameters retrived from add/edit form before sending to the server
 * @method forceInjectValuesBeforeSave
 * @param params {Array} keys and values in form
 * @returns {Array} modified parameters
 */
IceHRMBase.method('forceInjectValuesBeforeSave', function(params) {
	return params;
});

/**
 * Override this method to do custom validations at client side
 * @method doCustomValidation
 * @param params {Array} keys and values in form
 * @returns {Null or String} return null if validation success, returns error message if unsuccessful
 * @example
 	EmployeeLeaveAdapter.method('doCustomValidation', function(params) {
		try{
			if(params['date_start'] != params['date_end']){
				var ds = new Date(params['date_start']);
				var de = new Date(params['date_end']);
				if(de < ds){
					return "Start date should be earlier than end date of the leave period";
				}
			}
		}catch(e){

		}
	return null;
});
 */
IceHRMBase.method('doCustomValidation', function(params) {
	return null;
});

IceHRMBase.method('filterQuery', function() {

	var validator = new FormValidation(this.getTableName()+"_filter",true,{'ShowPopup':false,"LabelErrorClass":"error"});
	if(validator.checkValues()){
		var params = validator.getFormParameters();
		if(this.doCustomFilterValidation(params)){

			//remove null params
			for (var prop in params) {
				if(params.hasOwnProperty(prop)){
					if(params[prop] == "NULL"){
						delete(params[prop]);
					}
				}
			}

			this.setFilter(params);
			this.filtersAlreadySet = true;
			$("#"+this.getTableName()+"_resetFilters").show();
			this.currentFilterString = this.getFilterString(params);

			this.get([]);
			this.closePlainMessage();
		}

	}
});


IceHRMBase.method('getFilterString', function(filters) {

	var str = '';
	var rmf, source, values, select2MVal, value, valueOrig;

	var filterFields = this.getFilters();


	if(values == null){
		values = [];
	}

	for (var prop in filters) {
		if(filters.hasOwnProperty(prop)){
			values = this.getMetaFieldValues(prop,filterFields);
			value = "";
			valueOrig = null;

			if((values['type'] == 'select' || values['type'] == 'select2')){

				if(values['remote-source']!= undefined && values['remote-source']!= null){
					rmf = values['remote-source'];
					if(filters[prop] == "NULL"){
						if(values['null-label'] != undefined && values['null-label'] != null){
							value = values['null-label'];
						}else{
							value = "Not Selected";
						}
					}else{
						value = this.fieldMasterData[rmf[0]+"_"+rmf[1]+"_"+rmf[2]][filters[prop]];
						valueOrig = value;
					}


				}else{
					source = values['source'][0];
					if(filters[prop] == "NULL"){
						if(values['null-label'] != undefined && values['null-label'] != null){
							value = values['null-label'];
						}else{
							value = "Not Selected";
						}
					}else{
						for(var i=0; i<source.length; i++){
							if(filters[prop] == values['source'][i][0]){
								value = values['source'][i][1];
								valueOrig = value;
								break;
							}
						}
					}


				}

			}else if (values['type'] == 'select2multi'){
				select2MVal = [];
				try{
					select2MVal = JSON.parse(filters[prop]);

				}catch(e){

				}

				value = select2MVal.join(",");
				if(value != ""){
					valueOrig = value;
				}

			}else{
				value = filters[prop];
				if(value != ""){
					valueOrig = value;
				}
			}

			if(valueOrig != null){
				if(str != ''){
					str += " | ";
				}

				str += values['label']+" = "+value;
			}
		}
	}

	return str;
});

/**
 * Override this method to do custom validations at client side for values selected in filters
 * @method doCustomFilterValidation
 * @param params {Array} keys and values in form
 * @returns {Null or String} return null if validation success, returns error message if unsuccessful
 */
IceHRMBase.method('doCustomFilterValidation', function(params) {
	return true;
});


/**
 * Reset selected filters
 * @method resetFilters
 */

IceHRMBase.method('resetFilters', function() {
	this.filter = this.origFilter;
	this.filtersAlreadySet = false;
	$("#"+this.getTableName()+"_resetFilters").hide();
	this.currentFilterString = "";
	this.get([]);
});




IceHRMBase.method('showFilters', function(object) {
	var formHtml = this.templates['filterTemplate'];
	var html = "";
	var fields = this.getFilters();

	for(var i=0;i<fields.length;i++){
		var metaField = this.getMetaFieldForRendering(fields[i][0]);
		if(metaField == "" || metaField == undefined){
			html += this.renderFormField(fields[i]);
		}else{
			var metaVal = object[metaField];
			if(metaVal != '' && metaVal != null && metaVal != undefined && metaVal.trim() != ''){
				html += this.renderFormField(JSON.parse(metaVal));
			}else{
				html += this.renderFormField(fields[i]);
			}
		}

	}
	formHtml = formHtml.replace(/_id_/g,this.getTableName()+"_filter");
	formHtml = formHtml.replace(/_fields_/g,html);

	var $tempDomObj;
	var randomFormId = this.generateRandom(14);
	$tempDomObj = $('<div class="reviewBlock popupForm" data-content="Form"></div>');
	$tempDomObj.attr('id',randomFormId);

	$tempDomObj.html(formHtml);


	$tempDomObj.find('.datefield').datepicker({'viewMode':2});
	$tempDomObj.find('.timefield').datetimepicker({
      language: 'en',
      pickDate: false
    });
	$tempDomObj.find('.datetimefield').datetimepicker({
      language: 'en'
    });

	$tempDomObj.find('.colorpick').colorpicker();
	tinymce.init({
		selector: '#'+$tempDomObj.attr('id')+' .tinymce',
		height: "400"
	});

	$tempDomObj.find('.simplemde').each(function() {
		var simplemde = new SimpleMDE({ element: $(this)[0] });
		$(this).data('simplemde', simplemde);
		//simplemde.value($(this).val());
	});

	//$tempDomObj.find('.select2Field').select2();
	$tempDomObj.find('.select2Field').each(function() {
		$(this).select2().select2('val', $(this).find("option:eq(0)").val());
	});

	$tempDomObj.find('.select2Multi').each(function() {
		$(this).select2().on("change",function(e){
			var parentRow = $(this).parents(".row");
			var height = parentRow.find(".select2-choices").height();
			parentRow.height(parseInt(height));
		});
	});

    /*
    $tempDomObj.find('.signatureField').each(function() {
        $(this).data('signaturePad',new SignaturePad($(this)));
    });
    */

	//var tHtml = $tempDomObj.wrap('<div>').parent().html();
	this.showDomElement("Edit",$tempDomObj,null,null,true);
	$(".filterBtn").off();
	$(".filterBtn").on('click',function(e) {
		e.preventDefault();
		e.stopPropagation();
		try{
			modJs.filterQuery();

		}catch(e){
		};
		return false;
	});

	if(this.filter != undefined && this.filter != null){
		this.fillForm(this.filter,"#"+this.getTableName()+"_filter", this.getFilters());
	}

});


/**
 * Override this method in your module class to make changes to data fo the form before showing the form
 * @method preRenderForm
 * @param object {Array} keys value list for populating form
 */

IceHRMBase.method('preRenderForm', function(object) {

});

/**
 * Create the form
 * @method renderForm
 * @param object {Array} keys value list for populating form
 */

IceHRMBase.method('renderForm', function(object) {

	var that = this;
    var signatureIds = [];
	if(object == null || object == undefined){
		this.currentId = null;
	}

	this.preRenderForm(object);

	var formHtml = this.templates['formTemplate'];
	var html = "";
	var fields = this.getFormFields();

	for(var i=0;i<fields.length;i++){
		var metaField = this.getMetaFieldForRendering(fields[i][0]);
		if(metaField == "" || metaField == undefined){
			html += this.renderFormField(fields[i]);
		}else{
			var metaVal = object[metaField];
			if(metaVal != '' && metaVal != null && metaVal != undefined && metaVal.trim() != ''){
				html += this.renderFormField(JSON.parse(metaVal));
			}else{
				html += this.renderFormField(fields[i]);
			}
		}

	}
	formHtml = formHtml.replace(/_id_/g,this.getTableName()+"_submit");
	formHtml = formHtml.replace(/_fields_/g,html);


	var $tempDomObj;
	var randomFormId = this.generateRandom(14);
	if(!this.showFormOnPopup){
		$tempDomObj = $("#"+this.getTableName()+'Form');
	}else{
		$tempDomObj = $('<div class="reviewBlock popupForm" data-content="Form"></div>');
		$tempDomObj.attr('id',randomFormId);

	}

	$tempDomObj.html(formHtml);


	$tempDomObj.find('.datefield').datepicker({'viewMode':2});
	$tempDomObj.find('.timefield').datetimepicker({
      language: 'en',
      pickDate: false
    });
	$tempDomObj.find('.datetimefield').datetimepicker({
      language: 'en'
    });

	$tempDomObj.find('.colorpick').colorpicker();

	tinymce.init({
		selector: '#'+$tempDomObj.attr('id')+' .tinymce',
		height: "400"
	});

	$tempDomObj.find('.simplemde').each(function() {
		var simplemde = new SimpleMDE({ element: $(this)[0] });
		$(this).data('simplemde', simplemde);
		//simplemde.value($(this).val());
	});

	//$tempDomObj.find('.select2Field').select2();
	$tempDomObj.find('.select2Field').each(function() {
		$(this).select2().select2('val', $(this).find("option:eq(0)").val());

	});

	$tempDomObj.find('.select2Multi').each(function() {
		$(this).select2().on("change",function(e){
			var parentRow = $(this).parents(".row");
			var height = parentRow.find(".select2-choices").height();
			parentRow.height(parseInt(height));
		});

	});


    $tempDomObj.find('.signatureField').each(function() {
        //$(this).data('signaturePad',new SignaturePad($(this)));
        signatureIds.push($(this).attr('id'));
    });

	for(var i=0;i<fields.length;i++){
		if(fields[i][1].type == "datagroup"){
			$tempDomObj.find("#"+fields[i][0]).data('field',fields[i]);
		}
	}

	if(this.showSave == false){
		$tempDomObj.find('.saveBtn').remove();
	}else{
		$tempDomObj.find('.saveBtn').off();
		$tempDomObj.find('.saveBtn').data("modJs",this);
		$tempDomObj.find('.saveBtn').on( "click", function() {
              if($(this ).data('modJs').saveSuccessItemCallback != null && $(this ).data('modJs').saveSuccessItemCallback!= undefined){
                  $(this ).data('modJs').save($(this ).data('modJs').retriveItemsAfterSave(), $(this ).data('modJs').saveSuccessItemCallback);
              }else{
                  $(this ).data('modJs').save();
              }

			  return false;
		});

	}

	if(this.showCancel== false){
		$tempDomObj.find('.cancelBtn').remove();
	}else{
		$tempDomObj.find('.cancelBtn').off();
		$tempDomObj.find('.cancelBtn').data("modJs",this);
		$tempDomObj.find('.cancelBtn').on( "click", function() {
			  $(this ).data('modJs').cancel();
			  return false;
		});

	}

	if(!this.showFormOnPopup){
		$("#"+this.getTableName()+'Form').show();
		$("#"+this.getTableName()).hide();

        for(var i=0;i<signatureIds.length;i++){
            $("#"+signatureIds[i])
                .data('signaturePad',
                new SignaturePad(document.getElementById(signatureIds[i])));

        }

		if(object != undefined && object != null){
			this.fillForm(object);
		}

		this.scrollToTop();

	}else{



		//var tHtml = $tempDomObj.wrap('<div>').parent().html();
		//this.showMessage("Edit",tHtml,null,null,true);
		this.showMessage("Edit","",null,null,true);

		$("#plainMessageModel .modal-body").html("");
		$("#plainMessageModel .modal-body").append($tempDomObj);


        for(var i=0;i<signatureIds.length;i++){
            $("#"+signatureIds[i])
                .data('signaturePad',
                new SignaturePad(document.getElementById(signatureIds[i])));

        }

		if(object != undefined && object != null){
			this.fillForm(object,"#"+randomFormId);
		}

	}

	this.postRenderForm(object,$tempDomObj);



});


IceHRMBase.method('retriveItemsAfterSave', function() {
    return true;
});

/**
 * Override this method in your module class to make changes to data fo the form after showing it
 * @method postRenderForm
 * @param object {Array} keys value list for populating form
 * @param $tempDomObj {DOM} a DOM element for the form
 * @example
 * 	UserAdapter.method('postRenderForm', function(object, $tempDomObj) {
		if(object == null || object == undefined){
			$tempDomObj.find("#changePasswordBtn").remove();
		}
	});
 */

IceHRMBase.method('postRenderForm', function(object, $tempDomObj) {

});

/**
 * Convert data group field to HTML
 * @method dataGroupToHtml
 * @param val {String} value in the field
 * @param field {Array} field meta data
 */

IceHRMBase.method('dataGroupToHtml', function(val, field) {
	var data = JSON.parse(val),
		deleteButton, t, sortFunction, item,key = null, i, html, template, itemHtml, itemVal;

	deleteButton = '<a id="#_id_#_delete" onclick="modJs.deleteDataGroupItem(\'#_id_#\');return false;" type="button" style="float:right;margin-right:3px;" tooltip="Delete"><li class="fa fa-times"></li></a>';
	editButton = '<a id="#_id_#_edit" onclick="modJs.editDataGroupItem(\'#_id_#\');return false;" type="button" style="float:right;margin-right:5px;" tooltip="Edit"><li class="fa fa-edit"></li></a>';

	template = field[1]['html'];

	if(data != null && data != undefined && field[1]['sort-function'] != undefined && field[1]['sort-function'] != null){
		data.sort(field[1]['sort-function']);
	}


	html = $('<div id="'+field[0]+"_div_inner"+'"></div>');



	for(i=0;i<data.length;i++){
		item = data[i];

		if(field[1]['pre-format-function'] != undefined && field[1]['pre-format-function'] != null){
			item = field[1]['pre-format-function'].apply(this,[item]);
		}

		t = template;
		t = t.replace('#_delete_#',deleteButton);
		t = t.replace('#_edit_#',editButton);
		t = t.replace(/#_id_#/g,item.id);

		for(key in item){
			itemVal = item[key];
			if(itemVal != undefined && itemVal != null && typeof itemVal == "string"){
				itemVal = itemVal.replace(/(?:\r\n|\r|\n)/g, '<br />');
			}
			t = t.replace('#_'+key+'_#', itemVal);
		}

        if(field[1]['render'] != undefined && field[1]['render'] != null){
            t = t.replace('#_renderFunction_#', field[1]['render'](item));
        }

		itemHtml = $(t);
		itemHtml.attr('fieldId',field[0]+"_div");
		html.append(itemHtml);
	}



	return html;
});

/**
 * Reset the DataGroup for a given field
 * @method resetDataGroup
 * @param field {Array} field meta data
 */
IceHRMBase.method('resetDataGroup', function(field) {
	$("#"+field[0]).val("");
	$("#"+field[0]+"_div").html("");
});

IceHRMBase.method('showDataGroup', function(field, object) {
	var formHtml = this.templates['datagroupTemplate'];
	var html = "";
	var fields = field[1]['form'];

	if(object != undefined && object != null && object.id != undefined){
		this.currentDataGroupItemId = object.id;
	}else{
		this.currentDataGroupItemId = null;
	}

	for(var i=0;i<fields.length;i++){
		html += this.renderFormField(fields[i]);

	}
	formHtml = formHtml.replace(/_id_/g,this.getTableName()+"_field_"+field[0]);
	formHtml = formHtml.replace(/_fields_/g,html);

	var $tempDomObj;
	var randomFormId = this.generateRandom(14);
	$tempDomObj = $('<div class="reviewBlock popupForm" data-content="Form"></div>');
	$tempDomObj.attr('id',randomFormId);

	$tempDomObj.html(formHtml);


	$tempDomObj.find('.datefield').datepicker({'viewMode':2});
	$tempDomObj.find('.timefield').datetimepicker({
      language: 'en',
      pickDate: false
    });
	$tempDomObj.find('.datetimefield').datetimepicker({
      language: 'en'
    });

	$tempDomObj.find('.colorpick').colorpicker();

	tinymce.init({
		selector: '#'+$tempDomObj.attr('id')+' .tinymce',
		height: "400"
	});

	$tempDomObj.find('.simplemde').each(function() {
		var simplemde = new SimpleMDE({ element: $(this)[0] });
		$(this).data('simplemde', simplemde);
		//simplemde.value($(this).val());
	});

	$tempDomObj.find('.select2Field').each(function() {
		$(this).select2().select2('val', $(this).find("option:eq(0)").val());
	});


	$tempDomObj.find('.select2Multi').each(function() {
		$(this).select2().on("change",function(e){
			var parentRow = $(this).parents(".row");
			var height = parentRow.find(".select2-choices").height();
			parentRow.height(parseInt(height));
		});
	});

    /*
    $tempDomObj.find('.signatureField').each(function() {
        $(this).data('signaturePad',new SignaturePad($(this)));
    });
    */

	this.currentDataGroupField = field;
	this.showDomElement("Add "+field[1]['label'],$tempDomObj,null,null,true);

	if(object != undefined && object != null){
		this.fillForm(object,"#"+this.getTableName()+"_field_"+field[0], field[1]['form']);
	}


	$(".groupAddBtn").off();
	if(object != undefined && object != null && object.id != undefined){
		$(".groupAddBtn").on('click',function(e) {
			e.preventDefault();
			e.stopPropagation();
			try{
				modJs.editDataGroup();

			}catch(e){
			};
			return false;
		});
	}else{
		$(".groupAddBtn").on('click',function(e) {
			e.preventDefault();
			e.stopPropagation();
			try{
				modJs.addDataGroup();

			}catch(e){
			};
			return false;
		});
	}


});

IceHRMBase.method('addDataGroup', function() {
	var field = this.currentDataGroupField, tempParams;
	$("#"+this.getTableName()+"_field_"+field[0]+"_error").html("");
	$("#"+this.getTableName()+"_field_"+field[0]+"_error").hide();
	var validator = new FormValidation(this.getTableName()+"_field_"+field[0],true,{'ShowPopup':false,"LabelErrorClass":"error"});
	if(validator.checkValues()){
		var params = validator.getFormParameters();
		if(field[1]['custom-validate-function'] != undefined && field[1]['custom-validate-function'] != null){
			tempParams = field[1]['custom-validate-function'].apply(this,[params]);
			if(tempParams['valid']){
				params = tempParams['params'];
			}else{
				$("#"+this.getTableName()+"_field_"+field[0]+"_error").html(tempParams['message']);
				$("#"+this.getTableName()+"_field_"+field[0]+"_error").show();
				return false;
			}
		}

		var val = $("#"+field[0]).val();
		if(val == ""){
			val = "[]";
		}
		var data = JSON.parse(val);

		params['id'] = field[0]+"_"+this.dataGroupGetNextAutoIncrementId(data);
		data.push(params);


		if(field[1]['sort-function'] != undefined && field[1]['sort-function'] != null){
			data.sort(field[1]['sort-function']);
		}

		val = JSON.stringify(data);

		var html = this.dataGroupToHtml(val,field);

		$("#"+field[0]+"_div").html("");
		$("#"+field[0]+"_div").append(html);

		this.makeDataGroupSortable(field, $("#"+field[0]+"_div_inner"));


		$("#"+field[0]).val(val);
		this.orderDataGroup(field);

		this.closeDataMessage();

		this.showMessage("Item Added","This change will be effective only when you save the form");


	}
});

IceHRMBase.method('nl2br' , function(str, len) {
	var t = "";
	try{
		var arr = str.split(" ");
		var count = 0;
		for(var i=0;i<arr.length;i++){
			count += arr[i].length + 1;
			if(count > len){
				t += arr[i] + "<br/>";
				count = 0;
			}else{
				t += arr[i] + " ";
			}
		}
	}catch(e){}
	return t;
});

IceHRMBase.method('makeDataGroupSortable', function(field, obj) {
	obj.data('field',field);
	obj.data('firstSort',true);
	obj.sortable({

		create:function(){
			$(this).height($(this).height());
		},

		'ui-floating': false,
		start: function(e, ui) {
			$('#sortable-ul-selector-id').sortable({
				sort: function(event, ui) {
					var $target = $(event.target);
					if (!/html|body/i.test($target.offsetParent()[0].tagName)) {
						var top = event.pageY - $target.offsetParent().offset().top - (ui.helper.outerHeight(true) / 2);
						ui.helper.css({'top' : top + 'px'});
					}
				}
			});

		},
		revert: true,
		stop: function() {
			modJs.orderDataGroup($(this).data('field'));
		},
		axis: "y",
		scroll: false,
		placeholder: "sortable-placeholder",
		cursor: "move"
	});


});

IceHRMBase.method('orderDataGroup', function(field) {
	var newArr = [], id;
	var list = $("#"+field[0]+"_div_inner [fieldid='"+field[0]+"_div']");
	var val = $("#"+field[0]).val();
	if(val == ""){
		val = "[]";
	}
	var data = JSON.parse(val);
	list.each(function(){
		id = $(this).attr('id');
		for(index in data){
			if(data[index].id == id){
				newArr.push(data[index]);
				break;
			}
		}
	});

	$("#"+field[0]).val(JSON.stringify(newArr));


});


IceHRMBase.method('editDataGroup', function() {
	var field = this.currentDataGroupField;
	var id = this.currentDataGroupItemId;
	var validator = new FormValidation(this.getTableName()+"_field_"+field[0],true,{'ShowPopup':false,"LabelErrorClass":"error"});
	if(validator.checkValues()){
		var params = validator.getFormParameters();

		if(field[1]['custom-validate-function'] != undefined && field[1]['custom-validate-function'] != null){
			tempParams = field[1]['custom-validate-function'].apply(this,[params]);
			if(tempParams['valid']){
				params = tempParams['params'];
			}else{
				$("#"+this.getTableName()+"_field_"+field[0]+"_error").html(tempParams['message']);
				$("#"+this.getTableName()+"_field_"+field[0]+"_error").show();
				return false;
			}
		}


		if(this.doCustomFilterValidation(params)){

			var val = $("#"+field[0]).val();
			if(val == ""){
				val = "[]";
			}
			var data = JSON.parse(val);

			var editVal = {};
			var editValIndex = -1;
			var newVals = [];
			for(var i=0;i<data.length;i++){
				item = data[i];
				if(item.id == id){
					editVal = item;
					editValIndex = i;
				}
				newVals.push(item);
			}



			params['id'] = editVal.id;
			newVals[editValIndex] = params;

			if(field[1]['sort-function'] != undefined && field[1]['sort-function'] != null){
				newVals.sort(field[1]['sort-function']);
			}

			val = JSON.stringify(newVals);
			$("#"+field[0]).val(val);

			var html = this.dataGroupToHtml(val,field);

			this.orderDataGroup(field);

			$("#"+field[0]+"_div").html("");
			$("#"+field[0]+"_div").append(html);

			this.makeDataGroupSortable(field, $("#"+field[0]+"_div_inner"));


			this.closeDataMessage();

			this.showMessage("Item Edited","This change will be effective only when you save the form");

		}

	}
});

IceHRMBase.method('editDataGroupItem', function(id) {
	var fieldId = id.substring(0,id.lastIndexOf("_"));

	var val = $("#"+fieldId).val();
	var data = JSON.parse(val);

	var editVal = {};

	for(var i=0;i<data.length;i++){
		item = data[i];
		if(item.id == id){
			editVal = item;
		}
	}

	this.showDataGroup($("#"+fieldId).data('field'),editVal);


});

IceHRMBase.method('dataGroupGetNextAutoIncrementId', function(data) {
	var autoId = 1, id;
	for(var i=0;i<data.length;i++){
		item = data[i];
		if(item.id == undefined || item.id == null){
			item.id = 1;
		}
		id= item.id.substring(item.id.lastIndexOf("_")+1,item.id.length);
		if(id >= autoId){
			autoId = parseInt(id) + 1;
		}
	}

	return autoId;

});


IceHRMBase.method('deleteDataGroupItem', function(id) {
	var fieldId = id.substring(0,id.lastIndexOf("_"));

	var val = $("#"+fieldId).val();
	var data = JSON.parse(val);

	var newVal = [];

	for(var i=0;i<data.length;i++){
		item = data[i];
		if(item.id != id){
			newVal.push(item);
		}
	}

	$("#"+fieldId).val(JSON.stringify(newVal));

	$("#"+id).remove();

	this.showMessage("Item Removed","Item removed. This change will be effective only when you save the form");

});


/**
 * Fill a form with required values after showing it
 * @method fillForm
 * @param object {Array} form data
 * @param formId {String} id of the form
 * @param formId {Array} field meta data
 */

IceHRMBase.method('fillForm', function(object, formId, fields) {
	var placeHolderVal;
	if(fields == null || fields == undefined){
		fields = this.getFormFields();
	}

	if(formId == null || formId == undefined || formId == ""){
		formId = "#"+this.getTableName()+'Form';
	}


	for(var i=0;i<fields.length;i++) {
		if(fields[i][1].type == 'date'){
			if(object[fields[i][0]] != '0000-00-00' && object[fields[i][0]] != '' && object[fields[i][0]] != null && object[fields[i][0]] != undefined){
				$(formId + ' #'+fields[i][0]+"_date").datepicker('setValue', object[fields[i][0]]);
			}
		}else if(fields[i][1].type == 'colorpick'){
			if(object[fields[i][0]] != null && object[fields[i][0]] != undefined){
				$(formId + ' #'+fields[i][0]+"_colorpick").colorpicker('setValue', object[fields[i][0]]);
				$(formId + ' #'+fields[i][0]).val(object[fields[i][0]]);
			}
		}else if(fields[i][1].type == 'datetime' || fields[i][1].type == 'time'){
			if(object[fields[i][0]] != '0000-00-00 00:00:00' && object[fields[i][0]] != '' && object[fields[i][0]] != null && object[fields[i][0]] != undefined){
				var tempDate = object[fields[i][0]];
				var arr = tempDate.split(" ");
				var dateArr = arr[0].split("-");
				var timeArr = arr[1].split(":");
				$(formId + ' #'+fields[i][0]+"_datetime").data('datetimepicker').setLocalDate(new Date(dateArr[0], parseInt(dateArr[1])-1, dateArr[2], timeArr[0], timeArr[1], timeArr[2]));
			}
		}else if(fields[i][1].type == 'label'){
			$(formId + ' #'+fields[i][0]).html(object[fields[i][0]]);
		}else if(fields[i][1].type == 'placeholder'){

			if(fields[i][1]['remote-source'] != undefined && fields[i][1]['remote-source'] != null){
				var key = fields[i][1]['remote-source'][0]+"_"+fields[i][1]['remote-source'][1]+"_"+fields[i][1]['remote-source'][2];
				placeHolderVal = this.fieldMasterData[key][object[fields[i][0]]];
			}else{
				placeHolderVal = object[fields[i][0]];
			}

			if(placeHolderVal == undefined || placeHolderVal == null){
				placeHolderVal = "";
			}else{
                try{
                    placeHolderVal = placeHolderVal.replace(/(?:\r\n|\r|\n)/g, '<br />');
                }catch(e){}

			}




			$(formId + ' #'+fields[i][0]).html(placeHolderVal);
		}else if(fields[i][1].type == 'fileupload'){
			if(object[fields[i][0]] != null && object[fields[i][0]] != undefined && object[fields[i][0]] != ""){
				$(formId + ' #'+fields[i][0]).html(object[fields[i][0]]);
				$(formId + ' #'+fields[i][0]).attr("val",object[fields[i][0]]);
				$(formId + ' #'+fields[i][0]).show();
				$(formId + ' #'+fields[i][0]+"_download").show();

			}
			if(fields[i][1].readonly == true){
				$(formId + ' #'+fields[i][0]+"_upload").remove();
			}
		}else if(fields[i][1].type == 'select'){
			if(object[fields[i][0]] == undefined || object[fields[i][0]] == null || object[fields[i][0]] == ""){
				object[fields[i][0]] = "NULL";
			}
			$(formId + ' #'+fields[i][0]).val(object[fields[i][0]]);

		}else if(fields[i][1].type == 'select2'){
			if(object[fields[i][0]] == undefined || object[fields[i][0]] == null || object[fields[i][0]] == ""){
				object[fields[i][0]] = "NULL";
			}
			$(formId + ' #'+fields[i][0]).select2('val',object[fields[i][0]]);

		}else if(fields[i][1].type == 'select2multi'){
			//TODO - SM
			if(object[fields[i][0]] == undefined || object[fields[i][0]] == null || object[fields[i][0]] == ""){
				object[fields[i][0]] = "NULL";
			}

			var msVal = [];
			if(object[fields[i][0]] != undefined && object[fields[i][0]] != null && object[fields[i][0]] != ""){
				try{
					msVal = JSON.parse(object[fields[i][0]]);
				}catch(e){}
			}

			$(formId + ' #'+fields[i][0]).select2('val',msVal);
			var select2Height = $(formId + ' #'+fields[i][0]).find(".select2-choices").height();
			$(formId + ' #'+fields[i][0]).find(".controls").css('min-height', select2Height+"px");
			$(formId + ' #'+fields[i][0]).css('min-height', select2Height+"px");

		}else if(fields[i][1].type == 'datagroup'){
			try{
				var html = this.dataGroupToHtml(object[fields[i][0]],fields[i]);
				$(formId + ' #'+fields[i][0]).val(object[fields[i][0]]);
				$(formId + ' #'+fields[i][0]+"_div").html("");
				$(formId + ' #'+fields[i][0]+"_div").append(html);

				this.makeDataGroupSortable(fields[i], $(formId + ' #'+fields[i][0]+"_div_inner"));


			}catch(e){}

		}else if(fields[i][1].type == 'signature'){

            if(object[fields[i][0]] != '' || object[fields[i][0]] != undefined
                || object[fields[i][0]] != null){
                $(formId + ' #'+fields[i][0]).data('signaturePad').fromDataURL(object[fields[i][0]]);
            }
		}else if(fields[i][1].type == 'simplemde'){
			$(formId + ' #'+fields[i][0]).data('simplemde').value(object[fields[i][0]]);
		}else{
			$(formId + ' #'+fields[i][0]).val(object[fields[i][0]]);
		}

	}
});

/**
 * Cancel edit or add new on modules
 * @method cancel
 */

IceHRMBase.method('cancel', function() {
	$("#"+this.getTableName()+'Form').hide();
	$("#"+this.getTableName()).show();
});

IceHRMBase.method('renderFormField', function(field) {
	var userId = 0;
	if(this.fieldTemplates[field[1].type] == undefined || this.fieldTemplates[field[1].type] == null){
		return "";
	}
	var t = this.fieldTemplates[field[1].type];
	field[1].label = this.gt(field[1].label);
	if(field[1].validation != "none" &&  field[1].validation != "emailOrEmpty" && field[1].validation != "numberOrEmpty" && field[1].type != "placeholder" && field[1].label.indexOf('*') < 0){
		var tempSelectBoxes = ['select','select2'];
		if(tempSelectBoxes.indexOf(field[1].type) >= 0 && field[1]['allow-null'] == true){

		}else{
			field[1].label = field[1].label + '<font class="redFont">*</font>';
		}

	}
	if(field[1].type == 'text' || field[1].type == 'textarea' || field[1].type == 'hidden' || field[1].type == 'label' || field[1].type == 'placeholder'){
		t = t.replace(/_id_/g,field[0]);
		t = t.replace(/_label_/g,field[1].label);

	}else if(field[1].type == 'select' || field[1].type == 'select2' || field[1].type == 'select2multi'){
		t = t.replace(/_id_/g,field[0]);
		t = t.replace(/_label_/g,field[1].label);
		if(field[1]['source'] != undefined && field[1]['source'] != null ){
			t = t.replace('_options_',this.renderFormSelectOptions(field[1].source, field));
		}else if(field[1]['remote-source'] != undefined && field[1]['remote-source'] != null ){
			var key = field[1]['remote-source'][0]+"_"+field[1]['remote-source'][1]+"_"+field[1]['remote-source'][2];
			t = t.replace('_options_',this.renderFormSelectOptionsRemote(this.fieldMasterData[key],field));
		}

	}else if(field[1].type == 'colorpick'){
		t = t.replace(/_id_/g,field[0]);
		t = t.replace(/_label_/g,field[1].label);

	}else if(field[1].type == 'date'){
		t = t.replace(/_id_/g,field[0]);
		t = t.replace(/_label_/g,field[1].label);

	}else if(field[1].type == 'datetime'){
		t = t.replace(/_id_/g,field[0]);
		t = t.replace(/_label_/g,field[1].label);

	}else if(field[1].type == 'time'){
		t = t.replace(/_id_/g,field[0]);
		t = t.replace(/_label_/g,field[1].label);

	}else if(field[1].type == 'fileupload'){
		t = t.replace(/_id_/g,field[0]);
		t = t.replace(/_label_/g,field[1].label);
		var ce = this.getCurrentProfile();
		if(ce != null && ce != undefined){
			userId = ce.id;
		}else{
			userId = this.getUser().id * -1;
		}
		t = t.replace(/_userId_/g,userId);
		t = t.replace(/_group_/g,this.tab);

		if(field[1].filetypes != undefined && field[1].filetypes != null){
			t = t.replace(/_filetypes_/g,field[1].filetypes);
		}else{
			t = t.replace(/_filetypes_/g,'all');
		}

		/*
		if(object != null && object != undefined && object[field[0]] != null && object[field[0]] != undefined && object[field[0]] != ""){
			t = t.replace(/_id___rand_/g,field[0]);
		}
		*/
		t = t.replace(/_rand_/g,this.generateRandom(14));

	}else if(field[1].type == 'datagroup'){
		t = t.replace(/_id_/g,field[0]);
		t = t.replace(/_label_/g,field[1].label);

	}else if(field[1].type == 'signature'){
        t = t.replace(/_id_/g,field[0]);
        t = t.replace(/_label_/g,field[1].label);

    }else if(field[1].type == 'tinymce' || field[1].type == 'simplemde'){
		t = t.replace(/_id_/g,field[0]);
		t = t.replace(/_label_/g,field[1].label);
	}


	if(field[1].validation != undefined && field[1].validation != null && field[1].validation != ""){
		t = t.replace(/_validation_/g,'validation="'+field[1].validation+'"');
	}else{
		t = t.replace(/_validation_/g,'');
	}

	if(field[1].help != undefined || field[1].help != null){
		t = t.replace(/_helpline_/g,field[1].help);
	}else{
		t = t.replace(/_helpline_/g,'');
	}

	return t;
});

IceHRMBase.method('renderFormSelectOptions', function(options, field) {
	var html = "";

	if(field != null && field != undefined){
		if(field[1]['allow-null'] == true){
			if(field[1]['null-label'] != undefined && field[1]['null-label'] != null){
				html += '<option value="NULL">'+this.gt(field[1]['null-label'])+'</option>';
			}else{
				html += '<option value="NULL">Select</option>';
			}

		}
	}


	//Sort options

	var tuples = [];

	for (var key in options) {
		tuples.push(options[key]);
	}
    if(field[1]['sort'] != 'none'){
        tuples.sort(function(a, b) {
            a = a[1];
            b = b[1];

            return a < b ? -1 : (a > b ? 1 : 0);
        });
    }


	for (var i = 0; i < tuples.length; i++) {
	    var prop = tuples[i][0];
	    var value = tuples[i][1];
	    var t = '<option value="_id_">_val_</option>';
		t = t.replace('_id_', prop);
		t = t.replace('_val_', value);
		html += t;

	}
	return html;

});

IceHRMBase.method('renderFormSelectOptionsRemote', function(options,field) {
	var html = "";
	if(field[1]['allow-null'] == true){
		if(field[1]['null-label'] != undefined && field[1]['null-label'] != null){
			html += '<option value="NULL">'+this.gt(field[1]['null-label'])+'</option>';
		}else{
			html += '<option value="NULL">Select</option>';
		}

	}

	//Sort options

	var tuples = [];

	for (var key in options) {
		tuples.push([key, options[key]]);
	}
    if(field[1]['sort'] != 'none') {
        tuples.sort(function (a, b) {
            a = a[1];
            b = b[1];

            return a < b ? -1 : (a > b ? 1 : 0);
        });
    }

	for (var i = 0; i < tuples.length; i++) {
	    var prop = tuples[i][0];
	    var value = tuples[i][1];

	    var t = '<option value="_id_">_val_</option>';
		t = t.replace('_id_', prop);
		t = t.replace('_val_', value);
		html += t;
	}


	return html;

});

IceHRMBase.method('setTemplates', function(templates) {
	this.templates = templates;
});

IceHRMBase.method('setCustomTemplates', function(templates) {
	this.customTemplates = templates;
});

IceHRMBase.method('setEmailTemplates', function(templates) {
	this.emailTemplates = templates;
});

IceHRMBase.method('getCustomTemplate', function(file) {
	return this.customTemplates[file];
});

IceHRMBase.method('setFieldTemplates', function(templates) {
	this.fieldTemplates = templates;
});


IceHRMBase.method('getMetaFieldForRendering', function(fieldName) {
	return "";
});

IceHRMBase.method('clearDeleteParams', function() {
	this.deleteParams = {};
});

IceHRMBase.method('getShowAddNew', function() {
	return this.showAddNew;
});

/**
 * Override this method to change add new button label
 * @method getAddNewLabel
 */

IceHRMBase.method('getAddNewLabel', function() {
	return "Add New";
});

/**
 * Used to set whether to show the add new button for a module
 * @method setShowAddNew
 * @param showAddNew {Boolean} value
 */

IceHRMBase.method('setShowAddNew', function(showAddNew) {
	this.showAddNew = showAddNew;
});

/**
 * Used to set whether to show delete button for each entry in module
 * @method setShowDelete
 * @param val {Boolean} value
 */
IceHRMBase.method('setShowDelete', function(val) {
	this.showDelete = val;
});


/**
 * Used to set whether to show edit button for each entry in module
 * @method setShowEdit
 * @param val {Boolean} value
 */

IceHRMBase.method('setShowEdit', function(val) {
	this.showEdit = val;
});

/**
 * Used to set whether to show save button in form
 * @method setShowSave
 * @param val {Boolean} value
 */


IceHRMBase.method('setShowSave', function(val) {
	this.showSave = val;
});


/**
 * Used to set whether to show cancel button in form
 * @method setShowCancel
 * @param val {Boolean} value
 */

IceHRMBase.method('setShowCancel', function(val) {
	this.showCancel = val;
});

/**
 * Datatable option array will be extended with associative array provided here
 * @method getCustomTableParams
 * @param val {Boolean} value
 */


IceHRMBase.method('getCustomTableParams', function() {
	return {};
});

IceHRMBase.method('getActionButtons', function(obj) {
	return modJs.getActionButtonsHtml(obj.aData[0],obj.aData);
});


/**
 * This return html for action buttons in each row. Override this method if you need to make changes to action buttons.
 * @method getActionButtonsHtml
 * @param id {int} id of the row
 * @param data {Array} data for the row
 * @returns {String} html for action buttons
 */

IceHRMBase.method('getActionButtonsHtml', function(id,data) {
	var editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
	var deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
	var cloneButton = '<img class="tableActionButton" src="_BASE_images/clone.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Copy" onclick="modJs.copyRow(_id_);return false;"></img>';
	var html = '<div style="width:80px;">_edit__delete__clone_</div>';

	if(this.showAddNew){
		html = html.replace('_clone_',cloneButton);
	}else{
		html = html.replace('_clone_','');
	}

	if(this.showDelete){
		html = html.replace('_delete_',deleteButton);
	}else{
		html = html.replace('_delete_','');
	}

	if(this.showEdit){
		html = html.replace('_edit_',editButton);
	}else{
		html = html.replace('_edit_','');
	}

	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	return html;
});


/**
 * Generates a random string
 * @method generateRandom
 * @param length {int} required length of the string
 * @returns {String} random string
 */

IceHRMBase.method('generateRandom', function(length) {
	var d = new Date();
	var chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	var result = '';
	for (var i = length; i > 0; --i) result += chars[Math.round(Math.random() * (chars.length - 1))];
	return result+d.getTime();
});



IceHRMBase.method('checkFileType', function (elementName, fileTypes) {
	var fileElement = document.getElementById(elementName);
	var fileExtension = "";
	if (fileElement.value.lastIndexOf(".") > 0) {
		fileExtension = fileElement.value.substring(fileElement.value.lastIndexOf(".") + 1, fileElement.value.length);
	}

	fileExtension = fileExtension.toLowerCase();

	var allowed = fileTypes.split(",");

	if (allowed.indexOf(fileExtension) < 0) {
		fileElement.value = "";
		this.showMessage("File Type Error",'Selected file type is not supported');
		this.clearFileElement(elementName);
		return false;
	}

	return true;

});

IceHRMBase.method('clearFileElement', function (elementName) {

	var control = $("#"+elementName);
	control.replaceWith( control = control.val('').clone( true ) );
});


IceHRMBase.method('fixJSON', function (json) {
	if(this.noJSONRequests == "1"){
		json = json.replace(/"/g,'|');
	}
	return json;
});


IceHRMBase.method('getClientDate', function (date) {

	var offset = this.getClientGMTOffset();
    var tzDate = date.addMinutes(offset*60);
    return tzDate;

});

IceHRMBase.method('getClientGMTOffset', function () {

	var rightNow = new Date();
	var jan1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);
	var temp = jan1.toGMTString();
	var jan2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
	var std_time_offset = (jan1 - jan2) / (1000 * 60 * 60);

	return std_time_offset;

});

/**
 * Override this method in a module to provide the help link for the module. Help link of the module on frontend will get updated with this.
 * @method getHelpLink
 * @returns {String} help link
 */

IceHRMBase.method('getHelpLink', function () {

	return null;

});

IceHRMBase.method('showLoader', function () {
	$('#iceloader').show();
});

IceHRMBase.method('hideLoader', function () {
	$('#iceloader').hide();
});

IceHRMBase.method('generateOptions', function (data) {
    var template = '<option value="__val__">__text__</option>';
    var options = "";
    for(index in data){
        options += template.replace("__val__",index).replace("__text__",data[index]);
    }

    return options;
});

IceHRMBase.method('isModuleInstalled', function (type, name) {
    if(modulesInstalled == undefined || modulesInstalled == null){
        return false;
    }

    return (modulesInstalled[type+"_"+name] == 1);
});

