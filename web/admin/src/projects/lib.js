/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';

/**
 * ClientAdapter
 */

class ClientAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'details',
      'address',
      'contact_number',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Details' },
      { sTitle: 'Address' },
      { sTitle: 'Contact Number' },
    ];
  }

  getFormFields() {
    if (this.showSave) {
      return [
        ['id', { label: 'ID', type: 'hidden' }],
        ['name', { label: 'Name', type: 'text' }],
        ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
        ['address', { label: 'Address', type: 'textarea', validation: 'none' }],
        ['contact_number', { label: 'Contact Number', type: 'text', validation: 'none' }],
        ['contact_email', { label: 'Contact Email', type: 'text', validation: 'none' }],
        ['company_url', { label: 'Company Url', type: 'text', validation: 'none' }],
        ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['Inactive', 'Inactive']] }],
        ['first_contact_date', { label: 'First Contact Date', type: 'date', validation: 'none' }],
      ];
    }
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'placeholder' }],
      ['details', { label: 'Details', type: 'placeholder', validation: 'none' }],
      ['address', { label: 'Address', type: 'placeholder', validation: 'none' }],
      ['contact_number', { label: 'Contact Number', type: 'placeholder', validation: 'none' }],
      ['contact_email', { label: 'Contact Email', type: 'placeholder', validation: 'none' }],
      ['company_url', { label: 'Company Url', type: 'placeholder', validation: 'none' }],
      ['status', { label: 'Status', type: 'placeholder', source: [['Active', 'Active'], ['Inactive', 'Inactive']] }],
      ['first_contact_date', { label: 'First Contact Date', type: 'placeholder', validation: 'none' }],
    ];
  }

  getHelpLink() {
    return 'http://blog.icehrm.com/docs/projects/';
  }
}


/**
 * ProjectAdapter
 */

class ProjectAdapter extends AdapterBase {
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


class EmployeeProjectAdapter extends AdapterBase {
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
  ClientAdapter,
  ProjectAdapter,
  EmployeeProjectAdapter,
};
