/**
 * Author: Thilina Hasantha
 */
function BaseGraphAdapter(endPoint) {
	this.initAdapter(endPoint);
}

BaseGraphAdapter.inherits(AdapterBase);

BaseGraphAdapter.method('getDataMapping', function() {
	return [];
});

BaseGraphAdapter.method('getHeaders', function() {
	return [];
});

BaseGraphAdapter.method('getFormFields', function() {
	return [];
});

BaseGraphAdapter.method('createTable', function(elementId) {
	
});

/*
 * TimeUtilizationGraphAdapter
 */


function AttendanceGraphAdapter(endPoint) {
	this.initAdapter(endPoint);
}

AttendanceGraphAdapter.inherits(BaseGraphAdapter);

AttendanceGraphAdapter.method('getFormFields', function() {
	return [];
});

AttendanceGraphAdapter.method('getFilters', function() {
	return [
	        [ "employee", {"label":"Employee","type":"select2","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],
	        [ "start", {"label":"Start Date","type":"date","validation":""}],
	        [ "end", {"label":"End Date","type":"date","validation":""}]
	        
	];
});

AttendanceGraphAdapter.method('get', function() {
	this.initFieldMasterData();
	this.getTimeUtilization();
});

AttendanceGraphAdapter.method('doCustomFilterValidation', function(params) {
	$("#"+this.table+"_filter_error").html("");
	$("#"+this.table+"_filter_error").hide();
	if(Date.parse(params.start).getTime() > Date.parse(params.end).getTime()){
		$("#"+this.table+"_filter_error").html("End date should be a later date than start date");
		$("#"+this.table+"_filter_error").show();
		return false;
	}
	
	var dateDiff = (Date.parse(params.end).getTime() - Date.parse(params.start).getTime())/(1000*60*60*24);
	
	if(dateDiff > 45 && (params['employee'] == undefined || params['employee'] == null || params['employee'] == "NULL")){
		$("#"+this.table+"_filter_error").html("Differance between start and end dates should not be more than 45 days, when creating chart for all employees");
		$("#"+this.table+"_filter_error").show();
		return false;
		
	}else if(dateDiff > 90){
		$("#"+this.table+"_filter_error").html("Differance between start and end dates should not be more than 90 days");
		$("#"+this.table+"_filter_error").show();
		return false;
	}
	
	return true;
});

AttendanceGraphAdapter.method('getTimeUtilization', function(object,callBackData) {
	var that = this;
	object = {};
	
	
	if(this.filter != null && this.filter != undefined){
		if(this.filter.employee != "NULL"){
			object['employee'] = this.filter.employee;
		}
		
		object['start'] = this.filter.start;
		object['end'] = this.filter.end;
	}
	
	var reqJson = JSON.stringify(object);
	
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getAttendanceSuccessCallBack';
	callBackData['callBackFail'] = 'getAttendanceFailCallBack';
	
	this.customAction('getAttendance','admin=charts',reqJson,callBackData);
});


AttendanceGraphAdapter.method('getAttendanceFailCallBack', function(callBackData) {
	this.showMessage("Error Occured while getting data for chart", callBackData);
});


AttendanceGraphAdapter.method('getAttendanceSuccessCallBack', function(callBackData) {

	var that = this;
	var chart;
	
	var filterHtml = that.getTableTopButtonHtml();
	$("#tabPageAttendanceGraph svg").remove();
	$("#tabPageAttendanceGraph div").remove();
    $("#tabPageAttendanceGraph").html("");
    $("#tabPageAttendanceGraph").html(filterHtml+"<svg></svg>");
    
 
	nv.addGraph(function() {
		
		var chart = nv.models.multiBarChart()
		  .margin({bottom: 200})
	      .transitionDuration(0)
	      .reduceXTicks(true)   //If 'false', every single x-axis tick label will be rendered.
	      .rotateLabels(45)      //Angle to rotate x-axis labels.
	      .showControls(false)   //Allow user to switch between 'Grouped' and 'Stacked' mode.
	      .groupSpacing(0.1)    //Distance between each group of bars.
	    ;

	    chart.yAxis
	        .tickFormat(d3.format(',.1f'));
		
	    chart.dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });

	    chart.tooltip(function (key, x, y, e, graph) {
	        return '<p><strong>' + key + '</strong></p>' +
	        '<p>' + y + ' on ' + x + '</p>';
	    });
	    

	    d3.select('#tabPageAttendanceGraph svg')
	        .datum(callBackData)
	       .call(chart);
	    
	    return chart;
	});

	
});

/*
 * TimeUtilizationGraphAdapter
 */


function TimeUtilizationGraphAdapter(endPoint) {
	this.initAdapter(endPoint);
}

TimeUtilizationGraphAdapter.inherits(BaseGraphAdapter);

TimeUtilizationGraphAdapter.method('getFormFields', function() {
	return [];
});

TimeUtilizationGraphAdapter.method('getFilters', function() {
	return [
	        [ "employee", {"label":"Employee","type":"select2","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],
	        [ "start", {"label":"Start Date","type":"date","validation":""}],
	        [ "end", {"label":"End Date","type":"date","validation":""}]
	        
	];
});

TimeUtilizationGraphAdapter.method('get', function() {
	this.initFieldMasterData();
	this.getTimeUtilization();
});

TimeUtilizationGraphAdapter.method('doCustomFilterValidation', function(params) {
	$("#"+this.table+"_filter_error").html("");
	$("#"+this.table+"_filter_error").hide();
	if(Date.parse(params.start).getTime() > Date.parse(params.end).getTime()){
		$("#"+this.table+"_filter_error").html("End date should be a later date than start date");
		$("#"+this.table+"_filter_error").show();
		return false;
	}
	
	var dateDiff = (Date.parse(params.end).getTime() - Date.parse(params.start).getTime())/(1000*60*60*24);
	
	if(dateDiff > 45 && (params['employee'] == undefined || params['employee'] == null || params['employee'] == "NULL")){
		$("#"+this.table+"_filter_error").html("Differance between start and end dates should not be more than 45 days, when creating chart for all employees");
		$("#"+this.table+"_filter_error").show();
		return false;
		
	}else if(dateDiff > 90){
		$("#"+this.table+"_filter_error").html("Differance between start and end dates should not be more than 90 days");
		$("#"+this.table+"_filter_error").show();
		return false;
	}
	
	return true;
});

TimeUtilizationGraphAdapter.method('getTimeUtilization', function(object,callBackData) {
	var that = this;
	object = {};
	
	
	if(this.filter != null && this.filter != undefined){
		if(this.filter.employee != "NULL"){
			object['employee'] = this.filter.employee;
		}
		
		object['start'] = this.filter.start;
		object['end'] = this.filter.end;
	}
	
	var reqJson = JSON.stringify(object);
	
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getTimeUtilizationSuccessCallBack';
	callBackData['callBackFail'] = 'getTimeUtilizationFailCallBack';
	
	this.customAction('getTimeUtilization','admin=charts',reqJson,callBackData);
});


TimeUtilizationGraphAdapter.method('getTimeUtilizationFailCallBack', function(callBackData) {
	this.showMessage("Error Occured while getting data for chart", callBackData);
});


TimeUtilizationGraphAdapter.method('getTimeUtilizationSuccessCallBack', function(callBackData) {

	var that = this;
	var chart;
	
	var filterHtml = that.getTableTopButtonHtml();
	$("#tabPageTimeUtilizationGraph svg").remove();
	$("#tabPageTimeUtilizationGraph div").remove();
    $("#tabPageTimeUtilizationGraph").html("");
    $("#tabPageTimeUtilizationGraph").html(filterHtml+"<svg></svg>");
    
	nv.addGraph(function() {
		
		var chart = nv.models.multiBarChart()
		  .margin({bottom: 200})
	      .transitionDuration(0)
	      .reduceXTicks(true)   //If 'false', every single x-axis tick label will be rendered.
	      .rotateLabels(45)      //Angle to rotate x-axis labels.
	      .showControls(true)   //Allow user to switch between 'Grouped' and 'Stacked' mode.
	      .groupSpacing(0.1)    //Distance between each group of bars.
	    ;

	    chart.yAxis
	        .tickFormat(d3.format(',.1f'));
		
	    

	    d3.select('#tabPageTimeUtilizationGraph svg')
	        .datum(callBackData)
	       .call(chart);

	    chart.dispatch.on('stateChange', function(e) { nv.log('New State:', JSON.stringify(e)); });

	    chart.tooltip(function (key, x, y, e, graph) {
	        return '<p><strong>' + key + '</strong></p>' +
	        '<p>' + y + ' on ' + x + '</p>';
	    });
	    
	    return chart;
	});

	
});




