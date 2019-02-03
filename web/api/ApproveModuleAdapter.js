/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import LogViewAdapter from './LogViewAdapter';

class ApproveModuleAdapter extends LogViewAdapter {
  cancelRequest(id) {
    const object = {};
    object.id = id;

    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'cancelSuccessCallBack';
    callBackData.callBackFail = 'cancelFailCallBack';

    this.customAction('cancel', `modules=${this.modulePathName}`, reqJson, callBackData);
  }

  // eslint-disable-next-line no-unused-vars
  cancelSuccessCallBack(callBackData) {
    this.showMessage('Successful', `${this.itemName} cancellation request sent`);
    this.get([]);
  }

  cancelFailCallBack(callBackData) {
    this.showMessage(`Error Occurred while cancelling ${this.itemName}`, callBackData);
  }

  getActionButtonsHtml(id, data) {
    const editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
    const deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
    const requestCancellationButton = `<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Cancel ${this.itemName}" onclick="modJs.cancelRequest(_id_);return false;"></img>`;
    const viewLogsButton = '<img class="tableActionButton" src="_BASE_images/log.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="View Logs" onclick="modJs.getLogs(_id_);return false;"></img>';


    let html = '<div style="width:120px;">_edit__logs__delete_</div>';

    html = html.replace('_logs_', viewLogsButton);

    if (this.showDelete) {
      if (data[7] === 'Approved') {
        html = html.replace('_delete_', requestCancellationButton);
      } else if (data[7] === 'Pending' || this.user.user_level === 'Admin') {
        html = html.replace('_delete_', deleteButton);
      } else {
        html = html.replace('_delete_', '');
      }
    } else {
      html = html.replace('_delete_', '');
    }

    if (this.showEdit) {
      html = html.replace('_edit_', editButton);
    } else {
      html = html.replace('_edit_', '');
    }

    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }
}

export default ApproveModuleAdapter;
