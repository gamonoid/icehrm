/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import {
  Form, Modal, Typography, Alert,
} from 'antd';
import SettingsField from './SettingsField';

const { Text } = Typography;

/**
 * SettingsForm Component
 * Renders a settings form modal based on meta JSON configuration
 */
class SettingsForm extends React.Component {
  constructor(props) {
    super(props);
    this.formRef = React.createRef();
    this.state = {
      visible: false,
      setting: null,
      formFields: null,
      loading: false,
    };
  }

  componentDidMount() {
    // Load remote data if needed
    if (this.props.adapter) {
      this.props.adapter.loadRemoteDataForSettings();
    }
  }

  show = (setting) => {
    if (!setting) {
      return;
    }

    // Parse meta JSON to get form field configuration
    let formFields = null;
    if (setting.meta && setting.meta !== '') {
      try {
        const metaField = JSON.parse(setting.meta);
        formFields = [
          ['id', { label: 'ID', type: 'hidden' }],
          metaField,
        ];
      } catch (e) {
        console.error('Error parsing meta JSON:', e);
        formFields = [
          ['id', { label: 'ID', type: 'hidden' }],
          ['value', { label: 'Value', type: 'text' }],
        ];
      }
    } else {
      formFields = [
        ['id', { label: 'ID', type: 'hidden' }],
        ['value', { label: 'Value', type: 'text' }],
      ];
    }

    this.setState({
      visible: true,
      setting,
      formFields,
    }, () => {
      // Set form values after a short delay to ensure form is ready
      setTimeout(() => {
        if (this.formRef.current) {
          const formValues = {
            id: setting.id,
            value: setting.value,
          };
          this.formRef.current.setFieldsValue(formValues);
        }
      }, 100);
    });
  };

  handleCancel = () => {
    this.setState({
      visible: false,
      setting: null,
      formFields: null,
    });
    if (this.formRef.current) {
      this.formRef.current.resetFields();
    }
  };

  handleOk = () => {
    if (this.formRef.current) {
      this.formRef.current.validateFields()
        .then((values) => {
          this.setState({ loading: true });
          
          // Call save callback
          if (this.props.onSave) {
            this.props.onSave(values, (response) => {
              this.setState({ loading: false });
              if (response && response.status === 'SUCCESS') {
                this.handleCancel();
              } else {
                // Error handling is done in adapter
              }
            });
          } else {
            this.setState({ loading: false });
            this.handleCancel();
          }
        })
        .catch((errorInfo) => {
          console.error('Validation failed:', errorInfo);
          this.setState({ loading: false });
        });
    }
  };

  handleFieldChange = (fieldName, value) => {
    if (this.formRef.current) {
      this.formRef.current.setFieldsValue({
        [fieldName]: value,
      });
    }
  };

  render() {
    const {
      visible, setting, formFields, loading,
    } = this.state;
    const { adapter } = this.props;

    if (!formFields || !setting) {
      return null;
    }

    // Extract the value field configuration
    const valueField = formFields.find((field) => field[0] === 'value');
    if (!valueField) {
      return (
        <Alert
          message="Invalid form configuration"
          type="error"
          showIcon
        />
      );
    }

    const [fieldName, fieldConfig] = valueField;

    return (
      <Modal
        title={setting.name || 'Edit Setting'}
        visible={visible}
        onOk={this.handleOk}
        onCancel={this.handleCancel}
        confirmLoading={loading}
        width={600}
        destroyOnClose
      >
        <Form
          ref={this.formRef}
          layout="horizontal"
          labelCol={{ span: 6 }}
          wrapperCol={{ span: 18 }}
        >
          {/* Hidden ID field */}
          <Form.Item name="id" style={{ display: 'none' }}>
            <input type="hidden" />
          </Form.Item>

          {/* Description help text */}
          {setting.description && (
            <Alert
              message={setting.description}
              type="info"
              showIcon
              style={{ marginBottom: 16 }}
            />
          )}

          {/* Value field rendered based on meta configuration */}
          <SettingsField
            fieldName={fieldName}
            fieldConfig={fieldConfig}
            value={setting.value}
            readOnly={false}
            adapter={adapter}
            onChange={this.handleFieldChange}
          />
        </Form>
      </Modal>
    );
  }
}

export default SettingsForm;
