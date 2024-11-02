/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import React from 'react';
import {
  Space, Tag, Button, Alert, Typography, Card,
} from 'antd';
const { Meta } = Card;
import {
  EditOutlined, DeleteOutlined, InfoCircleOutlined, SettingOutlined,
} from '@ant-design/icons';
import ReactifiedAdapterBase from '../../../api/ReactifiedAdapterBase';

const { Link } = Typography;

/**
 * DocumentAdapter
 */

class DocumentAdapter extends ReactifiedAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['expire_notification', { label: 'Notify Expiry', type: 'select', source: [['Yes', 'Yes'], ['No', 'No']] }],
      ['expire_notification_month', { label: 'Notify Expiry Before One Month', type: 'select', source: [['Yes', 'Yes'], ['No', 'No']] }],
      ['expire_notification_week', { label: 'Notify Expiry Before One Week', type: 'select', source: [['Yes', 'Yes'], ['No', 'No']] }],
      ['expire_notification_day', { label: 'Notify Expiry Before One Day', type: 'select', source: [['Yes', 'Yes'], ['No', 'No']] }],
      ['share_with_employee', { label: 'Share with Employee', type: 'select', source: [['Yes', 'Yes'], ['No', 'No']] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/training-and-reviews/document-management';
  }

  showViewButton() {
    return false;
  }
}


/**
 * CompanyDocumentAdapter
 */

class CompanyDocumentAdapter extends ReactifiedAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Status' },
    ];
  }

  getViewModeEnabledFields() {
    return ['details'];
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
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['details', { label: 'Details', type: 'quill', validation: 'none' }],
      ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['Inactive', 'Inactive'], ['Draft', 'Draft']] }],
      ['attachment', { label: 'Attachment', type: 'fileupload' }],
      [
        'share_departments',
        {
          label: 'Share Departments',
          type: 'select2multi',
          'allow-null': true,
          'null-label': 'All Departments',
          'remote-source': ['CompanyStructure', 'id', 'title'],
          help: 'This document will be visible to employees from selected department. If no department is selected only Admin users can see this',
        },
      ],
      [
        'share_employees',
        {
          label: 'Share Employees',
          type: 'select2multi',
          'allow-null': true,
          'null-label': 'All Employees',
          'remote-source': ['Employee', 'id', 'first_name+last_name'],
          validation: 'none',
          help: 'Instead of sharing with all the employees in a department, you can share it only with specific employees',
        },
      ],
    ];
  }

  showViewButton() {
    return true;
  }

  getHelpTitle() {
    return this.gt('Company Documents');
  }

  getHelpDescription() {
    return this.gt('Company Documents are used to share announcements, policies, procedures, etc. with employees. These documents can be shared with specific employees or departments.');
  }

  getHelpLink() {
    return 'https://icehrm.com/explore/docs/visibility-of-company-documents/';
  }
}

/**
 * EmployeeDocumentAdapter
 */

class EmployeeDocumentAdapter extends ReactifiedAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'document',
      'date_added',
      'status',
      'attachment',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Document' },
      { sTitle: 'Date Added' },
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
      ['document', { label: 'Document', type: 'select2', 'remote-source': ['Document', 'id', 'name'] }],
      ['date_added', { label: 'Date Added', type: 'date', validation: '' }],
      ['valid_until', { label: 'Valid Until', type: 'date', validation: 'none' }],
      ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['Inactive', 'Inactive'], ['Draft', 'Draft']] }],
      [
        'visible_to', {
          label: 'Visible To',
          type: 'select',
          source: [
            ['Owner', 'Admin, Manager and the Employee'],
            ['Owner Only', 'Only the Employee and an Admin'],
            ['Manager', 'Only an Admin and the Manager of the Employee'],
            ['Admin', 'Only an Admin'],
          ],
        },
      ],
      ['details', { label: 'Details', type: 'quill', validation: 'none' }],
      ['attachment', { label: 'Attachment', type: 'fileupload', validation: '' }],
    ];
  }


  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['document', {
        label: 'Document', type: 'select2', 'allow-null': true, 'remote-source': ['Document', 'id', 'name'],
      }],
    ];
  }


  getActionButtonsHtml(id, data) {
    let html = '<div style="width:80px;">'
      + '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>'
      + '<img class="tableActionButton" src="_BASE_images/download.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Download Document" onclick="download(\'_attachment_\');return false;"></img>'
      + '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>'
      + '</div>';
    html = html.replace(/_id_/g, id);
    html = html.replace(/_attachment_/g, data[6]);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        <Tag color="blue" onClick={() => modJs.edit(record.id)} style={{ cursor: 'pointer' }}>
          <EditOutlined />
          {` ${adapter.gt('Edit')}`}
        </Tag>
        <Tag color="green" onClick={() => download(record.attachment)} style={{ cursor: 'pointer' }}>
          <EditOutlined />
          {` ${adapter.gt('Download Document')}`}
        </Tag>
        {adapter.hasAccess('delete')
            && (
              <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
                <DeleteOutlined />
                {` ${adapter.gt('Delete')}`}
              </Tag>
            )}
      </Space>
    );
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}

/**
 * EmployeePayslipDocumentAdapter
 */

class EmployeePayslipDocumentAdapter extends EmployeeDocumentAdapter {
  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
    ];
  }
}

module.exports = {
  DocumentAdapter, CompanyDocumentAdapter, EmployeeDocumentAdapter, EmployeePayslipDocumentAdapter,
};
