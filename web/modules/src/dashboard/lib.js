/*
Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import AdapterBase from '../../../api/AdapterBase';

class DashboardAdapter extends AdapterBase {
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


  getPunch() {
    const that = this;
    const object = {};

    object.date = this.getClientDate(new Date()).toISOString().slice(0, 19).replace('T', ' ');
    object.offset = this.getClientGMTOffset();
    const reqJson = JSON.stringify(object);
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getPunchSuccessCallBack';
    callBackData.callBackFail = 'getPunchFailCallBack';

    this.customAction('getPunch', 'modules=attendance', reqJson, callBackData);
  }


  getPunchSuccessCallBack(callBackData) {
    const punch = callBackData;
    if (punch == null) {
      $('#lastPunchTime').html('Not');
      $('#punchTimeText').html('Punched In');
    } else {
      $('#lastPunchTime').html(Date.parse(punch.in_time).toString('h:mm tt'));
      $('#punchTimeText').html('Punched In');
    }
  }

  getPunchFailCallBack(callBackData) {

  }

  getInitData() {
    const that = this;
    const object = {};
    const reqJson = JSON.stringify(object);
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getInitDataSuccessCallBack';
    callBackData.callBackFail = 'getInitDataFailCallBack';

    this.customAction('getInitData', 'modules=dashboard', reqJson, callBackData);
  }

  getInitDataSuccessCallBack(data) {
    $('#timeSheetHoursWorked').html(data.lastTimeSheetHours);
    $('#numberOfProjects').html(data.activeProjects);
    $('#pendingLeaveCount').html(data.pendingLeaves);

    $('#numberOfEmployees').html(`${data.numberOfEmployees} Subordinates`);
    $('#numberOfCandidates').html(`${data.numberOfCandidates} Candidates`);
    $('#numberOfJobs').html(`${data.numberOfJobs} Active`);
    $('#numberOfCourses').html(`${data.numberOfCourses} Active`);
  }

  getInitDataFailCallBack(callBackData) {

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

module.exports = { DashboardAdapter };
