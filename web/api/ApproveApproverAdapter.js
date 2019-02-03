/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/**
 * ApproveApproverAdapter
 */

class ApproveApproverAdapter {
  getActionButtonsHtml(id, data) {
    const statusChangeButton = '<img class="tableActionButton" src="_BASE_images/run.png" style="cursor:pointer;" rel="tooltip" title="Change Status" onclick="modJs.openStatus(_id_, \'_cstatus_\');return false;"></img>';
    const viewLogsButton = '<img class="tableActionButton" src="_BASE_images/log.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="View Logs" onclick="modJs.getLogs(_id_);return false;"></img>';

    let html = '<div style="width:80px;">_status__logs_</div>';


    html = html.replace('_logs_', viewLogsButton);


    if (data[this.getStatusFieldPosition()] == 'Processing') {
      html = html.replace('_status_', statusChangeButton);
    } else {
      html = html.replace('_status_', '');
    }

    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    html = html.replace(/_cstatus_/g, data[this.getStatusFieldPosition()]);
    return html;
  }

  getStatusOptionsData(currentStatus) {
    const data = {};
    if (currentStatus != 'Processing') {

    } else {
      data.Approved = 'Approved';
      data.Rejected = 'Rejected';
    }

    return data;
  }

  getStatusOptions(currentStatus) {
    return this.generateOptions(this.getStatusOptionsData(currentStatus));
  }
}

export default ApproveApproverAdapter;
