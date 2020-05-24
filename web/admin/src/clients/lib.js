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

module.exports = {
  ClientAdapter,
};
