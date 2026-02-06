/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import { Form } from 'antd';
import IceUpload from '../../../../components/IceUpload';

/**
 * SettingFileUploadField Component
 * Renders a file upload field for settings (fileupload type)
 * Uses IceUpload component which uploads to fileupload-new.php
 */
class SettingFileUploadField extends React.Component {
  handleFileChange = (fileName) => {
    const { setting, onChange } = this.props;
    
    // fileName is the name from Files table (returned by fileupload-new.php response.name)
    // This is what should be saved to the setting value
    if (onChange) {
      // fileName can be null when file is deleted, so use empty string
      onChange(setting.id, fileName || '');
    }
  };

  render() {
    const {
      setting, value, adapter,
    } = this.props;
    
    // Get user ID for file upload
    // For settings, we can use a generic user ID or get from adapter
    let userId = null;
    if (adapter) {
      const currentEmployee = adapter.getCurrentProfile ? adapter.getCurrentProfile() : null;
      if (currentEmployee != null) {
        userId = currentEmployee.id;
      } else if (adapter.getUser) {
        userId = adapter.getUser().id * -1;
      }
    }
    
    // Use a default user ID if adapter doesn't provide one
    if (!userId) {
      userId = '_NONE_';
    }

    // File group can be the setting name or a generic group
    const fileGroup = setting.name || 'Settings';

    return (
      <Form.Item
        label={setting.name}
        name={`setting_${setting.id}`}
        initialValue={value || ''}
        rules={[]}
      >
        <IceUpload
          user={userId}
          fileGroup={fileGroup}
          fileName={`setting_${setting.id}`}
          adapter={adapter}
          accept="image/*"
          readOnly={false}
          value={value}
          onChange={this.handleFileChange}
        />
      </Form.Item>
    );
  }
}

export default SettingFileUploadField;
