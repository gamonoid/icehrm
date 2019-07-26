/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import FormValidation from '../../../api/FormValidation';
import AdapterBase from '../../../api/AdapterBase';


class UserAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'username',
      'email',
      'employee',
      'user_level',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID' },
      { sTitle: 'User Name' },
      { sTitle: 'Authentication Email' },
      { sTitle: 'Employee' },
      { sTitle: 'User Level' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['username', { label: 'User Name', type: 'text', validation: 'username' }],
      ['email', { label: 'Email', type: 'text', validation: 'email' }],
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['user_level', { label: 'User Level', type: 'select', source: [['Admin', 'Admin'], ['Manager', 'Manager'], ['Employee', 'Employee'], ['Other', 'Other']] }],
      ['lang', {
        label: 'Language', type: 'select2', 'allow-null': true, 'remote-source': ['SupportedLanguage', 'id', 'description'],
      }],
    ];
  }

  postRenderForm(object, $tempDomObj) {
    if (object == null || object === undefined) {
      $tempDomObj.find('#changePasswordBtn').remove();
    }
  }

  changePassword() {
    $('#adminUsersModel').modal('show');
    $('#adminUsersChangePwd #newpwd').val('');
    $('#adminUsersChangePwd #conpwd').val('');
  }

  saveUserSuccessCallBack(callBackData, serverData) {
    const user = callBackData[0];
    if (callBackData[1]) {
      this.showMessage('Create User', `An email has been sent to ${user.email} with a temporary password to login to IceHrm.`);
    } else {
      this.showMessage('Create User', 'User created successfully. But there was a problem sending welcome email.');
    }
    this.get([]);
  }

  saveUserFailCallBack(callBackData, serverData) {
    this.showMessage('Error', callBackData);
  }

  doCustomValidation(params) {
    let msg = null;
    if ((params.user_level !== 'Admin' && params.user_level !== 'Other') && params.employee === 'NULL') {
      msg = 'For this user type, you have to assign an employee when adding or editing the user.<br/>';
      msg += " You may create a new employee through 'Admin'->'Employees' menu";
    }
    return msg;
  }

  save() {
    const validator = new FormValidation(`${this.getTableName()}_submit`, true, { ShowPopup: false, LabelErrorClass: 'error' });
    if (validator.checkValues()) {
      const params = validator.getFormParameters();

      const msg = this.doCustomValidation(params);
      if (msg == null) {
        const id = $(`#${this.getTableName()}_submit #id`).val();
        params.csrf = $(`#${this.getTableName()}Form`).data('csrf');
        if (id != null && id !== undefined && id !== '') {
          params.id = id;
          this.add(params, []);
        } else {
          const reqJson = JSON.stringify(params);

          const callBackData = [];
          callBackData.callBackData = [];
          callBackData.callBackSuccess = 'saveUserSuccessCallBack';
          callBackData.callBackFail = 'saveUserFailCallBack';

          this.customAction('saveUser', 'admin=users', reqJson, callBackData);
        }
      } else {
        // $("#"+this.getTableName()+'Form .label').html(msg);
        // $("#"+this.getTableName()+'Form .label').show();
        this.showMessage('Error Saving User', msg);
      }
    }
  }


  changePasswordConfirm() {
    $('#adminUsersChangePwd_error').hide();

    const passwordValidation = function (str) {
      return str.length > 7;
    };

    const password = $('#adminUsersChangePwd #newpwd').val();

    if (!passwordValidation(password)) {
      $('#adminUsersChangePwd_error').html('Password should be longer than 7 characters');
      $('#adminUsersChangePwd_error').show();
      return;
    }

    const conPassword = $('#adminUsersChangePwd #conpwd').val();

    if (conPassword !== password) {
      $('#adminUsersChangePwd_error').html("Passwords don't match");
      $('#adminUsersChangePwd_error').show();
      return;
    }

    const req = { id: this.currentId, pwd: conPassword };
    const reqJson = JSON.stringify(req);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'changePasswordSuccessCallBack';
    callBackData.callBackFail = 'changePasswordFailCallBack';

    this.customAction('changePassword', 'admin=users', reqJson, callBackData);
  }

  closeChangePassword() {
    $('#adminUsersModel').modal('hide');
  }

  changePasswordSuccessCallBack(callBackData, serverData) {
    this.closeChangePassword();
    this.showMessage('Password Change', 'Password changed successfully');
  }

  changePasswordFailCallBack(callBackData, serverData) {
    this.closeChangePassword();
    this.showMessage('Error', callBackData);
  }
}


/**
 * UserRoleAdapter
 */

class UserRoleAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
    ];
  }


  postRenderForm(object, $tempDomObj) {
    $tempDomObj.find('#changePasswordBtn').remove();
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
    ];
  }
}

module.exports = {
  UserAdapter,
  UserRoleAdapter,
};
