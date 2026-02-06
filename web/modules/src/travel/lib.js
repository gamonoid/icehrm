/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import { Space, Tag, Modal, Avatar } from 'antd';
import { CheckOutlined, CloseOutlined, ExclamationCircleOutlined, MonitorOutlined } from '@ant-design/icons';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import ApproveModuleAdapter from '../../../api/ApproveModuleAdapter';
import ReactApproveModuleAdapter from '../../../api/ReactApproveModuleAdapter';

import {
  EmployeeTravelRecordAdminAdapter,
} from '../../../admin/src/travel/lib';

class EmployeeImmigrationAdapter extends ReactModalAdapterBase {
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

  getTableColumns() {
    return [
      {
        title: 'Document',
        dataIndex: 'document',
        sorter: true,
      },
      {
        title: 'Document Id',
        dataIndex: 'documentname',
        sorter: true,
      },
      {
        title: 'Valid Until',
        dataIndex: 'valid_until',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy') : '',
      },
      {
        title: 'Status',
        dataIndex: 'status',
        sorter: true,
      },
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


class EmployeeTravelRecordAdapter extends ReactApproveModuleAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.itemName = 'Travel';
    this.itemNameLower = 'employeetravelrecord';
    this.modulePathName = 'travel';
  }

  getDataMapping() {
    return [
      'id',
      'trip_type',
      'type',
      'project_code',
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
      { sTitle: 'Trip Type' },
      { sTitle: 'Transportation' },
      { sTitle: 'Purpose' },
      { sTitle: 'From' },
      { sTitle: 'To' },
      { sTitle: 'Travel Date' },
      { sTitle: 'Return Date' },
      { sTitle: 'Status' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Trip Type',
        dataIndex: 'trip_type',
        sorter: true,
      },
      {
        title: 'Transportation',
        dataIndex: 'type',
        sorter: true,
      },
      {
        title: 'Travel Project',
        dataIndex: 'project_code',
        sorter: true,
      },
      {
        title: 'From',
        dataIndex: 'travel_from',
        sorter: true,
        render: (text) => {
          if (!text) return '-';
          try {
            const parsed = JSON.parse(text);
            const country = parsed.country || '';
            const city = parsed.city || '';
            if (country && city) return `${city}, ${country}`;
            if (city) return city;
            if (country) return country;
            return '-';
          } catch (e) {
            return text;
          }
        },
      },
      {
        title: 'To',
        dataIndex: 'travel_to',
        sorter: true,
        render: (text) => {
          if (!text) return '-';
          try {
            const parsed = JSON.parse(text);
            const country = parsed.country || '';
            const city = parsed.city || '';
            if (country && city) return `${city}, ${country}`;
            if (city) return city;
            if (country) return country;
            return '-';
          } catch (e) {
            return text;
          }
        },
      },
      {
        title: 'Travel Date',
        dataIndex: 'travel_date',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy HH:mm') : '',
      },
      {
        title: 'Return Date',
        dataIndex: 'return_date',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy HH:mm') : '',
      },
      {
        title: 'Status',
        dataIndex: 'status',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    return this.addCustomFields([
      ['id', { label: 'ID', type: 'hidden' }],
      ['_country_loader', { label: 'Country Loader', type: 'select2', 'remote-source': ['Country', 'name', 'name'], validation: 'none', display: 'none' }],
      // Trip Classification
      ['trip_type', {
        label: 'Trip Type',
        type: 'select',
        validation: 'none',
        source: [
          ['Domestic', '🏠 Domestic'],
          ['International', '🌍 International'],
          ['Regional', '🗺️ Regional'],
        ],
      }],

      // Transportation Mode (updated options)
      ['type', {
        label: 'Transportation Mode',
        type: 'select',
        source: [
          ['Airplane', '✈️ Airplane'],
          ['High-Speed Rail', '🚅 High-Speed Rail'],
          ['Train', '🚂 Train'],
          ['Bus/Coach', '🚌 Bus/Coach'],
          ['Ride-hailing (Uber/Lyft)', '🚕 Ride-hailing (Uber/Lyft)'],
          ['Taxi', '🚖 Taxi'],
          ['Personal Vehicle', '🚗 Personal Vehicle'],
          ['Rental Car', '🚙 Rental Car'],
          ['Company Car/Carpool', '🚐 Company Car/Carpool'],
          ['Ferry/Boat', '🚤 Ferry/Boat'],
          ['Metro/Subway', '🚇 Metro/Subway'],
          ['Bike/E-scooter Rental', '🚲 Bike/E-scooter Rental'],
          ['Helicopter', '🚁 Helicopter'],
          ['Other', 'Other'],
        ],
      }],

      // Travel Details
      ['purpose', { label: 'Travel Details', type: 'textarea', validation: '' }],
      ['travel_from', { label: 'Traveling From', type: 'location', validation: '' }],
      ['travel_to', { label: 'Traveling To', type: 'location', validation: '' }],
      ['travel_date', { label: 'Departure Date & Time', type: 'datetime', validation: '' }],
      ['return_date', {
        label: 'Return Date & Time',
        type: 'datetime',
        validation: '',
        customValidation: (value, formData) => {
          if (value && formData.travel_date) {
            const departureDate = new Date(formData.travel_date);
            const returnDate = new Date(value);
            if (returnDate <= departureDate) {
              return 'Return date must be after departure date';
            }
          }
          return null;
        },
      }],

      // Booking Information
      ['confirmation_number', {
        label: 'Booking Reference/Confirmation Number',
        type: 'text',
        validation: 'none',
      }],

      ['airline_name', {
        label: 'Airline Name',
        type: 'text',
        validation: 'none',
        help: 'For flight bookings',
      }],

      ['flight_number', {
        label: 'Flight Number',
        type: 'text',
        validation: 'none',
      }],

      // Project Tracking
      ['project_code', {
        label: 'Travel Project',
        type: 'select2',
        'allow-null': false,
        'remote-source': ['TravelProject', 'code', 'name'],
        help: 'Select the project this travel is associated with',
      }],

      // Budget Information
      ['details', { label: 'Additional Notes', type: 'textarea', validation: 'none' }],
      ['currency', {
        label: 'Funding Currency',
        type: 'select2',
        'allow-null': false,
        'remote-source': ['CurrencyType', 'id', 'code'],
        help: 'This is a rough estimate. Exact amounts can be submitted as an expense',
      }],
      ['funding', {
        label: 'Total Funding Proposed',
        type: 'text',
        validation: 'float',
        default: '0.00',
        mask: '9{0,10}.99',
      }],

      // Attachments
      ['attachment1', { label: 'Attachment (Booking Confirmation)', type: 'fileupload', validation: 'none' }],
      ['attachment2', { label: 'Attachment (Visa/Insurance)', type: 'fileupload', validation: 'none' }],
      ['attachment3', { label: 'Attachment (Other)', type: 'fileupload', validation: 'none' }],
    ]);
  }

  getMappedFields() {
    const fields = this.getFormFields();
    const steps = [
      {
        title: 'Basic Information',
        description: 'Basic travel details and itinerary',
        fields: [
          'id',
          '_country_loader',
          'trip_type',
          'type',
          'travel_from',
          'travel_to',
          'travel_date',
          'return_date',
        ],
      },
      {
        title: 'Booking Details',
        description: 'Transportation and booking information',
        fields: [
          'purpose',
          'confirmation_number',
          'airline_name',
          'flight_number',
        ],
      },
      {
        title: 'Project & Budget',
        description: 'Project tracking and funding information',
        fields: [
          'project_code',
          'currency',
          'funding',
          'details',
        ],
      },
      {
        title: 'Attachments',
        description: 'Upload supporting documents',
        fields: [
          'attachment1',
          'attachment2',
          'attachment3',
        ],
      },
    ];

    return this.addActualFieldsForStepModal(steps, fields);
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
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.itemName = 'Travel';
    this.itemNameLower = 'employeetravelrecord';
    this.modulePathName = 'travel';
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        <Tag color="blue" onClick={() => modJs.viewElement(record.id)} style={{ cursor: 'pointer' }}>
          <MonitorOutlined />
          {` ${adapter.gt('View')}`}
        </Tag>
        {(record.status === 'Pending' || record.status === 'Processing') && (
          <>
            <Tag color="green" onClick={() => adapter.changeSubordinateStatus(record.id, 'Approved')} style={{ cursor: 'pointer' }}>
              <CheckOutlined />
              {' Approve'}
            </Tag>
            <Tag color="volcano" onClick={() => adapter.changeSubordinateStatus(record.id, 'Rejected')} style={{ cursor: 'pointer' }}>
              <CloseOutlined />
              {' Reject'}
            </Tag>
          </>
        )}
      </Space>
    );
  }

  changeSubordinateStatus(id, newStatus) {
    const statusText = newStatus === 'Approved' ? 'approve' : 'reject';
    const statusAction = newStatus === 'Approved' ? 'Approve' : 'Reject';

    Modal.confirm({
      title: `${statusAction} Travel Request`,
      icon: <ExclamationCircleOutlined />,
      content: `Are you sure you want to ${statusText} this travel request?`,
      okText: statusAction,
      okType: newStatus === 'Approved' ? 'primary' : 'danger',
      cancelText: 'Cancel',
      onOk: () => {
        const object = { id, status: newStatus };
        const reqJson = JSON.stringify(object);

        const callBackData = [];
        callBackData.callBackData = [];
        callBackData.callBackSuccess = 'changeSubordinateStatusSuccessCallBack';
        callBackData.callBackFail = 'changeSubordinateStatusFailCallBack';

        this.customAction('changeStatus', 'admin=travel', reqJson, callBackData);
      },
    });
  }

  changeSubordinateStatusSuccessCallBack(callBackData) {
    this.showMessage('Successful', 'Travel request status changed successfully');
    this.get([]);
  }

  changeSubordinateStatusFailCallBack(callBackData) {
    this.showMessage('Error', 'Error occurred while changing travel request status');
  }

  isSubProfileTable() {
    return true;
  }
}

module.exports = {
  EmployeeImmigrationAdapter,
  EmployeeTravelRecordAdapter,
  EmployeeTravelRecordApproverAdapter,
  SubordinateEmployeeTravelRecordAdapter,
};
