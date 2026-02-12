/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import { Form, Switch } from 'antd';

/**
 * SettingSwitchField Component
 * Renders a Switch/Toggle component for Yes/No settings
 * Used when source has only Yes/No options: [["1","Yes"],["0","No"]]
 */
class SettingSwitchField extends React.Component {
  render() {
    const {
      setting, value, onChange, adapter,
    } = this.props;

    // Convert value to boolean
    // "1" or 1 = true, "0" or 0 = false, null/undefined = false
    const boolValue = value === '1' || value === 1 || value === true;

    return (
      <Form.Item
        label={setting.name}
        name={`setting_${setting.id}`}
        initialValue={boolValue}
        rules={[]}
        valuePropName="checked"
        normalize={(val) => {
          // Normalize: convert "1"/"0" strings to boolean for Switch component
          if (val === '1' || val === 1 || val === true) {
            return true;
          }
          return false;
        }}
        getValueFromEvent={(checked) => {
          // Convert boolean back to "1" or "0" string for storage
          return checked ? '1' : '0';
        }}
      >
        <Switch
          checkedChildren="Yes"
          unCheckedChildren="No"
          onChange={(checked) => {
            if (onChange) {
              // Save as "1" or "0" string
              onChange(setting.id, checked ? '1' : '0');
            }
          }}
        />
      </Form.Item>
    );
  }
}

export default SettingSwitchField;
