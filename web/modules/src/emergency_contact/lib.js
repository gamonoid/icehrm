/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';


class EmergencyContactAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'relationship',
      'home_phone',
      'work_phone',
      'mobile_phone',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Relationship' },
      { sTitle: 'Home Phone' },
      { sTitle: 'Work Phone' },
      { sTitle: 'Mobile Phone' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['relationship', { label: 'Relationship', type: 'text', validation: 'none' }],
      ['home_phone', { label: 'Home Phone', type: 'text', validation: 'none' }],
      ['work_phone', { label: 'Work Phone', type: 'text', validation: 'none' }],
      ['mobile_phone', { label: 'Mobile Phone', type: 'text', validation: 'none' }],
    ];
  }
}

module.exports = { EmergencyContactAdapter };
