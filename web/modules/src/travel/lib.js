/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';
import ApproveModuleAdapter from '../../../api/ApproveModuleAdapter';

import {
  EmployeeTravelRecordAdminAdapter,
} from '../../../admin/src/travel/lib';

class EmployeeImmigrationAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'document',
      'documentname',
      'valid_until',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Document' },
      { sTitle: 'Document Id' },
      { sTitle: 'Valid Until' },
      { sTitle: 'Status' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['document', { label: 'Document', type: 'select2', 'remote-source': ['ImmigrationDocument', 'id', 'name'] }],
      ['documentname', { label: 'Document Id', type: 'text', validation: '' }],
      ['valid_until', { label: 'Valid Until', type: 'date', validation: 'none' }],
      ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['Inactive', 'Inactive'], ['Draft', 'Draft']] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
      ['attachment1', { label: 'Attachment 1', type: 'fileupload', validation: 'none' }],
      ['attachment2', { label: 'Attachment 2', type: 'fileupload', validation: 'none' }],
      ['attachment3', { label: 'Attachment 3', type: 'fileupload', validation: 'none' }],
    ];
  }
}


class EmployeeTravelRecordAdapter extends ApproveModuleAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.itemName = 'Travel';
    this.itemNameLower = 'employeetravelrecord';
    this.modulePathName = 'travel';
  }

  getDataMapping() {
    return [
      'id',
      'type',
      'purpose',
      'travel_from',
      'travel_to',
      'travel_date',
      'return_date',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Travel Type' },
      { sTitle: 'Purpose' },
      { sTitle: 'From' },
      { sTitle: 'To' },
      { sTitle: 'Travel Date' },
      { sTitle: 'Return Date' },
      { sTitle: 'Status' },
    ];
  }

  getFormFields() {
    return this.addCustomFields([
      ['id', { label: 'ID', type: 'hidden' }],
      ['type', {
        label: 'Means of Transportation',
        type: 'select',
        source: [
          ['Plane', 'Plane'],
          ['Rail', 'Rail'],
          ['Taxi', 'Taxi'],
          ['Own Vehicle', 'Own Vehicle'],
          ['Rented Vehicle', 'Rented Vehicle'],
          ['Other', 'Other'],
        ],
      }],
      ['purpose', { label: 'Purpose of Travel', type: 'textarea', validation: '' }],
      ['travel_from', { label: 'Travel From', type: 'text', validation: '' }],
      ['travel_to', { label: 'Travel To', type: 'text', validation: '' }],
      ['travel_date', { label: 'Travel Date', type: 'datetime', validation: '' }],
      ['return_date', { label: 'Return Date', type: 'datetime', validation: '' }],
      ['details', { label: 'Notes', type: 'textarea', validation: 'none' }],
      ['currency', {
        label: 'Currency', type: 'select2', 'allow-null': false, 'remote-source': ['CurrencyType', 'id', 'code'],
      }],
      ['funding', {
        label: 'Total Funding Proposed', type: 'text', validation: 'float', default: '0.00', mask: '9{0,10}.99',
      }],
      ['attachment1', { label: 'Attachment', type: 'fileupload', validation: 'none' }],
      ['attachment2', { label: 'Attachment', type: 'fileupload', validation: 'none' }],
      ['attachment3', { label: 'Attachment', type: 'fileupload', validation: 'none' }],
    ]);
  }
}


/*
 EmployeeTravelRecordApproverAdapter
 */

class EmployeeTravelRecordApproverAdapter extends EmployeeTravelRecordAdminAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.itemName = 'Travel';
    this.itemNameLower = 'employeetravelrecord';
    this.modulePathName = 'travel';
  }

  getActionButtonsHtml(id, data) {
    const statusChangeButton = '<img class="tableActionButton" src="_BASE_images/run.png" style="cursor:pointer;" rel="tooltip" title="Change Status" onclick="modJs.openStatus(_id_, \'_cstatus_\');return false;"></img>';
    const viewLogsButton = '<img class="tableActionButton" src="_BASE_images/log.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="View Logs" onclick="modJs.getLogs(_id_);return false;"></img>';

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
 SubordinateExpenseModuleAdapter
 */

class SubordinateEmployeeTravelRecordAdapter extends EmployeeTravelRecordAdminAdapter {

}

module.exports = {
  EmployeeImmigrationAdapter,
  EmployeeTravelRecordAdapter,
  EmployeeTravelRecordApproverAdapter,
  SubordinateEmployeeTravelRecordAdapter,
};
