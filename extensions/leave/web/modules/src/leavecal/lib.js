/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import React from 'react';
import ReactDOM from 'react-dom';
import ReactModalAdapterBase from '../../../../../../web/api/ReactModalAdapterBase';
import LeaveCalendarView from './view';

class EmployeeLeaveCalendarAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [];
  }

  getHeaders() {
    return [];
  }

  getFormFields() {
    return [];
  }

  initTable() {
    if (this.tableInitialized) {
      return false;
    }
    const tableDom = document.getElementById(`${this.tab}Table`);
    if (tableDom) {
      this.tableContainer = React.createRef();

      ReactDOM.render(
        <LeaveCalendarView
          apiClient={this.apiClient}
          ice={this}
        />,
        tableDom,
      );
    }

    this.tableInitialized = true;

    return true;
  }

  getLeaveJsonUrl() {
    return `${this.moduleRelativeURL}?a=ca&sa=getLeavesForMeAndSubordinates&t=${this.table}&mod=modules%3Dleavecal`;
  }
}

module.exports = { EmployeeLeaveCalendarAdapter };
