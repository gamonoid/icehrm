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


  getInitData() {
    const that = this;
    const object = {};
    const reqJson = JSON.stringify(object);
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getInitDataSuccessCallBack';
    callBackData.callBackFail = 'getInitDataFailCallBack';

    this.customAction('getInitData', 'admin=dashboard', reqJson, callBackData);
  }


  getInitDataSuccessCallBack(data) {
    $('#numberOfEmployees').html(`${data.numberOfEmployees} Employees`);
    $('#numberOfCompanyStuctures').html(`${data.numberOfCompanyStuctures} Departments`);
    $('#numberOfUsers').html(`${data.numberOfUsers} Users`);
    $('#numberOfProjects').html(`${data.numberOfProjects} Active Projects`);
    $('#numberOfAttendanceLastWeek').html(`${data.numberOfAttendanceLastWeek} Entries Last Week`);
    $('#numberOfLeaves').html(`${data.numberOfLeaves} Upcoming`);
    $('#numberOfTimeEntries').html(data.numberOfTimeEntries);
    $('#numberOfCandidates').html(`${data.numberOfCandidates} Candidates`);
    $('#numberOfJobs').html(`${data.numberOfJobs} Active`);
    $('#numberOfCourses').html(`${data.numberOfCourses} Courses`);
  }

  getInitDataFailCallBack(callBackData) {

  }
}

module.exports = { DashboardAdapter };
