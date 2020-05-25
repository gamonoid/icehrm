/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/* eslint-disable prefer-destructuring,no-restricted-globals */

/* global Base64, modJs, nl2br, ga */
/**
 * Author: Thilina Hasantha
 */

import AdapterBase from '../../../api/AdapterBase';
import SubAdapterBase from '../../../api/SubAdapterBase';

/**
 * @class EmployeeSubSkillsAdapter
 * @param endPoint
 * @param tab
 * @param filter
 * @param orderBy
 * @returns
 */


class EmployeeSubSkillsAdapter extends SubAdapterBase {
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

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'hidden' }],
      ['skill_id', {
        label: 'Skill', type: 'select2', 'allow-null': true, 'remote-source': ['Skill', 'id', 'name'],
      }],
      ['details', { label: 'Details', type: 'textarea', validation: '' }],
    ];
  }


  forceInjectValuesBeforeSave(params) {
    params.employee = this.parent.currentId;
    return params;
  }

  getSubHeaderTitle() {
    const addBtn = `<button class="btn btn-small btn-success" onclick="modJs.subModJsList['tab${this.tab}'].renderForm();" style="margin-right:10px;"><i class="fa fa-plus"></i></button>`;
    return addBtn + this.gt('Skills');
  }

  getSubItemHtml(item, itemDelete, itemEdit) {
    const itemHtml = $(`<div class="list-group-item sub-tab-item"><h5 class="list-group-item-heading" style="font-weight:bold;">${item[2]}${itemDelete}${itemEdit}</h5><p class="list-group-item-text">${nl2br(item[3])}</p></div>`);
    return itemHtml;
  }
}


/**
 * @class EmployeeSubEducationAdapter
 * @param endPoint
 * @param tab
 * @param filter
 * @param orderBy
 * @returns
 */


class EmployeeSubEducationAdapter extends SubAdapterBase {
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
      ['employee', { label: 'Employee', type: 'hidden' }],
      ['education_id', {
        label: 'Qualification', type: 'select2', 'allow-null': false, 'remote-source': ['Education', 'id', 'name'],
      }],
      ['institute', { label: 'Institute', type: 'text', validation: '' }],
      ['date_start', { label: 'Start Date', type: 'date', validation: 'none' }],
      ['date_end', { label: 'Completed On', type: 'date', validation: 'none' }],
    ];
  }


  forceInjectValuesBeforeSave(params) {
    params.employee = this.parent.currentId;
    return params;
  }

  getSubHeaderTitle() {
    const addBtn = `<button class="btn btn-small btn-success" onclick="modJs.subModJsList['tab${this.tab}'].renderForm();" style="margin-right:10px;"><i class="fa fa-plus"></i></button>`;
    return addBtn + this.gt('Education');
  }

  getSubItemHtml(item, itemDelete, itemEdit) {
    let start = '';
    try {
      start = Date.parse(item[4]).toString('MMM d, yyyy');
    } catch (e) {
      console.log(`Error:${e.message}`);
    }

    let end = '';
    try {
      end = Date.parse(item[5]).toString('MMM d, yyyy');
    } catch (e) {
      console.log(`Error:${e.message}`);
    }
    // eslint-disable-next-line max-len
    return $(`<div class="list-group-item sub-tab-item"><h5 class="list-group-item-heading" style="font-weight:bold;">${item[2]}${itemDelete}${itemEdit}</h5><p class="list-group-item-text"><i class="fa fa-calendar"></i> Start: <b>${start}</b></p><p class="list-group-item-text"><i class="fa fa-calendar"></i> Completed: <b>${end}</b></p><p class="list-group-item-text">`
      + `<i class="fa fa-building-o"></i> Institute: <b>${item[3]}</b></p></div>`);
  }
}


/**
 * @class EmployeeSubCertificationAdapter
 * @param endPoint
 * @param tab
 * @param filter
 * @param orderBy
 * @returns
 */

class EmployeeSubCertificationAdapter extends SubAdapterBase {
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

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'hidden' }],
      ['certification_id', {
        label: 'Certification', type: 'select2', 'allow-null': false, 'remote-source': ['Certification', 'id', 'name'],
      }],
      ['institute', { label: 'Institute', type: 'text', validation: '' }],
      ['date_start', { label: 'Granted On', type: 'date', validation: 'none' }],
      ['date_end', { label: 'Valid Thru', type: 'date', validation: 'none' }],
    ];
  }


  forceInjectValuesBeforeSave(params) {
    params.employee = this.parent.currentId;
    return params;
  }

  getSubHeaderTitle() {
    const addBtn = `<button class="btn btn-small btn-success" onclick="modJs.subModJsList['tab${this.tab}'].renderForm();" style="margin-right:10px;"><i class="fa fa-plus"></i></button>`;
    return addBtn + this.gt('Certifications');
  }

  getSubItemHtml(item, itemDelete, itemEdit) {
    let start = '';
    try {
      start = Date.parse(item[4]).toString('MMM d, yyyy');
    } catch (e) {
      console.log(`Error:${e.message}`);
    }

    let end = '';
    try {
      end = Date.parse(item[5]).toString('MMM d, yyyy');
    } catch (e) {
      console.log(`Error:${e.message}`);
    }
    // eslint-disable-next-line max-len
    return $(`<div class="list-group-item sub-tab-item"><h5 class="list-group-item-heading" style="font-weight:bold;">${item[2]}${itemDelete}${itemEdit}</h5><p class="list-group-item-text"><i class="fa fa-calendar"></i> Granted On: <b>${start}</b></p><p class="list-group-item-text"><i class="fa fa-calendar"></i> Valid Thru: <b>${end}</b></p><p class="list-group-item-text"><i class="fa fa-building-o"></i> Institute: <b>${item[3]}</b></p></div>`);
  }
}

/**
 * @class EmployeeSubLanguageAdapter
 * @param endPoint
 * @param tab
 * @param filter
 * @param orderBy
 * @returns
 */

class EmployeeSubLanguageAdapter extends SubAdapterBase {
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
      { sTitle: 'Understanding' },
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
      ['employee', { label: 'Employee', type: 'hidden' }],
      ['language_id', {
        label: 'Language', type: 'select2', 'allow-null': false, 'remote-source': ['Language', 'id', 'name'],
      }],
      ['reading', { label: 'Reading', type: 'select', source: compArray }],
      ['speaking', { label: 'Speaking', type: 'select', source: compArray }],
      ['writing', { label: 'Writing', type: 'select', source: compArray }],
      ['understanding', { label: 'Understanding', type: 'select', source: compArray }],
    ];
  }


  forceInjectValuesBeforeSave(params) {
    params.employee = this.parent.currentId;
    return params;
  }

  getSubHeaderTitle() {
    const addBtn = `<button class="btn btn-small btn-success" onclick="modJs.subModJsList['tab${this.tab}'].renderForm();" style="margin-right:10px;"><i class="fa fa-plus"></i></button>`;
    return addBtn + this.gt('Languages');
  }

  getSubItemHtml(item, itemDelete, itemEdit) {
    // eslint-disable-next-line max-len
    return $(`<div class="list-group-item sub-tab-item"><h5 class="list-group-item-heading" style="font-weight:bold;">${item[2]}${itemDelete}${itemEdit}</h5><p class="list-group-item-text"><i class="fa fa-asterisk"></i> Reading: <b>${item[3]}</b></p><p class="list-group-item-text"><i class="fa fa-asterisk"></i> Speaking: <b>${item[4]}</b></p><p class="list-group-item-text"><i class="fa fa-asterisk"></i> Writing: <b>${item[5]}</b></p><p class="list-group-item-text"><i class="fa fa-asterisk"></i> Understanding: <b>${item[6]}</b></p></div>`);
  }

  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}


/**
 * @class EmployeeSubDependentAdapter
 * @param endPoint
 * @param tab
 * @param filter
 * @param orderBy
 * @returns
 */

class EmployeeSubDependentAdapter extends SubAdapterBase {
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

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['relationship', { label: 'Relationship', type: 'select', source: [['Child', 'Child'], ['Spouse', 'Spouse'], ['Parent', 'Parent'], ['Other', 'Other']] }],
      ['dob', { label: 'Date of Birth', type: 'date', validation: '' }],
      ['id_number', { label: 'Id Number', type: 'text', validation: 'none' }],
    ];
  }


  forceInjectValuesBeforeSave(params) {
    params.employee = this.parent.currentId;
    return params;
  }

  getSubHeaderTitle() {
    const addBtn = `<button class="btn btn-small btn-success" onclick="modJs.subModJsList['tab${this.tab}'].renderForm();" style="margin-right:10px;"><i class="fa fa-plus"></i></button>`;
    return addBtn + this.gt('Dependents');
  }

  getSubItemHtml(item, itemDelete, itemEdit) {
    // eslint-disable-next-line max-len
    const itemHtml = $(`<div class="list-group-item sub-tab-item"><h5 class="list-group-item-heading" style="font-weight:bold;">${item[2]}${itemDelete}${itemEdit}</h5><p class="list-group-item-text"><i class="fa fa-users"></i> Relationship: <b>${item[3]}</b></p><p class="list-group-item-text"><i class="fa fa-user"></i> Name: <b>${item[2]}</b></p></div>`);
    return itemHtml;
  }
}


/**
 * @class EmployeeSubEmergencyContactAdapter
 * @param endPoint
 * @param tab
 * @param filter
 * @param orderBy
 * @returns
 */

class EmployeeSubEmergencyContactAdapter extends SubAdapterBase {
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

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['relationship', { label: 'Relationship', type: 'text', validation: 'none' }],
      ['home_phone', { label: 'Home Phone', type: 'text', validation: 'none' }],
      ['work_phone', { label: 'Work Phone', type: 'text', validation: 'none' }],
      ['mobile_phone', { label: 'Mobile Phone', type: 'text', validation: 'none' }],
    ];
  }


  forceInjectValuesBeforeSave(params) {
    params.employee = this.parent.currentId;
    return params;
  }

  getSubHeaderTitle() {
    const addBtn = `<button class="btn btn-small btn-success" onclick="modJs.subModJsList['tab${this.tab}'].renderForm();" style="margin-right:10px;"><i class="fa fa-plus"></i></button>`;
    return addBtn + this.gt('Emergency Contacts');
  }

  getSubItemHtml(item, itemDelete, itemEdit) {
    // eslint-disable-next-line max-len
    const itemHtml = $(`<div class="list-group-item sub-tab-item"><h5 class="list-group-item-heading" style="font-weight:bold;">${item[2]}${itemDelete}${itemEdit}</h5><p class="list-group-item-text"><i class="fa fa-users"></i> Relationship: <b>${item[3]}</b></p><p class="list-group-item-text"><i class="fa fa-user"></i> Name: <b>${item[2]}</b></p><p class="list-group-item-text"><i class="fa fa-phone"></i> Home Phone: <b>${item[4]}</b></p><p class="list-group-item-text"><i class="fa fa-phone"></i> Mobile Phone: <b>${item[6]}</b></p></div>`);
    return itemHtml;
  }
}

/**
 * @class EmployeeSubDocumentAdapter
 * @param endPoint
 * @param tab
 * @param filter
 * @param orderBy
 * @returns
 */

class EmployeeSubDocumentAdapter extends SubAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'document',
      'details',
      'date_added',
      'valid_until',
      'status',
      'attachment',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Document' },
      { sTitle: 'Details' },
      { sTitle: 'Date Added' },
      { sTitle: 'Status' },
      { sTitle: 'Attachment', bVisible: false },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'hidden' }],
      ['document', { label: 'Document', type: 'select2', 'remote-source': ['Document', 'id', 'name'] }],
      ['date_added', { label: 'Date Added', type: 'date', validation: '' }],
      ['valid_until', { label: 'Valid Until', type: 'date', validation: 'none' }],
      ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['Inactive', 'Inactive'], ['Draft', 'Draft']] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
      ['attachment', { label: 'Attachment', type: 'fileupload', validation: 'none' }],
    ];
  }


  forceInjectValuesBeforeSave(params) {
    params.employee = this.parent.currentId;
    return params;
  }

  getSubHeaderTitle() {
    const addBtn = `<button class="btn btn-small btn-success" onclick="modJs.subModJsList['tab${this.tab}'].renderForm();" style="margin-right:10px;"><i class="fa fa-plus"></i></button>`;
    return addBtn + this.gt('Documents');
  }

  getSubItemHtml(item, itemDelete, itemEdit) {
    let expire = '';
    try {
      expire = Date.parse(item[5]).toString('MMM d, yyyy');
    } catch (e) {
      console.log(e.message);
    }

    const downloadButton = `<button id="#_id_#_download" onclick="download('${item[7]}');return false;" type="button" style="position: absolute;bottom: 5px;right: 70px;font-size: 13px;" tooltip="Download"><li class="fa fa-cloud-download"></li></button>`;

    const itemHtml = $(`<div class="list-group-item sub-tab-item"><h5 class="list-group-item-heading" style="font-weight:bold;">${item[2]}${downloadButton}${itemDelete}${itemEdit}</h5><p class="list-group-item-text">${nl2br(item[3])}</p><p class="list-group-item-text"><i class="fa fa-calendar"></i> Expire On: <b>${expire}</b></p></div>`);
    return itemHtml;
  }


  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin';
  }
}

class SubProfileEnabledAdapterBase extends AdapterBase {
  isSubProfileTable() {
    return this.user.user_level !== 'Admin' && this.user.user_level !== 'Restricted Admin'
  }
}


class EmployeeAdapter extends SubProfileEnabledAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.fieldNameMap = {};
    this.hiddenFields = {};
    this.tableFields = {};
    this.formOnlyFields = {};
  }

  setFieldNameMap(fields) {
    let field;
    for (let i = 0; i < fields.length; i++) {
      field = fields[i];
      this.fieldNameMap[field.name] = field;
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
      'mobile_phone',
      'department',
      'gender',
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
      ['gender', { label: 'Gender', type: 'select', source: [['Male', 'Male'], ['Female', 'Female'], ['Divers', 'Divers']] }],
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
      ['job_title', { label: 'Job Title', type: 'select2', 'remote-source': ['JobTitle', 'id', 'name'] }],
      ['pay_grade', {
        label: 'Pay Grade', type: 'select2', 'allow-null': true, 'remote-source': ['PayGrade', 'id', 'name'],
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
      ['joined_date', { label: 'Joined Date', type: 'date', validation: '' }],
      ['confirmation_date', { label: 'Confirmation Date', type: 'date', validation: 'none' }],
      ['termination_date', { label: 'Termination Date', type: 'date', validation: 'none' }],
      ['department', { label: 'Department', type: 'select2', 'remote-source': ['CompanyStructure', 'id', 'title'] }],
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
    let deleteBtn = '<img class="tableActionButton" src="_BASE_images/connect-no.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Terminate Employee" onclick="modJs.terminateEmployee(_id_);return false;"></img>';
    if (this.showDelete === false) {
      deleteBtn = '';
    }
    // eslint-disable-next-line max-len
    let html = `<div style="width:110px;"><img class="tableActionButton" src="_BASE_images/user.png" style="cursor:pointer;" rel="tooltip" title="Login as this Employee" onclick="modJs.setAdminProfile(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="View" onclick="modJs.view(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;margin-left:15px;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>${deleteBtn}</div>`;
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
    this.showMessage('Error', 'Error Occured while retriving candidate');
  }

  renderEmployee(data) {
    let title;
    const fields = this.getFormFields();
    const currentEmpId = data[1];
    const currentId = data[1];
    const userEmpId = data[2];
    data = data[0];
    this.currentEmployee = data;
    let html = this.getCustomTemplate('myDetails.html');


    for (let i = 0; i < fields.length; i++) {
      if (this.fieldNameMap[fields[i][0]] !== undefined && this.fieldNameMap[fields[i][0]] !== null) {
        title = this.gt(this.fieldNameMap[fields[i][0]].textMapped);
        html = html.replace(`#_label_${fields[i][0]}_#`, title);
      }
    }

    html = html.replace(/#_.+_#/gi, '');
    html = html.replace(/_id_/g, data.id);

    $(`#${this.getTableName()}`).html(html);

    for (let i = 0; i < fields.length; i++) {
      $(`#${this.getTableName()} #${fields[i][0]}`).html(data[fields[i][0]]);
      $(`#${this.getTableName()} #${fields[i][0]}_Name`).html(data[`${fields[i][0]}_Name`]);
    }

    let subordinates = '';
    for (let i = 0; i < data.subordinates.length; i++) {
      if (data.subordinates[i].first_name !== undefined && data.subordinates[i].first_name !== null) {
        subordinates += `${data.subordinates[i].first_name} `;
      }

      if (data.subordinates[i].middle_name !== undefined && data.subordinates[i].middle_name !== null && data.subordinates[i].middle_name !== '') {
        subordinates += `${data.subordinates[i].middle_name} `;
      }

      if (data.subordinates[i].last_name !== undefined && data.subordinates[i].last_name !== null && data.subordinates[i].last_name !== '') {
        subordinates += data.subordinates[i].last_name;
      }
      subordinates += '<br/>';
    }

    $(`#${this.getTableName()} #subordinates`).html(subordinates);


    $(`#${this.getTableName()} #name`).html(`${data.first_name} ${data.last_name}`);
    this.currentUserId = data.id;

    $(`#${this.getTableName()} #profile_image_${data.id}`).attr('src', data.image);


    // Add custom fields
    if (data.customFields !== undefined && data.customFields !== null && Object.keys(data.customFields).length > 0) {
      const ct = '<div class="col-xs-6 col-md-3" style="font-size:16px;"><label class="control-label col-xs-12" style="font-size:13px;">#_label_#</label><label class="control-label col-xs-12 iceLabel" style="font-size:13px;font-weight: bold;">#_value_#</label></div>';

      const sectionTemplate = '<div class="panel panel-default" style="width:97.5%;"><div class="panel-heading"><h4>#_section.name_#</h4></div> <div class="panel-body"  id="cont_#_section_#"> </div></div>';
      let customFieldHtml;
      for (const index in data.customFields) {
        if (!data.customFields[index][1]) {
          data.customFields[index][1] = this.gt('Other Details');
        }

        let sectionId = data.customFields[index][1].toLocaleLowerCase();
        sectionId = sectionId.replace(' ', '_');

        if ($(`#cont_${sectionId}`).length <= 0) {
          // Add section
          let sectionHtml = sectionTemplate;
          sectionHtml = sectionHtml.replace('#_section_#', sectionId);
          sectionHtml = sectionHtml.replace('#_section.name_#', data.customFields[index][1]);
          $('#customFieldsCont').append($(sectionHtml));
        }

        customFieldHtml = ct;
        customFieldHtml = customFieldHtml.replace('#_label_#', index);
        if (data.customFields[index][2] === 'fileupload') {
          customFieldHtml = customFieldHtml.replace(
            '#_value_#',
            `<button onclick="download('${data.customFields[index][0]}');return false;" class="btn btn-mini btn-inverse" type="button">View: ${index}</button>`,
          );
        } else {
          customFieldHtml = customFieldHtml.replace('#_value_#', data.customFields[index][0]);
        }
        $(`#cont_${sectionId}`).append($(customFieldHtml));
      }
    } else {
      $('#customFieldsCont').remove();
    }


    this.cancel();

    if (!this.isModuleInstalled('admin', 'documents')) {
      $('#tabDocuments').remove();
    }


    window.modJs = this;
    modJs.subModJsList = [];

    modJs.subModJsList.tabEmployeeSkillSubTab = new EmployeeSubSkillsAdapter('EmployeeSkill', 'EmployeeSkillSubTab', { employee: data.id });
    modJs.subModJsList.tabEmployeeSkillSubTab.parent = this;

    modJs.subModJsList.tabEmployeeEducationSubTab = new EmployeeSubEducationAdapter('EmployeeEducation', 'EmployeeEducationSubTab', { employee: data.id });
    modJs.subModJsList.tabEmployeeEducationSubTab.parent = this;

    modJs.subModJsList.tabEmployeeCertificationSubTab = new EmployeeSubCertificationAdapter('EmployeeCertification', 'EmployeeCertificationSubTab', { employee: data.id });
    modJs.subModJsList.tabEmployeeCertificationSubTab.parent = this;

    modJs.subModJsList.tabEmployeeLanguageSubTab = new EmployeeSubLanguageAdapter('EmployeeLanguage', 'EmployeeLanguageSubTab', { employee: data.id });
    modJs.subModJsList.tabEmployeeLanguageSubTab.parent = this;

    modJs.subModJsList.tabEmployeeDependentSubTab = new EmployeeSubDependentAdapter('EmployeeDependent', 'EmployeeDependentSubTab', { employee: data.id });
    modJs.subModJsList.tabEmployeeDependentSubTab.parent = this;

    modJs.subModJsList.tabEmployeeEmergencyContactSubTab = new EmployeeSubEmergencyContactAdapter('EmergencyContact', 'EmployeeEmergencyContactSubTab', { employee: data.id });
    modJs.subModJsList.tabEmployeeEmergencyContactSubTab.parent = this;

    if (this.isModuleInstalled('admin', 'documents')) {
      modJs.subModJsList.tabEmployeeDocumentSubTab = new EmployeeSubDocumentAdapter('EmployeeDocument', 'EmployeeDocumentSubTab', { employee: data.id });
      modJs.subModJsList.tabEmployeeDocumentSubTab.parent = this;
    }
    for (const prop in modJs.subModJsList) {
      if (modJs.subModJsList.hasOwnProperty(prop)) {
        modJs.subModJsList[prop].setTranslationsSubModules(this.translations);
        modJs.subModJsList[prop].setPermissions(this.permissions);
        modJs.subModJsList[prop].setFieldTemplates(this.fieldTemplates);
        modJs.subModJsList[prop].setTemplates(this.templates);
        modJs.subModJsList[prop].setCustomTemplates(this.customTemplates);
        modJs.subModJsList[prop].setEmailTemplates(this.emailTemplates);
        modJs.subModJsList[prop].setUser(this.user);
        modJs.subModJsList[prop].initFieldMasterData();
        modJs.subModJsList[prop].setBaseUrl(this.baseUrl);
        modJs.subModJsList[prop].setCurrentProfile(this.currentProfile);
        modJs.subModJsList[prop].setInstanceId(this.instanceId);
        modJs.subModJsList[prop].setGoogleAnalytics(ga);
        modJs.subModJsList[prop].setNoJSONRequests(this.noJSONRequests);
      }
    }

    modJs.subModJsList.tabEmployeeSkillSubTab.setShowFormOnPopup(true);
    modJs.subModJsList.tabEmployeeSkillSubTab.setShowAddNew(false);
    modJs.subModJsList.tabEmployeeSkillSubTab.setShowCancel(false);
    modJs.subModJsList.tabEmployeeSkillSubTab.get([]);

    modJs.subModJsList.tabEmployeeEducationSubTab.setShowFormOnPopup(true);
    modJs.subModJsList.tabEmployeeEducationSubTab.setShowAddNew(false);
    modJs.subModJsList.tabEmployeeEducationSubTab.setShowCancel(false);
    modJs.subModJsList.tabEmployeeEducationSubTab.get([]);

    modJs.subModJsList.tabEmployeeCertificationSubTab.setShowFormOnPopup(true);
    modJs.subModJsList.tabEmployeeCertificationSubTab.setShowAddNew(false);
    modJs.subModJsList.tabEmployeeCertificationSubTab.setShowCancel(false);
    modJs.subModJsList.tabEmployeeCertificationSubTab.get([]);

    modJs.subModJsList.tabEmployeeLanguageSubTab.setShowFormOnPopup(true);
    modJs.subModJsList.tabEmployeeLanguageSubTab.setShowAddNew(false);
    modJs.subModJsList.tabEmployeeLanguageSubTab.setShowCancel(false);
    modJs.subModJsList.tabEmployeeLanguageSubTab.get([]);

    modJs.subModJsList.tabEmployeeDependentSubTab.setShowFormOnPopup(true);
    modJs.subModJsList.tabEmployeeDependentSubTab.setShowAddNew(false);
    modJs.subModJsList.tabEmployeeDependentSubTab.setShowCancel(false);
    modJs.subModJsList.tabEmployeeDependentSubTab.get([]);

    modJs.subModJsList.tabEmployeeEmergencyContactSubTab.setShowFormOnPopup(true);
    modJs.subModJsList.tabEmployeeEmergencyContactSubTab.setShowAddNew(false);
    modJs.subModJsList.tabEmployeeEmergencyContactSubTab.setShowCancel(false);
    modJs.subModJsList.tabEmployeeEmergencyContactSubTab.get([]);

    if (this.isModuleInstalled('admin', 'documents')) {
      modJs.subModJsList.tabEmployeeDocumentSubTab.setShowFormOnPopup(true);
      modJs.subModJsList.tabEmployeeDocumentSubTab.setShowAddNew(false);
      modJs.subModJsList.tabEmployeeDocumentSubTab.setShowCancel(false);
      modJs.subModJsList.tabEmployeeDocumentSubTab.get([]);
    }

    $('#subModTab a').off().on('click', function (e) {
      e.preventDefault();
      $(this).tab('show');
    });
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
      'image',
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

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['employee_id', { label: 'Employee Number', type: 'text', validation: '' }],
      ['first_name', { label: 'First Name', type: 'text', validation: '' }],
      ['middle_name', { label: 'Middle Name', type: 'text', validation: 'none' }],
      ['last_name', { label: 'Last Name', type: 'text', validation: '' }],
      ['nationality', { label: 'Nationality', type: 'select2', 'remote-source': ['Nationality', 'id', 'name'] }],
      ['birthday', { label: 'Date of Birth', type: 'date', validation: '' }],
      ['gender', { label: 'Gender', type: 'select', source: [['Male', 'Male'], ['Female', 'Female'], ['Divers', 'Divers']] }],
      ['marital_status', { label: 'Marital Status', type: 'select', source: [['Married', 'Married'], ['Single', 'Single'], ['Divorced', 'Divorced'], ['Widowed', 'Widowed'], ['Other', 'Other']] }],
      ['ssn_num', { label: 'SSN/NRIC', type: 'text', validation: 'none' }],
      ['nic_num', { label: 'NIC', type: 'text', validation: 'none' }],
      ['other_id', { label: 'Other ID', type: 'text', validation: 'none' }],
      ['driving_license', { label: 'Driving License No', type: 'text', validation: 'none' }],
      /* [ "driving_license_exp_date", {"label":"License Exp Date","type":"date","validation":"none"}], */
      ['employment_status', { label: 'Employment Status', type: 'select2', 'remote-source': ['EmploymentStatus', 'id', 'name'] }],
      ['job_title', { label: 'Job Title', type: 'select2', 'remote-source': ['JobTitle', 'id', 'name'] }],
      ['pay_grade', {
        label: 'Pay Grade', type: 'select2', 'allow-null': true, 'remote-source': ['PayGrade', 'id', 'name'],
      }],
      ['work_station_id', { label: 'Work Station Id', type: 'text', validation: 'none' }],
      ['address1', { label: 'Address Line 1', type: 'text', validation: 'none' }],
      ['address2', { label: 'Address Line 2', type: 'text', validation: 'none' }],
      ['city', { label: 'City', type: 'text', validation: 'none' }],
      ['country', { label: 'Country', type: 'select2', 'remote-source': ['Country', 'code', 'name'] }],
      ['province', {
        label: 'Province', type: 'select2', 'allow-null': true, 'remote-source': ['Province', 'id', 'name'],
      }],
      ['postal_code', { label: 'Postal/Zip Code', type: 'text', validation: 'none' }],
      ['home_phone', { label: 'Home Phone', type: 'text', validation: 'none' }],
      ['mobile_phone', { label: 'Mobile Phone', type: 'text', validation: 'none' }],
      ['work_phone', { label: 'Work Phone', type: 'text', validation: 'none' }],
      ['work_email', { label: 'Work Email', type: 'text', validation: 'emailOrEmpty' }],
      ['private_email', { label: 'Private Email', type: 'text', validation: 'emailOrEmpty' }],
      ['joined_date', { label: 'Joined Date', type: 'date', validation: '' }],
      ['confirmation_date', { label: 'Confirmation Date', type: 'date', validation: 'none' }],
      ['termination_date', { label: 'Termination Date', type: 'date', validation: 'none' }],
      ['department', { label: 'Department', type: 'select2', 'remote-source': ['CompanyStructure', 'id', 'title'] }],
      ['supervisor', {
        label: 'Supervisor', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['notes', {
        label: 'Notes',
        type: 'datagroup',
        form: [
          ['note', { label: 'Note', type: 'textarea', validation: '' }],
        ],
        html: '<div id="#_id_#" class="panel panel-default"><div class="panel-body">#_delete_##_edit_#<span style="color:#999;font-size:13px;font-weight:bold">Date: #_date_#</span><hr/>#_note_#</div></div>',
        validation: 'none',
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
    let html = `<div style="width:110px;">
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

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['employee_id', { label: 'Employee Number', type: 'text', validation: '' }],
      ['first_name', { label: 'First Name', type: 'text', validation: '' }],
      ['middle_name', { label: 'Middle Name', type: 'text', validation: 'none' }],
      ['last_name', { label: 'Last Name', type: 'text', validation: '' }],
      ['gender', { label: 'Gender', type: 'select', source: [['Male', 'Male'], ['Female', 'Female'], ['Divers', 'Divers']] }],
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
    let html = '<div style="width:110px;"><img class="tableActionButton" src="_BASE_images/download.png" style="cursor:pointer;" rel="tooltip" title="Download Archived Data" onclick="modJs.download(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Remove Archived Data" onclick="modJs.deleteRow(_id_);return false;"></img></div>';
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
}


/*
 * ==========================================================
 */


class EmployeeSkillAdapter extends SubProfileEnabledAdapterBase {
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
        'allow-null': false,
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
        'allow-null': false,
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
        'allow-null': false,
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
      { sTitle: 'Understanding' },
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
      ['understanding', { label: 'Understanding', type: 'select', source: compArray }],
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

/**
 * EmployeeDocumentAdapter
 */

class EmployeeDocumentAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'document',
      'details',
      'date_added',
      'status',
      'attachment',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Document' },
      { sTitle: 'Details' },
      { sTitle: 'Date Added' },
      { sTitle: 'Status' },
      { sTitle: 'Attachment', bVisible: false },
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
      ['document', { label: 'Document', type: 'select2', 'remote-source': ['Document', 'id', 'name'] }],
      ['date_added', { label: 'Date Added', type: 'date', validation: '' }],
      ['valid_until', { label: 'Valid Until', type: 'date', validation: 'none' }],
      ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['Inactive', 'Inactive'], ['Draft', 'Draft']] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
      ['attachment', { label: 'Attachment', type: 'fileupload', validation: 'none' }],
    ];
  }


  getFilters() {
    return [
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],

    ];
  }


  getActionButtonsHtml(id, data) {
    let html = '<div style="width:80px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img><img class="tableActionButton" src="_BASE_images/download.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Download Document" onclick="download(\'_attachment_\');return false;"></img><img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img></div>';
    html = html.replace(/_id_/g, id);
    html = html.replace(/_attachment_/g, data[6]);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
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
  EmployeeSubSkillsAdapter,
  EmployeeSubEducationAdapter,
  EmployeeSubCertificationAdapter,
  EmployeeSubLanguageAdapter,
  EmployeeSubDependentAdapter,
  EmployeeSubEmergencyContactAdapter,
  EmployeeSubDocumentAdapter,
  EmployeeDocumentAdapter,
};
