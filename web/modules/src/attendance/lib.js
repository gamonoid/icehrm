/*
Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/* global modJs, modJsList */

import AdapterBase from '../../../api/AdapterBase';
import FormValidation from '../../../api/FormValidation';

class AttendanceAdapter extends AdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.punch = null;
    this.useServerTime = 0;
    this.photoTaken = 0;
    this.photoAttendance = 0;
  }

  updatePunchButton() {
    this.getPunch('changePunchButtonSuccessCallBack');
  }

  setUseServerTime(val) {
    this.useServerTime = val;
  }

  setPhotoAttendance(val) {
    this.photoAttendance = parseInt(val, 10);
  }


  getDataMapping() {
    return [
      'id',
      'in_time',
      'out_time',
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


  getCustomTableParams() {
    const that = this;
    const dataTableParams = {
      aoColumnDefs: [
        {
          fnRender(data, cell) {
            return that.preProcessRemoteTableData(data, cell, 1);
          },
          aTargets: [1],
        },
        {
          fnRender(data, cell) {
            return that.preProcessRemoteTableData(data, cell, 2);
          },
          aTargets: [2],
        },
        {
          fnRender(data, cell) {
            return that.preProcessRemoteTableData(data, cell, 3);
          },
          aTargets: [3],
        },
        {
          fnRender: that.getActionButtons,
          aTargets: [that.getDataMapping().length],
        },
      ],
    };
    return dataTableParams;
  }

  preProcessRemoteTableData(data, cell, id) {
    if (id === 1) {
      if (cell === '0000-00-00 00:00:00' || cell === '' || cell === undefined || cell === null) {
        return '';
      }
      return Date.parse(cell).toString('yyyy MMM d  <b>HH:mm</b>');
    } if (id === 2) {
      if (cell === '0000-00-00 00:00:00' || cell === '' || cell === undefined || cell === null) {
        return '';
      }
      return Date.parse(cell).toString('MMM d  <b>HH:mm</b>');
    } if (id === 3) {
      if (cell !== undefined && cell !== null) {
        if (cell.length > 20) {
          return `${cell.substring(0, 20)}..`;
        }
      }
      return cell;
    }
    return cell;
  }


  getActionButtonsHtml(id, data) {
    return '';
  }

  getTableTopButtonHtml() {
    if (this.punch === null || this.punch === undefined) {
      return '<button id="punchButton" style="float:right;" onclick="modJs.showPunchDialog();return false;" class="btn btn-small">Punch-in <span class="icon-time"></span></button>';
    }
    return '<button id="punchButton" style="float:right;" onclick="modJs.showPunchDialog();return false;" class="btn btn-small">Punch-out <span class="icon-time"></span></button>';
  }


  save() {
    const that = this;
    const validator = new FormValidation(`${this.getTableName()}_submit`, true, { ShowPopup: false, LabelErrorClass: 'error' });
    if (validator.checkValues()) {
      const msg = this.doCustomValidation();
      if (msg == null) {
        let params = validator.getFormParameters();
        params = this.forceInjectValuesBeforeSave(params);
        params.cdate = this.getClientDate(new Date()).toISOString().slice(0, 19).replace('T', ' ');
        const reqJson = JSON.stringify(params);
        const callBackData = [];
        callBackData.callBackData = [];
        callBackData.callBackSuccess = 'saveSuccessCallback';
        callBackData.callBackFail = 'getPunchFailCallBack';

        this.customAction('savePunch', 'modules=attendance', reqJson, callBackData, true);
      } else {
        $(`#${this.getTableName()}Form .label`).html(msg);
        $(`#${this.getTableName()}Form .label`).show();
      }
    }
  }

  saveSuccessCallback(callBackData) {
    this.punch = callBackData;
    this.getPunch('changePunchButtonSuccessCallBack');
    $('#PunchModel').modal('hide');
    this.get([]);
  }


  cancel() {
    $('#PunchModel').modal('hide');
  }

  showPunchDialog() {
    this.getPunch('showPunchDialogShowPunchSuccessCallBack');
  }

  getPunch(successCallBack) {
    const that = this;
    const object = {};

    object.date = this.getClientDate(new Date()).toISOString().slice(0, 19).replace('T', ' ');
    object.offset = this.getClientGMTOffset();
    const reqJson = JSON.stringify(object);
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = successCallBack;
    callBackData.callBackFail = 'getPunchFailCallBack';

    this.customAction('getPunch', 'modules=attendance', reqJson, callBackData);
  }


  showPunchDialogShowPunchSuccessCallBack(callBackData) {
    this.punch = callBackData;
    $('#PunchModel').modal('show');
    if (this.punch === null) {
      $('#PunchModel').find('h3').html('Punch Time-in');
      modJs.renderForm();
    } else {
      $('#PunchModel').find('h3').html('Punch Time-out');
      modJs.renderForm(this.punch);
    }
    $('#Attendance').show();
    const picker = $('#time_datetime').data('datetimepicker');
    picker.setLocalDate(new Date());
  }

  changePunchButtonSuccessCallBack(callBackData) {
    this.punch = callBackData;
    if (this.punch === null) {
      $('#punchButton').html('Punch-in <span class="icon-time"></span>');
    } else {
      $('#punchButton').html('Punch-out <span class="icon-time"></span>');
    }
  }

  getPunchFailCallBack(callBackData) {
    this.showMessage('Error Occured while Time Punch', callBackData);
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

  doCustomValidation(params) {
    if (this.photoAttendance === 1 && !this.photoTaken) {
      return 'Please attach a photo before submitting';
    }
    return null;
  }

  forceInjectValuesBeforeSave(params) {
    if (this.photoAttendance === 1) {
      const canvas = document.getElementById('attendnaceCanvas');
      params.image = canvas.toDataURL();
    }
    return params;
  }

  postRenderForm() {
    if (this.photoAttendance === 1) {
      $('.photoAttendance').show();
      const video = document.getElementById('attendnaceVideo');

      // Get access to the camera!
      if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        navigator.mediaDevices.getUserMedia({ video: true }).then((stream) => {
          video.src = window.URL.createObjectURL(stream);
          video.play();
        });
      }
      this.photoTaken = false;
      this.configureEvents();
    } else {
      $('.photoAttendance').remove();
    }
  }

  configureEvents() {
    const that = this;
    const canvas = document.getElementById('attendnaceCanvas');
    const context = canvas.getContext('2d');
    const video = document.getElementById('attendnaceVideo');
    $('.attendnaceSnap').click(() => {
      context.drawImage(video, 0, 0, 208, 156);
      that.photoTaken = true;
      return false;
    });
  }
}


class EmployeeAttendanceSheetAdapter extends AdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.currentTimesheetId = null;
    this.currentTimesheet = null;
  }

  getDataMapping() {
    return [
      'id',
      'date_start',
      'date_end',
      'total_time',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Start Date' },
      { sTitle: 'End Date' },
      { sTitle: 'Total Time' },
      { sTitle: 'Status' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['date_start', { label: 'TimeSheet Start Date', type: 'date', validation: '' }],
      ['date_end', { label: 'TimeSheet End Date', type: 'date', validation: '' }],
      ['details', { label: 'Reason', type: 'textarea', validation: 'none' }],
    ];
  }

  preProcessTableData(row) {
    row[1] = Date.parse(row[1]).toString('MMM d, yyyy (dddd)');
    row[2] = Date.parse(row[2]).toString('MMM d, yyyy (dddd)');
    return row;
  }

  renderForm(object) {
    const formHtml = this.templates.formTemplate;
    const html = '';

    $(`#${this.getTableName()}Form`).html(formHtml);
    $(`#${this.getTableName()}Form`).show();
    $(`#${this.getTableName()}`).hide();

    $('#attendnacesheet_start').html(Date.parse(object.date_start).toString('MMM d, yyyy (dddd)'));
    $('#attendnacesheet_end').html(Date.parse(object.date_end).toString('MMM d, yyyy (dddd)'));

    this.currentTimesheet = object;

    this.getTimeEntries();
  }


  getTimeEntries() {
    const timesheetId = this.currentId;
    const sourceMappingJson = JSON.stringify(modJsList.tabEmployeeTimeEntry.getSourceMapping());

    const reqJson = JSON.stringify({ id: timesheetId, sm: sourceMappingJson });

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getTimeEntriesSuccessCallBack';
    callBackData.callBackFail = 'getTimeEntriesFailCallBack';

    this.customAction('getTimeEntries', 'modules=time_sheets', reqJson, callBackData);
  }

  getTimeEntriesSuccessCallBack(callBackData) {
    const entries = callBackData;
    let html = '';
    const temp = '<tr><td><img class="tableActionButton" src="_BASE_images/delete.png" style="cursor:pointer;" rel="tooltip" title="Delete" onclick="modJsList[\'tabEmployeeTimeEntry\'].deleteRow(_id_);return false;"></img></td><td>_start_</td><td>_end_</td><td>_duration_</td><td>_project_</td><td>_details_</td>';

    for (let i = 0; i < entries.length; i++) {
      try {
        let t = temp;
        t = t.replace(/_start_/g, Date.parse(entries[i].date_start).toString('MMM d, yyyy [hh:mm tt]'));
        t = t.replace(/_end_/g, Date.parse(entries[i].date_end).toString('MMM d, yyyy [hh:mm tt]'));

        const mili = Date.parse(entries[i].date_end) - Date.parse(entries[i].date_start);
        const minutes = Math.round(mili / 60000);
        const hourMinutes = (minutes % 60);
        const hours = (minutes - hourMinutes) / 60;

        t = t.replace(/_duration_/g, `Hours (${hours}) - Min (${hourMinutes})`);
        if (entries[i].project === 'null' || entries[i].project === null || entries[i].project === undefined) {
          t = t.replace(/_project_/g, 'None');
        } else {
          t = t.replace(/_project_/g, entries[i].project);
        }
        t = t.replace(/_project_/g, entries[i].project);
        t = t.replace(/_details_/g, entries[i].details);
        t = t.replace(/_id_/g, entries[i].id);
        t = t.replace(/_BASE_/g, this.baseUrl);
        html += t;
      } catch (e) {
        // DN
      }
    }

    $('.timesheet_entries_table_body').html(html);
    if (modJs.getTableName() === 'SubEmployeeTimeSheetAll') {
      $('#submit_sheet').hide();
      $('#add_time_sheet_entry').hide();
    } else if (this.currentElement.status === 'Approved') {
      $('#submit_sheet').hide();
      $('#add_time_sheet_entry').hide();
    } else {
      $('#submit_sheet').show();
      $('#add_time_sheet_entry').show();
    }
  }

  getTimeEntriesFailCallBack(callBackData) {
    this.showMessage('Error', 'Error occured while getting timesheet entries');
  }


  createPreviousAttendnacesheet(id) {
    const reqJson = JSON.stringify({ id });

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'createPreviousAttendnacesheetSuccessCallBack';
    callBackData.callBackFail = 'createPreviousAttendnacesheetFailCallBack';

    this.customAction('createPreviousAttendnaceSheet', 'modules=attendnace', reqJson, callBackData);
  }

  createPreviousAttendnacesheetSuccessCallBack(callBackData) {
    $('.tooltip').css('display', 'none');
    $('.tooltip').remove();
    // this.showMessage("Success", "Previous Timesheet created");
    this.get([]);
  }

  createPreviousAttendnacesheetFailCallBack(callBackData) {
    this.showMessage('Error', callBackData);
  }


  getActionButtonsHtml(id, data) {
    let html = '';
    if (this.getTableName() === 'EmployeeTimeSheetAll') {
      html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.edit(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/redo.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Create previous time sheet" onclick="modJs.createPreviousAttendnacesheet(_id_);return false;"></img></div>';
    } else {
      html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.edit(_id_);return false;"></img></div>';
    }
    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }

  getCustomTableParams() {
    const that = this;
    const dataTableParams = {
      aoColumnDefs: [
        {
          fnRender(data, cell) {
            return that.preProcessRemoteTableData(data, cell, 1);
          },
          aTargets: [1],
        },
        {
          fnRender(data, cell) {
            return that.preProcessRemoteTableData(data, cell, 2);
          },
          aTargets: [2],
        },
        {
          fnRender: that.getActionButtons,
          aTargets: [that.getDataMapping().length],
        },
      ],
    };
    return dataTableParams;
  }

  preProcessRemoteTableData(data, cell, id) {
    return Date.parse(cell).toString('MMM d, yyyy (dddd)');
  }
}

module.exports = {
  AttendanceAdapter,
  EmployeeAttendanceSheetAdapter,
};
