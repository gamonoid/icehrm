/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import ReactApproveAdminAdapter from '../../../api/ReactApproveAdminAdapter';
import ReactIdNameAdapter from '../../../api/ReactIdNameAdapter';
import { Avatar } from 'antd';

// Overtime hours, computed on the client from the start/end datetimes (no server
// field needed). Returns a 2-decimal string, or '' when times are missing/invalid.
export const overtimeHours = (record) => {
  if (!record || !record.start_time || !record.end_time) return '';
  const start = new Date(String(record.start_time).replace(' ', 'T'));
  const end = new Date(String(record.end_time).replace(' ', 'T'));
  const h = (end.getTime() - start.getTime()) / 3600000;
  return Number.isFinite(h) && h > 0 ? h.toFixed(2) : '';
};

/**
 * OvertimeCategoryAdapter
 */

class OvertimeCategoryAdapter extends ReactIdNameAdapter {
}


/**
 * EmployeeOvertimeAdminAdapter
 */


class EmployeeOvertimeAdminAdapter extends ReactApproveAdminAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.itemName = 'OvertimeRequest';
    this.itemNameLower = 'overtimerequest';
    this.modulePathName = 'overtime';
  }

  getDataMapping() {
    return [
      'id',
      'image',
      'employee',
      'category',
      'start_time',
      'end_time',
      'project',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: '' },
      { sTitle: 'Employee' },
      { sTitle: 'Category' },
      { sTitle: 'Start Time' },
      { sTitle: 'End Time' },
      { sTitle: 'Project' },
      { sTitle: 'Status' },
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
        title: 'Category',
        dataIndex: 'category',
        sorter: true,
      },
      {
        title: 'Start Time',
        dataIndex: 'start_time',
      },
      {
        title: 'End Time',
        dataIndex: 'end_time',
      },
      {
        title: 'Hours',
        render: (text, record) => overtimeHours(record),
      },
      {
        title: 'Project',
        dataIndex: 'project',
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
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['category', {
        label: 'Category', type: 'select2', 'allow-null': false, 'remote-source': ['OvertimeCategory', 'id', 'name'],
      }],
      ['start_time', { label: 'Start Time', type: 'datetime', validation: '' }],
      ['end_time', { label: 'End Time', type: 'datetime', validation: '' }],
      ['project', {
        label: 'Project', type: 'select2', 'allow-null': true, 'null=label': 'none', 'remote-source': ['Project', 'id', 'name'],
      }],
      ['notes', { label: 'Notes', type: 'textarea', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, validation: 'none', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['status', {
        label: 'Status',
        type: 'select',
        'allow-null': true,
        validation: 'none',
        source: [
          ['Pending', 'Pending'],
          ['Approved', 'Approved'],
          ['Rejected', 'Rejected'],
          ['Processing', 'Processing'],
          ['Cancellation Requested', 'Cancellation Requested'],
          ['Cancelled', 'Cancelled'],
        ],
      }],
      ['project', {
        label: 'Project', type: 'select2', 'allow-null': true, validation: 'none', 'remote-source': ['Project', 'id', 'name'],
      }],
    ];
  }
}

module.exports = {
  OvertimeCategoryAdapter,
  EmployeeOvertimeAdminAdapter,
};
