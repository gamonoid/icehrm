/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import ReactModalAdapterBase from "../../../api/ReactModalAdapterBase";
import { Avatar } from "antd";

/**
 * CourseAdapter
 */

class CourseAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'code',
      'name',
      'coordinator',
      'trainer',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Code' },
      { sTitle: 'Name' },
      { sTitle: 'Coordinator' },
      { sTitle: 'Trainer' },
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
      },
      {
        title: 'Coordinator',
        dataIndex: 'coordinator',
      },
      {
        title: 'Trainer',
        dataIndex: 'trainer',
      },
    ];
  }

  getViewModeEnabledFields() {
    return ['description'];
  }

  getViewModeShowLabel() {
    return false;
  }

  getFormLayout(viewOnly) {
    return viewOnly ? 'vertical' : 'horizontal';
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['code', { label: 'Code', type: 'text', validation: '' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['description', { label: 'Details', type: 'quill', validation: '' }],
      ['coordinator', {
        label: 'Coordinator', type: 'select2', 'allow-null': false, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['trainer', { label: 'Trainer', type: 'text', validation: 'none' }],
      ['trainer_info', { label: 'Trainer Details', type: 'textarea', validation: 'none' }],
      // ['paymentType', { label: 'Payment Type', type: 'select', source: [['Company Sponsored', 'Company Sponsored'], ['Paid by Employee', 'Paid by Employee']] }],
      // ['currency', { label: 'Currency', type: 'select2', 'remote-source': ['CurrencyType', 'code', 'code+name'] }],
      // ['cost', { label: 'Cost', type: 'text', validation: 'float' }],
      ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['Inactive', 'Inactive']] }],
    ];
  }

  getHelpLink() {
    return 'https://icehrm.com/explore/docs/training-module/';
  }
}


class TrainingSessionAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'course',
      'scheduled',
      'status',
      'deliveryMethod',
      'deliveryLocation',
      'attendanceType',
      'requireProof',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Course' },
      { sTitle: 'Scheduled Time' },
      { sTitle: 'Status' },
      { sTitle: 'Training Type' },
      { sTitle: 'Location' },
      { sTitle: 'Registration Method' },
      { sTitle: 'Training Certificate Required' },
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
        title: 'Course',
        dataIndex: 'course',
        sorter: true,
      },
      {
        title: 'Scheduled Time',
        dataIndex: 'scheduled',
        sorter: true,
      },
      {
        title: 'Status',
        dataIndex: 'status',
      },
      {
        title: 'Training Type',
        dataIndex: 'deliveryMethod',
      },
      {
        title: 'Location',
        dataIndex: 'deliveryLocation',
      },
      {
        title: 'Registration Method',
        dataIndex: 'attendanceType',
      },
      {
        title: 'Training Certificate Required',
        dataIndex: 'requireProof',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['course', { label: 'Course', type: 'select2', 'remote-source': ['Course', 'id', 'name+code'] }],
      ['description', { label: 'Details', type: 'textarea', validation: 'none' }],
      ['scheduled', { label: 'Scheduled Time', type: 'datetime', validation: '' }],
      ['dueDate', { label: 'Assignment Due Date', type: 'date', validation: 'none' }],
      ['deliveryMethod', { label: 'Training Type', type: 'select', source: [['Classroom', 'Classroom'], ['Self Study', 'Self Study'], ['Online', 'Online']] }],
      ['deliveryLocation', { label: 'Location', type: 'text', validation: 'none' }],
      // [ "status", {"label":"Status","type":"select","source":[["Pending","Pending"],["Approved","Approved"],["Completed","Completed"],["Cancelled","Cancelled"]]}],
      ['attendanceType', { label: 'Registration Method', type: 'select', source: [['Sign Up', 'Sign Up'], ['Assign', 'Assign']] }],
      ['attachment', { label: 'Attachment', type: 'fileupload', validation: 'none' }],
      ['requireProof', { label: 'Training Certificate Required', type: 'select', source: [['Yes', 'Yes'], ['No', 'No']] }],
    ];
  }

  forceInjectValuesBeforeSave(params) {
    if (params.status === undefined || params.status === null || params.status === '') {
      params.status = 'Approved';
    }
    return params;
  }

  getHelpLink() {
    return 'https://icehrm.com/explore/docs/training-module/';
  }
}


class EmployeeTrainingSessionAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'image',
      'employee',
      'trainingSession',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: '' },
      { sTitle: 'Employee' },
      { sTitle: 'Training Session' },
      { sTitle: 'Status' },
    ];
  }

  getFilters() {
    return [
      ['trainingSession', {
        label: 'Training Session', type: 'select', 'allow-null': true, 'remote-source': ['TrainingSession', 'id', 'name'],
      }],
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': true,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
    ];
  }

  getFormFields() {
    // if(this.user.user_level == "Admin") {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['trainingSession', {
        label: 'Training Session',
        type: 'select2',
        'allow-null': false,
        'remote-source': ['TrainingSession', 'id', 'name'],
      }],
      ['status', {
        label: 'Status',
        type: 'select',
        sort: 'none',
        source: [['Scheduled', 'Scheduled'], ['Attended', 'Attended'], ['Not-Attended', 'Not-Attended']],
      }],
    ];
  }

  getTableColumns() {
    return [
      {
        title: '',
        dataIndex: 'image',
        render: (text, record) => <Avatar src={text} />,
      },
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
      {
        title: 'Training Session',
        dataIndex: 'trainingSession',
        sorter: true,
      },
      {
        title: 'Status',
        dataIndex: 'status',
      },
    ];
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }

  forceInjectValuesBeforeSave(params) {
    if (params.status === undefined || params.status === null || params.status === '') {
      params.status = 'Scheduled';
    }
    return params;
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/training-and-reviews/training#subscribing-to-a-training-session';
  }
}

module.exports = {
  CourseAdapter,
  TrainingSessionAdapter,
  EmployeeTrainingSessionAdapter,
};
