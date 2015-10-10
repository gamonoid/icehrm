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


DashboardAdapter.method('getInitData', function() {
	var that = this;
	var object = {};
	var reqJson = JSON.stringify(object);
	var callBackData = [];
	callBackData['callBackData'] = [];
	callBackData['callBackSuccess'] = 'getInitDataSuccessCallBack';
	callBackData['callBackFail'] = 'getInitDataFailCallBack';
	
	this.customAction('getInitData','admin=dashboard',reqJson,callBackData);
});



DashboardAdapter.method('getInitDataSuccessCallBack', function(data) {

	$("#numberOfEmployees").html(data['numberOfEmployees']+" Employees");
	$("#numberOfCompanyStuctures").html(data['numberOfCompanyStuctures']);
	$("#numberOfUsers").html(data['numberOfUsers']+" Users");
	$("#numberOfProjects").html(data['numberOfProjects']);
	$("#numberOfAttendanceLastWeek").html(data['numberOfAttendanceLastWeek']+" Entries Last Week");
	$("#numberOfLeaves").html(data['numberOfLeaves']);
	$("#numberOfTimeEntries").html(data['numberOfTimeEntries']);
	
});

DashboardAdapter.method('getInitDataFailCallBack', function(callBackData) {
	
});
