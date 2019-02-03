/*
Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';

/**
 * EmployeeDependentAdapter
 */

class EmployeeDependentAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'relationship',
      'dob',
      'id_number',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Relationship' },
      { sTitle: 'Date of Birth' },
      { sTitle: 'Id Number' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['relationship', { label: 'Relationship', type: 'select', source: [['Child', 'Child'], ['Spouse', 'Spouse'], ['Parent', 'Parent'], ['Other', 'Other']] }],
      ['dob', { label: 'Date of Birth', type: 'date', validation: '' }],
      ['id_number', { label: 'Id Number', type: 'text', validation: 'none' }],
    ];
  }
}

module.exports = { EmployeeDependentAdapter };
