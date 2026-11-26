/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/* eslint-disable prefer-destructuring,no-restricted-globals */

/* global Base64, modJs, nl2br, ga, modJsList */
/**
 * Author: Thilina Hasantha
 */

import React from 'react';
import {
  Avatar, Space, Tag, message, Modal, Button,
} from 'antd';
import {
  CloudDownloadOutlined,
  DeleteOutlined,
  UndoOutlined,
  MonitorOutlined,
  LoginOutlined,
  EditOutlined,
  CopyOutlined,
  SyncOutlined,
  CloudUploadOutlined,
  ExclamationCircleOutlined,
} from '@ant-design/icons';
import ReactDOM from 'react-dom';
import ReactModalAdapterBase, { shellThemeWrap } from '../../../api/ReactModalAdapterBase';
import IceDataPipe from '../../../api/IceDataPipe';
import EmployeeProfile from './components/EmployeeProfile';
import EmployeeWizardModal from './components/EmployeeWizard';
import IceTable from '../../../components/IceTable';
import EmployeeAdminView from './view';
import {
  UserAdapter,
} from '../users/lib';


const EMPLOYMENT_TYPES = [['Full-time', 'Full-time'], ['Part-time', 'Part-time'], ['Contractor', 'Contractor'], ['Intern', 'Intern'], ['Temporary', 'Temporary']];
const WORK_MODES = [['Office', 'Office'], ['Remote', 'Remote'], ['Hybrid', 'Hybrid']];
const TERMINATION_TYPES = [['Voluntary', 'Voluntary'], ['Involuntary', 'Involuntary'], ['Mutual agreement', 'Mutual agreement'], ['End of contract', 'End of contract'], ['Retirement', 'Retirement']];
const REHIRE_OPTIONS = [['Yes', 'Yes'], ['No', 'No'], ['Conditional', 'Conditional']];
const YES_NO = [['Yes', 'Yes'], ['No', 'No']];
const CAREER_CHANGE_REASONS = [['Hire', 'Hire'], ['Promotion', 'Promotion'], ['Transfer', 'Transfer'], ['Reorganisation', 'Reorganisation'], ['Contract change', 'Contract change'], ['Other', 'Other']];
const GENDERS = [['Male', 'Male'], ['Female', 'Female'], ['Non-binary', 'Non-binary'], ['Other', 'Other'], ['Prefer not to say', 'Prefer not to say']];

/** Offboarding details, edited from the Resigned tab. */
const OFFBOARDING_FIELDS = [
  ['id', { label: 'ID', type: 'hidden', validation: '' }],
  ['termination_date', { label: 'Termination Date', type: 'date', validation: 'none' }],
  ['last_working_day', { label: 'Last Working Day', type: 'date', validation: 'none' }],
  ['termination_type', {
    label: 'Termination Type', type: 'select', 'allow-null': true, source: TERMINATION_TYPES,
  }],
  ['termination_reason', {
    label: 'Termination Reason', type: 'select2', 'allow-null': true, 'remote-source': ['TerminationReason', 'id', 'name'],
  }],
  ['eligible_for_rehire', {
    label: 'Eligible for Rehire', type: 'select', 'allow-null': true, source: REHIRE_OPTIONS,
  }],
  ['regrettable', {
    label: 'Regrettable', type: 'select', 'allow-null': true, source: YES_NO,
  }],
];

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
    this.employeeProfileRef = null;
    this.employeeListRef = null;
    this.employeeTopBar = null;
    this.allowSwitchToEmployeeProfile = false;
    // When on, the Employee Number is auto-generated: the field is read-only and
    // prefilled with the next number when adding a new employee.
    this.generateEmployeeNumbers = false;
    this.employeeNumberPrefix = '';
  }

  setEmployeeNumberGeneration(enabled, prefix) {
    this.generateEmployeeNumbers = !!enabled;
    this.employeeNumberPrefix = prefix || '';
  }

  // When any module has multi-level approvals enabled, the employee profile shows
  // an "Approvals" tab to manage approver1/2/3 (the supervisor is the read-only
  // initial approver). The approver fields are removed from the edit form.
  setMultiLevelApprovals(enabled) {
    this.multiLevelApprovals = !!enabled;
  }

  // When auto-numbering is on, adding a new employee prefills the (read-only)
  // Employee Number with the next number fetched from the server. Edit/view are
  // unaffected. The backend regenerates authoritatively on save (handles any
  // collision), so this is only the preview.
  renderForm(object = null, viewOnly = false) {
    const adding = object == null && !viewOnly;
    if (adding && this.generateEmployeeNumbers && this.apiClient) {
      this.apiClient.get('employees/next-employee-number')
        .then((res) => {
          const number = (res && res.data && res.data.number) || '';
          super.renderForm(number ? { employee_id: number } : null, viewOnly);
        })
        .catch(() => super.renderForm(null, viewOnly));
      return;
    }
    super.renderForm(object, viewOnly);
  }

  // Add/edit uses the guided employee wizard (a re-skinned step modal, same
  // pattern as the Leave Type and Candidate wizards); the field set, dynamic
  // field-name mappings, validation and save path are unchanged.
  getStepFormModalComponent() {
    return EmployeeWizardModal;
  }

  showViewButton() {
    return true;
  }

  setAllowSwitchToEmployeeProfile(value) {
    this.allowSwitchToEmployeeProfile = value;
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

  reloadCurrentElement() {
    this.reloadEmployee();
  }

  reloadEmployeeList() {
    if (this.employeeListRef && this.employeeListRef.current) {
      this.employeeListRef.current.reloadList();
    }
  }

  initTable() {
    if (this.tableInitialized) {
      return false;
    }
    const tableDom = document.getElementById(`${this.tab}Table`);
    if (tableDom) {
      this.tableContainer = React.createRef();

      ReactDOM.render(
        shellThemeWrap(
          <EmployeeAdminView
            ref={this.tableContainer}
            apiClient={this.apiClient}
            ice={this}
          />,
        ),
        tableDom,
      );
    }

    this.tableInitialized = true;

    return true;
  }

  setTableContainerFilterData(values) {
    this.employeeTopBar.current.setFilterData(values);
    this.reloadEmployeeList();
  }

  reloadEmployee(id) {
    this.employeeProfileRef.current.reloadEmployee(id);
  }

  inviteEmployee() {
    if (this.inviteRoute) {
      // Tell Company Onboarding to open its "Invite your team" step (when set up).
      try {
        window.sessionStorage.setItem('company-onboarding-open', 'invite');
      } catch (e) {
        // Storage blocked: the setup opens on its current step instead.
      }
      window.location.hash = `#${encodeURIComponent(this.inviteRoute)}`;
      return;
    }
    modJsList.tabUserInvitation.initFieldMasterData(() => {
      modJsList.tabUserInvitation.renderForm();
    });
  }

  add(object, getFunctionCallBackData, callGetFunction, successCallback, failCallback) {
    const that = this;
    let callback;
    let content;
    if (!object.id) {
      content = `${that.gt('Setting up employee profile for')} ${object.first_name} ${object.last_name} ...`;
      callback = (serverData) => {
        successCallback.apply(that, [serverData]);
        message.success({
          content: that.gt('New employee profile created. Please find the new employee profile at the end of the list'),
          key: 'updateEmployeeProfile',
          duration: 4,
          style: {
            marginTop: '9vh',
          },
        });
        this.reloadEmployeeList();
      };
    } else {
      content = `${that.gt('Updating the employee profile for')} ${object.first_name} ${object.last_name} ...`;
      callback = (serverData) => {
        successCallback.apply(that, [serverData]);
        message.success({
          content: that.gt('Employee profile updated'),
          key: 'updateEmployeeProfile',
          duration: 3,
          style: {
            marginTop: '9vh',
          },
        });
        this.reloadEmployee();
      };
    }
    message.loading({
      content,
      key: 'updateEmployeeProfile',
      style: {
        marginTop: '9vh',
      },
    });
    super.add(object, getFunctionCallBackData, callGetFunction, callback, failCallback);
  }

  addFailCallBack(callBackData, serverData, failCallback, thisObject) {
    message.error({
      content: this.gt('Error updating employee profile'),
      key: 'updateEmployeeProfile',
      duration: 3,
      style: {
        marginTop: '9vh',
      },
    });

    super.addFailCallBack(callBackData, serverData, failCallback, thisObject);
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
      // 'mobile_phone',
      'department',
      // 'gender',
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
      ['employee_id', {
        label: 'Employee Number', type: 'text', validation: '', readonly: this.generateEmployeeNumbers,
      }],
      ['first_name', { label: 'First Name', type: 'text', validation: '' }],
      ['middle_name', { label: 'Middle Name', type: 'text', validation: 'none' }],
      ['last_name', { label: 'Last Name', type: 'text', validation: '' }],
      ['name_preferred', { label: 'Preferred Name', type: 'text', validation: 'none' }],
      ['pronouns', { label: 'Pronouns', type: 'text', validation: 'none' }],
      ['name_pronunciation', { label: 'Name Pronunciation', type: 'text', validation: 'none' }],
      ['nationality', { label: 'Nationality', type: 'select2', 'remote-source': ['Nationality', 'id', 'name'] }],
      ['birthday', { label: 'Date of Birth', type: 'date', validation: '' }],
      ['gender', { label: 'Gender', type: 'select', source: GENDERS }],
      ['marital_status', { label: 'Marital Status', type: 'select', source: [['Married', 'Married'], ['Single', 'Single'], ['Divorced', 'Divorced'], ['Widowed', 'Widowed'], ['Other', 'Other']] }],
      ['ethnicity', {
        label: 'Ethnicity', type: 'select2', 'allow-null': true, 'remote-source': ['Ethnicity', 'id', 'name'],
      }],
      ['immigration_status', {
        label: 'Immigration Status', type: 'select2', 'allow-null': true, 'remote-source': ['ImmigrationStatus', 'id', 'name'],
      }],
      ['tax_id', { label: 'Personal Tax ID', type: 'text', validation: 'none' }],
      ['ssn_num', { label: 'Social Insurance', type: 'text', validation: 'none' }],
      ['nic_num', { label: 'National ID', type: 'text', validation: 'none' }],
      ['other_id', { label: 'Additional IDs', type: 'text', validation: 'none' }],
      ['driving_license', { label: 'Driving License', type: 'text', validation: 'none' }],
      ['driving_license_exp_date', { label: 'Driving License Expiry', type: 'date', validation: 'none' }],
      ['health_insurance', { label: 'Health Insurance', type: 'text', validation: 'none' }],

      ['employment_status', { label: 'Employment Status', type: 'select2', 'remote-source': ['EmploymentStatus', 'id', 'name'] }],
      ['employment_type', {
        label: 'Employment Type', type: 'select', 'allow-null': true, source: EMPLOYMENT_TYPES,
      }],
      ['department', { label: 'Department', type: 'select2', 'remote-source': ['CompanyStructure', 'id', 'title'] }],
      ['job_title', { label: 'Job Title', type: 'select2', 'remote-source': ['JobTitle', 'id', 'name'] }],
      ['pay_grade', {
        label: 'Pay Grade', type: 'select2', 'allow-null': true, 'remote-source': ['PayGrade', 'id', 'name'],
      }],
      ['joined_date', { label: 'Joined Date', type: 'date', validation: '' }],
      ['original_hire_date', { label: 'Original Hire Date', type: 'date', validation: 'none' }],
      ['confirmation_date', { label: 'Probation End Date', type: 'date', validation: 'none' }],
      ['contract_end_date', { label: 'Contract End Date', type: 'date', validation: 'none' }],
      ['termination_date', { label: 'Termination Date', type: 'date', validation: 'none' }],
      ['work_location', {
        label: 'Work Location', type: 'select2', 'allow-null': true, 'remote-source': ['WorkLocation', 'id', 'name'],
      }],
      ['work_mode', {
        label: 'Work Arrangement', type: 'select', 'allow-null': true, source: WORK_MODES,
      }],
      ['weekly_hours', { label: 'Weekly Hours', type: 'text', validation: 'none' }],
      ['fte', {
        label: 'Full-time Equivalent (FTE)',
        type: 'text',
        validation: 'none',
        help: 'How much of a full-time job this person works: 1 is full time, 0.5 is half time. Leave it empty to work it out from Weekly Hours and the Standard Weekly Hours company setting.',
      }],
      ['cost_center', {
        label: 'Cost Center', type: 'select2', 'allow-null': true, 'remote-source': ['CostCenter', 'id', 'name'],
      }],
      ['timezone', {
        label: 'Time Zone', type: 'select2', 'allow-null': false, 'remote-source': ['Timezone', 'name', 'details', 'getTimezonesWithOffset'],
      }],
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
      ['linkedin_url', { label: 'LinkedIn', type: 'text', validation: 'none' }],
      ['about', { label: 'About', type: 'textarea', validation: 'none' }],

      ['supervisor', {
        label: 'Direct Manager', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['indirect_supervisors', {
        label: 'Indirect Managers', type: 'select2multi', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      // Approver1/2/3 are managed under the employee profile's "Approvals" tab
      // (shown when multi-level approvals are enabled), not this form.
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
          'name_preferred',
          'pronouns',
          'name_pronunciation',
          'nationality',
          'birthday',
          'gender',
          'marital_status',
          'ethnicity',
          'about',
        ],
      },
      {
        title: this.gt('Identification'),
        description: this.gt('Identification details'),
        fields: [
          'immigration_status',
          'tax_id',
          'ssn_num',
          'nic_num',
          'other_id',
          'driving_license',
          'driving_license_exp_date',
          'health_insurance',
        ],
      },
      {
        title: this.gt('Work'),
        description: this.gt('Work related details'),
        fields: [
          'employment_status',
          'employment_type',
          'department',
          'job_title',
          'pay_grade',
          'work_location',
          'work_mode',
          'weekly_hours',
          'fte',
          'cost_center',
          'joined_date',
          'original_hire_date',
          'confirmation_date',
          'contract_end_date',
          // termination_date is deliberately absent from the form: it is set by
          // the resignation action (see EmployeesActionManager::terminateEmployee)
          // to the date the employee moved to the Resigned tab, and cleared again
          // on activation. A blank field here would have overwritten that. The
          // field definition is kept in getFormFields so it still appears in the
          // field-name mapping screens.
          'work_station_id',
          'timezone',
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
          'private_email', 'linkedin_url',
        ],
      },
      {
        title: this.gt('Report'),
        description: this.gt('To Whom this Employee Reports'),
        fields: [
          'supervisor',
          'indirect_supervisors',
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
        label: 'Manager', type: 'select2', 'allow-null': true, 'null-label': 'Anyone', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
    ];
  }

  getEditButtonJsx() {
    return (
      <>
        {this.props.loading
        && (
        <Tag icon={<SyncOutlined spin />} color="processing">
          {this.props.adapter.gt('Edit')}
        </Tag>
        )}
        {!this.props.loading
        && (
        <Tag
          icon={<EditOutlined />}
          color="processing"
          onClick={() => modJs.edit(this.props.element.id)}
        >
          {this.props.adapter.gt('Edit')}
        </Tag>
        )}
      </>
    );
  }

  getTableActionButtonJsx(adapter, id, loading) {
    // Theme-aware action buttons (antd Buttons adapt to dark/light, unlike the
    // old colour-filled Tags). Semantic emphasis: destructive = danger (red),
    // the primary action (Edit) = ghost-primary, the rest = neutral.
    const canSave = adapter.hasAccess('save');
    return (
      <Space size="small" wrap>
        {this.user.employee !== id && modJs.allowSwitchToEmployeeProfile
          && (
          <Button size="small" icon={<LoginOutlined />} loading={loading} onClick={() => modJs.setAdminProfile(id)}>
            {adapter.gt('Switch to Employee')}
          </Button>
          )}
        {canSave
          && (
          <Button size="small" icon={<CloudUploadOutlined />} loading={loading} onClick={() => modJs.employeeProfileRef.current.updateProfileImage()}>
            {adapter.gt('Upload Photo')}
          </Button>
          )}
        {canSave
          && (
          <Button size="small" color="gold" variant="outlined" icon={<DeleteOutlined />} loading={loading} onClick={() => modJs.deleteProfileImage(id)}>
            {adapter.gt('Remove Photo')}
          </Button>
          )}
        {canSave && adapter.showEdit
          && (
          <Button size="small" type="primary" ghost icon={<EditOutlined />} loading={loading} onClick={() => modJs.edit(id)}>
            {adapter.gt('Edit')}
          </Button>
          )}
        {adapter.hasAccess('delete') && adapter.showDelete
          && (
          <Button size="small" danger icon={<DeleteOutlined />} loading={loading} onClick={() => modJs.terminateEmployee(id)}>
            {adapter.gt('Initiate Resignation')}
          </Button>
          )}
        {canSave
          && (
          <Button size="small" icon={<CopyOutlined />} loading={loading} onClick={() => modJs.copyRow(id)}>
            {adapter.gt('Copy')}
          </Button>
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

  createUser(employee) {
    // Route the save through the token-authenticated REST endpoint
    // (employees/{id}/create-user) rather than the CSRF-guarded saveUser action,
    // since the SPA shell does not prime a per-form CSRF token here.
    modJsList.tabUser.createUserEmployeeId = employee.id;
    modJsList.tabUser.setSaveCompleteCallback(() => { modJs.reloadEmployee(employee.id); });
    modJsList.tabUser.initFieldMasterData(() => {
      modJsList.tabUser.renderForm(
        {
          employee: employee.id,
          username: `${employee.first_name}.${employee.last_name}`.toLowerCase(),
        },
      );
    });
  }

  // Opens the User adapter's edit form for the account linked to this employee
  // (shown on the profile, admin only). Unlike createUser this goes through the
  // normal CSRF-guarded saveUser path, so the token primed at init (userEditCsrf)
  // is placed on #UserForm — the div saveUser reads it from — before the form opens.
  editUser(employee) {
    if (!employee || !employee.user_id) {
      return;
    }
    const userAdapter = modJsList.tabUser;
    let form = document.getElementById('UserForm');
    if (!form) {
      form = document.createElement('div');
      form.id = 'UserForm';
      form.style.display = 'none';
      document.body.appendChild(form);
    }
    if (userAdapter.userEditCsrf) {
      $(form).data('csrf', userAdapter.userEditCsrf);
    }
    // Ensure the create branch of saveCallback is not taken (edit, not create).
    userAdapter.createUserEmployeeId = null;
    userAdapter.setSaveCompleteCallback(() => { modJs.reloadEmployee(employee.id); });
    userAdapter.initFieldMasterData(() => {
      userAdapter.edit(employee.user_id);
    });
  }

  // Opens the change-password form for the account linked to this employee (shown on
  // the profile, admin only). Posts to the token-authenticated user/password endpoint,
  // which lets an admin set any user's password — so no CSRF token is needed here.
  changeUserPassword(employee) {
    if (!employee || !employee.user_id) {
      return;
    }
    let container = document.getElementById('UserPasswordChangeForm');
    if (!container) {
      container = document.createElement('div');
      container.id = 'UserPasswordChangeForm';
      container.style.display = 'none';
      document.body.appendChild(container);
    }
    modJsList.tabUser.showPasswordChangeForm(employee.user_id);
  }

  // Archives the employee (the deleteEmployee sub action copies the profile and
  // all related records into ArchivedEmployees before removing the row) — this
  // is never a plain row delete.
  // `options` is passed by the SPA card list, which runs its own confirm and
  // reloads itself; the legacy table has neither, so it keeps the browser
  // confirm and the built-in message + refresh.
  deleteEmployee(id, options) {
    const opts = options || {};
    const driven = typeof opts.onSuccess === 'function' || typeof opts.onFail === 'function';

    // eslint-disable-next-line no-alert, no-restricted-globals
    if (!driven && !confirm('Are you sure you want to archive this employee? Data for this employee will be saved to an archive table. But you will not be able to covert the archived employee data into a normal employee.')) {
      return;
    }

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = driven
      ? (data) => { if (opts.onSuccess) { opts.onSuccess(data); } }
      : 'deleteEmployeeSuccessCallback';
    callBackData.callBackFail = driven
      ? (data) => { if (opts.onFail) { opts.onFail(data); } }
      : 'deleteEmployeeFailCallback';

    this.customAction(
      'deleteEmployee',
      'admin=employees',
      JSON.stringify({ id }), callBackData,
    );
  }


  deleteEmployeeSuccessCallback(callBackData) {
    this.showMessage(this.gt('Delete Success'), this.gt('Employee deleted. You can find archived information for this employee in Archived Employees tab'));
    this.get([]);
  }


  deleteEmployeeFailCallback(callBackData) {
    this.showMessage(this.gt('Error occurred while deleting Employee'), callBackData);
  }


  terminateEmployee(id) {
    const that = this;
    Modal.confirm({
      title: this.gt('Initiate Resignation'),
      icon: <ExclamationCircleOutlined />,
      content: this.gt('Are you sure you want to mark this employee as resigned? They move to the Resigned tab, and you can still see all their details.'),
      okText: this.gt('Yes, Mark as Resigned'),
      okType: 'danger',
      cancelText: this.gt('Cancel'),
      onOk() {
        const params = {};
        params.id = id;
        const reqJson = JSON.stringify(params);
        const callBackData = [];
        callBackData.callBackData = [];
        callBackData.callBackSuccess = 'terminateEmployeeSuccessCallback';
        callBackData.callBackFail = 'terminateEmployeeFailCallback';

        that.customAction('terminateEmployee', 'admin=employees', reqJson, callBackData);
      },
    });
  }


  terminateEmployeeSuccessCallback(callBackData) {
    this.showMessage(this.gt('Success'), this.gt('The employee is marked as resigned and moved to the Resigned tab.'));
    this.reloadEmployeeList();
  }


  terminateEmployeeFailCallback(callBackData) {
    this.showMessage(this.gt('The employee could not be marked as resigned.'), callBackData);
  }


  activateEmployee(id) {
    const that = this;
    Modal.confirm({
      title: this.gt('Re-activate Employee'),
      icon: <ExclamationCircleOutlined />,
      content: this.gt('Are you sure you want to re-activate this employee contract?'),
      okText: this.gt('Yes, Activate'),
      cancelText: this.gt('Cancel'),
      onOk() {
        const params = {};
        params.id = id;
        const reqJson = JSON.stringify(params);
        const callBackData = [];
        callBackData.callBackData = [];
        callBackData.callBackSuccess = 'activateEmployeeSuccessCallback';
        callBackData.callBackFail = 'activateEmployeeFailCallback';

        that.customAction('activateEmployee', 'admin=employees', reqJson, callBackData);
      },
    });
  }


  activateEmployeeSuccessCallback(callBackData) {
    this.showMessage(this.gt('Success'), this.gt('Employee contract re-activated.'));
    this.get([]);
  }


  activateEmployeeFailCallback(callBackData) {
    this.showMessage(this.gt('Error occurred while activating Employee'), callBackData);
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
    this.showMessage(this.gt('Error'), this.gt('Error Occurred while retrieving candidate'));
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
    this.reloadEmployee();
  }
}

/*
 * Terminated Employee
 */

class TerminatedEmployeeAdapter extends EmployeeAdapter {
  getTableChildComponents() {
    return (<EmployeeProfile />);
  }

  // Editing a resigned employee records the offboarding details only.
  getFormFields() {
    return OFFBOARDING_FIELDS.map(([name, field]) => {
      const mapped = this.fieldNameMap[name];
      return [name, mapped ? { ...field, label: mapped.textMapped } : { ...field }];
    });
  }

  renderForm(object = null, viewOnly = false) {
    ReactModalAdapterBase.prototype.renderForm.call(this, object, viewOnly);
  }

  // The list's element only carries raw ids (this adapter's form has just the
  // offboarding fields, so nothing else is mapped). The profile needs the same
  // enriched record as the Employees tab ({id, display} for manager, nationality,
  // country, job title, department...), so load it from the REST API.
  showElement(element) {
    const set = (el) => {
      if (this.tableContainer && this.tableContainer.current) {
        this.tableContainer.current.setCurrentElement(el);
      }
    };
    if (!element || !element.id || !this.apiClient) {
      set(element);
      return;
    }
    this.apiClient.get(`employees/${element.id}`)
      .then((response) => set(response && response.data && response.data.id ? response.data : element))
      .catch(() => set(element));
  }

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
      // Requested through `cl`, so the list actually returns it for the card.
      'termination_date',
      // Not a column: the card uses it to flag hires whose onboarding was cancelled.
      'termination_type',
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
      { sTitle: 'Manager' },
      { sTitle: 'Last Day of Employment' },
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
        title: 'Manager',
        dataIndex: 'supervisor',
      },
      {
        title: 'Last Day of Employment',
        dataIndex: 'termination_date',
      },
    ];
  }

  add(object, getFunctionCallBackData, callGetFunction, successCallback, failCallback) {
    const that = this;
    if (callGetFunction === undefined || callGetFunction === null) {
      // eslint-disable-next-line no-param-reassign
      callGetFunction = true;
    }
    $(object).attr('a', 'add');
    $(object).attr('t', this.table);
    that.showLoader();
    this.requestCache.invalidateTable(this.table);
    $.post(this.moduleRelativeURL, object, (data) => {
      if (data.status === 'SUCCESS') {
        that.addSuccessCallBack(getFunctionCallBackData, data.object, callGetFunction, successCallback, that);
      } else {
        that.addFailCallBack(getFunctionCallBackData, data.object, failCallback, that);
      }
    }, 'json')
      .fail((e) => {
        // See AdapterBase.add — the fail path has to run here as well so the
        // caller's saving state is released on a 500/timeout.
        if (e.status === 403) {
          that.showMessage(that.gt('Access Forbidden'), that.getRequestFailureMessage(e));
          if (failCallback !== undefined && failCallback !== null) {
            failCallback.apply(that, [null]);
          }
          that.trackEvent('addFailed', that.tab, that.table);
          return;
        }
        that.addFailCallBack(getFunctionCallBackData, that.getRequestFailureMessage(e), failCallback, that);
      })
      .always(() => { that.hideLoader(); });
    this.trackEvent('add', this.tab, this.table);
  }

  addFailCallBack(callBackData, serverData, failCallback, thisObject) {
    try {
      this.closePlainMessage();
    } catch (e) {
      // No need to report
    }
    if (failCallback !== undefined && failCallback !== null) {
      failCallback.apply(thisObject || this, [serverData]);
    }
    this.showMessage(this.gt('Error saving'), serverData);
    this.trackEvent('addFailed', this.tab, this.table);
  }

  // Add original method
  initTable() {
    this.initTableTopComponent();
    if (this.tableInitialized) {
      return false;
    }
    const tableDom = document.getElementById(`${this.tab}Table`);
    if (tableDom) {
      this.tableContainer = React.createRef();
      let columns = this.getTableColumns();
      if (this.hasAccess('save')
        || this.hasAccess('delete')
        || this.hasAccess('element')
        || this.hasCustomButtons()
      ) {
        columns.push({
          title: 'Actions',
          key: 'actions',
          render: this.getTableActionButtonJsx(this),
        });
      }

      columns = columns.map((item) => {
        item.title = this.gt(item.title);
        return item;
      });

      ReactDOM.render(
        <IceTable
          ref={this.tableContainer}
          reader={this.dataPipe}
          columns={columns}
          adapter={this}
        >
          {this.getTableChildComponents()}
        </IceTable>,
        tableDom,
      );
    }

    this.tableInitialized = true;

    return true;
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
        label: 'Manager', type: 'select2', 'allow-null': true, 'null-label': 'Anyone', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
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
        {adapter.hasAccess('element')
        && (
          <Tag color="blue" onClick={() => modJs.viewElement(record.id)} style={{ cursor: 'pointer' }}>
            <MonitorOutlined />
            {` ${adapter.gt('View')}`}
          </Tag>
        )}
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

  showViewButton() {
    return true;
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
      { sTitle: 'Manager' },
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
        title: 'Manager',
        dataIndex: 'supervisor',
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['employee_id', {
        label: 'Employee Number', type: 'text', validation: '', readonly: this.generateEmployeeNumbers,
      }],
      ['first_name', { label: 'First Name', type: 'text', validation: '' }],
      ['middle_name', { label: 'Middle Name', type: 'text', validation: 'none' }],
      ['last_name', { label: 'Last Name', type: 'text', validation: '' }],
      ['gender', { label: 'Gender', type: 'select', source: [['Male', 'Male'], ['Female', 'Female'], ['Non-binary', 'Non-binary'], ['Other', 'Other'], ['Prefer not to say', 'Prefer not to say']] }],
      ['ssn_num', { label: 'SSN/NRIC', type: 'text', validation: 'none' }],
      ['nic_num', { label: 'NIC', type: 'text', validation: 'none' }],
      ['other_id', { label: 'Other ID', type: 'text', validation: 'none' }],
      ['driving_license', { label: 'Driving License No', type: 'text', validation: 'none' }],
      ['department', { label: 'Department', type: 'select2', 'remote-source': ['CompanyStructure', 'id', 'title'] }],
      ['supervisor', {
        label: 'Manager', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
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
        label: 'Manager', type: 'select2', 'allow-null': true, 'null-label': 'Anyone', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
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
    const compArray = [['Elementary Proficiency', this.gt('Elementary Proficiency')],
      ['Limited Working Proficiency', this.gt('Limited Working Proficiency')],
      ['Professional Working Proficiency', this.gt('Professional Working Proficiency')],
      ['Full Professional Proficiency', this.gt('Full Professional Proficiency')],
      ['Native or Bilingual Proficiency', this.gt('Native or Bilingual Proficiency')]];

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
      'gender',
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
      { sTitle: 'Gender' },
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
      ['relationship', { label: 'Relationship', type: 'select', source: [['Child', 'Child'], ['Spouse', 'Spouse'], ['Partner', 'Partner'], ['Parent', 'Parent'], ['Sibling', 'Sibling'], ['Other', 'Other']] }],
      ['gender', {
        label: 'Gender', type: 'select', 'allow-null': true, source: GENDERS,
      }],
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
      'is_primary',
      'mobile_phone',
      'email',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Name' },
      { sTitle: 'Relationship' },
      { sTitle: 'Primary' },
      { sTitle: 'Mobile Phone' },
      { sTitle: 'Email' },
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
        title: 'Primary',
        dataIndex: 'is_primary',
      },
      {
        title: 'Mobile Phone',
        dataIndex: 'mobile_phone',
      },
      {
        title: 'Email',
        dataIndex: 'email',
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
      ['is_primary', {
        label: 'Primary Contact', type: 'select', 'allow-null': true, source: YES_NO,
      }],
      ['mobile_phone', { label: 'Mobile Phone', type: 'text', validation: 'none' }],
      ['home_phone', { label: 'Home Phone', type: 'text', validation: 'none' }],
      ['work_phone', { label: 'Work Phone', type: 'text', validation: 'none' }],
      ['email', { label: 'Email', type: 'text', validation: 'emailOrEmpty' }],
      ['address', { label: 'Address', type: 'textarea', validation: 'none' }],
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
      'change_reason',
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
        title: 'Change Reason',
        dataIndex: 'change_reason',
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
        title: 'Manager',
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
        label: 'Manager', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['employment_status', { label: 'Employment Status', type: 'select2', 'remote-source': ['EmploymentStatus', 'id', 'name'] }],
      ['employment_type', {
        label: 'Employment Type', type: 'select', 'allow-null': true, source: EMPLOYMENT_TYPES,
      }],
      ['pay_grade', {
        label: 'Pay Grade', type: 'select2', 'allow-null': true, 'remote-source': ['PayGrade', 'id', 'name'],
      }],
      ['work_location', {
        label: 'Work Location', type: 'select2', 'allow-null': true, 'remote-source': ['WorkLocation', 'id', 'name'],
      }],
      ['change_reason', {
        label: 'Change Reason', type: 'select', 'allow-null': true, source: CAREER_CHANGE_REASONS,
      }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }

  getHelpTitle() {
    return this.gt('Work History');
  }

  getHelpDescription() {
    return this.gt('A row with no end date that starts today or earlier is the current job. Saving it updates the employee record, and editing the job details on the employee record adds a row here.');
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
      ['job_title', {
        label: 'Job Title', type: 'select2', 'allow-null': true, 'remote-source': ['JobTitle', 'id', 'name'],
      }],
      ['department', {
        label: 'Department', type: 'select2', 'allow-null': true, 'remote-source': ['CompanyStructure', 'id', 'title'],
      }],
      ['supervisor', {
        label: 'Manager', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['employment_status', {
        label: 'Employment Status', type: 'select2', 'allow-null': true, 'remote-source': ['EmploymentStatus', 'id', 'name'],
      }],

    ];
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}


/*
 * EmployeeBankAccountAdapter
 */

const BANK_SPLIT_TYPES = [['Percentage', 'Percentage'], ['Fixed amount', 'Fixed amount'], ['Remainder', 'Remainder']];

function bankAccountFormFields(withEmployee) {
  const fields = [['id', { label: 'ID', type: 'hidden' }]];
  if (withEmployee) {
    fields.push(['employee', {
      label: 'Employee',
      type: 'select2',
      sort: 'none',
      'allow-null': false,
      'remote-source': ['Employee', 'id', 'first_name+last_name'],
    }]);
  }
  return fields.concat([
    ['account_holder', { label: 'Account Holder', type: 'text', validation: '' }],
    ['bank_name', { label: 'Bank Name', type: 'text', validation: '' }],
    ['branch', { label: 'Branch', type: 'text', validation: 'none' }],
    ['account_number', { label: 'Account Number', type: 'text', validation: 'none' }],
    ['iban', { label: 'IBAN', type: 'text', validation: 'none' }],
    ['swift_bic', { label: 'SWIFT / BIC', type: 'text', validation: 'none' }],
    ['routing_code', {
      label: 'Routing / Sort Code', type: 'text', validation: 'none', help: 'ABA routing number, sort code, BSB, IFSC or similar',
    }],
    ['currency', {
      label: 'Currency', type: 'select2', 'allow-null': true, 'remote-source': ['CurrencyType', 'id', 'code+name'],
    }],
    ['is_primary', { label: 'Primary Account', type: 'select', source: YES_NO }],
    ['split_type', {
      label: 'Salary Split', type: 'select', 'allow-null': true, 'null-label': 'Whole salary', source: BANK_SPLIT_TYPES,
    }],
    ['split_value', { label: 'Split Value', type: 'text', validation: 'none' }],
    ['notes', { label: 'Notes', type: 'textarea', validation: 'none' }],
  ]);
}

class EmployeeBankAccountAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'account_holder',
      'bank_name',
      'account_number',
      'is_primary',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Account Holder' },
      { sTitle: 'Bank Name' },
      { sTitle: 'Account Number' },
      { sTitle: 'Primary' },
    ];
  }

  getTableColumns() {
    return [
      { title: 'Employee', dataIndex: 'employee', sorter: true },
      { title: 'Account Holder', dataIndex: 'account_holder', sorter: true },
      { title: 'Bank Name', dataIndex: 'bank_name', sorter: true },
      { title: 'Account Number', dataIndex: 'account_number' },
      { title: 'Primary', dataIndex: 'is_primary' },
    ];
  }

  getFormFields() {
    return bankAccountFormFields(true);
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
    ];
  }

  getHelpTitle() {
    return this.gt('Bank Accounts');
  }

  getHelpDescription() {
    return this.gt('Bank accounts employees are paid into. Account numbers and IBANs are stored encrypted; employees see only the last four characters of their own accounts.');
  }
}

module.exports = {
  EmployeeBankAccountAdapter,
  bankAccountFormFields,
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
