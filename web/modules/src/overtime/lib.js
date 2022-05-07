/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import { Space, Tag } from 'antd';
import { CopyOutlined, MonitorOutlined } from '@ant-design/icons';
import React from 'react';
import {
  EmployeeOvertimeAdminAdapter,
} from '../../../admin/src/overtime/lib';
import ReactApproveModuleAdapter from '../../../api/ReactApproveModuleAdapter';


class EmployeeOvertimeAdapter extends ReactApproveModuleAdapter {
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

  getTableColumns() {
    return [
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

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        {record.status === 'Processing'
        && (
          <Tag color="blue" onClick={() => modJs.openStatus(record.id, record.status)} style={{ cursor: 'pointer' }}>
            <MonitorOutlined />
            {` ${adapter.gt('Change Status')}`}
          </Tag>
        )}
        <Tag color="cyan" onClick={() => modJs.getLogs(record.id)} style={{ cursor: 'pointer' }}>
          <CopyOutlined />
          {` ${adapter.gt('View Logs')}`}
        </Tag>
      </Space>
    );
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

  isSubProfileTable() {
    return true;
  }
}

module.exports = {
  EmployeeOvertimeAdapter,
  EmployeeOvertimeApproverAdapter,
  SubordinateEmployeeOvertimeAdapter,
};
