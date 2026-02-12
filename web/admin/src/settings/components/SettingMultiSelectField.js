/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import { Form, Select } from 'antd';

/**
 * SettingMultiSelectField Component
 * Renders a multi-select dropdown field for settings (select2multi type)
 */
class SettingMultiSelectField extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      options: [],
      loading: false,
    };
  }

  componentDidMount() {
    this.loadOptions();
  }

  componentDidUpdate(prevProps) {
    const { setting } = this.props;
    if (prevProps.setting.meta !== setting.meta) {
      this.loadOptions();
    }
  }
  
  parseValue = (val) => {
    // Parse value for multi-select (stored as JSON string)
    let parsedValue = [];
    try {
      // Handle empty/null/undefined values
      if (!val || val === '' || val === null || val === undefined) {
        parsedValue = [];
      } else {
        // Parse JSON string
        const parsed = JSON.parse(val);
        // Ensure it's an array
        if (Array.isArray(parsed)) {
          // Filter out any null/undefined/empty values and convert to strings
          parsedValue = parsed
            .filter((item) => item !== null && item !== undefined && item !== '')
            .map((item) => String(item));
          // Remove duplicates
          parsedValue = [...new Set(parsedValue)];
        } else {
          parsedValue = [];
        }
      }
    } catch (e) {
      // If parsing fails, use empty array
      parsedValue = [];
    }
    return parsedValue;
  }

  loadOptions = () => {
    const { setting, adapter } = this.props;
    const { source, 'remote-source': remoteSource } = setting.metaConfig || {};

    if (remoteSource && adapter) {
      this.setState({ loading: true });
      const key = `${remoteSource[0]}_${remoteSource[1]}_${remoteSource[2]}`;
      
      if (adapter.fieldMasterData && adapter.fieldMasterData[key]) {
        const data = adapter.fieldMasterData[key];
        this.setState({
          options: this.formatRemoteData(data, remoteSource),
          loading: false,
        });
      } else {
        const callBackData = {
          callBack: 'initFieldMasterDataResponse',
          callBackData: [key],
        };
        adapter.getFieldValues(remoteSource, callBackData);
        
        let attempts = 0;
        const maxAttempts = 20;
        const checkInterval = setInterval(() => {
          attempts++;
          if (adapter.fieldMasterData && adapter.fieldMasterData[key]) {
            const data = adapter.fieldMasterData[key];
            this.setState({
              options: this.formatRemoteData(data, remoteSource),
              loading: false,
            });
            clearInterval(checkInterval);
          } else if (attempts >= maxAttempts) {
            this.setState({ loading: false });
            clearInterval(checkInterval);
          }
        }, 200);
      }
    } else if (source && Array.isArray(source)) {
      const options = source.map((item) => {
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
      this.setState({ options });
    }
  };

  formatRemoteData = (data, remoteSource) => {
    const options = [];
    
    if (Array.isArray(data)) {
      data.forEach((item) => {
        if (Array.isArray(item)) {
          options.push({
            value: String(item[0]),
            label: String(item[1]),
          });
        } else {
          options.push({
            value: String(item),
            label: String(item),
          });
        }
      });
    } else if (typeof data === 'object' && data !== null) {
      Object.keys(data).forEach((key) => {
        options.push({
          value: String(key),
          label: String(data[key]),
        });
      });
    }

    return options;
  };

  render() {
    const {
      setting, value, onChange, adapter,
    } = this.props;
    const { label } = setting.metaConfig || {};
    const { options, loading } = this.state;

    // Parse value for multi-select (stored as JSON string)
    const parsedValue = this.parseValue(value);

    return (
      <Form.Item
        label={setting.name}
        name={`setting_${setting.id}`}
        initialValue={parsedValue}
        rules={[]}
      >
        <Select
          mode="multiple"
          showSearch
          placeholder={`Select ${label || setting.name}`}
          optionFilterProp="label"
          filterOption={(input, option) => option.label
            .toLowerCase()
            .indexOf(input.toLowerCase()) >= 0}
          loading={loading}
          allowClear
          options={options}
          onChange={(selectedValues) => {
            if (onChange) {
              // Ensure selectedValues is an array and remove duplicates
              let cleanValues = [];
              if (Array.isArray(selectedValues)) {
                cleanValues = selectedValues
                  .filter((item) => item !== null && item !== undefined && item !== '')
                  .map((item) => String(item));
                // Remove duplicates
                cleanValues = [...new Set(cleanValues)];
              }
              // Convert to JSON string for storage
              const jsonValue = JSON.stringify(cleanValues);
              onChange(setting.id, jsonValue);
            }
          }}
          style={{ width: '100%' }}
        />
      </Form.Item>
    );
  }
}

export default SettingMultiSelectField;
