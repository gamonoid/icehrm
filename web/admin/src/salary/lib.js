/*
Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';

/**
 * SalaryComponentTypeAdapter
 */

class SalaryComponentTypeAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'code',
      'name',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Code' },
      { sTitle: 'Name' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['code', { label: 'Code', type: 'text', validation: '' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
    ];
  }
}


/**
 * SalaryComponentAdapter
 */

class SalaryComponentAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'componentType',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Salary Component Type' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['componentType', { label: 'Salary Component Type', type: 'select2', 'remote-source': ['SalaryComponentType', 'id', 'name'] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }
}


/*
 * EmployeeSalaryAdapter
 */

class EmployeeSalaryAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'component',
      'amount',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Salary Component' },
      { sTitle: 'Amount' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],
      ['component', { label: 'Salary Component', type: 'select2', 'remote-source': ['SalaryComponent', 'id', 'name'] }],
      ['amount', { label: 'Amount', type: 'text', validation: 'float' }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],

    ];
  }
}

module.exports = {
  SalaryComponentTypeAdapter,
  SalaryComponentAdapter,
  EmployeeSalaryAdapter,
};
