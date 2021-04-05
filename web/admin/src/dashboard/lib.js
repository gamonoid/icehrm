/* global document */
/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import React from 'react';
import {
  Space, Card, Spin, Row, Col,
} from 'antd';
import { Donut, Pie } from '@antv/g2plot';
import ReactDOM from 'react-dom';
import AdapterBase from '../../../api/AdapterBase';
import TaskList from '../../../components/TaskList';

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
    this.initializeReactDashboard();
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

  getSpinner() {
    return (
      <Row>
        <Col span={8}> </Col>
        <Col span={8}><Spin size="large" /></Col>
        <Col span={8}> </Col>
      </Row>
    );
  }

  initializeReactDashboard() {
    //this.drawCompanyLeaveEntitlementChart();
    this.drawOnlineOfflineEmployeeChart();
    this.drawEmployeeDistributionChart();
    this.buildTaskList();
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

  drawEmployeeDistributionChart() {
    const that = this;
    document.getElementById('EmployeeDistributionChart').style.display = 'none';
    ReactDOM.render(
      this.getSpinner(),
      document.getElementById('EmployeeDistributionChartLoader'),
    );

    this.apiClient
      .get('charts/employees-distribution')
      .then((data) => {
        const chartData = Object.keys(data.data).map((key) => ({
          type: key.charAt(0).toUpperCase() + key.slice(1),
          value: data.data[key],
        }));
        const props = {
          forceFit: true,
          title: {
            visible: true,
            text: that.gt('Employee Distribution'),
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
          label: {
            visible: true,
            type: 'outer',
            offset: 20,
          },
        };
        ReactDOM.unmountComponentAtNode(document.getElementById('EmployeeDistributionChartLoader'));
        document.getElementById('EmployeeDistributionChart').style.display = 'block';
        const plot = new Pie(document.getElementById('EmployeeDistributionChart'), props);
        plot.render();
      });
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

  drawCompanyLeaveEntitlementChart() {
    const that = this;
    document.getElementById('CompanyLeaveEntitlementChart').style.display = 'none';
    ReactDOM.render(
      this.getSpinner(),
      document.getElementById('CompanyLeaveEntitlementChartLoader'),
    );

    this.apiClient
      .get('charts/company-leave-entitlement')
      .then((data) => {
        const chartData = Object.keys(data.data).map((key) => ({
          type: key,
          value: data.data[key],
        }));
        const props = {
          forceFit: true,
          title: {
            visible: true,
            text: that.gt('Company Vacation Usage'),
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
        ReactDOM.unmountComponentAtNode(document.getElementById('CompanyLeaveEntitlementChartLoader'));
        document.getElementById('CompanyLeaveEntitlementChart').style.display = 'block';
        const donutPlot = new Donut(document.getElementById('CompanyLeaveEntitlementChart'), props);
        donutPlot.render();
      });
  }
}

module.exports = { DashboardAdapter };
