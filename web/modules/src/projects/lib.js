/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';

class EmployeeProjectAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'project',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Project' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['project', { label: 'Project', type: 'select2', 'remote-source': ['Project', 'id', 'name'] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }
}

module.exports = { EmployeeProjectAdapter };
