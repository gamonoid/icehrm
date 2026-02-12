/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import { Space, Tag } from 'antd';
import {
  CopyOutlined, DeleteOutlined, EditOutlined, MonitorOutlined,
} from '@ant-design/icons';
import React from 'react';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import AdapterBase from '../../../api/AdapterBase';
/**
 * ModuleAdapter
 */

class ModuleAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'label',
      'menu',
      'mod_group',
      'mod_order',
      'status',
      'version',
      'update_path',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Menu', bVisible: false },
      { sTitle: 'Group' },
      { sTitle: 'Order' },
      { sTitle: 'Status' },
      { sTitle: 'Version', bVisible: false },
      { sTitle: 'Path', bVisible: false },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'label',
        sorter: true,
      },
      {
        title: 'Menu',
        dataIndex: 'menu',
        sorter: true,
      },
      {
        title: 'Group',
        dataIndex: 'mod_group',
      },
      {
        title: 'Order',
        dataIndex: 'mod_order',
      },
      {
        title: 'Status',
        dataIndex: 'status',
      },
      {
        title: 'Version',
        dataIndex: 'version',
      },
      {
        title: 'Path',
        dataIndex: 'update_path',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['label', { label: 'Label', type: 'placeholder', validation: '' }],
      ['status', { label: 'Status', type: 'select', source: [['Enabled', 'Enabled'], ['Disabled', 'Disabled']] }],
      ['user_levels', { label: 'User Levels', type: 'select2multi', source: [['Admin', 'Admin'], ['Manager', 'Manager'], ['Employee', 'Employee']] }],
      ['user_roles', { label: 'Allowed User Roles', validation: 'none', type: 'select2multi', 'remote-source': ['UserRole', 'id', 'name'] }],
      ['user_roles_blacklist', { label: 'Disallowed User Roles', validation: 'none', type: 'select2multi', 'remote-source': ['UserRole', 'id', 'name'] }],
    ];
  }

  getTableActionButtonJsx(adapter) {
    const blockedPaths = () => {
      return {
        'admin>modules': 1,
        'admin>connection': 1,
        'admin>settings': 1,
      };
    };
    return (text, record) => (
      <Space size="middle">
        {adapter.hasAccess('save') && adapter.showEdit && !blockedPaths()[record.update_path]
              && (
              <Tag color="green" onClick={() => modJs.edit(record.id)} style={{ cursor: 'pointer' }}>
                <EditOutlined />
                {` ${adapter.gt('Edit')}`}
              </Tag>
              )}
      </Space>
    );
  }
}


/**
 * UsageAdapter
 */

class UsageAdapter extends AdapterBase {
  getDataMapping() {
    return [];
  }

  getHeaders() {
    return [];
  }

  getFormFields() {
    return [];
  }


  get(callBackData) {

  }

  saveUsage() {
    const object = {};
    const arr = [];
    $('.module-check').each(function () {
      if ($(this).is(':checked')) {
        arr.push($(this).val());
      }
    });

    if (arr.length === 0) {
      alert('Please select one or more module groups');
      return;
    }

    object.groups = arr.join(',');

    const reqJson = JSON.stringify(object);
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getInitDataSuccessCallBack';
    callBackData.callBackFail = 'getInitDataFailCallBack';

    this.customAction('saveUsage', 'admin=modules', reqJson, callBackData);
  }


  saveUsageSuccessCallBack(data) {

  }

  saveUsageFailCallBack(callBackData) {

  }
}

module.exports = { ModuleAdapter };
