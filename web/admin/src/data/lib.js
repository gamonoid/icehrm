/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
/* global dependOnField, window, modJs */
import AdapterBase from '../../../api/AdapterBase';

/**
 * DataImportAdapter
 */

class DataImportAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'dataType',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Data Type' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['dataType', {
        label: 'Data Type',
        type: 'select',
        sort: 'none',
        source: [
          ['EmployeeDataImporter', 'Employee Data'],
          ['AttendanceDataImporter', 'Attendance Data'],
          ['PayrollDataImporter', 'Payroll Data'],
          ['UserDataImporter', 'User Data'],
          ['CommonDataImporter', 'Common Data Importer'],
        ],
      }],
      ['objectType', {
        label: 'Object Type',
        type: 'select',
        sort: 'none',
        'allow-null': true,
        'null-label': 'None',
        source: [
          ['LeaveStartingBalance', 'Leave Starting Balance'],
          ['HoliDay', 'Holidays'],
          ['EmployeeExpense', 'Employee Expenses'],
          ['Project', 'Projects'],
          ['EmployeeProject', 'Employee Projects'],
          ['EmployeeSalary', 'Employee Salary'],
          ['PayrollEmployee', 'Company Payroll'],
          ['Client', 'Clients'],
          ['Province', 'Provinces'],
          ['Industry', 'Industry'],
          ['Industry', 'Industry'],
          ['EmergencyContact', 'Emergency Contacts'],
          ['Ethnicity', 'Ethnicity'],
          ['Nationality', 'Nationality'],
          ['JobTitle', 'Job Titles'],
          ['PayFrequency', 'Pay Frequency'],
          ['PayrollEmployee', 'Payroll Employees'],
          ['SalaryComponent', 'Salary Components'],
          ['EmployeeSalary', 'Employee Salary'],
          ['CompanyStructure', 'Company Structure'],
        ],
      }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
      ['columns', {
        label: 'Columns',
        type: 'datagroup',
        form: [
          ['name', { label: 'CSV Field Name', type: 'text', validation: '' }],
          ['title', { label: 'Field Title', type: 'text', validation: 'none' }],
          ['type', {
            label: 'Type', type: 'select', sort: 'none', source: [['Normal', 'Normal'], ['Reference', 'Reference']],
          }],
          ['dependOn', {
            label: 'Depends On',
            type: 'select',
            'allow-null': true,
            'null-label': 'N/A',
            source: [
              ['EmergencyContact', 'Emergency Contacts'],
              ['Ethnicity', 'Ethnicity'],
              ['Nationality', 'Nationality'],
              ['JobTitle', 'JobTitle'],
              ['PayFrequency', 'Pay Frequency'],
              ['PayGrade', 'Pay Grade'],
              ['EmploymentStatus', 'Employment Status'],
              ['CompanyStructure', 'Company Structure'],
              ['Employee', 'Employee'],
              ['ImmigrationStatus', 'Immigration Status'],
              ['Industry', 'Industry'],
              ['CurrencyType', 'CurrencyType'],
              ['Document', 'Document'],
              ['Education', 'Education'],
              ['ExpensesCategory', 'Expenses Category'],
              ['ExpensesPaymentMethod', 'Expenses Payment Method'],
              ['ExperienceLevel', 'Experience Level'],
              ['Form', 'Form'],
              ['HiringPipeline', 'Hiring Pipeline'],
              ['HoliDay', 'HoliDay'],
              ['Language', 'Language'],
              ['LeaveGroup', 'Leave Group'],
              ['LeavePeriod', 'Leave Period'],
              ['LeaveRule', 'Leave Rule'],
              ['LeaveType', 'Leave Type'],
              ['OvertimeCategory', 'Overtime Category'],
              ['Project', 'Project'],
              ['Client', 'Client'],
              ['ReviewTemplate', 'Review Template'],
              ['SalaryComponent', 'Salary Component'],
              ['SalaryComponentType', 'Salary Component Type'],
              ['Skill', 'Skill'],
              ['Timezone', 'Timezone'],
              ['AssetType', 'Asset Type'],
              ['Benifit', 'Benifit'],
              ['Certification', 'Certification'],
              ['Country', 'Country'],
              ['Province', 'Province'],
              ['TrainingSession', 'TrainingSession'],
              ['User', 'User'],
            ],
          }],
          ['dependOnField', {
            label: 'Depends On Field',
            type: 'select',
            'allow-null': true,
            'null-label': 'N/A',
            source: [
              ['id', 'id'],
              ['employee_id', 'employee_id'],
              ['name', 'name'],
              ['code', 'code'],
              ['title', 'title'],
              ['employee', 'employee'],
            ],
          }],
          ['idField', {
            label: 'Is ID Field', type: 'select', validation: '', source: [['No', 'No'], ['Yes', 'Yes']],
          }],
          ['sampleValue', { label: 'Sample Value', type: 'text' }],
          ['help', { label: 'Help Text', type: 'text' }],
        ],
        html: '<div id="#_id_#" class="panel panel-default"><div class="panel-heading"><b>#_name_#</b> #_delete_##_edit_#</div><div class="panel-body"><b>Title: </b>#_title_#<br/><span style="color:#999;font-size:11px;font-weight:bold">Type: #_type_# </span><br/><b>Sample: </b>#_sampleValue_#<br/><i class="fa fa-info-circle help-info" style="font-size: 11px;"/><span style="color:#999;font-size:11px;">&nbsp;#_help_#</span><br/></div></div>',
        validation: 'none',
        'custom-validate-function': function (data) {
          const res = {};
          res.params = data;
          res.valid = true;
          if (data.type === 'Reference') {
            if (data.dependOn === 'NULL') {
              res.message = 'If the type is Reference this field should referring another object';
              res.valid = false;
            } else if (dependOnField == null || dependOnField === 'NULL') {
              res.message = "If the type is Reference then 'Depends On Field' can not be empty";
              res.valid = false;
            }
          } else if (data.type === 'Normal') {
            if (data.dependOn !== 'NULL') {
              res.message = 'If the type is Reference this field should not refer another object';
              res.valid = false;
            } else if (dependOnField == null || dependOnField === 'NULL') {
              res.message = "If the type is Reference then 'Depends On Field' should be empty";
              res.valid = false;
            }
          }

          return res;
        },

      }],
    ];
  }

  getActionButtonsHtml(id, data) {
    const editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
    const download = '<img class="tableActionButton" src="_BASE_images/download.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Download CSV Template" onclick="modJs.downloadTemplate(_id_);return false;"></img>';
    const deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
    const cloneButton = '<img class="tableActionButton" src="_BASE_images/clone.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Copy" onclick="modJs.copyRow(_id_);return false;"></img>';

    let html = '<div style="width:132px;">_edit__download__clone__delete_</div>';


    if (this.showAddNew) {
      html = html.replace('_clone_', cloneButton);
    } else {
      html = html.replace('_clone_', '');
    }

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

    html = html.replace('_download_', download);

    html = html.replace(/_id_/g, id);
    html = html.replace(/_status_/g, data[6]);
    html = html.replace(/_BASE_/g, this.baseUrl);

    return html;
  }

  downloadTemplate(id) {
    const params = { t: this.table, sa: 'downloadTemplate', mod: 'admin=data' };
    params.req = JSON.stringify({ id });
    const downloadUrl = modJs.getCustomActionUrl('ca', params);
    window.open(downloadUrl, '_blank');
  }
}


/**
 * DataImportFileAdapter
 */

class DataImportFileAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'data_import_definition',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Data Import Definition' },
      { sTitle: 'Status' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['data_import_definition', { label: 'Data Import Definitions', type: 'select', 'remote-source': ['DataImport', 'id', 'name'] }],
      ['file', {
        label: 'File to Import', type: 'fileupload', validation: '', filetypes: 'csv,txt',
      }],
      ['details', { label: 'Last Export Result', type: 'textarea', validation: 'none' }],
    ];
  }

  getActionButtonsHtml(id, data) {
    const editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>';
    const processButton = '<img class="tableActionButton" src="_BASE_images/run.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Process" onclick="modJs.process(_id_,\'_status_\');return false;"></img>';
    const deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
    const cloneButton = '<img class="tableActionButton" src="_BASE_images/clone.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Copy" onclick="modJs.copyRow(_id_);return false;"></img>';

    let html = '<div style="width:132px;">_edit__process__clone__delete_</div>';


    if (this.showAddNew) {
      html = html.replace('_clone_', cloneButton);
    } else {
      html = html.replace('_clone_', '');
    }

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

    if (data[3] === 'Not Processed') {
      html = html.replace('_process_', processButton);
    } else {
      html = html.replace('_process_', '');
    }


    html = html.replace(/_id_/g, id);
    html = html.replace(/_status_/g, data[6]);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }


  process(id) {
    const object = { id };
    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'processSuccessCallBack';
    callBackData.callBackFail = 'processFailCallBack';

    this.customAction('processDataFile', 'admin=data', reqJson, callBackData);
  }

  processSuccessCallBack(callBackData) {
    this.showMessage('Success', 'File imported successfully.');
    this.get([]);
  }


  processFailCallBack(callBackData) {
    this.showMessage('Error', `File import unsuccessful. Result:${callBackData}`);
  }
}

module.exports = { DataImportAdapter, DataImportFileAdapter };
