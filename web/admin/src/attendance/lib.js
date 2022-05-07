/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';
import FormValidation from '../../../api/FormValidation';
import ReactModalAdapterBase from "../../../api/ReactModalAdapterBase";
import {Avatar, Progress, Space, Tag, Typography } from "antd";
import {CopyOutlined, DeleteOutlined, EditOutlined, MonitorOutlined, EnvironmentTwoTone} from "@ant-design/icons";

const { Text } = Typography;
import React from "react";

class AttendanceAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
  }

  getDataMapping() {
    return [
      'id',
      'employee',
      'in_time',
      'out_time',
      'hours',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
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
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
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
    ];
  }

  getFormFields() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': false, 'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['id', { label: 'ID', type: 'hidden' }],
      ['in_time', { label: 'Time-In', type: 'datetime' }],
      ['out_time', { label: 'Time-Out', type: 'datetime', validation: 'none' }],
      ['note', { label: 'Note', type: 'textarea', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': false, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],

    ];
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
        {adapter.hasAccess('element')
        && (
          <Tag color="blue" onClick={() => modJs.showPunchImages(record.id)} style={{ cursor: 'pointer' }}>
            <MonitorOutlined />
            {` ${adapter.gt('View')}`}
          </Tag>
        )}
        {adapter.hasAccess('delete') && adapter.showDelete
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

  showPunchImages(id) {
    const reqJson = JSON.stringify({ id });
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getImagesSuccessCallback';
    callBackData.callBackFail = 'getImagesFailCallback';
    this.customAction('getImages', 'admin=attendance', reqJson, callBackData);
  }

  getImagesSuccessCallback(callBackData) {

    $('#attendnaceCanvasPunchInTime').html('');
    $('#attendnaceCanvasPunchOutTime').html('');
    $('#punchInLocation').html('');
    $('#punchOutLocation').html('');
    $('#punchInIp').html('');
    $('#punchOutIp').html('');

    $('#attendnaceMapCanvasIn').remove();
    $('#attendnaceMapCanvasInWrapper').html('<canvas id="attendnaceMapCanvasIn" height="156" width="208" style="border: 1px #222 dotted;"></canvas>');

    $('#attendnaceMapCanvasOut').remove();
    $('#attendnaceMapCanvasOutWrapper').html('<canvas id="attendnaceMapCanvasOut" height="156" width="208" style="border: 1px #222 dotted;"></canvas>');

    $('#attendancePhotoModel').modal('show');
    $('#attendnaceCanvasEmp').html(callBackData.employee_Name);
    if (callBackData.in_time) {
      $('#attendnaceCanvasPunchInTime').html(Date.parse(callBackData.in_time).toString('yyyy MMM d  <b>HH:mm</b>'));
    }

    if (callBackData.out_time) {
      $('#attendnaceCanvasPunchOutTime').html(Date.parse(callBackData.out_time).toString('yyyy MMM d  <b>HH:mm</b>'));
    }

    if (callBackData.map_lat) {
      $('#attendanceMap').show();
      $('#punchInLocation').html(`${callBackData.map_lat},${callBackData.map_lng}`);
    }

    if (callBackData.map_out_lat) {
      $('#attendanceMap').show();
      $('#punchOutLocation').html(`${callBackData.map_out_lat},${callBackData.map_out_lng}`);
    }

    if (callBackData.in_ip) {
      $('#punchInIp').html(callBackData.in_ip);
    }
    if (callBackData.out_ip) {
      $('#punchOutIp').html(callBackData.out_ip);
    }

    if (callBackData.map_snapshot) {
      $('#attendanceMap').show();
      const myCanvas = document.getElementById('attendnaceMapCanvasIn');
      try {
        const ctx = myCanvas.getContext('2d');
        const img = new Image();
        img.onload = function () {
          ctx.drawImage(img, 0, 0); // Or at whatever offset you like
        };
        img.src = callBackData.map_snapshot;
      } catch (e) {
        console.log(e);
      }
    }

    if (callBackData.map_out_snapshot) {
      $('#attendanceMap').show();
      const myCanvas = document.getElementById('attendnaceMapCanvasOut');
      try {
        const ctx = myCanvas.getContext('2d');
        const img = new Image();
        img.onload = function () {
          ctx.drawImage(img, 0, 0); // Or at whatever offset you like
        };
        img.src = callBackData.map_out_snapshot;
      } catch (e) {
        console.log(e);
      }
    }

    if (callBackData.note) {
      $('#attendanceNoteWrapper').show();
      $('#attendanceNote').html(callBackData.note);
    } else {
      $('#attendanceNoteWrapper').hide();
    }

  }


  getImagesFailCallback(callBackData) {
    this.showMessage('Error', callBackData);
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/time-and-attendance/attendance-time-management';
  }
}


/*
 Attendance Status
 */

class AttendanceStatusAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Clocked In Status' },
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
        title: 'Clocked In Status',
        dataIndex: 'status',
        render: (text, record) => (
          <Space>
            <EnvironmentTwoTone twoToneColor={text === 'Not Clocked In'?'orange':'#52c41a'}/>
            <Text>{text}</Text>
          </Space>
        ),
        sorter: true,
      },
    ];
  }

  getFormFields() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': false, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
    ];
  }

  getFilters() {
    return [
      ['department', {
        label: 'Department', type: 'select2', 'allow-null': true, 'null-label': 'All Departments', 'remote-source': ['CompanyStructure', 'id', 'title'],
      }],
    ];
  }

  getActionButtonsHtml(id, data) {
    let html = '<div class="online-button-_COLOR_"></div>';
    html = html.replace(/_BASE_/g, this.baseUrl);
    if (data[2] == 'Not Clocked In') {
      html = html.replace(/_COLOR_/g, 'gray');
    } else if (data[2] == 'Clocked Out') {
      html = html.replace(/_COLOR_/g, 'yellow');
    } else if (data[2] == 'Clocked In') {
      html = html.replace(/_COLOR_/g, 'green');
    }
    return html;
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/time-and-attendance/attendance-time-management';
  }
}

module.exports = { AttendanceAdapter, AttendanceStatusAdapter };
