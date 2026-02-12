/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import ReactDOM from 'react-dom';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import { SettingsForm } from './components';

/**
 * SettingAdapterReact
 * React-based adapter for Settings module using Ant Design components
 */
class SettingAdapterReact extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'value',
      'description',
      'meta',
      'category',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Value' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    // Form fields are dynamically generated from meta JSON
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['value', { label: 'Value', type: 'text', validation: 'none' }],
    ];
  }

  getTableSize() {
    return 50;
  }

  getMetaFieldForRendering(fieldName) {
    if (fieldName === 'value') {
      return 'meta';
    }
    return '';
  }

  edit(id) {
    this.loadRemoteDataForSettings();
    super.edit(id);
  }

  fillForm(object) {
    const metaField = this.getMetaFieldForRendering('value');
    const metaVal = object[metaField];
    let formFields = null;

    if (metaVal !== '' && metaVal !== undefined) {
      try {
        formFields = [
          ['id', { label: 'ID', type: 'hidden' }],
          JSON.parse(metaVal),
        ];
      } catch (e) {
        console.error('Error parsing meta JSON:', e);
        formFields = [
          ['id', { label: 'ID', type: 'hidden' }],
          ['value', { label: 'Value', type: 'text', validation: 'none' }],
        ];
      }
    } else {
      formFields = [
        ['id', { label: 'ID', type: 'hidden' }],
        ['value', { label: 'Value', type: 'text', validation: 'none' }],
      ];
    }

    super.fillForm(object, null, formFields);
  }

  loadRemoteDataForSettings() {
    const fields = [];
    let field = null;
    fields.push(['country', { label: 'Country', type: 'select2multi', 'remote-source': ['Country', 'id', 'name'] }]);
    fields.push(['countryCompany', { label: 'Country', type: 'select2', 'remote-source': ['Country', 'code', 'name'] }]);
    fields.push(['currency', { label: 'Currency', type: 'select2multi', 'remote-source': ['CurrencyType', 'id', 'code+name'] }]);
    fields.push(['nationality', { label: 'Nationality', type: 'select2multi', 'remote-source': ['Nationality', 'id', 'name'] }]);
    fields.push(['supportedLanguage', {
      label: 'Value', type: 'select2', 'allow-null': false, 'remote-source': ['SupportedLanguage', 'name', 'description'],
    }]);

    for (const index in fields) {
      field = fields[index];
      if (field[1]['remote-source'] !== undefined && field[1]['remote-source'] !== null) {
        const key = `${field[1]['remote-source'][0]}_${field[1]['remote-source'][1]}_${field[1]['remote-source'][2]}`;
        this.fieldMasterDataKeys[key] = false;
        this.sourceMapping[field[0]] = field[1]['remote-source'];

        const callBackData = {};
        callBackData.callBack = 'initFieldMasterDataResponse';
        callBackData.callBackData = [key];

        this.getFieldValues(field[1]['remote-source'], callBackData);
      }
    }
  }

  getHelpLink() {
    return 'http://blog.icehrm.com/docs/settings/';
  }

  // Override to use custom SettingsForm component
  renderForm(object) {
    const formContainer = React.createRef();
    const formId = `${this.tab}Form`;

    // Create or get form container
    let formElement = document.getElementById(formId);
    if (!formElement) {
      formElement = document.createElement('div');
      formElement.id = formId;
      document.body.appendChild(formElement);
    }

    ReactDOM.render(
      <SettingsForm
        ref={formContainer}
        adapter={this}
        onSave={this.handleSave.bind(this)}
      />,
      formElement,
    );

    if (formContainer.current) {
      formContainer.current.show(object);
    }
  }

  handleSave(values, callback) {
    const data = {
      t: this.table,
      a: 'save',
      id: values.id,
      value: values.value,
    };

    this.save(data, callback);
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <a
        onClick={() => {
          adapter.edit(record.id);
        }}
        style={{ marginRight: 8 }}
      >
        Edit
      </a>
    );
  }
}

module.exports = { SettingAdapterReact };
