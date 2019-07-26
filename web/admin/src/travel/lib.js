/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';
import CustomFieldAdapter from '../../../api/CustomFieldAdapter';
import ApproveAdminAdapter from '../../../api/ApproveAdminAdapter';

/**
 * ImmigrationDocumentAdapter
 */

class ImmigrationDocumentAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'details',
      'required',
      'alert_on_missing',
      'alert_before_expiry',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Details' },
      { sTitle: 'Compulsory' },
      { sTitle: 'Alert If Not Found' },
      { sTitle: 'Alert Before Expiry' },
    ];
  }

  getFormFields() {
    const fields = [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
      ['required', { label: 'Compulsory', type: 'select', source: [['No', 'No'], ['Yes', 'Yes']] }],
      ['alert_on_missing', { label: 'Alert If Not Found', type: 'select', source: [['No', 'No'], ['Yes', 'Yes']] }],
      ['alert_before_expiry', { label: 'Alert Before Expiry', type: 'select', source: [['No', 'No'], ['Yes', 'Yes']] }],
      ['alert_before_day_number', { label: 'Days for Expiry Alert', type: 'text', validation: '' }],
    ];

    for (let i = 0; i < this.customFields.length; i++) {
      fields.push(this.customFields[i]);
    }

    return fields;
  }
}


/**
 * EmployeeImmigrationAdapter
 */


class EmployeeImmigrationAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'document',
      'documentname',
      'valid_until',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Document' },
      { sTitle: 'Document Id' },
      { sTitle: 'Valid Until' },
      { sTitle: 'Status' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],
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


  getFilters() {
    return [
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],

    ];
  }
}


/**
 * EmployeeTravelRecordAdminAdapter
 */


class EmployeeTravelRecordAdminAdapter extends ApproveAdminAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.itemName = 'TravelRequest';
    this.itemNameLower = 'travelrequest';
    this.modulePathName = 'travel';
  }

  getDataMapping() {
    return [
      'id',
      'employee',
      'type',
      'purpose',
      'travel_from',
      'travel_to',
      'travel_date',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Travel Type' },
      { sTitle: 'Purpose' },
      { sTitle: 'From' },
      { sTitle: 'To' },
      { sTitle: 'Travel Date' },
      { sTitle: 'Status' },
    ];
  }

  getFormFields() {
    return this.addCustomFields([
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
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

module.exports = {
  ImmigrationDocumentAdapter,
  EmployeeImmigrationAdapter,
  EmployeeTravelRecordAdminAdapter,
  CustomFieldAdapter,
};
