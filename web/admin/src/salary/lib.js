/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

//import AdapterBase from '../../../api/AdapterBase';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';

/**
 * SalaryComponentTypeAdapter
 */

class SalaryComponentTypeAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.fieldNameMap = {};
    this.hiddenFields = {};
    this.tableFields = {};
    this.formOnlyFields = {};
  }

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

  getTableColumns() {
    return [
      {
        title: 'Code',
        dataIndex: 'code',
        sorter: true,
      },
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      }
    ];
  }

  getFormFields() {
    if(this.showSave){
      return [
        ['id', { label: 'ID', type: 'hidden' }],
        ['code', { label: 'Code', type: 'text', validation: '' }],
        ['name', { label: 'Name', type: 'text', validation: '' }],
      ];
    }
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

class SalaryComponentAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.fieldNameMap = {};
    this.hiddenFields = {};
    this.tableFields = {};
    this.formOnlyFields = {};
  }

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

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Salary Component Type',
        dataIndex: 'componentType',
        sorter: true,
      },
      {
        title: 'Details',
        dataIndex: 'details',
        sorter: true,
      }
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

class EmployeeSalaryAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.fieldNameMap = {};
    this.hiddenFields = {};
    this.tableFields = {};
    this.formOnlyFields = {};
  }

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

  getTableColumns() {
    return [
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
      {
        title: 'Salary Component',
        dataIndex: 'component',
        sorter: true,
      },
      {
        title: 'Amount',
        dataIndex: 'amount',
        sorter: true,
      },
      {
        title: 'Details',
        dataIndex: 'details',
        sorter: true,
      }
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
