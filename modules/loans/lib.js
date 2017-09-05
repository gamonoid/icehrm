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

function EmployeeCompanyLoanAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeCompanyLoanAdapter.inherits(AdapterBase);



EmployeeCompanyLoanAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "loan",
	        "start_date",
	        "period_months",
	        "currency",
	        "amount",
	        "status"
	];
});

EmployeeCompanyLoanAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Loan Type" },
			{ "sTitle": "Loan Start Date"},
			{ "sTitle": "Loan Period (Months)"},
			{ "sTitle": "Currency"},
			{ "sTitle": "Amount"},
			{ "sTitle": "Status"}
	];
});

EmployeeCompanyLoanAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "loan", {"label":"Loan Type","type":"placeholder","remote-source":["CompanyLoan","id","name"]}],
	        [ "start_date", {"label":"Loan Start Date","type":"placeholder","validation":""}],
	        [ "last_installment_date", {"label":"Last Installment Date","type":"placeholder","validation":"none"}],
	        [ "period_months", {"label":"Loan Period (Months)","type":"placeholder","validation":"number"}],
	        [ "currency", {"label":"Currency","type":"placeholder","remote-source":["CurrencyType","id","name"]}],
	        [ "amount", {"label":"Loan Amount","type":"placeholder","validation":"float"}],
	        [ "monthly_installment", {"label":"Monthly Installment","type":"placeholder","validation":"float"}],
	        [ "status", {"label":"Status","type":"placeholder","source":[["Approved","Approved"],["Paid","Paid"],["Suspended","Suspended"]]}],
	        [ "details", {"label":"Details","type":"placeholder","validation":"none"}]
	];
});


EmployeeCompanyLoanAdapter.method('getActionButtonsHtml', function(id,data) {	
	var editButton = '<img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;" rel="tooltip" title="View" onclick="modJs.edit(_id_);return false;"></img>';
	var deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
	var html = '<div style="width:80px;">_edit__delete_</div>';
	
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
