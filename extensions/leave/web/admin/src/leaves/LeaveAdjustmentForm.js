/*
 * LeaveAdjustmentForm — add/edit a leave balance adjustment (LeaveStartingBalance).
 *
 * The amount field is always a positive decimal; an Increase / Decrease toggle
 * decides the direction, and the sign is applied to the stored value (decrease
 * is saved as a negative number). The +/- is never shown in the amount input.
 *
 * The adapter is used purely as the data/save backend (adapter.add).
 */
import React from 'react';
import {
  Modal, Form, Select, InputNumber, Input, Segmented, Button, Space, Typography, message,
} from 'antd';
import { PlusCircleOutlined, MinusCircleOutlined } from '@ant-design/icons';

const { Text } = Typography;

class LeaveAdjustmentForm extends React.Component {
  constructor(props) {
    super(props);
    this.formRef = React.createRef();
    this.editingObject = null;
    this.state = {
      visible: false,
      viewOnly: false,
      saving: false,
      direction: 'increase',
      leaveTypes: [],
      employees: [],
      periods: [],
    };
  }

  componentDidMount() {
    this.loadOptions();
  }

  setViewOnly(viewOnly) {
    this.setState({ viewOnly: !!viewOnly });
  }

  dataBase() {
    const { adapter } = this.props;
    return adapter && adapter.moduleRelativeURL
      ? adapter.moduleRelativeURL.replace('service.php', 'data.php') : '';
  }

  scope() {
    const { adapter } = this.props;
    return adapter && adapter.spaModuleGroup
      ? `&mg=${encodeURIComponent(adapter.spaModuleGroup)}&mn=${encodeURIComponent(adapter.spaModuleName)}` : '';
  }

  fetchList(table, columns, filter) {
    const base = this.dataBase();
    if (!base) return Promise.resolve([]);
    const ft = filter ? `&ft=${encodeURIComponent(JSON.stringify(filter))}` : '';
    return fetch(
      `${base}?t=${table}&sm=${encodeURIComponent('{}')}`
        + `&cl=${encodeURIComponent(JSON.stringify(columns))}`
        + `${ft}&iDisplayStart=0&iDisplayLength=5000&version=v2${this.scope()}`,
      { method: 'POST', credentials: 'same-origin' },
    ).then((r) => r.json()).then((j) => j.objects || []).catch(() => []);
  }

  loadOptions() {
    this.fetchList('LeaveType', ['id', 'name']).then((rows) => this.setState({
      leaveTypes: rows.map((r) => ({ value: String(r.id), label: r.name })),
    }));
    this.fetchList('LeavePeriod', ['id', 'name']).then((rows) => this.setState({
      periods: rows.map((r) => ({ value: String(r.id), label: r.name })),
    }));
    this.fetchList('Employee', ['id', 'first_name', 'last_name'], { status: 'Active' }).then((rows) => this.setState({
      employees: rows.map((r) => ({ value: String(r.id), label: `${r.first_name} ${r.last_name}` })),
    }));
  }

  show(object) {
    this.editingObject = object || {};
    const amt = object && object.amount !== undefined && object.amount !== null && object.amount !== ''
      ? parseFloat(object.amount) : undefined;
    const direction = (typeof amt === 'number' && amt < 0) ? 'decrease' : 'increase';
    const values = {
      leave_type: object && object.leave_type ? String(object.leave_type) : undefined,
      employee: object && object.employee ? String(object.employee) : undefined,
      leave_period: object && object.leave_period ? String(object.leave_period) : undefined,
      amount: (typeof amt === 'number') ? Math.abs(amt) : undefined,
      note: object ? object.note : undefined,
    };
    this.setState({ visible: true, saving: false, direction }, () => {
      if (this.formRef.current) {
        this.formRef.current.resetFields();
        this.formRef.current.setFieldsValue(values);
      }
    });
  }

  close() {
    this.setState({ visible: false });
  }

  save() {
    const { adapter } = this.props;
    const { direction } = this.state;
    this.formRef.current.validateFields().then((vals) => {
      const magnitude = Math.abs(Number(vals.amount) || 0);
      const signed = direction === 'decrease' ? -magnitude : magnitude;
      const params = {
        leave_type: vals.leave_type,
        employee: vals.employee,
        leave_period: vals.leave_period,
        amount: String(signed),
        note: vals.note || '',
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
          message.success(`Leave adjustment ${params.id ? 'updated' : 'saved'}`);
          this.close();
        },
        () => this.setState({ saving: false }),
      );
    }).catch(() => { /* inline validation */ });
  }

  render() {
    const {
      visible, viewOnly, saving, direction, leaveTypes, employees, periods,
    } = this.state;
    const { adapter } = this.props;
    const gt = adapter && adapter.gt ? (s) => adapter.gt(s) : (s) => s;
    const editing = !!(this.editingObject && this.editingObject.id);
    const isDecrease = direction === 'decrease';

    return (
      <Modal
        open={visible}
        width={480}
        maskClosable={false}
        title={gt(editing ? 'Edit Leave Adjustment' : 'New Leave Adjustment')}
        onCancel={() => this.close()}
        footer={null}
      >
        <Form ref={this.formRef} layout="vertical" disabled={viewOnly}>
          <Form.Item name="leave_type" label={gt('Leave Type')} rules={[{ required: true, message: gt('Leave type is required') }]}>
            <Select showSearch optionFilterProp="label" options={leaveTypes} placeholder={gt('Select leave type')} />
          </Form.Item>
          <Form.Item name="employee" label={gt('Employee')} rules={[{ required: true, message: gt('Employee is required') }]}>
            <Select showSearch optionFilterProp="label" options={employees} placeholder={gt('Select employee')} />
          </Form.Item>
          <Form.Item name="leave_period" label={gt('Leave Period')} rules={[{ required: true, message: gt('Leave period is required') }]}>
            <Select showSearch optionFilterProp="label" options={periods} placeholder={gt('Select leave period')} />
          </Form.Item>

          <Form.Item label={gt('Adjustment')}>
            <Segmented
              block
              value={direction}
              onChange={(val) => this.setState({ direction: val })}
              options={[
                { label: gt('Increase leave balance'), value: 'increase', icon: <PlusCircleOutlined /> },
                { label: gt('Decrease leave balance'), value: 'decrease', icon: <MinusCircleOutlined /> },
              ]}
            />
          </Form.Item>

          <Form.Item
            name="amount"
            label={gt('Amount (days)')}
            rules={[{ required: true, message: gt('Amount is required') }]}
            extra={isDecrease
              ? gt('This many leave days will be deducted from the balance.')
              : gt('This many leave days will be added to the balance.')}
          >
            <InputNumber
              min={0}
              step={0.5}
              style={{ width: 200 }}
              addonBefore={isDecrease ? '−' : '+'}
              placeholder="0"
            />
          </Form.Item>

          <Form.Item name="note" label={gt('Note')}>
            <Input.TextArea rows={3} />
          </Form.Item>
        </Form>

        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 8 }}>
          <Text type="secondary" style={{ fontSize: 12.5 }}>
            {isDecrease ? gt('Saved as a negative adjustment') : gt('Saved as a positive adjustment')}
          </Text>
          <Space>
            <Button onClick={() => this.close()}>{gt('Cancel')}</Button>
            {!viewOnly && (
              <Button type="primary" loading={saving} onClick={() => this.save()}>
                {editing ? gt('Save changes') : gt('Save adjustment')}
              </Button>
            )}
          </Space>
        </div>
      </Modal>
    );
  }
}

export default LeaveAdjustmentForm;
