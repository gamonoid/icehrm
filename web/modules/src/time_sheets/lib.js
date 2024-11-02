/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/* global modJs, modJsList, moment */
import AdapterBase from '../../../api/AdapterBase';
import FormValidation from '../../../api/FormValidation';
import TableEditAdapter from '../../../api/TableEditAdapter';

const ValidationRules = FormValidation.getValidationRules();

class EmployeeTimeSheetAdapter extends AdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.currentTimesheetId = null;
    this.currentTimesheet = null;
    this.needStartEndTime = false;
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

  preProcessTableData(_row) {
    const row = _row;
    row[1] = Date.parse(row[1]).toString('MMM d, yyyy (dddd)');
    row[2] = Date.parse(row[2]).toString('MMM d, yyyy (dddd)');
    return row;
  }

  setNeedStartEndTime(status) {
    this.needStartEndTime = status;
  }

  renderForm(object) {
    const formHtml = this.templates.formTemplate;

    $('#EmployeeTimesheetBlock').remove();
    $(`#${this.getTableName()}Form`).html(formHtml);
    $(`#${this.getTableName()}Form`).show();
    $(`#${this.getTableName()}`).hide();

    $('.timesheet_start').html(Date.parse(object.date_start).toString('MMM d, yyyy (dddd)'));
    $('.timesheet_end').html(Date.parse(object.date_end).toString('MMM d, yyyy (dddd)'));

    this.currentTimesheet = object;

    this.getTimeEntries();

    const st = Date.parse(object.date_start);

    $('#EmployeeTimesheetBlock').fullCalendar({
      header: {
        // left: 'prev,next today',
        left: false,
        // center: 'title',
        center: false,
        // right: 'month,agendaWeek,agendaDay'
        right: false,
      },
      year: st.toString('yyyy'),
      month: st.toString('M'),
      date: st.toString('d'),

      defaultView: 'basicWeek',
      height: 200,
      editable: false,

      events: modJs.getScheduleJsonUrl(this.currentTimesheet.employee),

      loading(bool) {
        if (bool) $('#loadingBlock').show();
        else $('#loadingBlock').hide();
      },

      // eslint-disable-next-line no-unused-vars
      dayClick(date, jsEvent, view, resourceObj) {
        modJs.renderFormByDate(date.format());
      },

      // eslint-disable-next-line no-unused-vars
      eventClick(calEvent, jsEvent, view) {
        modJs.renderFormTimeEntryCalender(calEvent.id);
      },
      eventRender(event, element) {
        element.find('.fc-time').remove();
      },
    });

    $('#EmployeeTimesheetBlock').fullCalendar('gotoDate', st);

    $('.fc-toolbar').hide();
  }


  quickEdit(id, status, sdate, edate) {
    $('#Qtsheet').data('lastActiveTab', modJs.tab);
    // eslint-disable-next-line no-global-assign
    modJs = modJsList.tabQtsheet;
    modJs.setCurrentTimeSheetId(id);

    $('.timesheet_start').html(sdate);
    $('.timesheet_end').html(edate);

    $('#timesheetTabs').find('.active').find('.reviewBlock.reviewBlockTable').hide();
    $('#QtsheetHeader').show();
    $('#Qtsheet').show();
    $('#QtsheetDataButtons').show();

    if (status === 'Submitted') {
      $('.completeBtnTable').hide();
      $('.saveBtnTable').show();
    } else if (status === 'Approved') {
      $('.completeBtnTable').hide();
      $('.saveBtnTable').hide();
    } else {
      $('.completeBtnTable').show();
      $('.saveBtnTable').show();
    }

    modJs.get([]);
    this.getLeaveMessage(id);
  }

  getLeaveMessage(timesheetId) {
    const object = { id: timesheetId };

    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getLeaveMessageCallBack';
    callBackData.callBackFail = 'getLeaveMessageCallBack';

    this.customAction('getLeaveMessage', 'modules=time_sheets', reqJson, callBackData);
  }

  getLeaveMessageCallBack(callBackData) {
    if (callBackData !== '') {
      $('#LeaveDaysForTimeSheet').html(callBackData);
      $('#LeaveDaysForTimeSheet').show();
    } else {
      $('#LeaveDaysForTimeSheet').html('');
      $('#LeaveDaysForTimeSheet').hide();
    }
  }


  getScheduleJsonUrl(employeeId) {
    const url = `${this.moduleRelativeURL}?a=ca&sa=getEmployeeTimeEntries&t=${this.table}&mod=modules%3Dtime_sheets&e=${employeeId}`;
    return url;
  }


  renderFormByDate(_date) {
    let date = _date;

    if (date.indexOf('T') < 0) {
      const s1 = moment();
      date = `${date} ${s1.format('HH:mm:ss')}`;
    }

    const start = date.replace('T', ' ');
    const m1 = moment(start);
    m1.add(1, 'h');
    const end = m1.format('YYYY-MM-DD HH:mm:ss');

    const obj = {};
    obj.date = _date;
    obj.date_start = start;
    obj.date_end = end;

    this.renderFormTimeEntryCalender(obj);
  }


  renderFormTimeEntryCalender(object) {
    if (`${this.needStartEndTime}` === '0') {
      return;
    }
    this.openTimeEntryDialog(object);
    if (object.id !== undefined && object.id != null) {
      const cid = object.id;
      $('.deleteBtnWorkSchedule').show();
      $('.deleteBtnWorkSchedule').off().on('click', () => {
        modJs.deleteRow(cid);
        return false;
      });
    } else {
      $('.deleteBtnWorkSchedule').remove();
    }
  }


  openTimeEntryDialog(object) {
    this.currentTimesheetId = this.currentId;
    const obj = modJsList.tabEmployeeTimeEntry;
    $('#TimeEntryModel').modal({
      backdrop: 'static',
      keyboard: false,
    });
    obj.currentTimesheet = this.currentTimesheet;
    obj.renderForm(object);
    obj.timesheetId = this.currentId;
  }

  closeTimeEntryDialog() {
    $('#TimeEntryModel').modal('hide');
  }


  getTimeEntries() {
    const timesheetId = this.currentId;
    const sourceMappingJson = JSON.stringify(modJsList.tabEmployeeTimeEntry.getSourceMapping());
    const object = { id: timesheetId, sm: sourceMappingJson };

    const reqJson = JSON.stringify(object);

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
        if (entries[i].project === 'null' || entries[i].project == null || entries[i].project === undefined) {
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
        // Do nothing
      }
    }

    $('.timesheet_entries_table_body').html(html);
    if (modJs.getTableName() === 'SubEmployeeTimeSheetAll' || `${this.needStartEndTime}` === '0') {
      $('.submit_sheet').hide();
      $('.add_time_sheet_entry').hide();
    } else if (this.currentElement.status === 'Approved') {
      $('.submit_sheet').hide();
      $('.add_time_sheet_entry').hide();
    } else {
      $('.submit_sheet').show();
      $('.add_time_sheet_entry').show();
    }

    $('#EmployeeTimesheetBlock').fullCalendar('refetchEvents');
  }

  // eslint-disable-next-line no-unused-vars
  getTimeEntriesFailCallBack(callBackData) {
    this.showMessage('Error', 'Error occured while getting timesheet entries');
  }


  createPreviousTimesheet(id) {
    const object = { id };

    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'createPreviousTimesheetSuccessCallBack';
    callBackData.callBackFail = 'createPreviousTimesheetFailCallBack';

    this.customAction('createPreviousTimesheet', 'modules=time_sheets', reqJson, callBackData);
  }

  // eslint-disable-next-line no-unused-vars
  createPreviousTimesheetSuccessCallBack(callBackData) {
    $('.tooltip').css('display', 'none');
    $('.tooltip').remove();
    // this.showMessage("Success", "Previous Timesheet created");
    this.get([]);
  }

  createPreviousTimesheetFailCallBack(callBackData) {
    this.showMessage('Error', callBackData);
  }

  createNextWeekTimesheet(id) {
    const object = { id };

    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'createNextWeekTimesheetSuccessCallBack';
    callBackData.callBackFail = 'createNextWeekTimesheetFailCallBack';

    this.customAction('createNextWeekTimesheet', 'modules=time_sheets', reqJson, callBackData);
  }

  // eslint-disable-next-line no-unused-vars
  createNextWeekTimesheetSuccessCallBack(callBackData) {
    $('.tooltip').css('display', 'none');
    $('.tooltip').remove();
    this.get([]);
  }

  createNextWeekTimesheetFailCallBack(callBackData) {
    this.showMessage('Error', callBackData);
  }

  changeTimeSheetStatusWithId(id, status) {
    if (status === '' || status == null || status === undefined) {
      this.showMessage('Status Error', 'Please select a status');
      return;
    }

    const object = { id, status };

    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'changeTimeSheetStatusSuccessCallBack';
    callBackData.callBackFail = 'changeTimeSheetStatusFailCallBack';

    this.customAction('changeTimeSheetStatus', 'modules=time_sheets', reqJson, callBackData);
  }

  // eslint-disable-next-line no-unused-vars
  changeTimeSheetStatusSuccessCallBack(callBackData) {
    this.showMessage('Successful', 'Timesheet status changed successfully');
    this.get([]);
  }

  // eslint-disable-next-line no-unused-vars
  changeTimeSheetStatusFailCallBack(callBackData) {
    this.showMessage('Error', 'Error occured while changing Timesheet status');
  }


  getActionButtonsHtml(id, data) {
    let html = '';
    if (`${this.needStartEndTime}` === '0') {
      html = '<div style="width:150px;">'
        + '<img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.edit(_id_);return false;"></img>'
        + '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.quickEdit(_id_,\'_status_\',\'_sdate_\',\'_edate_\');return false;"></img>'
        + '_redoBtn_'
        + '_nextBtn_'
        + '</div>';
    } else {
      html = '<div style="width:120px;">'
        + '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.edit(_id_);return false;"></img>'
        + '_redoBtn_'
        + '_nextBtn_'
        + '</div>';
    }

    if (this.getTableName() === 'EmployeeTimeSheetAll') {
      const redoBtn = '<img class="tableActionButton" src="_BASE_images/prev_ts.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Create previous timesheet" onclick="modJs.createPreviousTimesheet(_id_);return false;"></img>';
      html = html.replace(/_redoBtn_/g, redoBtn);
    } else {
      html = html.replace(/_redoBtn_/g, '');
    }
    if (this.getTableName() === 'EmployeeTimeSheetAll') {
      const nextBtn = '<img class="tableActionButton" src="_BASE_images/next_ts.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Create next week timesheet" onclick="modJs.createNextWeekTimesheet(_id_);return false;"></img>';
      html = html.replace(/_nextBtn_/g, nextBtn);
    } else {
      html = html.replace(/_nextBtn_/g, '');
    }
    html = html.replace(/_id_/g, id);
    html = html.replace(/_sdate_/g, data[1]);
    html = html.replace(/_edate_/g, data[2]);
    html = html.replace(/_status_/g, data[4]);
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

  // eslint-disable-next-line no-unused-vars
  preProcessRemoteTableData(data, cell, id) {
    return Date.parse(cell).toString('MMM d, yyyy (dddd)');
  }
}


/*
 * Subordinate TimeSheets
 */

class SubEmployeeTimeSheetAdapter extends EmployeeTimeSheetAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.timeSheetStatusChangeId = null;
  }

  getDataMapping() {
    return [
      'id',
      'employee',
      'date_start',
      'date_end',
      'total_time',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee', bSearchable: true },
      { sTitle: 'Start Date', bSearchable: true },
      { sTitle: 'End Date', bSearchable: true },
      { sTitle: 'Total Time', bSearchable: false },
      { sTitle: 'Status' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee', type: 'select', 'allow-null': false, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['date_start', { label: 'TimeSheet Start Date', type: 'date', validation: '' }],
      ['date_end', { label: 'TimeSheet Start Date', type: 'date', validation: '' }],
      ['details', { label: 'Reason', type: 'textarea', validation: 'none' }],
    ];
  }


  isSubProfileTable() {
    return true;
  }

  getCustomSuccessCallBack(serverData) {
    const data = [];
    const mapping = this.getDataMapping();
    for (let i = 0; i < serverData.length; i++) {
      const row = [];
      for (let j = 0; j < mapping.length; j++) {
        row[j] = serverData[i][mapping[j]];
      }
      data.push(this.preProcessTableData(row));
    }

    this.tableData = data;

    this.createTable(this.getTableName());
    $(`#${this.getTableName()}Form`).hide();
    $(`#${this.getTableName()}`).show();
  }

  preProcessTableData(_row) {
    const row = _row;
    row[2] = Date.parse(row[2]).toString('MMM d, yyyy (dddd)');
    row[3] = Date.parse(row[3]).toString('MMM d, yyyy (dddd)');
    return row;
  }

  openTimeSheetStatus(timeSheetId, status) {
    this.currentTimesheetId = timeSheetId;
    $('#TimeSheetStatusModel').modal('show');
    $('#timesheet_status').val(status);
    this.timeSheetStatusChangeId = timeSheetId;
  }

  closeTimeSheetStatus() {
    $('#TimeSheetStatusModel').modal('hide');
  }

  changeTimeSheetStatus() {
    const timeSheetStatus = $('#timesheet_status').val();

    this.changeTimeSheetStatusWithId(this.timeSheetStatusChangeId, timeSheetStatus);

    this.closeTimeSheetStatus();
    this.timeSheetStatusChangeId = null;
  }


  getActionButtonsHtml(id, data) {
    let html;


    if (`${this.needStartEndTime}` === '0') {
      html = '<div style="width:100px;">'
        + '<img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.edit(_id_);return false;"></img>'
        + '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.quickEdit(_id_,\'_status_\',\'_sdate_\',\'_edate_\');return false;"></img>'
        + '<img class="tableActionButton" src="_BASE_images/run.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Change TimeSheet Status" onclick="modJs.openTimeSheetStatus(_id_,\'_status_\');return false;"></img>'
        + '</div>';
    } else {
      html = '<div style="width:80px;">'
        + '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit Timesheet Entries" onclick="modJs.edit(_id_);return false;"></img>'
        + '<img class="tableActionButton" src="_BASE_images/run.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Change TimeSheet Status" onclick="modJs.openTimeSheetStatus(_id_,\'_status_\');return false;"></img>'
        + '</div>';
    }


    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    html = html.replace(/_sdate_/g, data[1]);
    html = html.replace(/_edate_/g, data[2]);
    html = html.replace(/_status_/g, data[4]);
    return html;
  }


  getCustomTableParams() {
    const that = this;
    const dataTableParams = {
      aoColumnDefs: [
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

  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'null-label': 'All Employees', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['status', {
        label: 'Status', type: 'select', 'allow-null': true, 'null-label': 'All', source: [['Submitted', 'Submitted'], ['Pending', 'Pending'], ['Approved', 'Approved'], ['Rejected', 'Rejected']],
      }],
    ];
  }
}


/**
 * EmployeeTimeEntryAdapter
 */

class EmployeeTimeEntryAdapter extends AdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.timesheetId = null;
    this.currentTimesheet = null;
    this.allProjectsAllowed = 1;
    this.employeeProjects = [];
  }

  getDataMapping() {
    return [
      'id',
      'project',
      'date_start',
      'time_start',
      'date_end',
      'time_end',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Project' },
      { sTitle: 'Start Date' },
      { sTitle: 'Start Time' },
      { sTitle: 'End Date' },
      { sTitle: 'End Time' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['project', {
        label: 'Project', type: 'select2', 'allow-null': false, 'remote-source': ['Project', 'id', 'name', 'getEmployeeProjects'],
      }],
      ['date_select', { label: 'Date', type: 'select', source: [] }],
      ['date_start', { label: 'Start Time', type: 'time', validation: '' }],
      ['date_end', { label: 'End Time', type: 'time', validation: '' }],
      ['details', { label: 'Details', type: 'textarea', validation: '' }],
    ];
  }


  getDates(startDate, stopDate) {
    const dateArray = [];
    let currentDate = startDate;
    while (currentDate <= stopDate) {
      dateArray.push(new Date(currentDate));
      currentDate = currentDate.add({ days: 1 });
    }
    return dateArray;
  }

  renderForm(object) {
    this.initMasterDataReader();
    this.masterDataReader.updateAllMasterData()
      .then(() => {
        this._renderForm(object);
      });
  }

  _renderForm(object) {
    let formHtml = this.getCustomTemplate('time_entry_form.html');
    formHtml = formHtml.replace(/modJs/g, "modJsList['tabEmployeeTimeEntry']");
    let html = '';
    const fields = this.getFormFields();

    for (let i = 0; i < fields.length; i++) {
      const metaField = this.getMetaFieldForRendering(fields[i][0]);
      if (metaField === '' || metaField === undefined) {
        html += this.renderFormField(fields[i]);
      } else {
        const metaVal = object[metaField];
        if (metaVal !== '' && metaVal != null && metaVal !== undefined && metaVal.trim() !== '') {
          html += this.renderFormField(JSON.parse(metaVal));
        } else {
          html += this.renderFormField(fields[i]);
        }
      }
    }

    // append dates

    // var dateStart = new Date(this.currentTimesheet.date_start);
    // var dateStop = new Date(this.currentTimesheet.date_end);

    // var datesArray = this.getDates(dateStart, dateStop);

    let optionList = '';
    for (let i = 0; i < this.currentTimesheet.days.length; i++) {
      const k = this.currentTimesheet.days[i];
      // optionList += '<option value="'+timeUtils.getMySQLFormatDate(k)+'">'+k.toUTCString().slice(0, -13)+'</option>';
      optionList += `<option value="${k[0]}">${k[1]}</option>`;
    }


    formHtml = formHtml.replace(/_id_/g, `${this.getTableName()}_submit`);
    formHtml = formHtml.replace(/_fields_/g, html);
    $(`#${this.getTableName()}Form`).html(formHtml);
    $(`#${this.getTableName()}Form`).show();
    $(`#${this.getTableName()}`).hide();

    $(`#${this.getTableName()}Form .datefield`).datepicker({ viewMode: 2 });
    $(`#${this.getTableName()}Form .datetimefield`).datetimepicker({
      language: 'en',
    });
    $(`#${this.getTableName()}Form .timefield`).datetimepicker({
      language: 'en',
      pickDate: false,
    });

    $(`#${this.getTableName()}Form .select2Field`).select2();

    $('#date_select').html(optionList);


    if (object !== undefined && object != null) {
      this.fillForm(object);
    }
  }


  // eslint-disable-next-line no-unused-vars
  fillForm(object, _formId, fields) {
    let formId = _formId;
    if (formId == null || formId === undefined || formId === '') {
      formId = `#${this.getTableName()}Form`;
    }

    if (object.id != null && object.id !== undefined) {
      $(`${formId} #id`).val(object.id);
    }

    if (object.project != null && object.project !== undefined) {
      $(`${formId} #project`).select2('val', object.project);
    }

    if (object.date != null && object.date !== undefined) {
      $(`${formId} #date_select`).val(object.date);
    }
  }


  cancel() {
    $('#TimeEntryModel').modal('hide');
  }

  setAllProjectsAllowed(allProjectsAllowed) {
    this.allProjectsAllowed = allProjectsAllowed;
  }

  setEmployeeProjects(employeeProjects) {
    this.employeeProjects = employeeProjects;
  }


  save() {
    const validator = new FormValidation(`${this.getTableName()}_submit`, true, { ShowPopup: false, LabelErrorClass: 'error' });

    if (validator.checkValues()) {
      const params = validator.getFormParameters();
      params.timesheet = this.timesheetId;

      params.time_start = params.date_start;
      params.time_end = params.date_end;

      params.date_start = `${params.date_select} ${params.date_start}`;
      params.date_end = `${params.date_select} ${params.date_end}`;


      const msg = this.doCustomValidation(params);

      if (msg == null) {
        const id = $(`#${this.getTableName()}_submit #id`).val();
        if (id != null && id !== undefined && id !== '') {
          params.id = id;
        }
        this.add(params, []);
        this.cancel();
      } else {
        $(`#${this.getTableName()}Form .label`).html(msg);
        $(`#${this.getTableName()}Form .label`).show();
      }
    }
  }

  doCustomValidation(params) {
    const st = Date.parse(params.date_start);
    const et = Date.parse(params.date_end);
    if (st.compareTo(et) !== -1) {
      return 'Start time should be less than End time';
    }
    /*
    var sd = Date.parse(this.currentTimesheet.date_start);
    var ed = Date.parse(this.currentTimesheet.date_end).addDays(1);

    if(sd.compareTo(et) != -1 || sd.compareTo(st) > 0 || st.compareTo(ed) != -1 || et.compareTo(ed) != -1){
        return "Start time and end time shoud be with in " + sd.toString('MMM d, yyyy (dddd)') + " and " + ed.toString('MMM d, yyyy (dddd)');
    }
    */
    return null;
  }

  // eslint-disable-next-line no-unused-vars
  addSuccessCallBack(callBackData, serverData) {
    this.get(callBackData);
    modJs.getTimeEntries();
  }

  deleteRow(id) {
    this.deleteObj(id, []);
  }

  // eslint-disable-next-line no-unused-vars
  deleteSuccessCallBack(callBackData, serverData) {
    modJs.getTimeEntries();
  }
}


/**
 * QtsheetAdapter
 */

class QtsheetAdapter extends TableEditAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.cellDataUpdates = {};
    this.currentId = null;
  }

  validateCellValue(element, evt, newValue) {
    if (!ValidationRules.float(newValue)) {
      return false;
    }
    const val = parseFloat(newValue);
    if (val < 0 || val > 24) {
      return false;
    }

    // Update total
    // Find current column number
    // Adding 2 because nth child is based on 1 and we are adding a virtual column for row names
    const coldNum = this.columnIDMap[element.data('colId')] + 2;
    let columnTotal = 0;
    let columnTotalWithoutCurrent = 0;
    $(`#${this.getTableName()} tr td:nth-child(${coldNum})`).each(function () {
      const rowId = $(this).data('rowId');
      let tval = '';
      if (element.data('rowId') === rowId) {
        tval = newValue;
      } else {
        tval = $(this).html();
      }

      if (rowId !== -1) {
        if (ValidationRules.float(tval)) {
          columnTotal += parseFloat(tval);
          if (element.data('rowId') !== rowId) {
            columnTotalWithoutCurrent += parseFloat(tval);
          }
        }
      } else if (columnTotal > 24) {
        $(this).html(columnTotalWithoutCurrent);
      } else {
        $(this).html(columnTotal);
      }
    });

    if (columnTotal > 24) {
      return false;
    }

    modJs.addCellDataUpdate(element.data('colId'), element.data('rowId'), newValue);
    return true;
  }

  setCurrentTimeSheetId(val) {
    this.currentId = val;
    this.cellDataUpdates = {};
  }


  addAdditionalRequestData(type, req) {
    if (type === 'updateData') {
      req.currentId = this.currentId;
    } else if (type === 'updateAllData') {
      req.currentId = this.currentId;
    } else if (type === 'getAllData') {
      req.currentId = this.currentId;
    }

    return req;
  }

  modifyCSVHeader(header) {
    header.unshift('');
    return header;
  }

  getCSVData() {
    let csv = '';

    for (let i = 0; i < this.csvData.length; i++) {
      csv += this.csvData[i].join(',');
      if (i < this.csvData.length - 1) {
        csv += '\r\n';
      }
    }

    return csv;
  }

  downloadTimesheet() {
    const element = document.createElement('a');
    element.setAttribute('href', `data:text/plain;charset=utf-8,${encodeURIComponent(this.getCSVData())}`);
    element.setAttribute('download', `timesheet_${this.currentId}.csv`);

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
  }

  createTable(elementId) {
    const data = this.getTableData();
    const headers = this.getHeaders();

    if (this.showActionButtons()) {
      headers.push({ sTitle: '', sClass: 'center' });
    }


    if (this.showActionButtons()) {
      for (let i = 0; i < data.length; i++) {
        data[i].push(this.getActionButtonsHtml(data[i][0], data[i]));
      }
    }
    let html = '';
    html = `${this.getTableTopButtonHtml()}<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-bordered table-striped" id="grid"></table></div>`;

    // Find current page
    const activePage = $(`#${elementId} .dataTables_paginate .active a`).html();
    let start = 0;
    if (activePage !== undefined && activePage !== null) {
      start = parseInt(activePage, 10) * 100 - 100;
    }

    $(`#${elementId}`).html(html);

    const dataTableParams = {
      oLanguage: {
        sLengthMenu: '_MENU_ records per page',
      },
      aaData: data,
      aoColumns: headers,
      bSort: false,
      iDisplayLength: 500,
      iDisplayStart: start,
    };


    const customTableParams = this.getCustomTableParams();

    $.extend(dataTableParams, customTableParams);

    $(`#${elementId} #grid`).dataTable(dataTableParams);

    $(`#${elementId} #grid tr:last`).find('td').removeClass('editcell');

    $('.dataTables_paginate ul').addClass('pagination');
    $('.dataTables_length').hide();
    $('.dataTables_filter input').addClass('form-control');
    $('.dataTables_filter input').attr('placeholder', 'Search');
    $('.dataTables_filter label').contents().filter(function () {
      return (this.nodeType === 3);
    }).remove();
    // $('.tableActionButton').tooltip();
    $(`#${elementId} #grid`).editableTableWidget();

    $(`#${elementId} #grid .editcell`).on('validate', function (evt, newValue) {
      return modJs.validateCellValue($(this), evt, newValue);
    });
  }
}


module.exports = {
  EmployeeTimeSheetAdapter,
  SubEmployeeTimeSheetAdapter,
  EmployeeTimeEntryAdapter,
  QtsheetAdapter,
};
