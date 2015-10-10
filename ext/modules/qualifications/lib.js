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

function EmployeeSkillAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeSkillAdapter.inherits(AdapterBase);



EmployeeSkillAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "skill_id",
	        "details"
	];
});

EmployeeSkillAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Skill" },
			{ "sTitle": "Details"}
	];
});

EmployeeSkillAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "skill_id", {"label":"Skill","type":"select2","allow-null":true,"remote-source":["Skill","id","name"]}],
	        [ "details",  {"label":"Details","type":"textarea","validation":""}]
	];
});





/**
 * EmployeeEducationAdapter
 */

function EmployeeEducationAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeEducationAdapter.inherits(AdapterBase);



EmployeeEducationAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "education_id",
	        "institute",
	        "date_start",
	        "date_end"
	];
});

EmployeeEducationAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID", "bVisible":false},
			{ "sTitle": "Qualification" },
			{ "sTitle": "Institute"},
			{ "sTitle": "Start Date"},
			{ "sTitle": "Completed On"},
	];
});

EmployeeEducationAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "education_id", {"label":"Qualification","type":"select2","allow-null":false,"remote-source":["Education","id","name"]}],
	        [ "institute",  {"label":"Institute","type":"text","validation":""}],
	        [ "date_start", {"label":"Start Date","type":"date","validation":"none"}],
	        [ "date_end", {"label":"Completed On","type":"date","validation":"none"}]
	];
});





/**
 * EmployeeCertificationAdapter
 */

function EmployeeCertificationAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeCertificationAdapter.inherits(AdapterBase);



EmployeeCertificationAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "certification_id",
	        "institute",
	        "date_start",
	        "date_start"
	];
});

EmployeeCertificationAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID","bVisible":false},
			{ "sTitle": "Certification" },
			{ "sTitle": "Institute"},
			{ "sTitle": "Granted On"},
			{ "sTitle": "Valid Thru"},
	];
});

EmployeeCertificationAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "certification_id", {"label":"Certification","type":"select2","allow-null":false,"remote-source":["Certification","id","name"]}],
	        [ "institute",  {"label":"Institute","type":"text","validation":""}],
	        [ "date_start", {"label":"Granted On","type":"date","validation":"none"}],
	        [ "date_end", {"label":"Valid Thru","type":"date","validation":"none"}]
	];
});



/**
 * EmployeeLanguageAdapter
 */

function EmployeeLanguageAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeLanguageAdapter.inherits(AdapterBase);



EmployeeLanguageAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "language_id",
	        "reading",
	        "speaking",
	        "writing",
	        "understanding"
	];
});

EmployeeLanguageAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID", "bVisible":false },
			{ "sTitle": "Language" },
			{ "sTitle": "Reading"},
			{ "sTitle": "Speaking"},
			{ "sTitle": "Writing"},
			{ "sTitle": "Understanding"}
	];
});

EmployeeLanguageAdapter.method('getFormFields', function() {
	
	var compArray = [["Elementary Proficiency","Elementary Proficiency"],
	                 ["Limited Working Proficiency","Limited Working Proficiency"],
	                 ["Professional Working Proficiency","Professional Working Proficiency"],
	                 ["Full Professional Proficiency","Full Professional Proficiency"],
	                 ["Native or Bilingual Proficiency","Native or Bilingual Proficiency"]];
	
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "language_id", {"label":"Language","type":"select2","allow-null":false,"remote-source":["Language","id","name"]}],
	        [ "reading", {"label":"Reading","type":"select","source":compArray}],
	        [ "speaking", {"label":"Speaking","type":"select","source":compArray}],
	        [ "writing", {"label":"Writing","type":"select","source":compArray}],
	        [ "understanding", {"label":"Understanding","type":"select","source":compArray}]
	];
});

