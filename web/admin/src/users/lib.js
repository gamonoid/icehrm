/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import { Space, Tag, message } from 'antd';
import {
  CopyOutlined, DeleteOutlined, EditOutlined, MonitorOutlined,
} from '@ant-design/icons';
import React from 'react';
import ReactDOM from 'react-dom';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import IceFormModal from '../../../components/IceFormModal';

class UserAdapter extends ReactModalAdapterBase {
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

  getTableColumns() {
    return [
      {
        title: 'Username',
        dataIndex: 'username',
        sorter: true,
      },
      {
        title: 'Email',
        dataIndex: 'email',
        sorter: true,
      },
      {
        title: 'Employee',
        dataIndex: 'employee',
      },
      {
        title: 'User Level',
        dataIndex: 'user_level',
      },
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
      ['user_level',
        {
          label: 'User Level',
          type: 'select',
          source: [
            ['Admin', 'Admin'],
            ['Manager', 'Manager'],
            ['Employee', 'Employee'],
            ['Restricted Admin', 'Restricted Admin'],
            ['Restricted Manager', 'Restricted Manager'],
            ['Restricted Employee', 'Restricted Employee'],
          ],
        },
      ],
      ['user_roles', {
        label: 'User Roles', type: 'select2multi', validation: 'none', 'remote-source': ['UserRole', 'id', 'name'],
      }],
      ['lang', {
        label: 'Language', type: 'select2', 'allow-null': true, 'remote-source': ['SupportedLanguage', 'id', 'description'],
      }],
      ['default_module', {
        label: 'Default Module', type: 'select2', 'null-label': 'No Default Module', 'allow-null': true, 'remote-source': ['Module', 'id', 'name', 'getUserModules'],
      }],
    ];
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        {adapter.hasAccess('save') && adapter.showEdit
              && (
              <Tag color="green" onClick={() => modJs.edit(record.id)} style={{ cursor: 'pointer' }}>
                <EditOutlined />
                {` ${adapter.gt('Edit')}`}
              </Tag>
              )}
        {adapter.hasAccess('save')
              && (
              <Tag color="blue" onClick={() => modJs.showPasswordChangeForm(record.id)} style={{ cursor: 'pointer' }}>
                <MonitorOutlined />
                {` ${adapter.gt('Change Password')}`}
              </Tag>
              )}
        {adapter.hasAccess('delete') && adapter.showDelete
              && (
              <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
                <DeleteOutlined />
                {` ${adapter.gt('Delete')}`}
              </Tag>
              )}
        {adapter.hasAccess('save') && adapter.showAddNew
              && (
              <Tag color="cyan" onClick={() => modJs.copyRow(record.id)} style={{ cursor: 'pointer' }}>
                <CopyOutlined />
                {` ${adapter.gt('Copy')}`}
              </Tag>
              )}
      </Space>
    );
  }

  setSaveCompleteCallback(saveCompleteCallback) {
    this.saveCompleteCallback = saveCompleteCallback;
  }

  showPasswordChangeForm(id) {
    const fields = [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['password', {
        label: 'Password', type: 'password', validation: 'password', message: 'The password must contain at least one upper case character, one lower case character, one number and one special character and a minimum length of 8 characters',
      }],
      ['confirm_password', { label: 'Confirm', type: 'password', validation: 'password' }],
    ];
    const formContainer = React.createRef();
    const formReference = React.createRef();
    ReactDOM.render(
      <IceFormModal
        ref={formContainer}
        fields={fields}
        adapter={this}
        formReference={formReference}
        saveCallback={(values, showErrorCallback, closeCallback, adapter) => {
          modJs.apiClient.post('user/password', values)
            .then((response) => {
              closeCallback();
              ReactDOM.unmountComponentAtNode(document.getElementById('UserPasswordChangeForm'));
            }).catch((error) => {
              formContainer.current.iceFormReference.current.showError(
                error.response.data.error[0][0].message,
              );
            });
        }}
      />,
      document.getElementById('UserPasswordChangeForm'),
    );
    formContainer.current.show({ id });
  }

  saveCallback(params, showError, closeCallback, adapter) {
    params.csrf = $(`#${adapter.getTableName()}Form`).data('csrf');
    const reqJson = JSON.stringify(params);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'saveUserSuccessCallBack';
    callBackData.callBackFail = 'saveUserFailCallBack';
    adapter.customAction('saveUser', 'admin=users', reqJson, callBackData);
    adapter.closeCallback = closeCallback;
  }

  saveUserSuccessCallBack(callBackData, serverData) {
    const user = callBackData[0];
    if (callBackData[1] === 'sent') {
      this.showMessage('Create User', `An email has been sent to ${user.email} with a temporary password to login to IceHrm.`);
    } else if (callBackData[1] === 'not_sent') {
      this.showMessage('Create User', 'User created successfully. But there was a problem sending welcome email.');
    }
    this.get([]);
    if (this.saveCompleteCallback) {
      this.saveCompleteCallback();
    }
    this.closeCallback();
  }

  saveUserFailCallBack(callBackData, serverData) {
    this.showMessage('Error', callBackData);
  }
}


/**
 * UserRoleAdapter
 */

class UserRoleAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.tables = [];
  }

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

  getTableColumns() {
    return [
      {
        title: 'ID',
        dataIndex: 'id',
        sorter: true,
      },
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
    ];
  }

  setTables(tables) {
    this.tables = tables;
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['additional_permissions', {
        label: 'Additional Permissions',
        type: 'datagroup',
        form: [
          ['table',
            {
              label: 'Table',
              type: 'select2',
              source: this.tables,
            },
          ],
          ['permissions',
            {
              label: 'Permissions',
              type: 'select2multi',
              'allow-null': true,
              source: [
                ['get', 'List'],
                ['element', 'Get Details'],
                ['save', 'Add/Edit'],
                ['delete', 'Delete'],
              ],
            },
          ],
        ],
        columns: [
          {
            title: 'Table',
            dataIndex: 'table',
            key: 'table',
          },
          {
            title: 'Permissions',
            dataIndex: 'permissions',
            key: 'permissions',
          },
        ],
        validation: 'none',
      }],
    ];
  }
}

class UserInvitationAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.tables = [];
  }

  getDataMapping() {
    return [
      'id',
      'username',
      'email',
      'employee_id',
      'first_name',
      'last_name',
      'user_level',
      'invitation_status_text',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Username' },
      { sTitle: 'Email' },
      { sTitle: 'Employee ID' },
      { sTitle: 'First Name' },
      { sTitle: 'Last Name' },
      { sTitle: 'User Level' },
      { sTitle: 'Status' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'ID',
        dataIndex: 'id',
        sorter: true,
      },
      {
        title: 'Username',
        dataIndex: 'username',
        sorter: true,
      },
      {
        title: 'Email',
        dataIndex: 'email',
        sorter: true,
      },
      {
        title: 'Employee ID',
        dataIndex: 'employee_id',
        sorter: true,
      },
      {
        title: 'First Name',
        dataIndex: 'first_name',
      },
      {
        title: 'Last Name',
        dataIndex: 'last_name',
      },
      {
        title: 'User Level',
        dataIndex: 'user_level',
      },
      {
        title: 'Status',
        dataIndex: 'invitation_status_text',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['email', { label: 'Email', type: 'text', validation: 'email' }],
      ['employee_id', { label: 'Employee Number', type: 'text', validation: '' }],
      ['first_name', { label: 'First Name', type: 'text', validation: '' }],
      ['last_name', { label: 'Last Name', type: 'text', validation: '' }],
      ['user_level',
        {
          label: 'User Level',
          type: 'select',
          source: [
            ['Admin', 'Admin'],
            ['Manager', 'Manager'],
            ['Employee', 'Employee'],
          ],
        },
      ],
      ['joined_date', { label: 'Joined Date', type: 'date', validation: 'none' }],
      ['country', {
        label: 'Country', type: 'select2', 'remote-source': ['Country', 'code', 'name'],
      }],
      ['timezone', {
        label: 'Time Zone', type: 'select2', 'allow-null': true, 'remote-source': ['Timezone', 'name', 'details', 'getTimezonesWithOffset'],
      }],
      ['department', {
        label: 'Department', type: 'select2', 'allow-null': true, 'remote-source': ['CompanyStructure', 'id', 'title'], validation: 'none',
      }],
      ['job_title', {
        label: 'Job Title', type: 'select2', 'allow-null': true, 'remote-source': ['JobTitle', 'id', 'name'], validation: 'none',
      }],
      ['supervisor', {
        label: 'Direct Manager', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'], validation: 'none',
      }],
      ['employment_status', {
        label: 'Employment Status', type: 'select2', validation: 'none', 'allow-null': true, 'remote-source': ['EmploymentStatus', 'id', 'name'],
      }],
      ['pay_grade', {
        label: 'Pay Grade', type: 'select2', 'allow-null': true, validation: 'none', 'remote-source': ['PayGrade', 'id', 'name'],
      }],
    ];
  }

  getMappedFields() {
    const fields = this.getFormFields();
    const steps = [
      {
        title: this.gt('Mandatory User Information'),
        description: this.gt('Mandatory User Information'),
        fields: [
          'id',
          'email',
          'employee_id',
          'first_name',
          'last_name',
          'user_level',
          'country',
        ],
      },
      {
        title: this.gt('Other Optional Details'),
        description: this.gt('Other Optional Details'),
        fields: [
          'joined_date',
          'timezone',
          'department',
          'job_title',
          'supervisor',
          'employment_status',
          'pay_grade',
        ],
      },
    ];

    if (this.customFields.length > 0) {
      steps.push({
        title: this.gt('Other'),
        description: this.gt('Custom Fields'),
        fields: this.customFields.map((item) => item[0]),
      });
    }

    return this.addActualFieldsForStepModal(steps, fields);
  }

  addSuccessCallBack(callBackData, serverData, callGetFunction, successCallback, thisObject) {
    message.config({
      top: 100,
    });
    if (callGetFunction) {
      this.get(callBackData);
    }
    this.initFieldMasterData();
    if (successCallback !== undefined && successCallback !== null) {
      successCallback.apply(thisObject, [serverData]);
    }
    this.trackEvent('addSuccess', this.tab, this.table);
    if (parseInt(serverData.invitation_status, 10) === 1) {
      message.success(this.gt('An email with an invitation link is sent.'));
    } else {
      message.error(this.gt('There was an error sending the invitation. Please check under System -> User -> User Invitations.'));
    }
  }

  invitationStatusToText(status) {
    const map = {
      0: 'Pending',
      1: 'Invited',
      2: 'Invitation Failed',
      3: 'Processing',
      4: 'Employee Created',
      5: 'Employee and User Created',
      6: 'Welcome Email Failed',
    };

    return map[status];
  }
}

module.exports = {
  UserAdapter,
  UserRoleAdapter,
  UserInvitationAdapter,
};
