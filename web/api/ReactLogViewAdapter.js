/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
/* global timeUtils */
/**
 * ReactLogViewAdapter
 */

import ReactModalAdapterBase from './ReactModalAdapterBase';

class ReactLogViewAdapter extends ReactModalAdapterBase {
  getLogs(id) {
    const object = { id };
    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getLogsSuccessCallBack';
    callBackData.callBackFail = 'getLogsFailCallBack';

    this.customAction('getLogs', `admin=${this.modulePathName}`, reqJson, callBackData);
  }

  getLogsSuccessCallBack(callBackData) {
    let tableLog = '<table class="table table-condensed table-bordered table-striped" style="font-size:14px;"><thead><tr><th>Notes</th></tr></thead><tbody>_days_</tbody></table> ';
    const rowLog = '<tr><td><span class="logTime label label-default">_date_</span>&nbsp;&nbsp;<b>_status_</b><br/>_note_</td></tr>';

    const logs = callBackData.data;
    let html = '';
    let rowsLogs = '';


    for (let i = 0; i < logs.length; i++) {
      let trow = rowLog;
      trow = trow.replace(/_date_/g, logs[i].time);
      trow = trow.replace(/_status_/g, `${logs[i].status_from} -> ${logs[i].status_to}`);
      trow = trow.replace(/_note_/g, logs[i].note);
      rowsLogs += trow;
    }

    if (rowsLogs !== '') {
      tableLog = tableLog.replace('_days_', rowsLogs);
      html += tableLog;
    }

    this.showMessage('Logs', html);

    timeUtils.convertToRelativeTime($('.logTime'));
  }

  // eslint-disable-next-line no-unused-vars
  getLogsFailCallBack(callBackData) {
    this.showMessage('Error', 'Error occured while getting data');
  }
}

export default ReactLogViewAdapter;
