/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import { Form, Select } from 'antd';

/**
 * SettingSelectField Component
 * Renders a select dropdown field for settings (select2 type)
 */
class SettingSelectField extends React.Component {
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

  loadOptions = () => {
    const { setting, adapter } = this.props;
    const { source, 'remote-source': remoteSource } = setting.metaConfig || {};

    if (remoteSource && adapter) {
      this.setState({ loading: true });
      // Load remote data
      const key = `${remoteSource[0]}_${remoteSource[1]}_${remoteSource[2]}`;
      
      if (adapter.fieldMasterData && adapter.fieldMasterData[key]) {
        const data = adapter.fieldMasterData[key];
        this.setState({
          options: this.formatRemoteData(data, remoteSource),
          loading: false,
        });
      } else {
        // Request data loading
        const callBackData = {
          callBack: 'initFieldMasterDataResponse',
          callBackData: [key],
        };
        adapter.getFieldValues(remoteSource, callBackData);
        
        // Poll for data
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
      // Use static source
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
    const { label, 'allow-null': allowNull } = setting.metaConfig || {};
    const { options, loading } = this.state;

    return (
      <Form.Item
        label={setting.name}
        name={`setting_${setting.id}`}
        initialValue={value ? String(value) : undefined}
        rules={[]}
      >
        <Select
          showSearch
          placeholder={`Select ${label || setting.name}`}
          optionFilterProp="label"
          filterOption={(input, option) => option.label
            .toLowerCase()
            .indexOf(input.toLowerCase()) >= 0}
          loading={loading}
          allowClear={allowNull !== false}
          options={options}
          onChange={(selectedValue) => {
            if (onChange) {
              onChange(setting.id, selectedValue);
            }
          }}
          style={{ width: '100%' }}
        />
      </Form.Item>
    );
  }
}

export default SettingSelectField;
