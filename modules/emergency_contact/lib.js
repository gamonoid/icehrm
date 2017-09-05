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


function EmergencyContactAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmergencyContactAdapter.inherits(AdapterBase);



EmergencyContactAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "relationship",
	        "home_phone",
	        "work_phone",
	        "mobile_phone"
	];
});

EmergencyContactAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Relationship"},
			{ "sTitle": "Home Phone"},
			{ "sTitle": "Work Phone"},
			{ "sTitle": "Mobile Phone"}
	];
});

EmergencyContactAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "name", {"label":"Name","type":"text","validation":""}],
	        [ "relationship", {"label":"Relationship","type":"text","validation":"none"}],
	        [ "home_phone", {"label":"Home Phone","type":"text","validation":"none"}],
	        [ "work_phone", {"label":"Work Phone","type":"text","validation":"none"}],
	        [ "mobile_phone", {"label":"Mobile Phone","type":"text","validation":"none"}]
	];
});
