/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/**
 * ReactApproveAdminAdapter
 */
import React from 'react';
import ReactLogViewAdapter from './ReactLogViewAdapter';
import {Space, Tag} from "antd";
import {CopyOutlined, DeleteOutlined, EditOutlined, MonitorOutlined} from "@ant-design/icons";

class ReactApproveAdminAdapter extends ReactLogViewAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
  }

  getStatusFieldPosition() {
    const dm = this.getDataMapping();
    return dm.length - 1;
  }

  openStatus(id, status) {
    $(`#${this.itemNameLower}StatusModel`).modal('show');
    $(`#${this.itemNameLower}_status`).html(this.getStatusOptions(status));
    $(`#${this.itemNameLower}_status`).val(status);
    this.statusChangeId = id;
  }

  closeDialog() {
    $(`#${this.itemNameLower}StatusModel`).modal('hide');
  }

  changeStatus() {
    const status = $(`#${this.itemNameLower}_status`).val();
    const reason = $(`#${this.itemNameLower}_reason`).val();

    if (status == undefined || status == null || status == '') {
      this.showMessage('Error', `Please select ${this.itemNameLower} status`);
      return;
    }

    const object = { id: this.statusChangeId, status, reason };

    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'changeStatusSuccessCallBack';
    callBackData.callBackFail = 'changeStatusFailCallBack';

    this.customAction('changeStatus', `admin=${this.modulePathName}`, reqJson, callBackData);

    this.closeDialog();
    this.statusChangeId = null;
  }

  changeStatusSuccessCallBack(callBackData) {
    this.showMessage('Successful', `${this.itemName} Request status changed successfully`);
    this.get([]);
  }

  changeStatusFailCallBack(callBackData) {
    this.showMessage('Error', `Error occurred while changing ${this.itemName} request status`);
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
        {adapter.hasAccess('delete') && adapter.showDelete
        && (
          <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
            <DeleteOutlined />
            {` ${adapter.gt('Delete')}`}
          </Tag>
        )}
        {Object.keys(this.getStatusOptionsData(record.status)).length > 0
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

  hasCustomButtons() {
    return true;
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }

  getStatusOptionsData(currentStatus) {
    const data = {};
    if (currentStatus === 'Approved') {

    } else if (currentStatus === 'Pending') {
      data.Approved = 'Approved';
      data.Rejected = 'Rejected';
    } else if (currentStatus === 'Rejected') {

    } else if (currentStatus === 'Cancelled') {

    } else if (currentStatus === 'Processing') {

    } else {
      data['Cancellation Requested'] = 'Cancellation Requested';
      data.Cancelled = 'Cancelled';
    }

    return data;
  }

  getStatusOptions(currentStatus) {
    return this.generateOptions(this.getStatusOptionsData(currentStatus));
  }
}

export default ReactApproveAdminAdapter;
