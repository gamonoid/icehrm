/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';

/**
 * ProjectAdapter
 */

class ProjectAdapter extends ReactModalAdapterBase {
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
      'client',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Client' },
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
        title: 'Client',
        dataIndex: 'client',
        sorter: true,
      }
    ];
  }

  getFormFields() {
    if (this.showSave) {
      return [
        ['id', { label: 'ID', type: 'hidden' }],
        ['name', { label: 'Name', type: 'text' }],
        ['client', {
          label: 'Client', type: 'select2', 'allow-null': true, 'remote-source': ['Client', 'id', 'name'],
        }],
        ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
        ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['On Hold', 'On Hold'], ['Completed', 'Completed'], ['Dropped', 'Dropped']] }],
      ];
    }
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'placeholder' }],
      ['client', {
        label: 'Client', type: 'placeholder', 'allow-null': true, 'remote-source': ['Client', 'id', 'name'],
      }],
      ['details', { label: 'Details', type: 'placeholder', validation: 'none' }],
      ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['On Hold', 'On Hold'], ['Completed', 'Completed'], ['Dropped', 'Dropped']] }],
    ];
  }

  getHelpLink() {
    return 'http://blog.icehrm.com/docs/projects/';
  }
}


/*
 * EmployeeProjectAdapter
 */


class EmployeeProjectAdapter extends ReactModalAdapterBase {
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
      'project',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Project' },
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
        title: 'Project',
        dataIndex: 'project',
        sorter: true,
      }
    ];
  }


  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],
      ['project', { label: 'Project', type: 'select2', 'remote-source': ['Project', 'id', 'name'] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],

    ];
  }

  getHelpLink() {
    return 'http://blog.icehrm.com/docs/projects/';
  }
}

module.exports = {
  ProjectAdapter,
  EmployeeProjectAdapter,
};
