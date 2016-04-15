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

function EmployeeSalaryAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeSalaryAdapter.inherits(AdapterBase);



EmployeeSalaryAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "component",
	        "amount",
	        "details"
	];
});

EmployeeSalaryAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Salary Component" },
			{ "sTitle": "Amount"},
			{ "sTitle": "Details"}
	];
});

EmployeeSalaryAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
            [ "component", {"label":"Salary Component","type":"select2","remote-source":["SalaryComponent","id","name"]}],
	        [ "amount", {"label":"Amount","type":"text","validation":"float"}],
	        [ "details", {"label":"Details","type":"textarea","validation":"none"}]
	];
});
