/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import React from 'react';
import ReactifiedAdapterBase from '../../../api/ReactifiedAdapterBase';
import { Space, Tag, Form } from 'antd';
import {
  EditOutlined, DeleteOutlined, InfoCircleOutlined, MonitorOutlined, DownloadOutlined, 
} from '@ant-design/icons';
import ReactModalAdapterBase from "../../../api/ReactModalAdapterBase";

class EmployeeDocumentAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'document',
      'date_added',
      'status',
      'attachment',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Document' },
      { sTitle: 'Date Added' },
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
        title: 'Date Added',
        dataIndex: 'date_added',
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
      ['document', { label: 'Document', type: 'select2', 'remote-source': ['Document', 'id', 'name', 'getDocumentTypesForUser'] }],
      // [ "date_added", {"label":"Date Added","type":"date","validation":""}],
      ['valid_until', { label: 'Valid Until', type: 'date', validation: 'none' }],
      ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['Inactive', 'Inactive'], ['Draft', 'Draft']] }],
      ['details', { label: 'Details', type: 'quill', validation: 'none' }],
      ['attachment', { label: 'Attachment', type: 'fileupload', validation: '' }],
    ];
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => {
      return (
        <Space size="middle">
          {adapter.hasAccess('save') && adapter.showEdit
            && (
              <Tag color="blue" onClick={() => modJs.edit(record.id)} style={{ cursor: 'pointer' }}>
                <EditOutlined />
                {` ${adapter.gt('Edit')}`}
              </Tag>
            )}
          <Tag color="green" onClick={() => download(record.attachment)} style={{ cursor: 'pointer' }}>
            <DownloadOutlined />
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
      )
    };
  }
}

/**
 * EmployeePayslipDocumentAdapter
 */

class EmployeePayslipDocumentAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'document',
      'date_added',
      'details',
      'attachment',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Document' },
      { sTitle: 'Date Added' },
      { sTitle: 'Details' },
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
        title: 'Date Added',
        dataIndex: 'date_added',
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
      ['document', { label: 'Document', type: 'select2', 'remote-source': ['Document', 'id', 'name', 'getDocumentTypesForUser'] }],
    ];
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => {
      return (
        <Space size="middle">
          <Tag color="green" onClick={() => download(record.attachment)} style={{ cursor: 'pointer' }}>
            <DownloadOutlined />
            {` ${adapter.gt('Download Document')}`}
          </Tag>
        </Space>
      )
    };
  }
}

/**
 * EmployeeCompanyDocumentAdapter
 */

class EmployeeCompanyDocumentAdapter extends ReactModalAdapterBase {

  getDataMapping() {
    return [
      'id',
      'name',
      'created',
      'type',
      'size',
      'attachment',
      'details'
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Date' },
      {
        sTitle: 'Type',
        fieldRenderer: (text, row) => {
          return <span className={`icon-con bg-${this.getColorByFileType(row.type)}`}>
            <i className={this.getIconByFileType(row.type)} /> - { row.type }
          </span>;
        }
      },
      { sTitle: 'Size' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      // {
      //   title: 'Date',
      //   dataIndex: 'created',
      //   sorter: true,
      // },
      // {
      //   title: 'Type',
      //   dataIndex: 'type',
      //   // render: (text, row) => {
      //   //   return <span className={`icon-con bg-${this.getColorByFileType(row.type)}`}>
      //   //     <i className={this.getIconByFileType(row.type)} /> - { row.type }
      //   //   </span>;
      //   // }
      // },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'placeholder', validation: '' }],
      ['details', { label: 'Details', validation: 'none', type: 'quill' }],
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

  preProcessTableData(row) {
    row.color = this.getColorByFileType(row.type);
    row.icon = this.getIconByFileType(row.type);
    row.details_long = this.nl2br(row.details);

    if (row.details.length > 30) {
      row.details = row.details.substring(0, 30);
    }

    if (row.size === undefined || row.size == null) {
      row.size = '';
    }
    return row;
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => {
      return (
        <Space size="middle">
          {adapter.hasAccess('element')
            && (
              <Tag color="blue" onClick={() => modJs.viewElement(record.id)} style={{ cursor: 'pointer' }}>
                <MonitorOutlined />
                {` ${adapter.gt('View')}`}
              </Tag>
            )}
          { record.attachment &&
            <Tag color="green" onClick={() => download(record.attachment)} style={{cursor: 'pointer'}}>
              <DownloadOutlined/>
              {` ${adapter.gt('Download Document')}`}
            </Tag>
          }
          {adapter.hasAccess('delete')
            && (
              <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
                <DeleteOutlined />
                {` ${adapter.gt('Delete')}`}
              </Tag>
            )}
        </Space>
      )
    };
  }

  getWidth() {
    return 1100;
  }
}


module.exports = {
  EmployeeDocumentAdapter,
  EmployeeCompanyDocumentAdapter,
  EmployeePayslipDocumentAdapter,
};
