/*
Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
/* global dependOnField */
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
      ['dataType', { label: 'Data Type', type: 'text', validation: '' }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
      ['columns', {
        label: 'Columns',
        type: 'datagroup',
        form: [
          ['name', { label: 'Name', type: 'text', validation: '' }],
          ['title', { label: 'Filed Title', type: 'text', validation: 'none' }],
          ['type', {
            label: 'Type', type: 'select', sort: 'none', source: [['Normal', 'Normal'], ['Reference', 'Reference'], ['Attached', 'Attached']],
          }],
          ['dependOn', {
            label: 'Depends On',
            type: 'select',
            'allow-null': true,
            'null-label': 'N/A',
            source: [['EmergencyContact', 'Emergency Contacts'], ['Ethnicity', 'Ethnicity'], ['Nationality', 'Nationality'], ['JobTitle', 'JobTitle'], ['PayFrequency', 'PayFrequency'], ['PayGrade', 'PayGrade'], ['EmploymentStatus', 'EmploymentStatus'], ['CompanyStructure', 'CompanyStructure'], ['Employee', 'Employee']],
          }],
          ['dependOnField', { label: 'Depends On Field', type: 'text', validation: 'none' }],
          ['isKeyField', {
            label: 'Is Key Field', type: 'select', validation: '', source: [['No', 'No'], ['Yes', 'Yes']],
          }],
          ['idField', {
            label: 'Is ID Field', type: 'select', validation: '', source: [['No', 'No'], ['Yes', 'Yes']],
          }],
        ],
        html: '<div id="#_id_#" class="panel panel-default"><div class="panel-heading"><b>#_name_#</b> #_delete_##_edit_#</div><div class="panel-body"><b>Header Title: </b>#_title_#<br/><span style="color:#999;font-size:11px;font-weight:bold">Type: #_type_# </span><br/></div></div>',
        validation: 'none',
        'custom-validate-function': function (data) {
          const res = {};
          res.params = data;
          res.valid = true;
          if (data.type === 'Reference') {
            if (data.dependOn === 'NULL') {
              res.message = 'If the type is Reference this field should referring another table';
              res.valid = false;
            } else if (dependOnField === null || dependOnField === undefined) {
              res.message = "If the type is Reference then 'Depends On Field' can not be empty";
              res.valid = false;
            }
          }

          return res;
        },

      }],
    ];
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

    let html = '<div style="width:120px;">_edit__process__clone__delete_</div>';


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
    const that = this;
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
  }


  processFailCallBack(callBackData) {
    this.showMessage('Error', `File import unsuccessful. Result:${callBackData}`);
  }
}

module.exports = { DataImportAdapter, DataImportFileAdapter };
