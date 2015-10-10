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


/**
 * EmployeeDependentAdapter
 */

function EmployeeDependentAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeDependentAdapter.inherits(AdapterBase);



EmployeeDependentAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "relationship",
	        "dob",
	        "id_number"
	];
});

EmployeeDependentAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Relationship"},
			{ "sTitle": "Date of Birth"},
			{ "sTitle": "Id Number"}
	];
});

EmployeeDependentAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "name", {"label":"Name","type":"text","validation":""}],
	        [ "relationship", {"label":"Relationship","type":"select","source":[["Child","Child"],["Spouse","Spouse"],["Parent","Parent"],["Other","Other"]]}],
	        [ "dob", {"label":"Date of Birth","type":"date","validation":""}],
	        [ "id_number", {"label":"Id Number","type":"text","validation":"none"}]
	];
});
