/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';
import ApproveAdminAdapter from '../../../api/ApproveAdminAdapter';

/**
 * OvertimeCategoryAdapter
 */

class OvertimeCategoryAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
    ];
  }
}


/**
 * EmployeeOvertimeAdminAdapter
 */


class EmployeeOvertimeAdminAdapter extends ApproveAdminAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.itemName = 'OvertimeRequest';
    this.itemNameLower = 'overtimerequest';
    this.modulePathName = 'overtime';
  }

  getDataMapping() {
    return [
      'id',
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
      { sTitle: 'Employee' },
      { sTitle: 'Category' },
      { sTitle: 'Start Time' },
      { sTitle: 'End Time' },
      { sTitle: 'Project' },
      { sTitle: 'Status' },
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
}

module.exports = {
  OvertimeCategoryAdapter,
  EmployeeOvertimeAdminAdapter,
};
