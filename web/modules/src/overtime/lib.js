/*
Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import ApproveModuleAdapter from '../../../api/ApproveModuleAdapter';

import {
  EmployeeOvertimeAdminAdapter,
} from '../../../admin/src/overtime/lib';


class EmployeeOvertimeAdapter extends ApproveModuleAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.itemName = 'Overtime';
    this.itemNameLower = 'employeeovertime';
    this.modulePathName = 'overtime';
  }

  getDataMapping() {
    return [
      'id',
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
      ['category', {
        label: 'Category', type: 'select2', 'allow-null': false, 'remote-source': ['OvertimeCategory', 'id', 'name'],
      }],
      ['start_time', { label: 'Start Time', type: 'datetime', validation: '' }],
      ['end_time', { label: 'End Time', type: 'datetime', validation: '' }],
      ['project', {
        label: 'Project',
        type: 'select2',
        'allow-null': true,
        'null=label': 'none',
        'remote-source': ['Project', 'id', 'name'],
      }],
      ['notes', { label: 'Notes', type: 'textarea', validation: 'none' }],
    ];
  }
}

/*
 EmployeeOvertimeApproverAdapter
 */

class EmployeeOvertimeApproverAdapter extends EmployeeOvertimeAdminAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.itemName = 'Overtime';
    this.itemNameLower = 'employeeovertime';
    this.modulePathName = 'overtime';
  }

  getActionButtonsHtml(id, data) {
    const statusChangeButton = '<img class="tableActionButton" src="_BASE_images/run.png" style="cursor:pointer;" '
      + 'rel="tooltip" title="Change Status" onclick="modJs.openStatus(_id_, \'_cstatus_\');return false;">'
      + '</img>';

    const viewLogsButton = '<img class="tableActionButton" src="_BASE_images/log.png" '
      + 'style="margin-left:15px;cursor:pointer;" rel="tooltip" title="View Logs" '
      + 'onclick="modJs.getLogs(_id_);return false;"></img>';

    let html = '<div style="width:80px;">_status__logs_</div>';


    html = html.replace('_logs_', viewLogsButton);


    if (data[this.getStatusFieldPosition()] === 'Processing') {
      html = html.replace('_status_', statusChangeButton);
    } else {
      html = html.replace('_status_', '');
    }

    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    html = html.replace(/_cstatus_/g, data[this.getStatusFieldPosition()]);
    return html;
  }

  getStatusOptionsData(currentStatus) {
    const data = {};
    if (currentStatus === 'Processing') {
      data.Approved = 'Approved';
      data.Rejected = 'Rejected';
    }

    return data;
  }

  getStatusOptions(currentStatus) {
    return this.generateOptions(this.getStatusOptionsData(currentStatus));
  }
}


/*
 EmployeeOvertimeAdapter
 */

class SubordinateEmployeeOvertimeAdapter extends EmployeeOvertimeAdminAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.itemName = 'Overtime';
    this.itemNameLower = 'employeeovertime';
    this.modulePathName = 'overtime';
  }
}

module.exports = {
  EmployeeOvertimeAdapter,
  EmployeeOvertimeApproverAdapter,
  SubordinateEmployeeOvertimeAdapter,
};
