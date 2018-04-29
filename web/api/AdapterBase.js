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


function AdapterBase(endPoint) {

}

this.moduleRelativeURL = null;
this.tableData = new Array();
this.sourceData = new Array();
this.filter = null;
this.origFilter = null;
this.orderBy = null;
this.currentElement = null;

AdapterBase.inherits(IceHRMBase);

AdapterBase.method('initAdapter' , function(endPoint,tab,filter,orderBy) {
	this.moduleRelativeURL = baseUrl;
	this.table = endPoint;
	if(tab == undefined || tab == null){
		this.tab = endPoint;
	}else{
		this.tab = tab;
	}

	if(filter == undefined || filter == null){
		this.filter = null;
	}else{
		this.filter = filter;
	}

	this.origFilter = this.filter;

	if(orderBy == undefined || orderBy == null){
		this.orderBy = null;
	}else{
		this.orderBy = orderBy;
	}

	this.trackEvent("initAdapter",tab);

	this.requestCache = new RequestCache();

});

AdapterBase.method('setFilter', function(filter) {
	this.filter = filter;
});

AdapterBase.method('preSetFilterExternal', function(filter) {
	this.initialFilter = filter;
});

AdapterBase.method('setFilterExternal', function(filter) {
	if(filter == undefined || filter == null){
		filter = this.initialFilter;
	}

	if(filter == undefined || filter == null){
		return;
	}

	this.setFilter(filter);
	this.filtersAlreadySet = true;
	$("#"+this.getTableName()+"_resetFilters").show();
	this.currentFilterString = this.getFilterString(filter);
});

AdapterBase.method('getFilter', function() {
	return this.filter;
});

AdapterBase.method('setOrderBy', function(orderBy) {
	this.orderBy = orderBy;
});

AdapterBase.method('getOrderBy', function() {
	return this.orderBy;
});

/**
 * @method add
 * @param object {Array} object data to be added to database
 * @param getFunctionCallBackData {Array} once a success is returned call get() function for this module with these parameters
 * @param callGetFunction {Boolean} if false the get function of the module will not be called (default: true)
 * @param successCallback {Function} this will get called after success response
 */

AdapterBase.method('add', function(object,getFunctionCallBackData,callGetFunction,successCallback) {
	var that = this;
	if(callGetFunction == undefined || callGetFunction == null){
		callGetFunction = true;
	}
	$(object).attr('a','add');
	$(object).attr('t',this.table);
	that.showLoader();
	this.requestCache.invalidateTable(this.table);
	$.post(this.moduleRelativeURL, object, function(data) {
		if(data.status == "SUCCESS"){
			that.addSuccessCallBack(getFunctionCallBackData,data.object, callGetFunction, successCallback, that);
		}else{
			that.addFailCallBack(getFunctionCallBackData,data.object);
		}
	},"json").always(function() {that.hideLoader()});
	this.trackEvent("add",this.tab,this.table);
});

AdapterBase.method('addSuccessCallBack', function(callBackData,serverData, callGetFunction, successCallback, thisObject) {
	if(callGetFunction){
		this.get(callBackData);
	}
	this.initFieldMasterData();
	if(successCallback != undefined && successCallback != null){
		successCallback.apply(thisObject,[serverData]);
	}
	this.trackEvent("addSuccess",this.tab,this.table);
});

AdapterBase.method('addFailCallBack', function(callBackData,serverData) {
	try{
		this.closePlainMessage();
	}catch(e){}
	this.showMessage("Error saving",serverData);
	this.trackEvent("addFailed",this.tab,this.table);
});

AdapterBase.method('deleteObj', function(id,callBackData) {
	var that = this;
	that.showLoader();
	this.requestCache.invalidateTable(this.table);
	$.post(this.moduleRelativeURL, {'t':this.table,'a':'delete','id':id}, function(data) {
		if(data.status == "SUCCESS"){
			that.deleteSuccessCallBack(callBackData,data.object);
		}else{
			that.deleteFailCallBack(callBackData,data.object);
		}
	},"json").always(function() {that.hideLoader()});
	this.trackEvent("delete",this.tab,this.table);
});

AdapterBase.method('deleteSuccessCallBack', function(callBackData,serverData) {
	this.get(callBackData);
	this.clearDeleteParams();
});

AdapterBase.method('deleteFailCallBack', function(callBackData,serverData) {
	this.clearDeleteParams();
	this.showMessage("Error Occurred while Deleting Item",serverData);
});

AdapterBase.method('get', function(callBackData) {
	var that = this;

	if(this.getRemoteTable()){
		this.createTableServer(this.getTableName());
		$("#"+this.getTableName()+'Form').hide();
		$("#"+this.getTableName()).show();
		return;
	}

	var sourceMappingJson = JSON.stringify(this.getSourceMapping());

	var filterJson = "";
	if(this.getFilter() != null){
		filterJson = JSON.stringify(this.getFilter());
	}

	var orderBy = "";
	if(this.getOrderBy() != null){
		orderBy = this.getOrderBy();
	}

	sourceMappingJson = this.fixJSON(sourceMappingJson);
	filterJson = this.fixJSON(filterJson);

	that.showLoader();
	$.post(this.moduleRelativeURL, {'t':this.table,'a':'get','sm':sourceMappingJson,'ft':filterJson,'ob':orderBy}, function(data) {
		if(data.status == "SUCCESS"){
			that.getSuccessCallBack(callBackData,data.object);
		}else{
			that.getFailCallBack(callBackData,data.object);
		}
	},"json").always(function() {that.hideLoader()});

	that.initFieldMasterData();

	this.trackEvent("get",this.tab,this.table);
	//var url = this.getDataUrl();
	//console.log(url);
});


AdapterBase.method('getDataUrl', function(columns) {
	var that = this;
	var sourceMappingJson = JSON.stringify(this.getSourceMapping());

	var columns = JSON.stringify(columns);

	var filterJson = "";
	if(this.getFilter() != null){
		filterJson = JSON.stringify(this.getFilter());
	}

	var orderBy = "";
	if(this.getOrderBy() != null){
		orderBy = this.getOrderBy();
	}

	var url = this.moduleRelativeURL.replace("service.php","data.php");
	url = url+"?"+"t="+this.table;
	url = url+"&"+"sm="+this.fixJSON(sourceMappingJson);
	url = url+"&"+"cl="+this.fixJSON(columns);
	url = url+"&"+"ft="+this.fixJSON(filterJson);
	url = url+"&"+"ob="+orderBy;

	if(this.isSubProfileTable()){
		url = url+"&"+"type=sub";
	}

	if(this.remoteTableSkipProfileRestriction()){
		url = url+"&"+"skip=1";
	}

	return url;
});

AdapterBase.method('isSubProfileTable', function() {
	return false;
});

AdapterBase.method('remoteTableSkipProfileRestriction', function() {
	return false;
});

AdapterBase.method('preProcessTableData', function(row) {
	return row;
});

AdapterBase.method('getSuccessCallBack', function(callBackData,serverData) {
	var data = [];
	var mapping = this.getDataMapping();
	for(var i=0;i<serverData.length;i++){
		var row = [];
		for(var j=0;j<mapping.length;j++){
			row[j] = serverData[i][mapping[j]];
		}
		data.push(this.preProcessTableData(row));
	}
	this.sourceData = serverData;
	if(callBackData['callBack']!= undefined && callBackData['callBack'] != null){
		if(callBackData['callBackData'] == undefined || callBackData['callBackData'] == null){
			callBackData['callBackData'] = new Array();
		}
		callBackData['callBackData'].push(serverData);
		callBackData['callBackData'].push(data);
		this.callFunction(callBackData['callBack'],callBackData['callBackData']);
	}

	this.tableData = data;

	if(callBackData['noRender']!= undefined && callBackData['noRender'] != null && callBackData['noRender'] == true){

	}else{
		this.createTable(this.getTableName());
		$("#"+this.getTableName()+'Form').hide();
		$("#"+this.getTableName()).show();
	}

});

AdapterBase.method('getFailCallBack', function(callBackData,serverData) {

});


AdapterBase.method('getElement', function(id,callBackData,clone) {
	var that = this;
	var sourceMappingJson = JSON.stringify(this.getSourceMapping());
	sourceMappingJson = this.fixJSON(sourceMappingJson);
	that.showLoader();
	$.post(this.moduleRelativeURL, {'t':this.table,'a':'getElement','id':id,'sm':sourceMappingJson}, function(data) {
		if(data.status == "SUCCESS"){
			if(clone){
				delete data.object.id;
			}
			this.currentElement = data.object;
			that.getElementSuccessCallBack.apply(that,[callBackData,data.object]);
		}else{
			that.getElementFailCallBack.apply(that,[callBackData,data.object]);
		}
	},"json").always(function() {that.hideLoader()});
	this.trackEvent("getElement",this.tab,this.table);
});

AdapterBase.method('getElementSuccessCallBack', function(callBackData,serverData) {
	if(callBackData['callBack']!= undefined && callBackData['callBack'] != null){
		if(callBackData['callBackData'] == undefined || callBackData['callBackData'] == null){
			callBackData['callBackData'] = new Array();
		}
		callBackData['callBackData'].push(serverData);
		this.callFunction(callBackData['callBack'],callBackData['callBackData'],this);
	}
	this.currentElement = serverData;
	if(callBackData['noRender']!= undefined && callBackData['noRender'] != null && callBackData['noRender'] == true){

	}else{
		this.renderForm(serverData);
	}
});

AdapterBase.method('getElementFailCallBack', function(callBackData,serverData) {

});


AdapterBase.method('getTableData', function() {
	return this.tableData;
});

AdapterBase.method('getTableName', function() {
	return this.tab;
});

AdapterBase.method('getFieldValues', function(fieldMaster,callBackData) {
	var that = this;
	var method =  "";
	var methodParams =  "";
	if(fieldMaster[3] != undefined && fieldMaster[3] != null){
		method = fieldMaster[3];
	}

	if(fieldMaster[4] != undefined && fieldMaster[4] != null){
		methodParams = JSON.stringify(fieldMaster[4]);
	}

	var key = this.requestCache.getKey(this.moduleRelativeURL,{'t':fieldMaster[0],'key':fieldMaster[1],'value':fieldMaster[2],'method':method,'methodParams':methodParams,'a':'getFieldValues'});
	var cacheData = this.requestCache.getData(key);

	if(cacheData != null){

		if(cacheData.status == "SUCCESS"){
			callBackData['callBackData'].push(cacheData.data);
			if(callBackData['callBackSuccess'] != null && callBackData['callBackSuccess'] != undefined){
				callBackData['callBackData'].push(callBackData['callBackSuccess']);
			}
			that.callFunction(callBackData['callBack'],callBackData['callBackData']);
		}

	}

	var callbackWraper = function(data) {
		if(data.status == "SUCCESS"){
			that.requestCache.setData(this.success.key, data);

			callBackData['callBackData'].push(data.data);
			if(callBackData['callBackSuccess'] != null && callBackData['callBackSuccess'] != undefined){
				callBackData['callBackData'].push(callBackData['callBackSuccess']);
			}
			that.callFunction(callBackData['callBack'],callBackData['callBackData']);
		}
	};

	callbackWraper.key = key;
	callbackWraper.table = fieldMaster[0];

	$.post(this.moduleRelativeURL, {'t':fieldMaster[0],'key':fieldMaster[1],'value':fieldMaster[2],'method':method,'methodParams':methodParams,'a':'getFieldValues'}, callbackWraper,"json");



});

AdapterBase.method('setAdminProfile', function(empId) {
	var that = this;
	try{
		localStorage.clear();
	}catch(e){}
	$.post(this.moduleRelativeURL, {'a':'setAdminEmp','empid':empId}, function(data) {
		top.location.href = clientUrl;
	},"json");
});

AdapterBase.method('customAction', function(subAction,module,request,callBackData, isPost) {
	var that = this;
	request = this.fixJSON(request);
	if (!isPost) {
		$.getJSON(this.moduleRelativeURL, {'t':this.table,'a':'ca','sa':subAction,'mod':module,'req':request}, function(data) {
			if(data.status == "SUCCESS"){
				callBackData['callBackData'].push(data.data);
				that.callFunction(callBackData['callBackSuccess'],callBackData['callBackData']);
			}else{
				callBackData['callBackData'].push(data.data);
				that.callFunction(callBackData['callBackFail'],callBackData['callBackData']);
			}
		});
	} else {
		$.post(this.moduleRelativeURL, {'t':this.table,'a':'ca','sa':subAction,'mod':module,'req':request}, function(data) {
			if(data.status == "SUCCESS"){
				callBackData['callBackData'].push(data.data);
				that.callFunction(callBackData['callBackSuccess'],callBackData['callBackData']);
			}else{
				callBackData['callBackData'].push(data.data);
				that.callFunction(callBackData['callBackFail'],callBackData['callBackData']);
			}
		}, "json");
	}

});


AdapterBase.method('sendCustomRequest', function(action,params,successCallback,failCallback) {
	var that = this;
	params['a'] = action;
	$.post(this.moduleRelativeURL, params, function(data) {
		if(data.status == "SUCCESS"){
			successCallback(data['data']);
		}else{
			failCallback(data['data']);
		}
	},"json");
});


AdapterBase.method('getCustomActionUrl', function(action,params) {

	params['a'] = action;
	var str = "";
	for(var key in params){
		if(params.hasOwnProperty(key)){
			if(str != ""){
				str += "&";
			}
			str += key + "=" + params[key];
		}
	}
	return this.moduleRelativeURL+"?"+str;
});


AdapterBase.method('getClientDataUrl', function() {
	return this.moduleRelativeURL.replace("service.php","")+"data/";
});

AdapterBase.method('getCustomUrl', function(str) {
	return this.moduleRelativeURL.replace("service.php",str);
});



/**
 * @class SubAdapterBase
 * @param endPoint
 * @param tab
 * @param filter
 * @param orderBy
 * @returns
 */


function SubAdapterBase(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
}

SubAdapterBase.inherits(AdapterBase);

SubAdapterBase.method('deleteRow', function(id) {
	this.deleteParams['id'] = id;
	this.confirmDelete();
});

SubAdapterBase.method('createTable', function(elementId) {
	var item, itemHtml,itemDelete,itemEdit;
	var data = this.getTableData();

	var deleteButton = '<button id="#_id_#_delete" onclick="modJs.subModJsList[\'tab'+elementId+'\'].deleteRow(\'_id_\');return false;" type="button" style="position: absolute;bottom: 5px;right: 5px;font-size: 13px;" tooltip="Delete"><li class="fa fa-times"></li></button>';
	var editButton = '<button id="#_id_#_edit" onclick="modJs.subModJsList[\'tab'+elementId+'\'].edit(\'_id_\');return false;" type="button" style="position: absolute;bottom: 5px;right: 35px;font-size: 13px;" tooltip="Edit"><li class="fa fa-edit"></li></button>';

	var table = $('<div class="list-group"></div>');

	//add Header
	var header = this.getSubHeader();
	table.append(header);
	if(data.length == 0){
		table.append('<a href="#" class="list-group-item">'+this.getNoDataMessage()+'</a>');
	}else{
		for(var i=0;i<data.length;i++){
			item = data[i];
			itemDelete = deleteButton.replace(/_id_/g,item[0]);
			itemEdit = editButton.replace(/_id_/g,item[0]);
			itemHtml = this.getSubItemHtml(item,itemDelete,itemEdit);
			table.append(itemHtml);
		}

	}
	$("#"+elementId).html("");
	$("#"+elementId).append(table);
	$('#plainMessageModel').modal('hide');

});

SubAdapterBase.method('getNoDataMessage', function() {
	return "No data found";
});

SubAdapterBase.method('getSubHeader', function() {
	var header = $('<a href="#" onclick="return false;" class="list-group-item" style="background:#eee;"><h4 class="list-group-item-heading">'+this.getSubHeaderTitle()+'</h4></a>');
	return header;
});



/**
 * IdNameAdapter
 */

function IdNameAdapter(endPoint) {
	this.initAdapter(endPoint);
}

IdNameAdapter.inherits(AdapterBase);



IdNameAdapter.method('getDataMapping', function() {
	return [
		"id",
		"name"
	];
});

IdNameAdapter.method('getHeaders', function() {
	return [
		{ "sTitle": "ID" ,"bVisible":false},
		{ "sTitle": "Name"}
	];
});

IdNameAdapter.method('getFormFields', function() {
	return [
		[ "id", {"label":"ID","type":"hidden"}],
		[ "name", {"label":"Name","type":"text","validation":""}]
	];
});


/**
 * LogViewAdapter
 */

function LogViewAdapter(endPoint,tab,filter,orderBy){
	this.initAdapter(endPoint,tab,filter,orderBy);
}

LogViewAdapter.inherits(AdapterBase);

LogViewAdapter.method('getLogs', function(id) {
	var that = this;
	var object = {"id":id};
	var reqJson = JSON.stringify(object);

	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getLogsSuccessCallBack';
	callBackData['callBackFail'] = 'getLogsFailCallBack';

	this.customAction('getLogs','admin='+this.modulePathName,reqJson,callBackData);
});

LogViewAdapter.method('getLogsSuccessCallBack', function(callBackData) {

	var tableLog = '<table class="table table-condensed table-bordered table-striped" style="font-size:14px;"><thead><tr><th>Notes</th></tr></thead><tbody>_days_</tbody></table> ';
	var rowLog = '<tr><td><span class="logTime label label-default">_date_</span>&nbsp;&nbsp;<b>_status_</b><br/>_note_</td></tr>';

	var logs = callBackData.data;
	var html = "";
	var rowsLogs = "";


	for(var i=0;i<logs.length;i++){
		trow = rowLog;
		trow = trow.replace(/_date_/g,logs[i].time);
		trow = trow.replace(/_status_/g,logs[i].status_from+" -> "+logs[i].status_to);
		trow = trow.replace(/_note_/g,logs[i].note);
		rowsLogs += trow;
	}

	if(rowsLogs != ""){
		tableLog = tableLog.replace('_days_',rowsLogs);
		html+= tableLog;
	}

	this.showMessage("Logs",html);

	timeUtils.convertToRelativeTime($(".logTime"));
});

LogViewAdapter.method('getLogsFailCallBack', function(callBackData) {
	this.showMessage("Error","Error occured while getting data");
});

/**
 * ApproveAdminAdapter
 */

function ApproveAdminAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
}

ApproveAdminAdapter.inherits(LogViewAdapter);

ApproveAdminAdapter.method('getStatusFieldPosition', function() {
	var dm = this.getDataMapping();
	return dm.length - 1;
});

ApproveAdminAdapter.method('openStatus', function(id,status) {
	$('#'+this.itemNameLower+'StatusModel').modal('show');
	$('#'+this.itemNameLower+'_status').html(this.getStatusOptions(status));
	$('#'+this.itemNameLower+'_status').val(status);
	this.statusChangeId = id;
});

ApproveAdminAdapter.method('closeDialog', function() {
	$('#'+this.itemNameLower+'StatusModel').modal('hide');
});

ApproveAdminAdapter.method('changeStatus', function() {
	var status = $('#'+this.itemNameLower+'_status').val();
	var reason = $('#'+this.itemNameLower+'_reason').val();

	if(status == undefined || status == null || status == ""){
		this.showMessage("Error", "Please select "+this.itemNameLower+" status");
		return;
	}

	var object = {"id":this.statusChangeId,"status":status,"reason":reason};

	var reqJson = JSON.stringify(object);

	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'changeStatusSuccessCallBack';
	callBackData['callBackFail'] = 'changeStatusFailCallBack';

	this.customAction('changeStatus','admin='+this.modulePathName,reqJson,callBackData);

	this.closeDialog();
	this.statusChangeId = null;
});

ApproveAdminAdapter.method('changeStatusSuccessCallBack', function(callBackData) {
	this.showMessage("Successful", this.itemName + " Request status changed successfully");
	this.get([]);
});

ApproveAdminAdapter.method('changeStatusFailCallBack', function(callBackData) {
	this.showMessage("Error", "Error occurred while changing "+this.itemName+" request status");
});



ApproveAdminAdapter.method('getActionButtonsHtml', function(id,data) {
	var editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
	var deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
	var statusChangeButton = '<img class="tableActionButton" src="_BASE_images/run.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Change Status" onclick="modJs.openStatus(_id_, \'_cstatus_\');return false;"></img>';
	var viewLogsButton = '<img class="tableActionButton" src="_BASE_images/log.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="View Logs" onclick="modJs.getLogs(_id_);return false;"></img>';

	var html = '<div style="width:120px;">_edit__delete__status__logs_</div>';

	var optiondata = this.getStatusOptionsData(data[this.getStatusFieldPosition()]);
	if(Object.keys(optiondata).length > 0){
		html = html.replace('_status_',statusChangeButton);
	}else{
		html = html.replace('_status_','');
	}

	html = html.replace('_logs_',viewLogsButton);

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
	html = html.replace(/_cstatus_/g,data[this.getStatusFieldPosition()]);
	return html;
});

ApproveAdminAdapter.method('isSubProfileTable', function() {
	if(this.user.user_level == "Admin"){
		return false;
	}else{
		return true;
	}
});

ApproveAdminAdapter.method('getStatusOptionsData', function(currentStatus) {
	var data = {};
	if(currentStatus == 'Approved'){

	}else if(currentStatus == 'Pending'){
		data["Approved"] = "Approved";
		data["Rejected"] = "Rejected";

	}else if(currentStatus == 'Rejected'){

	}else if(currentStatus == 'Cancelled'){

	}else if(currentStatus == 'Processing'){

	}else{
		data["Cancellation Requested"] = "Cancellation Requested";
		data["Cancelled"] = "Cancelled";
	}

	return data;
});

ApproveAdminAdapter.method('getStatusOptions', function(currentStatus) {

	return this.generateOptions(this.getStatusOptionsData(currentStatus));
});


/**
 * ApproveModuleAdapter
 */

function ApproveModuleAdapter(endPoint,tab,filter,orderBy) {
	this.initAdapter(endPoint,tab,filter,orderBy);
}

ApproveModuleAdapter.inherits(LogViewAdapter);

ApproveModuleAdapter.method('cancelRequest', function(id) {
	var that = this;
	var object = {};
	object['id'] = id;

	var reqJson = JSON.stringify(object);

	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'cancelSuccessCallBack';
	callBackData['callBackFail'] = 'cancelFailCallBack';

	this.customAction('cancel','modules='+this.modulePathName,reqJson,callBackData);
});

ApproveModuleAdapter.method('cancelSuccessCallBack', function(callBackData) {
	this.showMessage("Successful", this.itemName + " cancellation request sent");
	this.get([]);
});

ApproveModuleAdapter.method('cancelFailCallBack', function(callBackData) {
	this.showMessage("Error Occurred while cancelling "+this.itemName, callBackData);
});

ApproveModuleAdapter.method('getActionButtonsHtml', function(id,data) {
	var editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
	var deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
	var requestCancellationButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Cancel '+this.itemName+'" onclick="modJs.cancelRequest(_id_);return false;"></img>';
	var viewLogsButton = '<img class="tableActionButton" src="_BASE_images/log.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="View Logs" onclick="modJs.getLogs(_id_);return false;"></img>';


	var html = '<div style="width:120px;">_edit__logs__delete_</div>';

	html = html.replace('_logs_',viewLogsButton);

	if(this.showDelete){
		if(data[7] == "Approved"){
			html = html.replace('_delete_',requestCancellationButton);
		}else{
			html = html.replace('_delete_',deleteButton);
		}

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
 * ApproveApproverAdapter
 */

function ApproveApproverAdapter() {
}

ApproveApproverAdapter.method('getActionButtonsHtml', function(id,data) {
	var statusChangeButton = '<img class="tableActionButton" src="_BASE_images/run.png" style="cursor:pointer;" rel="tooltip" title="Change Status" onclick="modJs.openStatus(_id_, \'_cstatus_\');return false;"></img>';
	var viewLogsButton = '<img class="tableActionButton" src="_BASE_images/log.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="View Logs" onclick="modJs.getLogs(_id_);return false;"></img>';

	var html = '<div style="width:80px;">_status__logs_</div>';


	html = html.replace('_logs_',viewLogsButton);


	if(data[this.getStatusFieldPosition()] == 'Processing'){
		html = html.replace('_status_',statusChangeButton);

	}else{
		html = html.replace('_status_','');
	}

	html = html.replace(/_id_/g,id);
	html = html.replace(/_BASE_/g,this.baseUrl);
	html = html.replace(/_cstatus_/g,data[this.getStatusFieldPosition()]);
	return html;
});

ApproveApproverAdapter.method('getStatusOptionsData', function(currentStatus) {
	var data = {};
	if(currentStatus != 'Processing'){

	}else{
		data["Approved"] = "Approved";
		data["Rejected"] = "Rejected";

	}

	return data;
});

ApproveApproverAdapter.method('getStatusOptions', function(currentStatus) {
	return this.generateOptions(this.getStatusOptionsData(currentStatus));
});




/**
 * TableEditAdapter
 */

function TableEditAdapter(endPoint) {
	this.initAdapter(endPoint);
	this.cellDataUpdates = {};
	this.modulePath = '';
	this.rowFieldName = '';
	this.columnFieldName = '';
	this.rowTable = '';
	this.columnTable = '';
	this.valueTable = '';
	this.csvData = [];
	this.columnIDMap = {};
}

TableEditAdapter.inherits(AdapterBase);

TableEditAdapter.method('setModulePath', function(path) {
	this.modulePath = path;
});

TableEditAdapter.method('setRowFieldName', function(name) {
	this.rowFieldName = name;
});

TableEditAdapter.method('setTables', function(rowTable, columnTable, valueTable) {
	this.rowTable = rowTable;
	this.columnTable = columnTable;
	this.valueTable = valueTable;
});

TableEditAdapter.method('setColumnFieldName', function(name) {
	this.columnFieldName = name;
});

TableEditAdapter.method('getDataMapping', function() {
	return [
	];
});


TableEditAdapter.method('getFormFields', function() {
	return [
	];
});

TableEditAdapter.method('get', function() {
	this.getAllData();
});

TableEditAdapter.method('getAllData', function(save) {
	var req = {};
	req.rowTable = this.rowTable;
	req.columnTable = this.columnTable;
	req.valueTable = this.valueTable;
	req = this.addAdditionalRequestData('getAllData', req);
	req.save = (save == undefined || save == null || save == false)?0:1;
	var reqJson = JSON.stringify(req);

	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getAllDataSuccessCallBack';
	callBackData['callBackFail'] = 'getAllDataFailCallBack';

	this.customAction('getAllData',this.modulePath,reqJson,callBackData);
});

TableEditAdapter.method('getDataItem', function(row,column,allData) {
	var columnData = allData[1];
	var rowData = allData[0];
	var serverData = allData[2];

	if(column == -1){
		return rowData[row].name;
	}else{
		return this.getDataItemByKeyValues(this.rowFieldName, rowData[row].id, this.columnFieldName, columnData[column].id, serverData);
	}
});

TableEditAdapter.method('getDataItemByKeyValues', function(rowKeyName, rowKeyVal, colKeyName, colKeyVal, data) {
	for(var i=0;i<data.length;i++){
		if(data[i][rowKeyName] == rowKeyVal && data[i][colKeyName] == colKeyVal){
			return (data[i].amount != undefined && data[i].amount != null)?data[i].amount:"";
		}
	}
	return "";
});

TableEditAdapter.method('getAllDataSuccessCallBack', function(allData) {

	var serverData = allData[2];
	var columnData = allData[1];
	var rowData = allData[0];
	var data = [];
	for(var i=0;i<rowData.length;i++){
		var row = [];
		for(var j=-1;j<columnData.length;j++){
			row[j+1] = this.getDataItem(i, j,allData);
		}
		data.push(this.preProcessTableData(row));
	}
	this.sourceData = serverData;


	this.tableData = data;
	this.setHeaders(columnData,rowData);
	this.createTable(this.getTableName());
	$("#"+this.getTableName()+'Form').hide();
	$("#"+this.getTableName()).show();

	this.csvData = [];

	var tmpRow = [];
	for(var i=0;i<columnData.length;i++){
		tmpRow.push(columnData[i].name);
	}
	tmpRow = this.modifyCSVHeader(tmpRow);
	this.csvData.push(tmpRow);

	for(var i=0; i<data.length; i++){
		this.csvData.push(data[i]);
	}

});

TableEditAdapter.method('modifyCSVHeader', function(header) {
	return header;
});

TableEditAdapter.method('getAllDataFailCallBack', function(callBackData,serverData) {

});

TableEditAdapter.method('setHeaders', function(columns, rows) {
	var headers = [];
	headers.push({ "sTitle": "", sWidth:"180px;"});
	var sclass = "";
	this.columnIDMap = {};
	for(var i=0;i<columns.length;i++){
		this.columnIDMap[columns[i].id] = i;
		if(columns[i].editable == undefined || columns[i].editable == null || columns[i].editable == 'Yes'){
			sclass = "editcell";
		}else{
			sclass = "";
		}
		headers.push({ "sTitle": columns[i].name,sClass: sclass,
			"fnCreatedCell": function (nTd, sData, oData, iRow, iCol) {
				$(nTd).data('colId',columns[iCol-1].id);
				$(nTd).data('rowId',rows[iRow].id);

			}});
	}

	this.headers = headers;
});

TableEditAdapter.method('getHeaders', function() {
	return this.headers;
});

TableEditAdapter.method('createTable', function(elementId) {

	var data = this.getTableData();
	var headers = this.getHeaders();

	if(this.showActionButtons()){
		headers.push({ "sTitle": "", "sClass": "center" });
	}


	if(this.showActionButtons()){
		for(var i=0;i<data.length;i++){
			data[i].push(this.getActionButtonsHtml(data[i][0],data[i]));
		}
	}
	var html = "";
	html = this.getTableTopButtonHtml()+'<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="grid"></table></div>';

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
		"bSort": false,
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
	//$('.tableActionButton').tooltip();
	$('#'+elementId+' #grid').editableTableWidget();

	$('#'+elementId+' #grid .editcell').on('validate', function(evt, newValue) {

		return modJs.validateCellValue($(this), evt, newValue);

	});
});

TableEditAdapter.method('addCellDataUpdate' , function(colId, rowId, data) {

	this.cellDataUpdates[colId+"="+rowId] = [colId, rowId, data];
});

TableEditAdapter.method('addAdditionalRequestData' , function(type, req) {
	return req;
});

TableEditAdapter.method('sendCellDataUpdates' , function() {
	var req = this.cellDataUpdates;
	req.rowTable = this.rowTable;
	req.columnTable = this.columnTable;
	req.valueTable = this.valueTable;
	req = this.addAdditionalRequestData('updateData', req);
	var reqJson = JSON.stringify(req);

	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'updateDataSuccessCallBack';
	callBackData['callBackFail'] = 'updateDataFailCallBack';
	this.showLoader();
	this.customAction('updateData',this.modulePath,reqJson,callBackData);
});

TableEditAdapter.method('updateDataSuccessCallBack', function(callBackData,serverData) {
	this.hideLoader();
	modJs.cellDataUpdates = {};
	modJs.get();
});

TableEditAdapter.method('updateDataFailCallBack', function(callBackData,serverData) {
	this.hideLoader();
});

TableEditAdapter.method('sendAllCellDataUpdates' , function() {

	var req = this.cellDataUpdates;
	req.rowTable = this.rowTable;
	req.columnTable = this.columnTable;
	req.valueTable = this.valueTable;
	req = this.addAdditionalRequestData('updateAllData', req);
	var reqJson = JSON.stringify(req);

	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'updateDataAllSuccessCallBack';
	callBackData['callBackFail'] = 'updateDataAllFailCallBack';
	this.showLoader();
	this.customAction('updateAllData',this.modulePath,reqJson,callBackData);
});

TableEditAdapter.method('updateDataAllSuccessCallBack', function(callBackData,serverData) {
	this.hideLoader();
	modJs.cellDataUpdates = {};
	modJs.getAllData(true);
});

TableEditAdapter.method('updateDataAllFailCallBack', function(callBackData,serverData) {
	this.hideLoader();
});

TableEditAdapter.method('showActionButtons' , function() {
	return false;
});







/**
 * RequestCache
 */

function RequestCache() {

}

RequestCache.method('getKey', function(url,params) {
	var key = url+"|";
	for(index in params){
		key += index+"="+params[index]+"|";
	}
	return key;
});

RequestCache.method('invalidateTable', function(table) {

	var key;
	for (var i = 0; i < localStorage.length; i++){
		key = localStorage.key(i);
		if(key.indexOf('t='+table) > 0){
			localStorage.removeItem(key);
		}

	}
});


RequestCache.method('getData', function(key) {
	var data;

	if (typeof(Storage) == "undefined") {
		return null;
	}

	var strData = localStorage.getItem(key);
	if(strData != undefined && strData != null && strData != ""){
		data =  JSON.parse(strData);
		if(data == undefined || data == null){
			return null;
		}

		if(data.status != undefined && data.status != null && data.status != "SUCCESS"){
			return null;
		}

		return data;
	}

	return null;
});

RequestCache.method('setData', function(key, data) {

	if (typeof(Storage) == "undefined") {
		return null;
	}

	if(data.status != undefined && data.status != null && data.status != "SUCCESS"){
		return null;
	}

	var strData = JSON.stringify(data);
	localStorage.setItem(key,strData);
	return strData;
});


/**
* ObjectAdapter
*/

function ObjectAdapter(endPoint,tab,filter,orderBy){
	this.initAdapter(endPoint,tab,filter,orderBy);
	this.container = null;
	this.loadMoreButton = null;
	this.start = 0;
	this.pageSize = 6;
	this.currentPage = 1;
	this.hasMoreData = true;
	this.searchTerm = "";
	this.searchInput = null;
}

ObjectAdapter.inherits(AdapterBase);

ObjectAdapter.method('getObjectHTML', function(object) {
	var template = this.getCustomTemplate(this.getTemplateName());
	var t = template;
	for(index in object){
		t = t.replace(new RegExp('#_'+index+'_#', 'g'), object[index]);
	}
	return t;

});

ObjectAdapter.method('setPageSize', function(pageSize) {
	this.pageSize = pageSize;
});

ObjectAdapter.method('addDomEvents', function(object) {

});

ObjectAdapter.method('getTemplateName', function() {
	return '';
});

ObjectAdapter.method('renderObject', function(object) {

	var objDom = this.getObjectDom(object.id);

	var html = this.getObjectHTML(object);
	var domObj = $(html);


	if(objDom  != undefined && objDom != null){
		objDom.replace(domObj);
	}else{
		this.container.append(domObj);
	}

	this.addDomEvents(domObj);
});

ObjectAdapter.method('setContainer', function(container) {
	this.container = container;
});

ObjectAdapter.method('setLoadMoreButton', function(loadMoreButton) {
	var that = this;
	this.loadMoreButton = loadMoreButton;
	this.loadMoreButton.off().on('click',function(){
			that.loadMoreButton.attr('disabled','disabled');
			that.loadMore([]);
		}
	);
});

ObjectAdapter.method('showLoadError', function(msg) {
	$("#"+this.getTableName()+"_error").html(msg);
	$("#"+this.getTableName()+"_error").show();
});

ObjectAdapter.method('hideLoadError', function() {
	$("#"+this.getTableName()+"_error").hide();
});

ObjectAdapter.method('setSearchBox', function(searchInput) {
	var that = this;
	this.searchInput = searchInput;
	this.searchInput.off();
	this.searchInput.keydown(function(event){
		var val = $(this).val();
		if ( event.which == 13 ) {
			event.preventDefault();
			that.search([]);
		}else if(( event.which == 8 ||  event.which == 46) && val.length == 1 && that.searchTerm != ''){
			that.search([]);
		}
	});
});

ObjectAdapter.method('getObjectDom', function(id) {
	obj =  this.container.find("#obj_"+id);
	if(obj.length){
		return obj;
	}
	return null;
});

ObjectAdapter.method('loadMore', function(callBackData) {
	if(!this.hasMoreData){
		return;
	}
	this.currentPage++;
	this.get(callBackData, true);
});

ObjectAdapter.method('get', function(callBackData, loadMore) {
	var that = this;

	this.hideLoadError();

	if(!loadMore){
		this.currentPage = 1;
		if(this.container != null){
			this.container.html('');
		}
		this.hasMoreData = true;
		this.tableData = [];
	}

	this.start = (this.currentPage - 1) * this.pageSize;


	this.container = $("#"+this.getTableName()).find('.objectList');

	that.showLoader();


	var url = this.getDataUrl(that.getDataMapping()) +
		"&iDisplayStart="+this.start+"&iDisplayLength="+this.pageSize+"&objects=1";

	if(this.searchTerm != "" && this.searchTerm != undefined && this.searchTerm  != null){
		url += "&sSearch="+this.searchTerm;
	}

	$.post(url, function(data) {
		that.getSuccessCallBack(callBackData,data);
	},"json").always(function() {that.hideLoader()});

	that.initFieldMasterData();

	this.trackEvent("get",this.tab,this.table);
});

ObjectAdapter.method('search', function(callBackData) {
	var that = this;
	this.searchTerm = $("#"+this.getTableName()+"_search").val();

	this.get(callBackData);

});


ObjectAdapter.method('getSuccessCallBack', function(callBackData,serverData) {
	var data = [];

	if(serverData.length == 0 && this.container.html() == ""){
		this.showLoadError("No Results Found !!!");
		return;
	}

	if(this.getFilters() == null){
		$("#"+this.getTableName()+"_filterBtn").hide();
		$("#"+this.getTableName()+"_resetFilters").hide();
	}else{
		$("#"+this.getTableName()+"_filterBtn").show();
		$("#"+this.getTableName()+"_resetFilters").show();
		if(this.currentFilterString != "" && this.currentFilterString != null){
			$("#"+this.getTableName()+"_resetFilters").html(this.currentFilterString+'<i class="fa fa-times"></i>');
		}else{
			$("#"+this.getTableName()+"_resetFilters").html('Reset Filters');
			$("#"+this.getTableName()+"_resetFilters").hide();
		}
	}

	$("#"+this.getTableName()).find('.search-controls').show();
	if(serverData.length > this.pageSize){
		this.hasMoreData = true;
		serverData.pop();
		this.loadMoreButton.removeAttr('disabled');
		this.loadMoreButton.show();
	}else{
		this.hasMoreData = false;
		this.loadMoreButton.hide();
	}

	this.scrollToElementBottom(this.container);
	for(var i=0;i<serverData.length;i++){
		data.push(this.preProcessTableData(serverData[i]));
	}
	this.sourceData = serverData;
	if(callBackData['callBack']!= undefined && callBackData['callBack'] != null){
		if(callBackData['callBackData'] == undefined || callBackData['callBackData'] == null){
			callBackData['callBackData'] = new Array();
		}
		callBackData['callBackData'].push(serverData);
		callBackData['callBackData'].push(data);
		this.callFunction(callBackData['callBack'],callBackData['callBackData']);
	}

	this.tableData = data;

	if(callBackData['noRender']!= undefined && callBackData['noRender'] != null && callBackData['noRender'] == true){

	}else{
		for(var i=0;i<data.length;i++){
			this.renderObject(data[i]);
		}
	}

});
