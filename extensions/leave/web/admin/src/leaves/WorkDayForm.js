/*
 * WorkDayForm — a focused form for the Work Week (WorkDay) entries.
 *
 * The generic 1024px-wide modal looked sparse and misaligned for these few
 * short fields, so this renders a compact, vertical form. The adapter is used
 * purely as the data/save backend (adapter.add).
 */
import React from 'react';
import {
  Modal, Form, Select, Button, Space, message,
} from 'antd';

const DAY_OPTIONS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
  .map((d) => ({ value: d, label: d }));

const STATUS_OPTIONS = ['Full Day', 'Half Day', 'Non-working Day']
  .map((s) => ({ value: s, label: s }));

class WorkDayForm extends React.Component {
  constructor(props) {
    super(props);
    this.formRef = React.createRef();
    this.editingObject = null;
    this.state = {
      visible: false, viewOnly: false, saving: false, countries: [],
    };
  }

  componentDidMount() {
    this.loadCountries();
  }

  setViewOnly(viewOnly) {
    this.setState({ viewOnly: !!viewOnly });
  }

  show(object) {
    this.editingObject = object || {};
    const values = {
      name: object && object.name ? object.name : 'Monday',
      status: object && object.status ? object.status : 'Full Day',
      country: object && object.country ? String(object.country) : undefined,
    };
    this.setState({ visible: true, saving: false }, () => {
      if (this.formRef.current) {
        this.formRef.current.resetFields();
        this.formRef.current.setFieldsValue(values);
      }
    });
  }

  close() {
    this.setState({ visible: false });
  }

  loadCountries() {
    const { adapter } = this.props;
    const base = adapter && adapter.moduleRelativeURL
      ? adapter.moduleRelativeURL.replace('service.php', 'data.php') : '';
    if (!base) return;
    const scope = adapter.spaModuleGroup
      ? `&mg=${encodeURIComponent(adapter.spaModuleGroup)}&mn=${encodeURIComponent(adapter.spaModuleName)}` : '';
    fetch(
      `${base}?t=Country&sm=${encodeURIComponent('{}')}`
        + `&cl=${encodeURIComponent('["id","name"]')}`
        + `&iDisplayStart=0&iDisplayLength=500&version=v2${scope}`,
      { method: 'POST', credentials: 'same-origin' },
    ).then((r) => r.json()).then((j) => {
      this.setState({ countries: (j.objects || []).map((c) => ({ value: String(c.id), label: c.name })) });
    }).catch(() => { /* optional */ });
  }

  save() {
    const { adapter } = this.props;
    this.formRef.current.validateFields().then((vals) => {
      const params = {
        name: vals.name,
        status: vals.status,
        country: vals.country || 'NULL',
      };
      if (this.editingObject && this.editingObject.id) {
        params.id = this.editingObject.id;
      }
      this.setState({ saving: true });
      adapter.add(
        params,
        [],
        () => adapter.get([]),
        () => {
          this.setState({ saving: false });
          message.success(`Work day ${params.id ? 'updated' : 'created'}`);
          this.close();
        },
        () => this.setState({ saving: false }),
      );
    }).catch(() => { /* inline validation */ });
  }

  render() {
    const {
      visible, viewOnly, saving,
    } = this.state;
    const { adapter } = this.props;
    const gt = adapter && adapter.gt ? (s) => adapter.gt(s) : (s) => s;
    const editing = !!(this.editingObject && this.editingObject.id);

    return (
      <Modal
        open={visible}
        width={460}
        maskClosable={false}
        title={gt(editing ? 'Edit Work Day' : 'New Work Day')}
        onCancel={() => this.close()}
        footer={null}
      >
        <Form ref={this.formRef} layout="vertical" disabled={viewOnly}>
          <Form.Item name="name" label={gt('Day')} rules={[{ required: true, message: gt('Day is required') }]}>
            <Select options={DAY_OPTIONS} />
          </Form.Item>
          <Form.Item name="status" label={gt('Status')} rules={[{ required: true, message: gt('Status is required') }]}>
            <Select options={STATUS_OPTIONS} />
          </Form.Item>
          <Form.Item name="country" label={gt('Country')}>
            <Select
              allowClear
              showSearch
              optionFilterProp="label"
              placeholder={gt('For All Countries')}
              options={this.state.countries}
            />
          </Form.Item>
        </Form>

        <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: 8 }}>
          <Space>
            <Button onClick={() => this.close()}>{gt(viewOnly ? 'Close' : 'Cancel')}</Button>
            {!viewOnly && (
              <Button type="primary" loading={saving} onClick={() => this.save()}>
                {editing ? gt('Save changes') : gt('Create work day')}
              </Button>
            )}
          </Space>
        </div>
      </Modal>
    );
  }
}

export default WorkDayForm;
