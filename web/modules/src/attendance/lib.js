/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/* global modJs, modJsList, webkitURL */

import ReactModalAdapterBase from "../../../api/ReactModalAdapterBase";
import {Button, Progress, Typography} from "antd";
import React from "react";
import {PlusCircleOutlined} from "@ant-design/icons";

const { Text } = Typography;

class AttendanceAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.punch = null;
    this.useServerTime = 0;
    this.hasOpenPunch = 0;
    this.punchedOutToday = 0;
  }

  setUseServerTime(val) {
    this.useServerTime = val;
  }

  setHasOpenPunch(val) {
    this.hasOpenPunch = val;
  }

  setPunchedOutToday(val) {
    this.punchedOutToday = val;
  }

  getDataMapping() {
    return [
      'id',
      'in_time',
      'out_time',
      'hours',
      'note',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Time-In' },
      { sTitle: 'Time-Out' },
      { sTitle: 'Note' },
    ];
  }

  getTableColumns() {

    const dateRenderer = (text, record) => {
      if (text === '0000-00-00 00:00:00' || text === '' || text === undefined || text == null) {
        return '';
      }
      return (
        <>
          <Text code>{Date.parse(text).toString('yyyy MMM d')}</Text>
          <Text strong>{Date.parse(text).toString('HH:mm')}</Text>
        </>
      );
    };

    return [
      {
        title: 'Time-in',
        dataIndex: 'in_time',
        render: dateRenderer,
        sorter: true,
      },
      {
        title: 'Time-out',
        dataIndex: 'out_time',
        render: dateRenderer,
        sorter: true,
      },
      {
        title: 'Hours',
        render: (text, record) => (<Progress
          size="small"
          steps={25}
          percent={record.hours ? (record.hours / 8) * 100 : 0}
          format={(percent, successPercent)=> record.hours + 'h / 8h'}
        />),
        width: '25%',
        dataIndex: 'hours',
      },
      {
        title: 'Note',
        dataIndex: 'note',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    if (this.useServerTime === 0) {
      return [
        ['id', { label: 'ID', type: 'hidden' }],
        ['time', { label: 'Time', type: 'datetime' }],
        ['note', { label: 'Note', type: 'textarea', validation: 'none' }],
      ];
    }
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['note', { label: 'Note', type: 'textarea', validation: 'none' }],
    ];
  }

  hasCustomTopButtons() {
    return true;
  }

  getCustomTopButtons() {
    let btnLabel = 'Punch In';
    if (!this.hasOpenPunch && this.punchedOutToday) {
      btnLabel = 'Punch In Again';
    } else if (this.hasOpenPunch) {
      btnLabel = 'Punch Out';
    } else if (!this.hasOpenPunch) {
      btnLabel = 'Punch In';
    }
    return (<Button type="primary" onClick={()=>modJs.showPunchDialog()}><PlusCircleOutlined/>{btnLabel}</Button>);
  }

  add(object, getFunctionCallBackData, callGetFunction, successCallback) {
    const that = this;
    let params = object;
    params = this.forceInjectValuesBeforeSave(params);
    params.cdate = this.getClientDate(new Date()).toISOString().slice(0, 19).replace('T', ' ');
    const reqJson = JSON.stringify(params);
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'saveSuccessCallback';
    callBackData.callBackFail = 'getPunchFailCallBack';

    this.customAction('savePunch', 'modules=attendance', reqJson, callBackData, true);
    callGetFunction();
    successCallback();
  }

  saveSuccessCallback(callBackData) {
    this.get([]);

    if (this.hasOpenPunch) {
      this.hasOpenPunch = 0;
    } else {
      this.hasOpenPunch = 1;
    }

    if (this.hasOpenPunch && !this.punchedOutToday) {
      this.punchedOutToday = 1;
    }
  }

  showPunchDialog() {
    modJs.renderForm();
  }

  getPunchFailCallBack(callBackData) {
    this.showMessage('Error Occurred while Time Punch', callBackData);
  }

  getClientDate(date) {
    const offset = this.getClientGMTOffset();
    const tzDate = date.addMinutes(offset * 60);
    return tzDate;
  }

  getClientGMTOffset() {
    const rightNow = new Date();
    const jan1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);
    const temp = jan1.toGMTString();
    const jan2 = new Date(temp.substring(0, temp.lastIndexOf(' ') - 1));
    return (jan1 - jan2) / (1000 * 60 * 60);
  }
}

module.exports = {
  AttendanceAdapter,
};
