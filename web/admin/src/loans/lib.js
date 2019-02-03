/*
Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import AdapterBase from '../../../api/AdapterBase';

/**
 * CompanyLoanAdapter
 */

class CompanyLoanAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }
}


/*
 * EmployeeCompanyLoanAdapter
 */

class EmployeeCompanyLoanAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'loan',
      'start_date',
      'period_months',
      'currency',
      'amount',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Loan Type' },
      { sTitle: 'Loan Start Date' },
      { sTitle: 'Loan Period (Months)' },
      { sTitle: 'Currency' },
      { sTitle: 'Amount' },
      { sTitle: 'Status' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],
      ['loan', { label: 'Loan Type', type: 'select', 'remote-source': ['CompanyLoan', 'id', 'name'] }],
      ['start_date', { label: 'Loan Start Date', type: 'date', validation: '' }],
      ['last_installment_date', { label: 'Last Installment Date', type: 'date', validation: 'none' }],
      ['period_months', { label: 'Loan Period (Months)', type: 'text', validation: 'number' }],
      ['currency', { label: 'Currency', type: 'select2', 'remote-source': ['CurrencyType', 'id', 'name'] }],
      ['amount', { label: 'Loan Amount', type: 'text', validation: 'float' }],
      ['monthly_installment', { label: 'Monthly Installment', type: 'text', validation: 'float' }],
      ['status', { label: 'Status', type: 'select', source: [['Approved', 'Approved'], ['Paid', 'Paid'], ['Suspended', 'Suspended']] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'null-label': 'All Employees', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['loan', {
        label: 'Loan Type', type: 'select', 'allow-null': true, 'null-label': 'All Loan Types', 'remote-source': ['CompanyLoan', 'id', 'name'],
      }],

    ];
  }
}

module.exports = {
  CompanyLoanAdapter,
  EmployeeCompanyLoanAdapter,
};
