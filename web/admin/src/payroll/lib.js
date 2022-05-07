/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
/* global modJs, modJsList */
import AdapterBase from '../../../api/AdapterBase';
import TableEditAdapter from '../../../api/TableEditAdapter';

require('codemirror/mode/javascript/javascript');
require('codemirror/addon/edit/closebrackets');
require('codemirror/addon/display/autorefresh');
const CodeMirror = require('codemirror');


/**
 * PaydayAdapter
 */

class PaydayAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Select Pay Frequency' },
    ];
  }

  getFormFields() {
    return [
      ['name', { label: 'Name', type: 'text', validation: '' }],
    ];
  }

  getAddNewLabel() {
    return 'Run Payroll';
  }

  createTable(elementId) {
    $('#payday_all').off();
    super.createTable(elementId);
    $('#payday_all').off().on('click', function () {
      if ($(this).is(':checked')) {
        $('.paydayCheck').prop('checked', true);
      } else {
        $('.paydayCheck').prop('checked', false);
      }
    });
  }

  getActionButtonsHtml(id, data) {
    const editButton = '<input type="checkbox" class="paydayCheck" id="payday__id_" name="payday__id_" value="checkbox_payday__id_"/>';

    let html = '<div style="width:132px;">_edit_</div>';
    html = html.replace('_edit_', editButton);

    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }

  getActionButtonHeader() {
    return { sTitle: '<input type="checkbox" id="payday_all" name="payday_all" value="checkbox_payday_all"/>', sClass: 'center' };
  }
}


/**
 * PayrollAdapter
 */

class PayrollAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'pay_period',
      'department',
      'date_start',
      'date_end',
      'status',

    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Pay Frequency' },
      { sTitle: 'Department' },
      { sTitle: 'Date Start' },
      { sTitle: 'Date End' },
      { sTitle: 'Status' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text' }],
      ['pay_period', {
        label: 'Pay Frequency', type: 'select', 'remote-source': ['PayFrequency', 'id', 'name'], sort: 'none',
      }],
      ['deduction_group', {
        label: 'Payroll Group', type: 'select', 'remote-source': ['DeductionGroup', 'id', 'name'], sort: 'none',
      }],
      ['payslipTemplate', { label: 'Payslip Template', type: 'select', 'remote-source': ['PayslipTemplate', 'id', 'name'] }],
      ['department', {
        label: 'Department', type: 'select2', 'remote-source': ['CompanyStructure', 'id', 'title'], sort: 'none',
      }],
      ['date_start', { label: 'Start Date', type: 'date', validation: '' }],
      ['date_end', { label: 'End Date', type: 'date', validation: '' }],
      // [ "column_template", {"label":"Report Column Template","type":"select","remote-source":["PayrollColumnTemplate","id","name"]}],
      ['columns', { label: 'Payroll Columns', type: 'select2multi', 'remote-source': ['PayrollColumn', 'id', 'name'] }],
      ['status', {
        label: 'Status', type: 'select', source: [['Draft', 'Draft'], ['Completed', 'Completed']], sort: 'none',
      }],
    ];
  }

  postRenderForm(object, $tempDomObj) {
    if (object != null && object !== undefined && object.id !== undefined && object.id != null) {
      $tempDomObj.find('#pay_period').attr('disabled', 'disabled');
      $tempDomObj.find('#department').attr('disabled', 'disabled');
      // $tempDomObj.find("#date_start").attr('disabled','disabled');
      // $tempDomObj.find("#date_end").attr('disabled','disabled');
      // $tempDomObj.find("#column_template").attr('disabled','disabled');
    }
  }

  process(id, status) {
    // eslint-disable-next-line no-global-assign
    modJs = modJsList.tabPayrollData;
    modJs.setCurrentPayroll(id);
    $('#Payroll').hide();
    $('#PayrollData').show();
    $('#PayrollDataButtons').show();

    if (status === 'Completed') {
      $('.completeBtnTable').hide();
      $('.saveBtnTable').hide();
    } else {
      $('.completeBtnTable').show();
      $('.saveBtnTable').show();
    }

    modJs.get([]);
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

    html = html.replace('_process_', processButton);


    html = html.replace(/_id_/g, id);
    html = html.replace(/_status_/g, data[6]);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }

  get(callBackData) {
    $('#PayrollData').hide();
    $('#PayrollForm').hide();
    $('#PayrollDataButtons').hide();
    $('#Payroll').show();
    modJsList.tabPayrollData.setCurrentPayroll(null);
    super.get(callBackData);
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/payroll-and-expenses/payroll-management';
  }
}


/**
 * PayrollDataAdapter
 */

class PayrollDataAdapter extends TableEditAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.cellDataUpdates = {};
    this.payrollId = null;
  }

  validateCellValue(element, evt, newValue) {
    modJs.addCellDataUpdate(element.data('colId'), element.data('rowId'), newValue);
    return true;
  }

  setCurrentPayroll(val) {
    this.payrollId = val;
  }


  addAdditionalRequestData(type, req) {
    if (type === 'updateData') {
      req.payrollId = this.payrollId;
    } else if (type === 'updateAllData') {
      req.payrollId = this.payrollId;
    } else if (type === 'getAllData') {
      req.payrollId = this.payrollId;
    }

    return req;
  }

  modifyCSVHeader(header) {
    header.unshift('');
    return header;
  }

  getCSVData() {
    let csv = '';

    for (let i = 0; i < this.csvData.length; i++) {
      csv += this.csvData[i].join(',');
      if (i < this.csvData.length - 1) {
        csv += '\r\n';
      }
    }

    return csv;
  }

  downloadPayroll() {
    const element = document.createElement('a');
    element.setAttribute('href', `data:text/plain;charset=utf-8,${encodeURIComponent(this.getCSVData())}`);
    element.setAttribute('download', `payroll_${this.payrollId}.csv`);

    element.style.display = 'none';
    document.body.appendChild(element);

    element.click();

    document.body.removeChild(element);
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/payroll-and-expenses/payroll-management';
  }
}


/**
 * PayrollColumnAdapter
 */

class PayrollColumnAdapter extends AdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.codeMirror = CodeMirror;
  }

  getDataMapping() {
    return [
      'id',
      'name',
      'colorder',
      'calculation_hook',
      'deduction_group',
      'editable',
      'enabled',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Column Order' },
      { sTitle: 'Calculation Method' },
      { sTitle: 'Payroll Group' },
      { sTitle: 'Editable' },
      { sTitle: 'Enabled' },
    ];
  }

  getFormFields() {
    const fucntionColumnList = ['calculation_columns', {
      label: 'Calculation Columns',
      type: 'datagroup',
      form: [
        ['name', { label: 'Name', type: 'text', validation: '' }],
        ['column', { label: 'Column', type: 'select2', 'remote-source': ['PayrollColumn', 'id', 'name'] }],
      ],
      html: '<div id="#_id_#" class="panel panel-default">#_delete_##_edit_#<div class="panel-body">#_renderFunction_#</div></div>',
      validation: 'none',
      render(item) {
        const output = `Variable:${item.name}`;
        return output;
      },

    }];

    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['calculation_hook', {
        label: 'Predefined Calculations', type: 'select2', 'allow-null': true, 'null-label': 'None', 'remote-source': ['CalculationHook', 'code', 'name'],
      }],
      ['deduction_group', {
        label: 'Payroll Group', type: 'select2', 'allow-null': true, 'null-label': 'Common', 'remote-source': ['DeductionGroup', 'id', 'name'],
      }],
      ['salary_components', { label: 'Salary Components', type: 'select2multi', 'remote-source': ['SalaryComponent', 'id', 'name'] }],
      ['deductions', { label: 'Calculation Method', type: 'select2multi', 'remote-source': ['Deduction', 'id', 'name'] }],
      ['add_columns', { label: 'Columns to Add', type: 'select2multi', 'remote-source': ['PayrollColumn', 'id', 'name'] }],
      ['sub_columns', { label: 'Columns to Subtract', type: 'select2multi', 'remote-source': ['PayrollColumn', 'id', 'name'] }],
      ['colorder', { label: 'Column Order', type: 'text', validation: 'number' }],
      ['editable', { label: 'Editable', type: 'select', source: [['Yes', 'Yes'], ['No', 'No']] }],
      ['enabled', { label: 'Enabled', type: 'select', source: [['Yes', 'Yes'], ['No', 'No']] }],
      ['default_value', { label: 'Default Value', type: 'text', validation: '' }],
      fucntionColumnList,
      ['function_type', { label: 'Function Type', type: 'select', source: [['Advanced', 'Advanced'], ['Simple', 'Simple']] }],
      ['calculation_function', { label: 'Function', type: 'code', validation: 'none' }],
    ];
  }

  forceInjectValuesBeforeSave(params) {
    if (params.calculation_function && params.calculation_function.trim() !== '') {
      params.calculation_function = btoa(params.calculation_function);
    }
    return params;
  }

  getFilters() {
    return [
      ['deduction_group', {
        label: 'Payroll Group', type: 'select2', 'allow-null': false, 'remote-source': ['DeductionGroup', 'id', 'name'],
      }],
    ];
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/payroll-and-expenses/payroll-management';
  }
}


/**
 * PayrollColumnTemplateAdapter
 */

class PayrollColumnTemplateAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: true },
      { sTitle: 'Name' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['columns', { label: 'Payroll Columns', type: 'select2multi', 'remote-source': ['PayrollColumn', 'id', 'name'] }],
    ];
  }
}


/*
 * PayrollEmployeeAdapter
 */

class PayrollEmployeeAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'pay_frequency',
      'deduction_group',
      'currency',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Pay Frequency' },
      { sTitle: 'Payroll Group' },
      { sTitle: 'Currency' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],
      ['pay_frequency', { label: 'Pay Frequency', type: 'select2', 'remote-source': ['PayFrequency', 'id', 'name'] }],
      ['currency', { label: 'Currency', type: 'select2', 'remote-source': ['CurrencyType', 'id', 'code'] }],
      ['deduction_group', {
        label: 'Payroll Group', type: 'select2', 'allow-null': true, 'null-label': 'None', 'remote-source': ['DeductionGroup', 'id', 'name'],
      }],
      ['deduction_exemptions', {
        label: 'Calculation Exemptions', type: 'select2multi', 'remote-source': ['Deduction', 'id', 'name'], validation: 'none',
      }],
      ['deduction_allowed', {
        label: 'Calculations Assigned', type: 'select2multi', 'remote-source': ['Deduction', 'id', 'name'], validation: 'none',
      }],
    ];
  }

  getFilters() {
    return [
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],
    ];
  }
}


/**
 * DeductionAdapter
 */

class DeductionAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'deduction_group',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Payroll Group' },
    ];
  }

  getFormFields() {
    const rangeAmounts = ['rangeAmounts', {
      label: 'Calculation Process',
      type: 'datagroup',
      form: [
        ['lowerCondition', { label: 'Lower Limit Condition', type: 'select', source: [['No Lower Limit', 'No Lower Limit'], ['gt', 'Greater than'], ['gte', 'Greater than or Equal']] }],
        ['lowerLimit', { label: 'Lower Limit', type: 'text', validation: 'float' }],
        ['upperCondition', { label: 'Upper Limit Condition', type: 'select', source: [['No Upper Limit', 'No Upper Limit'], ['lt', 'Less than'], ['lte', 'Less than or Equal']] }],
        ['upperLimit', { label: 'Upper Limit', type: 'text', validation: 'float' }],
        ['amount', { label: 'Value', type: 'text', validation: '' }],
      ],
      html: '<div id="#_id_#" class="panel panel-default">#_delete_##_edit_#<div class="panel-body">#_renderFunction_#</div></div>',
      validation: 'none',
      'custom-validate-function': function (data) {
        const res = {};
        res.valid = true;
        if (data.lowerCondition === 'No Lower Limit') {
          data.lowerLimit = 0;
        }
        if (data.upperCondition === 'No Upper Limit') {
          data.upperLimit = 0;
        }
        res.params = data;
        return res;
      },
      render(item) {
        let output = '';
        const getSymbol = function (text) {
          const map = {};
          map.gt = '>';
          map.gte = '>=';
          map.lt = '<';
          map.lte = '<=';

          return map[text];
        };

        if (item.lowerCondition !== 'No Lower Limit') {
          output += `${item.lowerLimit} ${getSymbol(item.lowerCondition)} `;
        }

        if (item.upperCondition !== 'No Upper Limit') {
          output += ' and ';
          output += `${getSymbol(item.upperCondition)} ${item.upperLimit} `;
        }
        if (output === '') {
          return `Deduction is ${item.amount} for all ranges`;
        }
        return `If salary component ${output} deduction is ${item.amount}`;
      },

    }];

    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['componentType', {
        label: 'Salary Component Type', type: 'select2multi', 'allow-null': true, 'remote-source': ['SalaryComponentType', 'id', 'name'],
      }],
      ['component', {
        label: 'Salary Component', type: 'select2multi', 'allow-null': true, 'remote-source': ['SalaryComponent', 'id', 'name'],
      }],
      ['payrollColumn', {
        label: 'Payroll Report Column', type: 'select2', 'allow-null': true, 'remote-source': ['PayrollColumn', 'id', 'name'],
      }],
      rangeAmounts,
      ['deduction_group', {
        label: 'Payroll Group', type: 'select2', 'allow-null': false, 'remote-source': ['DeductionGroup', 'id', 'name'],
      }],

    ];
  }
}


/*
 * DeductionGroupAdapter
 */

class DeductionGroupAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'description',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['description', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }

  getActionButtonsHtml(id) {

    let html = '<div style="width:150px;">'
        + '<img class="tableActionButton" src="_BASE_images/edit.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>'
        + '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deletePayrollGroup(_id_);return false;"></img>'
        + '<img class="tableActionButton" src="_BASE_images/clone.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Duplicate" onclick="modJs.copyRow(_id_);return false;"></img>'
        + '</div>';
    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }

  deletePayrollGroup(id) {
    if (confirm('Are you sure you want to delete this payroll group? Deleting the payroll group will delete all the Payroll columns and Saved calculations attached to this Payroll Group')) {
      // Terminate
    } else {
      return;
    }

    const params = {};
    params.id = id;
    const reqJson = JSON.stringify(params);
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'deletePayrollGroupSuccessCallback';
    callBackData.callBackFail = 'deletePayrollGroupFailCallback';

    this.customAction('deletePayrollGroup', 'admin=payroll', reqJson, callBackData);
  }

  deletePayrollGroupSuccessCallback(callBackData) {
    this.showMessage('Success', 'Payroll Group Deleted ');
    this.get([]);
  }


  deletePayrollGroupFailCallback(callBackData) {
    this.showMessage('Error occured while deleting Payroll Group', callBackData);
  }
}


/*
 * PayslipTemplateAdapter
 */

class PayslipTemplateAdapter extends AdapterBase {
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

  getFormFields() {
    const payslipFields = ['data', {
      label: 'Payslip Fields',
      type: 'datagroup',
      form: [
        ['type', {
          label: 'Type', type: 'select', sort: 'none', source: [['Payroll Column', 'Payroll Column'], ['Text', 'Text'], ['Company Name', 'Company Name'], ['Company Logo', 'Company Logo'], ['Separators', 'Separators']],
        }],
        ['payrollColumn', {
          label: 'Payroll Column', type: 'select2', sort: 'none', 'allow-null': true, 'null-label': 'None', 'remote-source': ['PayrollColumn', 'id', 'name'],
        }],

        ['label', { label: 'Label', type: 'text', validation: 'none' }],
        ['text', { label: 'Text', type: 'textarea', validation: 'none' }],
        ['status', {
          label: 'Status', type: 'select', sort: 'none', source: [['Show', 'Show'], ['Hide', 'Hide']],
        }],
      ],

      // "html":'<div id="#_id_#" class="panel panel-default">#_delete_##_edit_#<div class="panel-body"><table class="table table-striped"><tr><td>Type</td><td>#_type_#</td></tr><tr><td>Label</td><td>#_label_#</td></tr><tr><td>Text</td><td>#_text_#</td></tr><tr><td>Font Size</td><td>#_fontSize_#</td></tr><tr><td>Font Style</td><td>#_fontStyle_#</td></tr><tr><td>Font Color</td><td>#_fontColor_#</td></tr><tr><td>Status</td><td>#_status_#</td></tr></table> </div></div>',
      html: '<div id="#_id_#" class="panel panel-default">#_delete_##_edit_#<div class="panel-body">#_type_# #_label_# <br/> #_text_#</div></div>',
      validation: 'none',
      'custom-validate-function': function (data) {
        const res = {};
        res.valid = true;
        if (data.type === 'Payroll Column') {
          if (data.payrollColumn === 'NULL') {
            res.valid = false;
            res.message = 'Please select payroll column';
          }
        } else {
          data.payrollColumn = 'NULL';
        }

        if (data.type === 'Text') {
          if (data.text === '') {
            res.valid = false;
            res.message = 'Text can not be empty';
          }
        }

        res.params = data;
        return res;
      },
    }];

    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      payslipFields,
    ];
  }
}


module.exports = {
  PaydayAdapter,
  PayrollAdapter,
  PayrollDataAdapter,
  PayrollColumnAdapter,
  PayrollColumnTemplateAdapter,
  PayrollEmployeeAdapter,
  DeductionAdapter,
  DeductionGroupAdapter,
  PayslipTemplateAdapter,
};
