/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import AdapterBase from '../../../api/AdapterBase';
/**
 * ModuleAdapter
 */

class ModuleAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'label',
      'menu',
      'mod_group',
      'mod_order',
      'status',
      'version',
      'update_path',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Menu', bVisible: false },
      { sTitle: 'Group' },
      { sTitle: 'Order' },
      { sTitle: 'Status' },
      { sTitle: 'Version', bVisible: false },
      { sTitle: 'Path', bVisible: false },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['label', { label: 'Label', type: 'text', validation: '' }],
      ['status', { label: 'Status', type: 'select', source: [['Enabled', 'Enabled'], ['Disabled', 'Disabled']] }],
      ['user_levels', { label: 'User Levels', type: 'select2multi', source: [['Admin', 'Admin'], ['Manager', 'Manager'], ['Employee', 'Employee'], ['Other', 'Other']] }],
      ['user_roles', { label: 'User Roles', type: 'select2multi', 'remote-source': ['UserRole', 'id', 'name'] }],
    ];
  }


  getActionButtonsHtml(id, data) {
    const nonEditableFields = {};
    nonEditableFields['admin_Company Structure'] = 1;
    nonEditableFields.admin_Employees = 1;
    nonEditableFields['admin_Job Details Setup'] = 1;
    nonEditableFields.admin_Leaves = 1;
    nonEditableFields['admin_Manage Modules'] = 1;
    nonEditableFields.admin_Projects = 1;
    nonEditableFields.admin_Qualifications = 1;
    nonEditableFields.admin_Settings = 1;
    nonEditableFields.admin_Users = 1;
    nonEditableFields.admin_Upgrade = 1;
    nonEditableFields.admin_Dashboard = 1;

    nonEditableFields['user_Basic Information'] = 1;
    nonEditableFields.user_Dashboard = 1;

    if (nonEditableFields[`${data[3]}_${data[1]}`] === 1) {
      return '';
    }
    let html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"/></div>';
    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }
}


/**
 * UsageAdapter
 */

class UsageAdapter extends AdapterBase {
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

  saveUsage() {
    const object = {};
    const arr = [];
    $('.module-check').each(function () {
      if ($(this).is(':checked')) {
        arr.push($(this).val());
      }
    });

    if (arr.length === 0) {
      alert('Please select one or more module groups');
      return;
    }

    object.groups = arr.join(',');

    const reqJson = JSON.stringify(object);
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getInitDataSuccessCallBack';
    callBackData.callBackFail = 'getInitDataFailCallBack';

    this.customAction('saveUsage', 'admin=modules', reqJson, callBackData);
  }


  saveUsageSuccessCallBack(data) {

  }

  saveUsageFailCallBack(callBackData) {

  }
}

module.exports = { ModuleAdapter, UsageAdapter };
