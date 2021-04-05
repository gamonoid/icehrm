/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import AdapterBase from './AdapterBase';

/*
 * CustomFieldAdapter
 */

class CustomFieldAdapter extends AdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.tableType = '';
  }

  getDataMapping() {
    return [
      'id',
      'name',
      'display',
      'display_order',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Display Status' },
      { sTitle: 'Priority' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['display', { label: 'Display Status', type: 'select', source: [['Form', 'Show'], ['Hidden', 'Hidden']] }],
      ['field_type', { label: 'Field Type', type: 'select', source: [['text', 'Text Field'], ['textarea', 'Text Area'], ['select', 'Select'], ['select2', 'Select2'], ['select2multi', 'Multi Select'], ['fileupload', 'File Upload'], ['date', 'Date'], ['datetime', 'Date Time'], ['time', 'Time'], ['signature', 'Signature']] }],
      ['field_label', { label: 'Field Label', type: 'text', validation: '' }],
      ['field_validation', {
        label: 'Validation', type: 'select2', validation: 'none', sort: 'none', 'null-label': 'Required', 'allow-null': true, source: [['none', 'None'], ['number', 'Number'], ['numberOrEmpty', 'Number or Empty'], ['float', 'Decimal'], ['email', 'Email'], ['emailOrEmpty', 'Email or Empty']],
      }],
      ['field_options', {
        label: 'Field Options',
        type: 'datagroup',
        form: [
          ['label', { label: 'Label', type: 'text', validation: '' }],
          ['value', { label: 'Value', type: 'text', validation: 'none' }],
        ],
        html: '<div id="#_id_#" class="panel panel-default"><div class="panel-body">#_delete_##_edit_#<span style="color:#999;font-size:13px;font-weight:bold">#_label_#</span>:#_value_#</div></div>',
        validation: 'none',
      }],
      ['display_order', { label: 'Priority', type: 'text', validation: 'number' }],
      ['display_section', { label: 'Display Section', type: 'text', validation: 'none' }],
    ];
  }

  setTableType(type) {
    this.tableType = type;
  }

  doCustomValidation(params) {
    const validateName = function (str) {
      const name = /^[a-z][a-z0-9._]+$/;
      return str != null && name.test(str);
    };

    if (!validateName(params.name)) {
      return 'Invalid name for custom field';
    }


    return null;
  }

  forceInjectValuesBeforeSave(params) {
    const data = [params.name]; const options = []; let
      optionsData;
    data.push({});
    data[1].label = params.field_label;
    data[1].type = params.field_type;
    data[1].validation = params.field_validation;
    if (['select', 'select2', 'select2multi'].indexOf(params.field_type) >= 0) {
      optionsData = (params.field_options === '' || params.field_options === undefined)
        ? [] : JSON.parse(params.field_options);
      for (const index in optionsData) {
        options.push([optionsData[index].value, optionsData[index].label]);
      }
      data[1].source = options;
    }
    if (params.field_validation == null || params.field_validation === undefined) {
      params.field_validation = '';
    }
    params.data = JSON.stringify(data);
    params.type = this.tableType;
    return params;
  }
}

export default CustomFieldAdapter;
