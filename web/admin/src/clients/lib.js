/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';

/**
 * ClientAdapter
 */

class ClientAdapter extends ReactModalAdapterBase {
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


  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Details',
        dataIndex: 'details',
        sorter: true,
      },
      {
        title: 'Address',
        dataIndex: 'address',
        sorter: true,
      },
      {
        title: 'Contact Number',
        dataIndex: 'contact_number',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    if (this.showSave) {
      return this.addCustomFields([
        ['id', { label: 'ID', type: 'hidden' }],
        ['name', { label: 'Name', type: 'text' }],
        ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
        ['address', { label: 'Address', type: 'textarea', validation: 'none' }],
        ['contact_number', { label: 'Contact Number', type: 'text', validation: 'none' }],
        ['contact_email', { label: 'Contact Email', type: 'text', validation: 'none' }],
        ['company_url', { label: 'Company Url', type: 'text', validation: 'none' }],
        ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['Inactive', 'Inactive']] }],
        ['first_contact_date', { label: 'First Contact Date', type: 'date', validation: 'none' }],
      ]);
    }
    return this.addCustomFields([
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'placeholder' }],
      ['details', { label: 'Details', type: 'placeholder', validation: 'none' }],
      ['address', { label: 'Address', type: 'placeholder', validation: 'none' }],
      ['contact_number', { label: 'Contact Number', type: 'placeholder', validation: 'none' }],
      ['contact_email', { label: 'Contact Email', type: 'placeholder', validation: 'none' }],
      ['company_url', { label: 'Company Url', type: 'placeholder', validation: 'none' }],
      ['status', { label: 'Status', type: 'placeholder', source: [['Active', 'Active'], ['Inactive', 'Inactive']] }],
      ['first_contact_date', { label: 'First Contact Date', type: 'placeholder', validation: 'none' }],
    ]);
  }

  getHelpTitle() {
    return this.gt('Clients of Your Organization');
  }

  getHelpDescription() {
    return this.gt('Here you can add and manage clients your organization is working with. These clients can be attached to Projects.');
  }

  getHelpLink() {
    return 'https://icehrm.com/explore/docs/projects-and-clients-for-timesheets/';
  }
}

module.exports = {
  ClientAdapter,
};
