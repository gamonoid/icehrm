/*
This file is part of iCE Hrm.

iCE Hrm is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

iCE Hrm is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with iCE Hrm. If not, see <http://www.gnu.org/licenses/>.

------------------------------------------------------------------

Original work Copyright (c) 2012 [Gamonoid Media Pvt. Ltd]  
Developer: Thilina Hasantha (thilina.hasantha[at]gmail.com / facebook.com/thilinah)
 */

function DashboardAdapter(endPoint) {
	this.initAdapter(endPoint);
}

DashboardAdapter.inherits(AdapterBase);



DashboardAdapter.method('getDataMapping', function() {
	return [];
});

DashboardAdapter.method('getHeaders', function() {
	return [];
});

DashboardAdapter.method('getFormFields', function() {
	return [];
});


DashboardAdapter.method('get', function(callBackData) {
});


DashboardAdapter.method('getPunch', function() {
	var that = this;
	var object = {};
	
	object['date'] = this.getClientDate(new Date()).toISOString().slice(0, 19).replace('T', ' ');
	object['offset'] = this.getClientGMTOffset();
	var reqJson = JSON.stringify(object);
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getPunchSuccessCallBack';
	callBackData['callBackFail'] = 'getPunchFailCallBack';
	
	this.customAction('getPunch','modules=attendance',reqJson,callBackData);
});



DashboardAdapter.method('getPunchSuccessCallBack', function(callBackData) {
	var punch = callBackData;
	if(punch == null){
		$("#lastPunchTime").html("Not");
		$("#punchTimeText").html("Punched In");
	}else{
		$("#lastPunchTime").html(Date.parse(punch.in_time).toString('h:mm tt'));
		$("#punchTimeText").html("Punched In");
	}	
});

DashboardAdapter.method('getPunchFailCallBack', function(callBackData) {
	
});


DashboardAdapter.method('getPendingLeaves', function() {
	var that = this;
	var object = {};

	var reqJson = JSON.stringify(object);
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getPendingLeavesSuccessCallBack';
	callBackData['callBackFail'] = 'getPendingLeavesFailCallBack';
	
	this.customAction('getPendingLeaves','modules=dashboard',reqJson,callBackData);
});



DashboardAdapter.method('getPendingLeavesSuccessCallBack', function(callBackData) {
	var leaveCount = callBackData;
	$("#pendingLeaveCount").html(leaveCount);
});

DashboardAdapter.method('getPendingLeavesFailCallBack', function(callBackData) {
	
});

DashboardAdapter.method('getLastTimeSheetHours', function() {
	var that = this;
	var object = {};

	var reqJson = JSON.stringify(object);
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getLastTimeSheetHoursSuccessCallBack';
	callBackData['callBackFail'] = 'getLastTimeSheetHoursFailCallBack';
	
	this.customAction('getLastTimeSheetHours','modules=dashboard',reqJson,callBackData);
});



DashboardAdapter.method('getLastTimeSheetHoursSuccessCallBack', function(callBackData) {
	var hours = callBackData;
	$("#timeSheetHoursWorked").html(hours);
});

DashboardAdapter.method('getLastTimeSheetHoursFailCallBack', function(callBackData) {
	
});



DashboardAdapter.method('getEmployeeActiveProjects', function() {
	var that = this;
	var object = {};

	var reqJson = JSON.stringify(object);
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getEmployeeActiveProjectsSuccessCallBack';
	callBackData['callBackFail'] = 'getEmployeeActiveProjectsFailCallBack';
	
	this.customAction('getEmployeeActiveProjects','modules=dashboard',reqJson,callBackData);
});



DashboardAdapter.method('getEmployeeActiveProjectsSuccessCallBack', function(callBackData) {
	var hours = callBackData;
	$("#numberOfProjects").html(hours);
});

DashboardAdapter.method('getEmployeeActiveProjectsFailCallBack', function(callBackData) {
	
});

DashboardAdapter.method('getClientDate', function (date) {

	var offset = this.getClientGMTOffset();
    var tzDate = date.addMinutes(offset*60);
    return tzDate;

});

DashboardAdapter.method('getClientGMTOffset', function () {
	
	var rightNow = new Date();
	var jan1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);
	var temp = jan1.toGMTString();
	var jan2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
	var std_time_offset = (jan1 - jan2) / (1000 * 60 * 60);
	
	return std_time_offset;
	
});
