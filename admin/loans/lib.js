/**
 * Author: Thilina Hasantha
 */


/**
 * CompanyLoanAdapter
 */

function CompanyLoanAdapter(endPoint) {
	this.initAdapter(endPoint);
}

CompanyLoanAdapter.inherits(AdapterBase);



CompanyLoanAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "name",
	        "details"
	];
});

CompanyLoanAdapter.method('getHeaders', function() {
	return [
			{ "sTitle": "ID" ,"bVisible":false},
			{ "sTitle": "Name" },
			{ "sTitle": "Details"}
	];
});

CompanyLoanAdapter.method('getFormFields', function() {
	return [
	        [ "id", {"label":"ID","type":"hidden"}],
	        [ "name", {"label":"Name","type":"text","validation":""}],
	        [ "details", {"label":"Details","type":"textarea","validation":"none"}]
	];
});

/*
 * EmployeeCompanyLoanAdapter
 */

function EmployeeCompanyLoanAdapter(endPoint) {
	this.initAdapter(endPoint);
}

EmployeeCompanyLoanAdapter.inherits(AdapterBase);



EmployeeCompanyLoanAdapter.method('getDataMapping', function() {
	return [
	        "id",
	        "employee",
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
			{ "sTitle": "Employee" },
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
	        [ "employee", {"label":"Employee","type":"select2","remote-source":["Employee","id","first_name+last_name"]}],
	        [ "loan", {"label":"Loan Type","type":"select","remote-source":["CompanyLoan","id","name"]}],
	        [ "start_date", {"label":"Loan Start Date","type":"date","validation":""}],
	        [ "last_installment_date", {"label":"Last Installment Date","type":"date","validation":"none"}],
	        [ "period_months", {"label":"Loan Period (Months)","type":"text","validation":"number"}],
	        [ "currency", {"label":"Currency","type":"select2","remote-source":["CurrencyType","id","name"]}],
	        [ "amount", {"label":"Loan Amount","type":"text","validation":"float"}],
	        [ "monthly_installment", {"label":"Monthly Installment","type":"text","validation":"float"}],
	        [ "status", {"label":"Status","type":"select","source":[["Approved","Approved"],["Paid","Paid"],["Suspended","Suspended"]]}],
	        [ "details", {"label":"Details","type":"textarea","validation":"none"}]
	];
});

EmployeeCompanyLoanAdapter.method('getFilters', function() {
	return [
	        [ "employee", {"label":"Employee","type":"select2","allow-null":true,"null-label":"All Employees","remote-source":["Employee","id","first_name+last_name"]}],
	        [ "loan", {"label":"Loan Type","type":"select","allow-null":true,"null-label":"All Loan Types","remote-source":["CompanyLoan","id","name"]}],
	        
	];
});
