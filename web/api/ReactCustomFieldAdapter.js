/*
   Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import React from 'react';
import { Space } from 'antd';
import AdapterBase from './ReactModalAdapterBase';

/*
 * CustomFieldAdapter
 */

class ReactCustomFieldAdapter extends AdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.tableType = '';
  }

  getDataMapping() {
    return [
      'id',
      'name',
      'field_type',
      'field_label',
      'display',
      'display_order',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Field Type' },
      { sTitle: 'Field Label' },
      { sTitle: 'Display Status' },
      { sTitle: 'Priority' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Field Label',
        dataIndex: 'field_label',
      },
      {
        title: 'Field Type',
        dataIndex: 'field_type',
      },
      {
        title: 'Display Status',
        dataIndex: 'display',
        sorter: true,
      },
      {
        title: 'Priority',
        dataIndex: 'display_order',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['field_label', { label: 'Field Label', type: 'text', validation: '' }],
      ['field_type', { label: 'Field Type', type: 'select', source: [['text', 'Text Field'], ['textarea', 'Multiline Text'], ['select', 'Dropdown'], ['select2', 'Searchable DropDown'], ['select2multi', 'Multi Select'], ['fileupload', 'File Upload'], ['date', 'Date'], ['datetime', 'Date Time'], ['time', 'Time'], ['signature', 'Signature']] }],
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
        columns: [
          {
            title: 'Label',
            dataIndex: 'label',
            key: 'label',
          },
          {
            title: 'Option Value',
            dataIndex: 'value',
            key: 'value',
          },
        ],
        validation: 'none',
      }],
      ['display_order', { label: 'Priority', type: 'text', validation: 'none' }],
    ];
  }

  getNameFromFieldName(fieldName) {
    return fieldName.replace(/[^a-z0-9+]+/gi, '').toLowerCase();
  }

  setTableType(type) {
    this.tableType = type;
  }

  doCustomValidation(params) {
    const validateName = function (str) {
      const name = /^[a-z][a-z0-9._]+$/;
      return str != null && name.test(str);
    };

    if (this.currentElement == null || this.currentElement.name == null || this.currentElement.name === '') {
      params.name = this.getNameFromFieldName(params.field_label);
      if (!validateName(params.name)) {
        return 'Invalid field label for custom field';
      }
    } else {
      params.name = this.currentElement.name;
    }

    if (!validateName(params.name)) {
      return 'Invalid name for custom field';
    }
    return null;
  }

  forceInjectValuesBeforeSave(params) {
    const data = ['', {}];
    const options = [];
    let optionsData;

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

    params.type = this.tableType;
    if (this.currentElement == null || this.currentElement.name == null || this.currentElement.name === '') {
      params.name = this.getNameFromFieldName(params.field_label);
    } else {
      params.name = this.currentElement.name;
    }

    data[0] = params.name;
    params.data = JSON.stringify(data);

    params.display = 'Form';
    params.display_order = parseInt(params.display_order);
    if (!Number.isInteger(params.display_order)) {
      params.display_order = 1;
    }

    return params;
  }
}

export default ReactCustomFieldAdapter;
