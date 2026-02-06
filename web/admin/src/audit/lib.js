/**
 * Author: Thilina Hasantha
 */


/**
 * AuditAdapter
 */

/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';

class AuditAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'time',
      'user',
      'employee',
      'type',
      'ip',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Time (GMT)' },
      { sTitle: 'User' },
      { sTitle: 'Logged In Employee' },
      { sTitle: 'Type' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Time (GMT)',
        dataIndex: 'time',
        sorter: true,
      },
      {
        title: 'User',
        dataIndex: 'user',
        sorter: true,
      },
      {
        title: 'Employee',
        dataIndex: 'employee',
      },
      {
        title: 'IP',
        dataIndex: 'ip',
      },
      {
        title: 'Type',
        dataIndex: 'type',
      },
      {
        title: 'Details',
        dataIndex: 'details',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['time', { label: 'Time (GMT)', type: 'placeholder', validation: 'none' }],
      ['user', {
        label: 'User', type: 'placeholder', validation: 'none', 'remote-source': ['User', 'id', 'username'],
      }],
      ['ip', { label: 'IP Address', type: 'placeholder', validation: 'none' }],
      ['employee', { label: 'Logged In Employee', type: 'placeholder', validation: 'none' }],
      ['type', { label: 'Type', type: 'placeholder', validation: 'none' }],
      ['details', { label: 'Details', type: 'placeholder', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['user', {
        label: 'User', type: 'select2', validation: '', 'allow-null': false, 'remote-source': ['User', 'id', 'username'],
      }],
    ];
  }
}

class EmailLogAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'subject',
      'toEmail',
      'created',
      'updated',
      'status',
    ];
  }

  getHeaders() {
    return [
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Added',
        dataIndex: 'created',
        sorter: true,
      },
      {
        title: 'Subject',
        dataIndex: 'subject',
        sorter: true,
      },
      {
        title: 'Sent To',
        dataIndex: 'toEmail',
        sorter: true,
      },
      {
        title: 'Updated',
        dataIndex: 'updated',
        sorter: true,
      },
      {
        title: 'Status',
        dataIndex: 'status',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['subject', { label: 'Subject', type: 'placeholder', validation: 'none' }],
      ['body', { label: 'Email', type: 'quill', validation: 'none' }],
    ];
  }

  modifyObjectBeforeView( object, viewOnly ) {
    object.body = atob(object.body);
    object.body = object.body.replace(/\n/g, '');
    return object;
  }

  getWidth() {
    return 1100;
  }
}


module.exports = {
  AuditAdapter,
  EmailLogAdapter,
};
