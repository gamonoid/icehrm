/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import ReactDOM from 'react-dom';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import { SettingsForm, SettingsPage } from './components';
import IceDataPipe from '../../../api/IceDataPipe';

/**
 * SettingAdapter
 * React-based adapter for Settings module using Ant Design components
 */

class SettingAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    // Note: Table initialization will be initialized when get() is called
    // which happens automatically via ReactModalAdapterBase.get()
  }

  getDataMapping() {
    return [
      'id',
      'name',
      'value',
      'description',
      'meta',
      'category',
      'setting_order',
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

  getTableColumns() {
    return [
      {
        title: 'ID',
        dataIndex: 'id',
        key: 'id',
        width: 80,
      },
      {
        title: 'Name',
        dataIndex: 'name',
        key: 'name',
        sorter: true,
      },
      {
        title: 'Value',
        dataIndex: 'value',
        key: 'value',
        render: (text) => {
          if (text && text.length > 50) {
            return `${text.substring(0, 50)}...`;
          }
          return text || '';
        },
      },
      {
        title: 'Details',
        dataIndex: 'description',
        key: 'description',
        render: (text) => text || '',
      },
    ];
  }

  getFormFields() {
    // Form fields are dynamically generated from meta JSON in SettingsForm component
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
    // This is handled by SettingsForm component now
    // Keep for compatibility but SettingsForm will parse meta JSON itself
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

  // Override getOrderBy to order by setting_order with 0 values last
  getOrderBy() {
    // Order by: non-zero setting_order values first (ascending), then zero/NULL values
    // SQL expression: CASE WHEN setting_order IS NULL OR setting_order = 0 THEN 1 ELSE 0 END, setting_order ASC
    // This ensures:
    // - Settings with setting_order > 0 are sorted ascending (first)
    // - Settings with setting_order = 0 or NULL go to the bottom
    return 'CASE WHEN setting_order IS NULL OR setting_order = 0 THEN 1 ELSE 0 END, setting_order ASC';
  }

  // Override initTable to render SettingsPage instead of table
  initTable() {
    this.initTableTopComponent();
    if (this.tableInitialized) {
      return false;
    }
    
    // Old ModuleBuilder uses just tab name, not tab + "Table"
    // Try both formats for compatibility
    let containerDom = document.getElementById(`${this.tab}Table`);
    if (!containerDom) {
      containerDom = document.getElementById(this.tab);
    }
    
    if (!containerDom) {
      console.warn(`Settings container not found for tab: ${this.tab}`);
      return false;
    }

    // Render SettingsPage component instead of table
    // Parse filter to get category info
    let filterObj = null;
    if (this.filter) {
      try {
        filterObj = typeof this.filter === 'string' ? JSON.parse(this.filter) : this.filter;
      } catch (e) {
        filterObj = this.filter; // Already an object
      }
    }

    ReactDOM.render(
      <SettingsPage
        adapter={this}
        filter={filterObj}
      />,
      containerDom,
    );

    this.tableInitialized = true;
    return true;
  }

  // Override initForm to use custom SettingsForm component
  initForm() {
    if (this.formInitialized) {
      return false;
    }
    
    this.formContainer = React.createRef();
    const formDom = document.getElementById(`${this.tab}Form`);
    
    if (formDom) {
      // Load remote data before initializing form
      this.loadRemoteDataForSettings();
      
      ReactDOM.render(
        <SettingsForm
          ref={this.formContainer}
          adapter={this}
          onSave={this.handleSettingsSave.bind(this)}
        />,
        formDom,
      );
    }

    this.formInitialized = true;
    return true;
  }

  // Handle settings save
  handleSettingsSave(values, callback) {
    const data = {
      t: this.table,
      a: 'save',
      id: values.id,
      value: values.value,
    };

    this.save(data, (response) => {
      if (callback) {
        callback(response);
      }
      
      if (response && response.status === 'SUCCESS') {
        // Refresh table
        this.get([]);
      } else {
        // Error will be shown by callback or adapter
        if (response && response.data && !callback) {
          alert(`Error: ${response.data}`);
        }
      }
    });
  }

  // Override edit to use SettingsForm
  edit(id) {
    // Load remote data first
    this.loadRemoteDataForSettings();
    
    const req = {
      t: this.table,
      a: 'getElement',
      id,
    };

    this.get(req, (response) => {
      if (response && response.status === 'SUCCESS' && response.data) {
        const setting = response.data;
        
        // Ensure form is initialized
        if (!this.formInitialized) {
          this.initForm();
        }
        
        // Wait for form to be ready and remote data to load
        const checkForm = () => {
          if (this.formContainer && this.formContainer.current) {
            this.formContainer.current.show(setting);
          } else {
            setTimeout(checkForm, 100);
          }
        };
        
        setTimeout(checkForm, 200);
      }
    });
  }

  // Override viewElement to use SettingsForm in read-only mode
  viewElement(id) {
    const req = {
      t: this.table,
      a: 'getElement',
      id,
    };

    this.get(req, (response) => {
      if (response && response.status === 'SUCCESS' && response.data) {
        const setting = response.data;
        
        // For view mode, we can show in a modal or use the form in read-only
        // For now, just use edit mode
        this.edit(id);
      }
    });
  }
}

module.exports = { SettingAdapter };
