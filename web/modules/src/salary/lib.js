/*
Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import AdapterBase from '../../../api/AdapterBase';

class EmployeeSalaryAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'component',
      'amount',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Salary Component' },
      { sTitle: 'Amount' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['component', { label: 'Salary Component', type: 'select2', 'remote-source': ['SalaryComponent', 'id', 'name'] }],
      ['amount', { label: 'Amount', type: 'text', validation: 'float' }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }
}

module.exports = { EmployeeSalaryAdapter };
