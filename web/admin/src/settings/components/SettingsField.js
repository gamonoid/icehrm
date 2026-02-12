/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import {
  Form, Input, Select, Typography, Alert,
} from 'antd';

const { TextArea } = Input;
const { Text } = Typography;

/**
 * SettingsField Component
 * Renders a setting field based on meta JSON configuration using Ant Design components
 */
class SettingsField extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      remoteData: [],
      loading: false,
      searchValue: '',
    };
  }

  componentDidMount() {
    const { fieldConfig } = this.props;
    if (fieldConfig['remote-source']) {
      this.loadRemoteData();
    }
  }

  componentDidUpdate(prevProps) {
    const { fieldConfig, adapter } = this.props;
    
    // Check if remote-source changed
    if (prevProps.fieldConfig['remote-source'] !== fieldConfig['remote-source']) {
      if (fieldConfig['remote-source']) {
        this.loadRemoteData();
      }
    }
    
    // Check if adapter's fieldMasterData was updated
    if (adapter && fieldConfig['remote-source']) {
      const remoteSource = fieldConfig['remote-source'];
      let key = `${remoteSource[0]}_${remoteSource[1]}_${remoteSource[2]}`;
      if (remoteSource.length === 4) {
        key = `${key}_${remoteSource[3]}`;
      }
      
      const prevData = prevProps.adapter && prevProps.adapter.fieldMasterData 
        ? prevProps.adapter.fieldMasterData[key] 
        : null;
      const currentData = adapter.fieldMasterData && adapter.fieldMasterData[key];
      
      if (!prevData && currentData && this.state.remoteData.length === 0) {
        // Data just became available, load it
        this.loadRemoteData();
      }
    }
  }

  loadRemoteData = async () => {
    const { fieldConfig, adapter } = this.props;
    const remoteSource = fieldConfig['remote-source'];
    
    if (!remoteSource || !adapter) {
      return;
    }

    this.setState({ loading: true });

    try {
      // Build the key for fieldMasterData lookup
      let key = `${remoteSource[0]}_${remoteSource[1]}_${remoteSource[2]}`;
      if (remoteSource.length === 4) {
        key = `${key}_${remoteSource[3]}`;
      }

      // Check if data is already loaded in adapter
      if (adapter && adapter.fieldMasterData && adapter.fieldMasterData[key]) {
        const data = adapter.fieldMasterData[key];
        this.setState({
          remoteData: this.formatRemoteData(data, remoteSource),
          loading: false,
        });
      } else if (adapter && adapter.getFieldValues) {
        // Load data using adapter's getFieldValues method
        const callBackData = {
          callBack: 'initFieldMasterDataResponse',
          callBackData: [key],
        };
        
        adapter.getFieldValues(remoteSource, callBackData);
        
        // Poll for data to be loaded (adapter loads asynchronously)
        let attempts = 0;
        const maxAttempts = 10;
        const checkInterval = setInterval(() => {
          attempts++;
          if (adapter.fieldMasterData && adapter.fieldMasterData[key]) {
            const data = adapter.fieldMasterData[key];
            this.setState({
              remoteData: this.formatRemoteData(data, remoteSource),
              loading: false,
            });
            clearInterval(checkInterval);
          } else if (attempts >= maxAttempts) {
            this.setState({ loading: false });
            clearInterval(checkInterval);
          }
        }, 200);
      } else {
        this.setState({ loading: false });
      }
    } catch (error) {
      console.error('Error loading remote data:', error);
      this.setState({ loading: false });
    }
  };

  formatRemoteData = (data, remoteSource) => {
    const options = [];
    
    if (Array.isArray(data)) {
      // Data is array of [value, label] pairs
      data.forEach((item) => {
        if (Array.isArray(item)) {
          options.push({
            value: item[0],
            label: item[1],
          });
        } else {
          options.push({
            value: item,
            label: item,
          });
        }
      });
    } else if (typeof data === 'object' && data !== null) {
      // Data is object with key-value pairs
      Object.keys(data).forEach((key) => {
        options.push({
          value: key,
          label: data[key],
        });
      });
    }

    return options;
  };

  handleSelectChange = (value) => {
    const { fieldName, fieldConfig, onChange } = this.props;
    
    // For multi-select, convert to JSON string
    if (fieldConfig.type === 'select2multi') {
      const jsonValue = JSON.stringify(value || []);
      if (onChange) {
        onChange(fieldName, jsonValue);
      }
    } else {
      if (onChange) {
        onChange(fieldName, value);
      }
    }
  };

  render() {
    const {
      fieldName, fieldConfig, value, readOnly, adapter,
    } = this.props;
    const { remoteData, loading } = this.state;

    if (!fieldConfig) {
      return null;
    }

    const {
      label, type, source, 'remote-source': remoteSource, 'allow-null': allowNull,
    } = fieldConfig;

    // Parse value for multi-select
    let parsedValue = value;
    if (type === 'select2multi') {
      try {
        parsedValue = value ? JSON.parse(value) : [];
        if (!Array.isArray(parsedValue)) {
          parsedValue = [];
        }
        // Convert to strings for Select component
        parsedValue = parsedValue.map((item) => String(item));
      } catch (e) {
        parsedValue = [];
      }
    } else if (type === 'select' || type === 'select2' || type === 'select2multi') {
      parsedValue = value ? String(value) : undefined;
    }

    // Determine if field is required
    const isRequired = !allowNull && type !== 'placeholder';

    // Build validation rules
    const rules = [];
    if (isRequired) {
      rules.push({
        required: true,
        message: `${label} is required`,
      });
    }

    const labelSpan = { span: 6 };

    // Render based on field type
    switch (type) {
      case 'text':
        return (
          <Form.Item
            labelCol={labelSpan}
            label={label}
            name={fieldName}
            key={fieldName}
            rules={rules}
          >
            {readOnly ? (
              <Text>{value || ''}</Text>
            ) : (
              <Input />
            )}
          </Form.Item>
        );

      case 'textarea':
        return (
          <Form.Item
            labelCol={labelSpan}
            label={label}
            name={fieldName}
            key={fieldName}
            rules={rules}
          >
            {readOnly ? (
              <Text>{value || ''}</Text>
            ) : (
              <TextArea rows={4} />
            )}
          </Form.Item>
        );

      case 'select':
      case 'select2':
      case 'select2multi': {
        let options = [];
        
        if (remoteSource && remoteData.length > 0) {
          // Use remote data
          options = remoteData;
        } else if (source && Array.isArray(source)) {
          // Use static source
          options = source.map((item) => {
            if (Array.isArray(item)) {
              return {
                value: String(item[0]),
                label: item[1],
              };
            }
            return {
              value: String(item),
              label: String(item),
            };
          });
        }

        const isMultiSelect = type === 'select2multi';
        const isSearchable = type === 'select2' || type === 'select2multi';

        return (
          <Form.Item
            labelCol={labelSpan}
            label={label}
            name={fieldName}
            key={fieldName}
            rules={rules}
          >
            {readOnly ? (
              <Text>
                {isMultiSelect && Array.isArray(parsedValue)
                  ? parsedValue.join(', ')
                  : parsedValue || ''}
              </Text>
            ) : (
              <Select
                mode={isMultiSelect ? 'multiple' : undefined}
                showSearch={isSearchable}
                loading={loading}
                placeholder={`Select ${label}`}
                optionFilterProp="label"
                filterOption={
                  isSearchable
                    ? (input, option) => option.label
                      .toLowerCase()
                      .indexOf(input.toLowerCase()) >= 0
                    : undefined
                }
                allowClear={allowNull !== false}
                disabled={readOnly}
                onChange={this.handleSelectChange}
                options={options}
                style={{ width: '100%' }}
              />
            )}
          </Form.Item>
        );
      }

      case 'placeholder':
        return (
          <Form.Item
            labelCol={labelSpan}
            label={label}
            name={fieldName}
            key={fieldName}
          >
            <Input
              bordered={false}
              disabled
              style={{ color: 'rgba(0, 0, 0, 0.85)' }}
              value={value || ''}
            />
          </Form.Item>
        );

      default:
        return (
          <Alert
            message={`Unknown field type: ${type}`}
            type="warning"
            showIcon
          />
        );
    }
  }
}

export default SettingsField;
