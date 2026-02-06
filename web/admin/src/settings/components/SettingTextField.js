/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import { Form, Input } from 'antd';

/**
 * SettingTextField Component
 * Renders a text input field for settings
 */
class SettingTextField extends React.Component {
  render() {
    const {
      setting, value, onChange, adapter,
    } = this.props;
    const { label } = setting.metaConfig || {};

    return (
      <Form.Item
        label={setting.name}
        name={`setting_${setting.id}`}
        initialValue={value}
        rules={[]}
      >
        <Input
          onChange={(e) => {
            if (onChange) {
              onChange(setting.id, e.target.value);
            }
          }}
        />
      </Form.Item>
    );
  }
}

export default SettingTextField;
