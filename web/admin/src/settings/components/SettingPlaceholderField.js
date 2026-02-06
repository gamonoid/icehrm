/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import { Form, Input, Typography } from 'antd';

const { Text } = Typography;

/**
 * SettingPlaceholderField Component
 * Renders a read-only placeholder field for settings
 */
class SettingPlaceholderField extends React.Component {
  render() {
    const {
      setting, value,
    } = this.props;

    return (
      <Form.Item
        label={setting.name}
        name={`setting_${setting.id}`}
        initialValue={value || ''}
      >
        <Input
          bordered={false}
          disabled
          style={{ color: 'rgba(0, 0, 0, 0.85)' }}
          value={value || ''}
        />
      </Form.Item>
    );
  }
}

export default SettingPlaceholderField;
