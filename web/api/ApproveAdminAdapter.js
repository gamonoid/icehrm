/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/**
 * ApproveAdminAdapter
 */
import LogViewAdapter from './LogViewAdapter';

class ApproveAdminAdapter extends LogViewAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
  }

  getStatusFieldPosition() {
    const dm = this.getDataMapping();
    return dm.length - 1;
  }

  openStatus(id, status) {
    $(`#${this.itemNameLower}StatusModel`).modal('show');
    $(`#${this.itemNameLower}_status`).html(this.getStatusOptions(status));
    $(`#${this.itemNameLower}_status`).val(status);
    this.statusChangeId = id;
  }

  closeDialog() {
    $(`#${this.itemNameLower}StatusModel`).modal('hide');
  }

  changeStatus() {
    const status = $(`#${this.itemNameLower}_status`).val();
    const reason = $(`#${this.itemNameLower}_reason`).val();

    if (status == undefined || status == null || status == '') {
      this.showMessage('Error', `Please select ${this.itemNameLower} status`);
      return;
    }

    const object = { id: this.statusChangeId, status, reason };

    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'changeStatusSuccessCallBack';
    callBackData.callBackFail = 'changeStatusFailCallBack';

    this.customAction('changeStatus', `admin=${this.modulePathName}`, reqJson, callBackData);

    this.closeDialog();
    this.statusChangeId = null;
  }

  changeStatusSuccessCallBack(callBackData) {
    this.showMessage('Successful', `${this.itemName} Request status changed successfully`);
    this.get([]);
  }

  changeStatusFailCallBack(callBackData) {
    this.showMessage('Error', `Error occurred while changing ${this.itemName} request status`);
  }


  getActionButtonsHtml(id, data) {
    const editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
    const deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
    const statusChangeButton = '<img class="tableActionButton" src="_BASE_images/run.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Change Status" onclick="modJs.openStatus(_id_, \'_cstatus_\');return false;"></img>';
    const viewLogsButton = '<img class="tableActionButton" src="_BASE_images/log.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="View Logs" onclick="modJs.getLogs(_id_);return false;"></img>';

    let html = '<div style="width:120px;">_edit__delete__status__logs_</div>';

    const optiondata = this.getStatusOptionsData(data[this.getStatusFieldPosition()]);
    if (Object.keys(optiondata).length > 0) {
      html = html.replace('_status_', statusChangeButton);
    } else {
      html = html.replace('_status_', '');
    }

    html = html.replace('_logs_', viewLogsButton);

    if (this.showDelete) {
      html = html.replace('_delete_', deleteButton);
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
    html = html.replace(/_cstatus_/g, data[this.getStatusFieldPosition()]);
    return html;
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }

  getStatusOptionsData(currentStatus) {
    const data = {};
    if (currentStatus == 'Approved') {

    } else if (currentStatus == 'Pending') {
      data.Approved = 'Approved';
      data.Rejected = 'Rejected';
    } else if (currentStatus == 'Rejected') {

    } else if (currentStatus == 'Cancelled') {

    } else if (currentStatus == 'Processing') {

    } else {
      data['Cancellation Requested'] = 'Cancellation Requested';
      data.Cancelled = 'Cancelled';
    }

    return data;
  }

  getStatusOptions(currentStatus) {
    return this.generateOptions(this.getStatusOptionsData(currentStatus));
  }
}

export default ApproveAdminAdapter;
