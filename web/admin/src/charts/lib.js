/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
/* global nv, d3 */
import BaseGraphAdapter from '../../../api/BaseGraphAdapter';

/*
 * AttendanceGraphAdapter
 */

class AttendanceGraphAdapter extends BaseGraphAdapter {
  getFormFields() {
    return [];
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'null-label': 'All Employees', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['start', { label: 'Start Date', type: 'date', validation: '' }],
      ['end', { label: 'End Date', type: 'date', validation: '' }],

    ];
  }

  get() {
    this.initFieldMasterData();
    this.getTimeUtilization();
  }


  doCustomFilterValidation(params) {
    const $errorElement = $(`#${this.table}_filter_error`);
    $errorElement.html('');
    $errorElement.hide();
    if (Date.parse(params.start).getTime() > Date.parse(params.end).getTime()) {
      $errorElement.html('End date should be a later date than start date');
      $errorElement.show();
      return false;
    }

    const dateDiff = (Date.parse(params.end).getTime() - Date.parse(params.start).getTime()) / (1000 * 60 * 60 * 24);

    if (dateDiff > 45 && (params.employee === undefined || params.employee == null || params.employee === 'NULL')) {
      $errorElement.html('Differance between start and end dates should not be more than 45 days, when creating chart for all employees');
      $errorElement.show();
      return false;
    } if (dateDiff > 90) {
      $errorElement.html('Differance between start and end dates should not be more than 90 days');
      $errorElement.show();
      return false;
    }

    return true;
  }

  getTimeUtilization(object, callBackData) {
    object = {};

    if (this.filter != null && this.filter !== undefined) {
      if (this.filter.employee !== 'NULL') {
        object.employee = this.filter.employee;
      }

      object.start = this.filter.start;
      object.end = this.filter.end;
    }

    const reqJson = JSON.stringify(object);

    callBackData = (callBackData === undefined || callBackData === null) ? [] : callBackData;
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getAttendanceSuccessCallBack';
    callBackData.callBackFail = 'getAttendanceFailCallBack';

    this.customAction('getAttendance', 'admin=charts', reqJson, callBackData);
  }


  getAttendanceFailCallBack(callBackData) {
    this.showMessage('Error Occured while getting data for chart', callBackData);
  }


  getAttendanceSuccessCallBack(callBackData) {
    const that = this;

    const filterHtml = that.getTableTopButtonHtml();
    $('#tabPageAttendanceGraph svg').remove();
    $('#tabPageAttendanceGraph div').remove();

    const $tabPageAttendanceGraph = $('#tabPageAttendanceGraph');
    $tabPageAttendanceGraph.html('');
    $tabPageAttendanceGraph.html(`${filterHtml}<svg></svg>`);


    nv.addGraph(() => {
      const chart = nv.models.multiBarChart()
        .margin({ bottom: 200 })
        .transitionDuration(0)
        .reduceXTicks(true) // If 'false', every single x-axis tick label will be rendered.
        .rotateLabels(45) // Angle to rotate x-axis labels.
        .showControls(false) // Allow user to switch between 'Grouped' and 'Stacked' mode.
        .groupSpacing(0.1); // Distance between each group of bars.

      chart.yAxis
        .tickFormat(d3.format(',.1f'));

      chart.dispatch.on('stateChange', (e) => { nv.log('New State:', JSON.stringify(e)); });

      chart.tooltip((key, x, y, e, graph) => `<p><strong>${key}</strong></p>`
                    + `<p>${y} on ${x}</p>`);


      d3.select('#tabPageAttendanceGraph svg')
        .datum(callBackData)
        .call(chart);

      return chart;
    });
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/insights/analytics';
  }
}


/*
 * TimeUtilizationGraphAdapter
 */


class TimeUtilizationGraphAdapter extends BaseGraphAdapter {
  getFormFields() {
    return [];
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'null-label': 'All Employees', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['start', { label: 'Start Date', type: 'date', validation: '' }],
      ['end', { label: 'End Date', type: 'date', validation: '' }],

    ];
  }

  get() {
    this.initFieldMasterData();
    this.getTimeUtilization();
  }

  doCustomFilterValidation(params) {
    const $errorElement = $(`#${this.table}_filter_error`);
    $errorElement.html('');
    $errorElement.hide();
    if (Date.parse(params.start).getTime() > Date.parse(params.end).getTime()) {
      $errorElement.html('End date should be a later date than start date');
      $errorElement.show();
      return false;
    }

    const dateDiff = (Date.parse(params.end).getTime() - Date.parse(params.start).getTime()) / (1000 * 60 * 60 * 24);

    if (dateDiff > 45 && (params.employee === undefined || params.employee == null || params.employee === 'NULL')) {
      $errorElement.html('Differance between start and end dates should not be more than 45 days, when creating chart for all employees');
      $errorElement.show();
      return false;
    } if (dateDiff > 90) {
      $errorElement.html('Differance between start and end dates should not be more than 90 days');
      $errorElement.show();
      return false;
    }

    return true;
  }

  getTimeUtilization(object, callBackData) {
    object = {};


    if (this.filter != null && this.filter !== undefined) {
      if (this.filter.employee !== 'NULL') {
        object.employee = this.filter.employee;
      }

      object.start = this.filter.start;
      object.end = this.filter.end;
    }

    const reqJson = JSON.stringify(object);

    callBackData = (callBackData === undefined || callBackData === null) ? [] : callBackData;
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getTimeUtilizationSuccessCallBack';
    callBackData.callBackFail = 'getTimeUtilizationFailCallBack';

    this.customAction('getTimeUtilization', 'admin=charts', reqJson, callBackData);
  }


  getTimeUtilizationFailCallBack(callBackData) {
    this.showMessage('Error Occured while getting data for chart', callBackData);
  }


  getTimeUtilizationSuccessCallBack(callBackData) {
    const that = this;

    const filterHtml = that.getTableTopButtonHtml();
    $('#tabPageTimeUtilizationGraph svg').remove();
    $('#tabPageTimeUtilizationGraph div').remove();

    const $tabPageTimeUtilizationGraph = $('#tabPageTimeUtilizationGraph');
    $tabPageTimeUtilizationGraph.html('');
    $tabPageTimeUtilizationGraph.html(`${filterHtml}<svg></svg>`);

    nv.addGraph(() => {
      const chart = nv.models.multiBarChart()
        .margin({ bottom: 200 })
        .transitionDuration(0)
        .reduceXTicks(true) // If 'false', every single x-axis tick label will be rendered.
        .rotateLabels(45) // Angle to rotate x-axis labels.
        .showControls(true) // Allow user to switch between 'Grouped' and 'Stacked' mode.
        .groupSpacing(0.1); // Distance between each group of bars.

      chart.yAxis
        .tickFormat(d3.format(',.1f'));


      d3.select('#tabPageTimeUtilizationGraph svg')
        .datum(callBackData)
        .call(chart);

      chart.dispatch.on('stateChange', (e) => { nv.log('New State:', JSON.stringify(e)); });

      chart.tooltip((key, x, y, e, graph) => `<p><strong>${key}</strong></p>`
                    + `<p>${y} on ${x}</p>`);

      return chart;
    });
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/insights/analytics';
  }
}


module.exports = { AttendanceGraphAdapter, TimeUtilizationGraphAdapter };
