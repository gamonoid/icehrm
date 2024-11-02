/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import React from 'react';
import ReactDOM from 'react-dom';
import { Donut, Pie } from '@antv/g2plot';
import AdapterBase from '../../../api/AdapterBase';
import { DashboardAdapter } from '../../../admin/src/dashboard/lib';
import TaskList from '../../../components/TaskList';
import EmployeeStatusDashboard from '../../../components/EmployeeStatusDashboard';
import EmployeeListWidget from '../../../admin/src/employees/components/EmployeeListWidget';

class ModuleDashboardAdapter extends DashboardAdapter {
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
    this.initializeReactDashboard();
  }

  initializeReactDashboard() {
    this.drawOnlineOfflineEmployeeChart();
    this.buildTaskList();
    this.buildEmployeeStatus();
    this.showEmployeeList();
  }

  showEmployeeList() {
    const that = this;
    document.getElementById('EmployeeListWrapper').style.display = 'none';


    this.apiClient
      .post('staff-random', { limit: 15 })
      .then((response) => {
        ReactDOM.render(
          <EmployeeListWidget url={CLIENT_BASE_URL+'?g=extension&n=directory|user&m=module_Company'} adapter={this} employees={response.data} />,
          document.getElementById('EmployeeList'),
        );
        document.getElementById('EmployeeListWrapper').style.display = 'block';
      });
  }

  buildTaskList() {
    document.getElementById('TaskListWrap').style.display = 'none';
    ReactDOM.render(
      this.getSpinner(),
      document.getElementById('TaskListLoader'),
    );
    this.apiClient
      .get('tasks')
      .then((data) => {
        document.getElementById('TaskListWrap').style.display = 'block';
        ReactDOM.render(
          <TaskList tasks={data.data} />,
          document.getElementById('TaskList'),
        );

        ReactDOM.unmountComponentAtNode(document.getElementById('TaskListLoader'));
      });
  }

  buildEmployeeStatus() {
    document.getElementById('EmployeeStatusWrap').style.display = 'block';
    ReactDOM.render(
      <EmployeeStatusDashboard
        adapter={this}
        apiClient={this.apiClient}
        employee={this.user.employee}
      />,
      document.getElementById('EmployeeStatus'),
    );
  }

  drawOnlineOfflineEmployeeChart() {
    const that = this;
    document.getElementById('EmployeeOnlineOfflineChart').style.display = 'none';
    ReactDOM.render(
      this.getSpinner(),
      document.getElementById('EmployeeOnlineOfflineChartLoader'),
    );

    this.apiClient
      .get('charts/employee-check-ins')
      .then((data) => {
        const chartData = Object.keys(data.data).map((key) => ({
          type: key,
          value: data.data[key],
        }));
        const props = {
          forceFit: true,
          title: {
            visible: true,
            text: that.gt('Employee Check-Ins'),
          },
          description: {
            visible: false,
            text: '',
          },
          statistic: {
            visible: true,
            content: {
              value: chartData.reduce((acc, item) => acc + item.value, 0),
              name: that.gt('Total'),
            },
          },
          legend: {
            visible: true,
            position: 'bottom-center',
          },
          radius: 0.8,
          padding: 'auto',
          data: chartData,
          angleField: 'value',
          colorField: 'type',
        };
        ReactDOM.unmountComponentAtNode(document.getElementById('EmployeeOnlineOfflineChartLoader'));
        document.getElementById('EmployeeOnlineOfflineChart').style.display = 'block';
        const donutPlot = new Donut(document.getElementById('EmployeeOnlineOfflineChart'), props);
        donutPlot.render();
      });
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

    $('#numberOfEmployees').html(`${data.numberOfEmployees} Direct Reports`);
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

module.exports = { ModuleDashboardAdapter };
