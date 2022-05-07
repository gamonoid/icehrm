/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/* eslint-disable prefer-destructuring,no-restricted-globals */

/* global Base64, modJs, nl2br, ga */
/**
 * Author: Thilina Hasantha
 */

import React from 'react';
import {Avatar, Space, Tag} from 'antd';
import {
  CloudDownloadOutlined, DeleteOutlined, UndoOutlined, MonitorOutlined, LoginOutlined, EditOutlined, CopyOutlined,
} from '@ant-design/icons';
import AdapterBase from '../../../api/AdapterBase';
import SubAdapterBase from '../../../api/SubAdapterBase';
import ReactLegacyModalAdapterBase from '../../../api/ReactLegacyModalAdapterBase';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import EmployeeProfile from './components/EmployeeProfile';


class SubProfileEnabledAdapterBase extends ReactModalAdapterBase {
  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}


class EmployeeAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.fieldNameMap = {};
    this.fieldNameMapOrig = {};
    this.hiddenFields = {};
    this.tableFields = {};
    this.formOnlyFields = {};
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }

  setFieldNameMap(fields) {
    let field;
    for (let i = 0; i < fields.length; i++) {
      field = fields[i];
      this.fieldNameMap[field.name] = field;
      this.fieldNameMapOrig[field.textOrig] = field.textMapped;
      if (field.display === 'Hidden') {
        this.hiddenFields[field.name] = field;
      } else if (field.display === 'Table and Form' || field.display === 'Form') {
        this.tableFields[field.name] = field;
      } else {
        this.formOnlyFields[field.name] = field;
      }
    }
  }

  getCustomTableParams() {
    const that = this;
    return {
      aoColumnDefs: [
        {
          fnRender(data, cell) {
            return that.preProcessRemoteTableData(data, cell, 1);
          },
          aTargets: [1],
        },
        {
          fnRender: that.getActionButtons,
          aTargets: [that.getDataMapping().length],
        },
      ],
    };
  }

  preProcessRemoteTableData(data, cell, id) {
    if (id === 1) {
      const tmp = '<img src="_img_" class="img-circle" style="width:45px;height: 45px;" alt="User Image">';
      return tmp.replace('_img_', cell);
    }
    return cell;
  }

  getTableHTMLTemplate() {
    return '<div class="box-body table-responsive"><table cellpadding="0" cellspacing="0" border="0" class="table table-striped" id="grid"></table></div>';
  }

  getTableFields() {
    return [
      'id',
      'image',
      'employee_id',
      'first_name',
      'last_name',
      //'mobile_phone',
      'department',
      //'gender',
      'supervisor',
    ];
  }

  getDataMapping() {
    const tableFields = this.getTableFields();

    const newTableFields = [];
    for (let i = 0; i < tableFields.length; i++) {
      if ((this.hiddenFields[tableFields[i]] === undefined || this.hiddenFields[tableFields[i]] === null)
        && (this.formOnlyFields[tableFields[i]] === undefined || this.formOnlyFields[tableFields[i]] === null)) {
        newTableFields.push(tableFields[i]);
      }
    }

    return newTableFields;
  }

  getHeaders() {
    const tableFields = this.getTableFields();
    const headers = [
      { sTitle: 'ID', bVisible: false },
      { sTitle: '', bSortable: false },
    ];
    let title = '';

    for (let i = 0; i < tableFields.length; i++) {
      if ((this.hiddenFields[tableFields[i]] === undefined || this.hiddenFields[tableFields[i]] === null)
        && (this.formOnlyFields[tableFields[i]] === undefined || this.formOnlyFields[tableFields[i]] === null)) {
        if (this.fieldNameMap[tableFields[i]] !== undefined && this.fieldNameMap[tableFields[i]] !== null) {
          title = this.fieldNameMap[tableFields[i]].textMapped;
          if (title === undefined || title === null || title === '') {
            headers.push({ sTitle: title });
          } else if (tableFields[i] === 'gender') {
            headers.push({ sTitle: title, translate: true });
          } else {
            headers.push({ sTitle: title });
          }
        }
      }
    }

    return headers;
  }

  getTableColumns() {
    const columns = this.getDataMapping();
    const headers = this.getHeaders();

    const tableColumns = [];
    for (let i = 1; i < columns.length; i++) {
      tableColumns.push({
        title: headers[i].sTitle,
        dataIndex: columns[i],
        sorter: true,
      });

      if (columns[i] === 'image') {
        tableColumns[i - 1].render = (text, record) => <Avatar src={text} />;
      }
    }

    return tableColumns;
  }

  getMappedText(text) {
    return this.fieldNameMapOrig[text] ? this.fieldNameMapOrig[text] : text;
  }

  showElement(element) {
    this.tableContainer.current.setCurrentElement(element);
  }

  getTableChildComponents() {
    return (<EmployeeProfile />);
  }

  getFormFields() {
    const newFields = [];
    let tempField; let
      title;
    const fields = [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['employee_id', { label: 'Employee Number', type: 'text', validation: '' }],
      ['first_name', { label: 'First Name', type: 'text', validation: '' }],
      ['middle_name', { label: 'Middle Name', type: 'text', validation: 'none' }],
      ['last_name', { label: 'Last Name', type: 'text', validation: '' }],
      ['nationality', { label: 'Nationality', type: 'select2', 'remote-source': ['Nationality', 'id', 'name'] }],
      ['birthday', { label: 'Date of Birth', type: 'date', validation: '' }],
      ['gender', { label: 'Gender', type: 'select', source: [['Male', 'Male'], ['Female', 'Female'], ['Non-binary', 'Non-binary'], ['Other', 'Other']] }],
      ['marital_status', { label: 'Marital Status', type: 'select', source: [['Married', 'Married'], ['Single', 'Single'], ['Divorced', 'Divorced'], ['Widowed', 'Widowed'], ['Other', 'Other']] }],
      ['ethnicity', {
        label: 'Ethnicity', type: 'select2', 'allow-null': true, 'remote-source': ['Ethnicity', 'id', 'name'],
      }],
      ['immigration_status', {
        label: 'Immigration Status', type: 'select2', 'allow-null': true, 'remote-source': ['ImmigrationStatus', 'id', 'name'],
      }],
      ['ssn_num', { label: 'SSN/NRIC', type: 'text', validation: 'none' }],
      ['nic_num', { label: 'NIC', type: 'text', validation: 'none' }],
      ['other_id', { label: 'Other ID', type: 'text', validation: 'none' }],
      ['driving_license', { label: 'Driving License No', type: 'text', validation: 'none' }],

      ['employment_status', { label: 'Employment Status', type: 'select2', 'remote-source': ['EmploymentStatus', 'id', 'name'] }],
      ['department', { label: 'Department', type: 'select2', 'remote-source': ['CompanyStructure', 'id', 'title'] }],
      ['job_title', { label: 'Job Title', type: 'select2', 'remote-source': ['JobTitle', 'id', 'name'] }],
      ['pay_grade', {
        label: 'Pay Grade', type: 'select2', 'allow-null': true, 'remote-source': ['PayGrade', 'id', 'name'],
      }],
      ['joined_date', { label: 'Joined Date', type: 'date', validation: '' }],
      ['confirmation_date', { label: 'Confirmation Date', type: 'date', validation: 'none' }],
      ['termination_date', { label: 'Termination Date', type: 'date', validation: 'none' }],
      ['work_station_id', { label: 'Work Station Id', type: 'text', validation: 'none' }],

      ['address1', { label: 'Address Line 1', type: 'text', validation: 'none' }],
      ['address2', { label: 'Address Line 2', type: 'text', validation: 'none' }],
      ['city', { label: 'City', type: 'text', validation: 'none' }],
      ['country', { label: 'Country', type: 'select2', 'remote-source': ['Country', 'code', 'name'] }],
      ['province', {
        label: 'State', type: 'select2', 'allow-null': true, 'remote-source': ['Province', 'id', 'name'],
      }],
      ['postal_code', { label: 'Postal/Zip Code', type: 'text', validation: 'none' }],
      ['home_phone', { label: 'Home Phone', type: 'text', validation: 'none' }],
      ['mobile_phone', { label: 'Mobile Phone', type: 'text', validation: 'none' }],
      ['work_phone', { label: 'Work Phone', type: 'text', validation: 'none' }],
      ['work_email', { label: 'Work Email', type: 'text', validation: 'emailOrEmpty' }],
      ['private_email', { label: 'Private Email', type: 'text', validation: 'emailOrEmpty' }],

      ['supervisor', {
        label: 'Direct Supervisor', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['indirect_supervisors', {
        label: 'Indirect Supervisors', type: 'select2multi', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['approver1', {
        label: 'First Level Approver', type: 'select2', 'allow-null': true, 'null-label': 'None', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['approver2', {
        label: 'Second Level Approver', type: 'select2', 'allow-null': true, 'null-label': 'None', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['approver3', {
        label: 'Third Level Approver', type: 'select2', 'allow-null': true, 'null-label': 'None', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['notes', {
        label: 'Notes',
        type: 'datagroup',
        form: [
          ['note', { label: 'Note', type: 'textarea', validation: '' }],
        ],
        html: '<div id="#_id_#" class="panel panel-default"><div class="panel-body">#_delete_##_edit_#<span style="color:#999;font-size:13px;font-weight:bold">Date: #_date_#</span><hr/>#_note_#</div></div>',
        validation: 'none',
        columns: [
          {
            title: 'Note',
            dataIndex: 'note',
            key: 'note',
          },
        ],
        'sort-function': function (a, b) {
          const t1 = Date.parse(a.date).getTime();
          const t2 = Date.parse(b.date).getTime();

          return (t1 < t2);
        },
        'custom-validate-function': function (data) {
          const res = {};
          res.valid = true;
          data.date = new Date().toString('d-MMM-yyyy hh:mm tt');
          res.params = data;
          return res;
        },

      }],
    ];

    for (let i = 0; i < this.customFields.length; i++) {
      fields.push(this.customFields[i]);
    }

    for (let i = 0; i < fields.length; i++) {
      tempField = fields[i];
      if (this.hiddenFields[tempField[0]] === undefined || this.hiddenFields[tempField[0]] === null) {
        if (this.fieldNameMap[tempField[0]] !== undefined && this.fieldNameMap[tempField[0]] !== null) {
          title = this.fieldNameMap[tempField[0]].textMapped;
          tempField[1].label = title;
        }
        newFields.push(tempField);
      }
    }

    return newFields;
  }

  getMappedFields() {
    const fields = this.getFormFields();
    const steps = [
      {
        title: this.gt('Personal'),
        description: this.gt('Personal Information'),
        fields: [
          'id',
          'employee_id',
          'first_name',
          'middle_name',
          'last_name',
          'nationality',
          'birthday',
          'gender',
          'marital_status',
          'ethnicity',
        ],
      },
      {
        title: this.gt('Identification'),
        description: this.gt('Personal Information'),
        fields: [
          'immigration_status',
          'ssn_num',
          'nic_num',
          'other_id',
          'driving_license',
        ],
      },
      {
        title: this.gt('Work'),
        description: this.gt('Work related details'),
        fields: [
          'employment_status',
          'department',
          'job_title',
          'pay_grade',
          'joined_date',
          'confirmation_date',
          'termination_date',
          'work_station_id',
        ],
      },
      {
        title: this.gt('Contact'),
        description: this.gt('Contact details'),
        fields: [
          'address1', 'address2',
          'city', 'country',
          'province', 'postal_code',
          'home_phone', 'mobile_phone',
          'work_phone', 'work_email',
          'private_email',
        ],
      },
      {
        title: this.gt('Report'),
        description: this.gt('Supervisors and reports'),
        fields: [
          'supervisor',
          'indirect_supervisors',
          'approver1',
          'approver2',
          'approver3',
          'notes',
        ],
      },
    ];

    if (this.customFields.length > 0) {
      steps.push({
        title: this.gt('Other'),
        description: this.gt('Additional details'),
        fields: this.customFields.map((item) => item[0]),
      });
    }

    return this.addActualFieldsForStepModal(steps, fields);
  }

  getFilters() {
    return [
      ['job_title', {
        label: 'Job Title', type: 'select2', 'allow-null': true, 'null-label': 'All Job Titles', 'remote-source': ['JobTitle', 'id', 'name'],
      }],
      ['department', {
        label: 'Department', type: 'select2', 'allow-null': true, 'null-label': 'All Departments', 'remote-source': ['CompanyStructure', 'id', 'title'],
      }],
      ['supervisor', {
        label: 'Supervisor', type: 'select2', 'allow-null': true, 'null-label': 'Anyone', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
    ];
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        <Tag color="orange" onClick={() => modJs.setAdminProfile(record.id)} style={{ cursor: 'pointer' }}>
          <LoginOutlined />
          {` ${adapter.gt('Login As')}`}
        </Tag>
        {adapter.hasAccess('save') && adapter.showEdit
        && (
          <Tag color="green" onClick={() => modJs.edit(record.id)} style={{ cursor: 'pointer' }}>
            <EditOutlined />
            {` ${adapter.gt('Edit')}`}
          </Tag>
        )}
        {adapter.hasAccess('element')
        && (
          <Tag color="blue" onClick={() => modJs.viewElement(record.id)} style={{ cursor: 'pointer' }}>
            <MonitorOutlined />
            {` ${adapter.gt('View')}`}
          </Tag>
        )}
        {adapter.hasAccess('delete') && adapter.showDelete
        && (
          <Tag color="volcano" onClick={() => modJs.terminateEmployee(record.id)} style={{ cursor: 'pointer' }}>
            <DeleteOutlined />
            {` ${adapter.gt('Deactivate')}`}
          </Tag>
        )}
        {adapter.hasAccess('save')
        && (
          <Tag color="cyan" onClick={() => modJs.copyRow(record.id)} style={{ cursor: 'pointer' }}>
            <CopyOutlined />
            {` ${adapter.gt('Copy')}`}
          </Tag>
        )}
      </Space>
    );
  }

  getActionButtonsHtml(id) {
    let deleteBtn = '<img class="tableActionButton" src="_BASE_images/connect-no.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Terminate Employee" onclick="modJs.terminateEmployee(_id_);return false;"></img>';
    if (this.showDelete === false) {
      deleteBtn = '';
    }
    // eslint-disable-next-line max-len
    let html = `<div style="width:132px;">
<img class="tableActionButton" src="_BASE_images/user.png" style="cursor:pointer;" rel="tooltip" title="Login as this Employee" onclick="modJs.setAdminProfile(_id_);return false;"></img>
<img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="View" onclick="modJs.view(_id_);return false;"></img>
<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>
${deleteBtn}
</div>`;
    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }

  getHelpLink() {
    return 'https://thilinah.gitbooks.io/icehrm-guide/content/employee-information-setup.html';
  }

  saveSuccessItemCallback(data) {
    this.lastSavedEmployee = data;
    if (this.currentId === null) {
      $('#createUserModel').modal('show');
    }
  }

  closeCreateUser() {
    $('#createUserModel').modal('hide');
  }

  createUser() {
    const data = {};
    data.employee = this.lastSavedEmployee.id;
    data.user_level = 'Employee';
    data.email = this.lastSavedEmployee.work_email;
    data.username = this.lastSavedEmployee.work_email.split('@')[0];
    top.location.href = this.getCustomUrl(
      `?g=admin&n=users&m=admin_Admin&action=new&object=${
        Base64.encodeURI(JSON.stringify(data))}`,
    );
  }

  deleteEmployee(id) {
    if (confirm('Are you sure you want to archive this employee? Data for this employee will be saved to an archive table. But you will not be able to covert the archived employee data into a normal employee.')) {
      // Archive
    } else {
      return;
    }

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'deleteEmployeeSuccessCallback';
    callBackData.callBackFail = 'deleteEmployeeFailCallback';

    this.customAction(
      'deleteEmployee',
      'admin=employees',
      JSON.stringify({ id }), callBackData,
    );
  }


  deleteEmployeeSuccessCallback(callBackData) {
    this.showMessage('Delete Success', 'Employee deleted. You can find archived information for this employee in Archived Employees tab');
    this.get([]);
  }


  deleteEmployeeFailCallback(callBackData) {
    this.showMessage('Error occurred while deleting Employee', callBackData);
  }


  terminateEmployee(id) {
    if (confirm('Are you sure you want to terminate this employee contract? You will still be able to access all details of this employee.')) {
      // Terminate
    } else {
      return;
    }

    const params = {};
    params.id = id;
    const reqJson = JSON.stringify(params);
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'terminateEmployeeSuccessCallback';
    callBackData.callBackFail = 'terminateEmployeeFailCallback';

    this.customAction('terminateEmployee', 'admin=employees', reqJson, callBackData);
  }


  terminateEmployeeSuccessCallback(callBackData) {
    this.showMessage('Success', 'Employee contract terminated. You can find terminated employee information under Terminated Employees menu.');
    this.get([]);
  }


  terminateEmployeeFailCallback(callBackData) {
    this.showMessage('Error occured while terminating Employee', callBackData);
  }


  activateEmployee(id) {
    if (confirm('Are you sure you want to re-activate this employee contract?')) {
      // Terminate
    } else {
      return;
    }

    const params = {};
    params.id = id;
    const reqJson = JSON.stringify(params);
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'activateEmployeeSuccessCallback';
    callBackData.callBackFail = 'activateEmployeeFailCallback';

    this.customAction('activateEmployee', 'admin=employees', reqJson, callBackData);
  }


  activateEmployeeSuccessCallback(callBackData) {
    this.showMessage('Success', 'Employee contract re-activated.');
    this.get([]);
  }


  activateEmployeeFailCallback(callBackData) {
    this.showMessage('Error occurred while activating Employee', callBackData);
  }


  view(id) {
    const that = this;
    this.currentId = id;
    const sourceMappingJson = JSON.stringify(this.getSourceMapping());
    const object = { id, map: sourceMappingJson };
    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'renderEmployee';
    callBackData.callBackFail = 'viewFailCallBack';

    this.customAction('get', 'modules=employees', reqJson, callBackData);
  }


  viewFailCallBack(callBackData) {
    this.showMessage('Error', 'Error Occurred while retrieving candidate');
  }

  deleteProfileImage(empId) {
    const req = { id: empId };
    const reqJson = JSON.stringify(req);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'modEmployeeDeleteProfileImageCallBack';
    callBackData.callBackFail = 'modEmployeeDeleteProfileImageCallBack';

    this.customAction('deleteProfileImage', 'modules=employees', reqJson, callBackData);
  }

  modEmployeeDeleteProfileImageCallBack(data) {
    // top.location.href = top.location.href;
  }
}

/*
 * Terminated Employee
 */

class TerminatedEmployeeAdapter extends EmployeeAdapter {
  getDataMapping() {
    return [
      'id',
      'employee_id',
      'first_name',
      'last_name',
      'mobile_phone',
      'department',
      'gender',
      'supervisor',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID' },
      { sTitle: '', bSortable: false },
      { sTitle: 'Employee Number' },
      { sTitle: 'First Name' },
      { sTitle: 'Last Name' },
      { sTitle: 'Mobile' },
      { sTitle: 'Department' },
      { sTitle: 'Gender' },
      { sTitle: 'Supervisor' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Employee Number',
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
        title: 'Department',
        dataIndex: 'department',
      },
      {
        title: 'Supervisor',
        dataIndex: 'supervisor',
      },
    ];
  }

  getFilters() {
    return [
      ['job_title', {
        label: 'Job Title', type: 'select2', 'allow-null': true, 'null-label': 'All Job Titles', 'remote-source': ['JobTitle', 'id', 'name'],
      }],
      ['department', {
        label: 'Department', type: 'select2', 'allow-null': true, 'null-label': 'All Departments', 'remote-source': ['CompanyStructure', 'id', 'title'],
      }],
      ['supervisor', {
        label: 'Supervisor', type: 'select2', 'allow-null': true, 'null-label': 'Anyone', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
    ];
  }

  getActionButtonsHtml(id) {
    // eslint-disable-next-line max-len
    let html = `<div style="width:132px;">
<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>
<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Archive Employee" onclick="modJs.deleteEmployee(_id_);return false;"></img>
<img class="tableActionButton" src="_BASE_images/redo.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Restore Employee" onclick="modJs.activateEmployee(_id_);return false;"></img>
</div>`;
    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }

  download(id) {
    const params = { t: 'ArchivedEmployee', sa: 'downloadArchivedEmployee', mod: 'admin=employees' };
    params.req = JSON.stringify({ id });
    const downloadUrl = modJs.getCustomActionUrl('ca', params);
    window.open(downloadUrl, '_blank');
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        <Tag color="cyan" onClick={() => modJs.activateEmployee(record.id)} style={{ cursor: 'pointer' }}>
          <UndoOutlined />
          {` ${adapter.gt('Activate')}`}
        </Tag>
        {adapter.hasAccess('delete') && adapter.showDelete
        && (
          <Tag color="volcano" onClick={() => modJs.deleteEmployee(record.id)} style={{ cursor: 'pointer' }}>
            <DeleteOutlined />
            {` ${adapter.gt('Delete')}`}
          </Tag>
        )}
      </Space>
    );
  }
}


/*
 * Archived Employee
 */

class ArchivedEmployeeAdapter extends SubProfileEnabledAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee_id',
      'first_name',
      'last_name',
      'work_email',
      'department',
      'gender',
      'supervisor',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID' },
      { sTitle: 'Employee Number' },
      { sTitle: 'First Name' },
      { sTitle: 'Last Name' },
      { sTitle: 'Work Email' },
      { sTitle: 'Department' },
      { sTitle: 'Gender' },
      { sTitle: 'Supervisor' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Employee Number',
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
        title: 'Department',
        dataIndex: 'department',
      },
      {
        title: 'Supervisor',
        dataIndex: 'supervisor',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['employee_id', { label: 'Employee Number', type: 'text', validation: '' }],
      ['first_name', { label: 'First Name', type: 'text', validation: '' }],
      ['middle_name', { label: 'Middle Name', type: 'text', validation: 'none' }],
      ['last_name', { label: 'Last Name', type: 'text', validation: '' }],
      ['gender', { label: 'Gender', type: 'select', source: [['Male', 'Male'], ['Female', 'Female'], ['Non-binary', 'Non-binary'], ['Other', 'Other']] }],
      ['ssn_num', { label: 'SSN/NRIC', type: 'text', validation: 'none' }],
      ['nic_num', { label: 'NIC', type: 'text', validation: 'none' }],
      ['other_id', { label: 'Other ID', type: 'text', validation: 'none' }],
      ['driving_license', { label: 'Driving License No', type: 'text', validation: 'none' }],
      ['department', { label: 'Department', type: 'select2', 'remote-source': ['CompanyStructure', 'id', 'title'] }],
      ['supervisor', {
        label: 'Supervisor', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
    ];
  }

  getFilters() {
    return [
      ['job_title', {
        label: 'Job Title', type: 'select2', 'allow-null': true, 'null-label': 'All Job Titles', 'remote-source': ['JobTitle', 'id', 'name'],
      }],
      ['department', {
        label: 'Department', type: 'select2', 'allow-null': true, 'null-label': 'All Departments', 'remote-source': ['CompanyStructure', 'id', 'title'],
      }],
      ['supervisor', {
        label: 'Supervisor', type: 'select2', 'allow-null': true, 'null-label': 'Anyone', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
    ];
  }

  getActionButtonsHtml(id) {
    // eslint-disable-next-line max-len
    let html = '<div style="width:132px;"><img class="tableActionButton" src="_BASE_images/download.png" style="cursor:pointer;" rel="tooltip" title="Download Archived Data" onclick="modJs.download(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Remove Archived Data" onclick="modJs.deleteRow(_id_);return false;"></img></div>';
    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }

  download(id) {
    const params = { t: 'ArchivedEmployee', sa: 'downloadArchivedEmployee', mod: 'admin=employees' };
    params.req = JSON.stringify({ id });
    const downloadUrl = modJs.getCustomActionUrl('ca', params);
    window.open(downloadUrl, '_blank');
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        {adapter.hasAccess('element')
        && (
          <Tag color="blue" onClick={() => modJs.viewElement(record.id)} style={{ cursor: 'pointer' }}>
            <MonitorOutlined />
            {` ${adapter.gt('View')}`}
          </Tag>
        )}
        <Tag color="cyan" onClick={() => modJs.download(record.id)} style={{ cursor: 'pointer' }}>
          <CloudDownloadOutlined />
          {` ${adapter.gt('Download')}`}
        </Tag>
        {adapter.hasAccess('delete') && adapter.showDelete
        && (
          <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
            <DeleteOutlined />
            {` ${adapter.gt('Delete')}`}
          </Tag>
        )}
      </Space>
    );
  }
}


/*
 * ==========================================================
 */


class EmployeeSkillAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'skill_id',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Skill' },
      { sTitle: 'Details' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
      {
        title: 'Skill',
        dataIndex: 'skill_id',
        sorter: true,
      },
      {
        title: 'Details',
        dataIndex: 'details',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['skill_id', {
        label: 'Skill', type: 'select2', 'allow-null': true, 'remote-source': ['Skill', 'id', 'name'],
      }],
      ['details', { label: 'Details', type: 'textarea', validation: '' }],
    ];
  }


  getFilters() {
    return [
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': true,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['skill_id', {
        label: 'Skill', type: 'select2', 'allow-null': true, 'null-label': 'All Skills', 'remote-source': ['Skill', 'id', 'name'],
      }],

    ];
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}


/**
 * EmployeeEducationAdapter
 */

class EmployeeEducationAdapter extends SubProfileEnabledAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'education_id',
      'institute',
      'date_start',
      'date_end',
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
      {
        title: 'Qualification',
        dataIndex: 'education_id',
        sorter: true,
      },
      {
        title: 'Institute',
        dataIndex: 'institute',
        sorter: true,
      },
      {
        title: 'Start Date',
        dataIndex: 'date_start',
        sorter: true,
      },
      {
        title: 'Completed On',
        dataIndex: 'date_end',
        sorter: true,
      },
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Qualification' },
      { sTitle: 'Institute' },
      { sTitle: 'Start Date' },
      { sTitle: 'Completed On' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['education_id', {
        label: 'Qualification', type: 'select2', 'allow-null': false, 'remote-source': ['Education', 'id', 'name'],
      }],
      ['institute', { label: 'Institute', type: 'text', validation: '' }],
      ['date_start', { label: 'Start Date', type: 'date', validation: 'none' }],
      ['date_end', { label: 'Completed On', type: 'date', validation: 'none' }],
    ];
  }


  getFilters() {
    return [
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': true,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['education_id', {
        label: 'Qualification', type: 'select2', 'allow-null': true, 'null-label': 'All Qualifications', 'remote-source': ['Education', 'id', 'name'],
      }],

    ];
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}


/**
 * EmployeeCertificationAdapter
 */

class EmployeeCertificationAdapter extends SubProfileEnabledAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'certification_id',
      'institute',
      'date_start',
      'date_end',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Certification' },
      { sTitle: 'Institute' },
      { sTitle: 'Granted On' },
      { sTitle: 'Valid Thru' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
      {
        title: 'Certification',
        dataIndex: 'certification_id',
        sorter: true,
      },
      {
        title: 'Institute',
        dataIndex: 'institute',
        sorter: true,
      },
      {
        title: 'Granted On',
        dataIndex: 'date_start',
        sorter: true,
      },
      {
        title: 'Valid Until',
        dataIndex: 'date_end',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['certification_id', {
        label: 'Certification', type: 'select2', 'allow-null': false, 'remote-source': ['Certification', 'id', 'name'],
      }],
      ['institute', { label: 'Institute', type: 'text', validation: '' }],
      ['date_start', { label: 'Granted On', type: 'date', validation: 'none' }],
      ['date_end', { label: 'Valid Thru', type: 'date', validation: 'none' }],
    ];
  }


  getFilters() {
    return [
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': true,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['certification_id', {
        label: 'Certification', type: 'select2', 'allow-null': true, 'null-label': 'All Certifications', 'remote-source': ['Certification', 'id', 'name'],
      }],

    ];
  }


  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}


/**
 * EmployeeLanguageAdapter
 */

class EmployeeLanguageAdapter extends SubProfileEnabledAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'language_id',
      'reading',
      'speaking',
      'writing',
      'understanding',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Language' },
      { sTitle: 'Reading' },
      { sTitle: 'Speaking' },
      { sTitle: 'Writing' },
      { sTitle: 'Listening' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
      {
        title: 'Language',
        dataIndex: 'language_id',
        sorter: true,
      },
      {
        title: 'Reading',
        dataIndex: 'reading',
        sorter: true,
      },
      {
        title: 'Speaking',
        dataIndex: 'speaking',
        sorter: true,
      },
      {
        title: 'Writing',
        dataIndex: 'writing',
        sorter: true,
      },
      {
        title: 'Listening',
        dataIndex: 'understanding',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    const compArray = [['Elementary Proficiency', 'Elementary Proficiency'],
      ['Limited Working Proficiency', 'Limited Working Proficiency'],
      ['Professional Working Proficiency', 'Professional Working Proficiency'],
      ['Full Professional Proficiency', 'Full Professional Proficiency'],
      ['Native or Bilingual Proficiency', 'Native or Bilingual Proficiency']];

    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['language_id', {
        label: 'Language', type: 'select2', 'allow-null': false, 'remote-source': ['Language', 'id', 'name'],
      }],
      ['reading', { label: 'Reading', type: 'select', source: compArray }],
      ['speaking', { label: 'Speaking', type: 'select', source: compArray }],
      ['writing', { label: 'Writing', type: 'select', source: compArray }],
      ['understanding', { label: 'Listening', type: 'select', source: compArray }],
    ];
  }


  getFilters() {
    return [
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': true,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['language_id', {
        label: 'Language', type: 'select2', 'allow-null': true, 'null-label': 'All Languages', 'remote-source': ['Language', 'id', 'name'],
      }],

    ];
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}


/**
 * EmployeeDependentAdapter
 */


class EmployeeDependentAdapter extends SubProfileEnabledAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'name',
      'relationship',
      'dob',
      'id_number',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Name' },
      { sTitle: 'Relationship' },
      { sTitle: 'Date of Birth' },
      { sTitle: 'Id Number' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Relationship',
        dataIndex: 'relationship',
        sorter: true,
      },
      {
        title: 'Date of Birth',
        dataIndex: 'dob',
        sorter: true,
      },
      {
        title: 'Id Number',
        dataIndex: 'id_number',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['relationship', { label: 'Relationship', type: 'select', source: [['Child', 'Child'], ['Spouse', 'Spouse'], ['Parent', 'Parent'], ['Other', 'Other']] }],
      ['dob', { label: 'Date of Birth', type: 'date', validation: '' }],
      ['id_number', { label: 'Id Number', type: 'text', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
    ];
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}


/*
 * EmergencyContactAdapter
 */


class EmergencyContactAdapter extends SubProfileEnabledAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'name',
      'relationship',
      'home_phone',
      'work_phone',
      'mobile_phone',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Name' },
      { sTitle: 'Relationship' },
      { sTitle: 'Home Phone' },
      { sTitle: 'Work Phone' },
      { sTitle: 'Mobile Phone' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Relationship',
        dataIndex: 'relationship',
        sorter: true,
      },
      {
        title: 'Home Phone',
        dataIndex: 'home_phone',
      },
      {
        title: 'Work Phone',
        dataIndex: 'work_phone',
      },
      {
        title: 'Mobile Phone',
        dataIndex: 'mobile_phone',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['relationship', { label: 'Relationship', type: 'text', validation: 'none' }],
      ['home_phone', { label: 'Home Phone', type: 'text', validation: 'none' }],
      ['work_phone', { label: 'Work Phone', type: 'text', validation: 'none' }],
      ['mobile_phone', { label: 'Mobile Phone', type: 'text', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
    ];
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}


/*
 * EmployeeImmigrationAdapter
 */


class EmployeeImmigrationAdapter extends SubProfileEnabledAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'document',
      'doc_number',
      'issued',
      'expiry',
      'status',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Document', sClass: 'columnMain' },
      { sTitle: 'Number' },
      { sTitle: 'Issued Date' },
      { sTitle: 'Expiry Date' },
      { sTitle: 'Status' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['document', { label: 'Document', type: 'select2', source: [['Passport', 'Passport'], ['Visa', 'Visa']] }],
      ['doc_number', { label: 'Number', type: 'text', validation: '' }],
      ['issued', { label: 'Issued Date', type: 'date', validation: '' }],
      ['expiry', { label: 'Expiry Date', type: 'date', validation: '' }],
      ['status', { label: 'Status', type: 'text', validation: 'none' }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],
    ];
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}

class EmployeeCareerAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'job_title',
      'date_start',
      'date_end',
      'employment_status',
      'department',
      'supervisor',
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
      {
        title: 'Job Title',
        dataIndex: 'job_title',
        sorter: true,
      },
      {
        title: 'Start Date',
        dataIndex: 'date_start',
        sorter: true,
      },
      {
        title: 'End Date',
        dataIndex: 'date_end',
        sorter: true,
      },
      {
        title: 'Department',
        dataIndex: 'department',
        sorter: true,
      },
      {
        title: 'Supervisor',
        dataIndex: 'supervisor',
        sorter: true,
      },
      {
        title: 'Employment Status',
        dataIndex: 'employment_status',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['job_title', { label: 'Job Title', type: 'select2', 'remote-source': ['JobTitle', 'id', 'name'] }],
      ['date_start', { label: 'Start Date', type: 'date', validation: '' }],
      ['date_end', { label: 'End Date', type: 'date', validation: 'none' }],
      ['department', { label: 'Department', type: 'select2', 'remote-source': ['CompanyStructure', 'id', 'title'] }],
      ['supervisor', {
        label: 'Supervisor', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['employment_status', { label: 'Employment Status', type: 'select2', 'remote-source': ['EmploymentStatus', 'id', 'name'] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }


  getFilters() {
    return [
      ['employee', {
        label: 'Employee',
        type: 'select2',
        sort: 'none',
        'allow-null': true,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['job_title', { label: 'Job Title', type: 'select2', 'allow-null': true, 'remote-source': ['JobTitle', 'id', 'name'] }],
      ['department', { label: 'Department', type: 'select2', 'allow-null': true, 'remote-source': ['CompanyStructure', 'id', 'title'] }],
      ['supervisor', {
        label: 'Supervisor', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['employment_status', { label: 'Employment Status', type: 'select2', 'allow-null': true, 'remote-source': ['EmploymentStatus', 'id', 'name'] }],

    ];
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}


module.exports = {
  EmployeeAdapter,
  TerminatedEmployeeAdapter,
  ArchivedEmployeeAdapter,
  EmployeeSkillAdapter,
  EmployeeEducationAdapter,
  EmployeeCertificationAdapter,
  EmployeeLanguageAdapter,
  EmployeeDependentAdapter,
  EmergencyContactAdapter,
  EmployeeImmigrationAdapter,
  EmployeeCareerAdapter,
};
