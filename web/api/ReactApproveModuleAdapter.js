/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import React from 'react';
import { Space, Tag } from 'antd';
import {
  CopyOutlined, DeleteOutlined, EditOutlined, MonitorOutlined,
} from '@ant-design/icons';
import ReactLogViewAdapter from './ReactLogViewAdapter';

class ReactApproveModuleAdapter extends ReactLogViewAdapter {
  cancelRequest(id) {
    const object = {};
    object.id = id;

    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'cancelSuccessCallBack';
    callBackData.callBackFail = 'cancelFailCallBack';

    this.customAction('cancel', `modules=${this.modulePathName}`, reqJson, callBackData);
  }

  // eslint-disable-next-line no-unused-vars
  cancelSuccessCallBack(callBackData) {
    this.showMessage('Successful', `${this.itemName} cancellation request sent`);
    this.get([]);
  }

  cancelFailCallBack(callBackData) {
    this.showMessage(`Error Occurred while cancelling ${this.itemName}`, callBackData);
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        {adapter.hasAccess('save') && adapter.showEdit
        && (
          <Tag color="green" onClick={() => modJs.edit(record.id)} style={{ cursor: 'pointer' }}>
            <EditOutlined />
            {` ${adapter.gt('Edit')}`}
          </Tag>
        )}
        {adapter.hasAccess('delete') && adapter.showDelete && record.status === 'Approved'
        && (
          <Tag color="volcano" onClick={() => modJs.cancelRequest(record.id)} style={{ cursor: 'pointer' }}>
            <DeleteOutlined />
            {` ${adapter.gt('Cancel')}`}
          </Tag>
        )}
        {adapter.hasAccess('delete') && adapter.showDelete && record.status === 'Pending'
        && this.user.user_level === 'Admin'
        && (
          <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
            <DeleteOutlined />
            {` ${adapter.gt('Delete')}`}
          </Tag>
        )}
        <Tag color="cyan" onClick={() => modJs.getLogs(record.id)} style={{ cursor: 'pointer' }}>
          <CopyOutlined />
          {` ${adapter.gt('View Logs')}`}
        </Tag>
      </Space>
    );
  }
}

export default ReactApproveModuleAdapter;
