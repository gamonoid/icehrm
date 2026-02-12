/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import ReactDOM from 'react-dom';
import { Avatar, Space, Tag, Modal } from 'antd';
import { MonitorOutlined, EditOutlined, DeleteOutlined } from '@ant-design/icons';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import CustomFieldAdapter from '../../../api/CustomFieldAdapter';
import ApproveAdminAdapter from '../../../api/ApproveAdminAdapter';
import CustomAction from '../../../api/CustomAction';
import TravelRequestView from './components/TravelRequestView';

/**
 * ImmigrationDocumentAdapter
 */

class ImmigrationDocumentAdapter extends ReactModalAdapterBase {
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

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Details',
        dataIndex: 'details',
        sorter: true,
      },
      {
        title: 'Compulsory',
        dataIndex: 'required',
        sorter: true,
      },
      {
        title: 'Alert If Not Found',
        dataIndex: 'alert_on_missing',
        sorter: true,
      },
      {
        title: 'Alert Before Expiry',
        dataIndex: 'alert_before_expiry',
        sorter: true,
      },
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


class EmployeeImmigrationAdapter extends ReactModalAdapterBase {
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

  getTableColumns() {
    return [
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
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


class EmployeeTravelRecordAdminAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.itemName = 'TravelRequest';
    this.itemNameLower = 'travelrequest';
    this.modulePathName = 'travel';
  }

  getDataMapping() {
    return [
      'id',
      'image',
      'employee',
      'trip_type',
      'type',
      'project_code',
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
      { sTitle: 'Trip Type' },
      { sTitle: 'Transportation' },
      { sTitle: 'Purpose' },
      { sTitle: 'From' },
      { sTitle: 'To' },
      { sTitle: 'Travel Date' },
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
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],

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
        'allow-null': true,
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
          'employee',
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

  getFilters() {
    return [
      ['employee', { label: 'Employee', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'] }],
      ['trip_type', {
        label: 'Trip Type',
        type: 'select',
        'allow-null': true,
        source: [
          ['Domestic', '🏠 Domestic'],
          ['International', '🌍 International'],
          ['Regional', '🗺️ Regional'],
        ],
      }],
      ['status', {
        label: 'Status',
        type: 'select',
        'allow-null': true,
        source: [
          ['Pending', 'Pending'],
          ['Processing', 'Processing'],
          ['Approved', 'Approved'],
          ['Rejected', 'Rejected'],
          ['Cancelled', 'Cancelled'],
          ['Cancellation Requested', 'Cancellation Requested'],
        ],
      }],
    ];
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        {adapter.showViewButton() && (
          <Tag color="blue" onClick={() => modJs.viewElement(record.id)} style={{ cursor: 'pointer' }}>
            <MonitorOutlined />
            {` ${adapter.gt('View')}`}
          </Tag>
        )}
        {adapter.hasAccess('save') && adapter.showEdit && (
          <Tag color="green" onClick={() => modJs.edit(record.id)} style={{ cursor: 'pointer' }}>
            <EditOutlined />
            {` ${adapter.gt('Edit')}`}
          </Tag>
        )}
        {adapter.hasAccess('delete') && adapter.showDelete && (
          <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
            <DeleteOutlined />
            {` ${adapter.gt('Delete')}`}
          </Tag>
        )}
      </Space>
    );
  }

  showViewButton() {
    return true;
  }

  viewElement(id) {
    const customAction = new CustomAction(this);

    const req = { id };
    const reqJson = JSON.stringify(req);

    customAction.execute('viewFullTravelRequest', 'admin=travel', reqJson)
      .then((response) => {
        const data = response.data;
        if (data.status === 'SUCCESS') {
          this.showTravelRequestModal({
            employee: data.data.employee,
            travelRequest: data.data.travelRequest,
            attachments: data.data.attachments || [],
            statusLogs: data.data.statusLogs || [],
          });
        }
      });
  }

  showTravelRequestModal(element) {
    const modalContainer = document.createElement('div');
    document.body.appendChild(modalContainer);

    const closeModal = () => {
      ReactDOM.unmountComponentAtNode(modalContainer);
      if (modalContainer.parentNode) {
        modalContainer.parentNode.removeChild(modalContainer);
      }
    };

    const onStatusChange = () => {
      this.get([]);
    };

    ReactDOM.render(
      <Modal
        title={this.gt('Travel Request Details')}
        open={true}
        width={1200}
        centered={false}
        maskClosable={true}
        footer={null}
        onCancel={closeModal}
        destroyOnClose={true}
        style={{ top: 65 }}
        bodyStyle={{
          maxHeight: 'calc(100vh - 200px)',
          overflowY: 'auto',
          paddingTop: '20px',
          paddingBottom: '20px'
        }}
      >
        <TravelRequestView
          element={element}
          adapter={this}
          onStatusChange={onStatusChange}
        />
      </Modal>,
      modalContainer
    );
  }
}

/**
 * TravelProjectAdapter
 */

class TravelProjectAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'code',
      'name',
      'status',
      'budget',
      'start_date',
      'end_date',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Code' },
      { sTitle: 'Name' },
      { sTitle: 'Status' },
      { sTitle: 'Budget' },
      { sTitle: 'Start Date' },
      { sTitle: 'End Date' },
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
        title: 'Status',
        dataIndex: 'status',
        sorter: true,
      },
      {
        title: 'Budget',
        dataIndex: 'budget',
        sorter: true,
        render: (text, record) => {
          if (text && record.currency_code) {
            return `${record.currency_code} ${parseFloat(text).toFixed(2)}`;
          }
          return text ? parseFloat(text).toFixed(2) : '';
        },
      },
      {
        title: 'Start Date',
        dataIndex: 'start_date',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy') : '',
      },
      {
        title: 'End Date',
        dataIndex: 'end_date',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy') : '',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['code', {
        label: 'Project Code',
        type: 'text',
        validation: '',
        help: 'Unique project code (e.g., PROJ2025-001)',
      }],
      ['name', {
        label: 'Project Name',
        type: 'text',
        validation: '',
      }],
      ['description', {
        label: 'Description',
        type: 'textarea',
        validation: 'none',
      }],
      ['status', {
        label: 'Status',
        type: 'select',
        source: [['Active', 'Active'], ['Inactive', 'Inactive']],
      }],
      ['budget', {
        label: 'Budget',
        type: 'text',
        validation: 'float',
        default: '0.00',
        mask: '9{0,10}.99',
      }],
      ['currency', {
        label: 'Currency',
        type: 'select2',
        'allow-null': true,
        'remote-source': ['CurrencyType', 'id', 'code'],
      }],
      ['start_date', {
        label: 'Start Date',
        type: 'date',
        validation: 'none',
      }],
      ['end_date', {
        label: 'End Date',
        type: 'date',
        validation: 'none',
      }],
    ];
  }

  getFilters() {
    return [
      ['status', {
        label: 'Status',
        type: 'select',
        'allow-null': true,
        source: [['Active', 'Active'], ['Inactive', 'Inactive']],
      }],
    ];
  }
}

module.exports = {
  ImmigrationDocumentAdapter,
  EmployeeImmigrationAdapter,
  TravelProjectAdapter,
  EmployeeTravelRecordAdminAdapter,
  CustomFieldAdapter,
};
