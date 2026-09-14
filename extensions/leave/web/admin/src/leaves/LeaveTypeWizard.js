/*
 * LeaveTypeWizard — a brand-new guided, step-by-step form for creating and
 * editing a Leave Type. It replaces the old generic "Steps" modal (IceStepForm)
 * for the LeaveType adapter only. The adapter is used purely as the data/save
 * backend (adapter.add / adapter.getRandomColor / adapter.doCustomValidation),
 * so all existing server behaviour is preserved.
 *
 * Each step asks a single, simple question group with plain-language labels and
 * helper text. Yes/No options are toggles, numbers use steppers, and the
 * carry-forward details only appear once carry-forward is switched on.
 */
import React from 'react';
import {
  Modal, Form, Input, InputNumber, Switch, Select, Steps, Button, Space, Typography, message,
} from 'antd';
import {
  InfoCircleOutlined, TeamOutlined,
} from '@ant-design/icons';

const { Text, Title } = Typography;

// Month options shared by the "waiting period" and similar selectors.
const MONTH_OPTIONS = [
  { value: '0', label: 'No waiting period' },
  ...Array.from({ length: 11 }, (_, i) => ({ value: String(i + 1), label: `${i + 1} month${i ? 's' : ''}` })),
  { value: '12', label: '1 year' },
];

const CARRY_AVAILABILITY_OPTIONS = [
  { value: '30', label: '1 month' }, { value: '60', label: '2 months' }, { value: '90', label: '3 months' },
  { value: '120', label: '4 months' }, { value: '150', label: '5 months' }, { value: '180', label: '6 months' },
  { value: '210', label: '7 months' }, { value: '240', label: '8 months' }, { value: '270', label: '9 months' },
  { value: '300', label: '10 months' }, { value: '330', label: '11 months' }, { value: '365', label: '1 year' },
  { value: '0', label: 'No limit' },
];

// Field definitions — `kind` drives how each value is rendered and how it is
// converted to/from the value the server stores.
//   yesno  -> stored as 'Yes' / 'No'        (rendered as a Switch)
//   bool01 -> stored as '1' / '0'            (rendered as a Switch)
//   number -> stored as a numeric string     (rendered as an InputNumber)
//   select -> stored verbatim                (rendered as a Select)
const FIELD_DEFS = {
  name: {
    kind: 'text', label: 'Leave name', required: true, placeholder: 'e.g. Annual Leave',
    extra: 'The name employees will see when they request time off.',
  },
  default_per_year: {
    kind: 'number', label: 'Days allowed per leave period', required: true, min: 0, step: 0.5, addonAfter: 'days',
    extra: 'Total days an employee can take in one leave period.',
  },
  leave_group: {
    kind: 'group', label: 'Leave group',
    extra: 'Limit this leave to a specific group of employees. Leave blank to offer it to everyone.',
  },

  employee_can_apply: {
    kind: 'yesno', label: 'Employees can request this leave themselves',
    extra: 'Turn this off to make it an admin/manager-assigned leave only.',
  },
  supervisor_leave_assign: {
    kind: 'yesno', label: 'Admins & managers can assign this leave',
    extra: 'Let an admin or manager grant this leave on an employee’s behalf.',
  },
  apply_beyond_current: {
    kind: 'yesno', label: 'Allow requests beyond the available balance',
    extra: 'Useful for sick or medical leave that may exceed the remaining balance.',
  },
  attachment_mandatory: {
    kind: 'yesno', label: 'Require a supporting document',
    extra: 'The employee must attach a file (e.g. a medical certificate) when requesting.',
  },
  reason_required: {
    kind: 'yesno', label: 'Require a reason',
    extra: 'The employee must enter a reason when requesting this leave.',
  },

  notice_period: {
    kind: 'number', label: 'Minimum notice', min: 0, step: 1, addonAfter: 'days',
    extra: 'How many days in advance an employee must request this leave. 0 means no minimum.',
  },
  leave_lock_period: {
    kind: 'select', label: 'Waiting period for new joiners', options: MONTH_OPTIONS,
    extra: 'New employees can’t request this leave until this long after they join.',
  },
  employee_leave_period: {
    kind: 'yesno', label: 'Start the leave period from each employee’s join date',
    extra: 'Use each employee’s joining date instead of the company-wide leave period.',
  },
  leave_accrue: {
    kind: 'yesno', label: 'Accrue the balance gradually',
    extra: 'Build the balance up day-by-day across the period instead of granting it all up front.',
  },
  propotionate_on_joined_date: {
    kind: 'yesno', label: 'Pro-rate for mid-period joiners',
    extra: 'New joiners receive a share of the days based on the time left in the period.',
  },

  carried_forward: {
    kind: 'yesno', label: 'Carry unused days to the next period',
    extra: 'Let any remaining balance roll over into the next leave period.',
  },
  carried_forward_percentage: {
    kind: 'number', label: 'Percentage carried over', min: 0, max: 100, step: 1, addonAfter: '%',
    extra: 'How much of the leftover balance rolls over (0–100).',
  },
  max_carried_forward_amount: {
    kind: 'number', label: 'Maximum days carried over', min: 0, step: 0.5, addonAfter: 'days',
    extra: 'Cap on the number of rolled-over days. 0 means no cap.',
  },
  carried_forward_leave_availability: {
    kind: 'select', label: 'Carried-over days remain usable for', options: CARRY_AVAILABILITY_OPTIONS,
    extra: 'How long carried-over days can still be used in the new period.',
  },

  sandwich_leave: {
    kind: 'bool01', label: 'Count holidays that fall between leave days',
    extra: 'Holidays or weekends sitting between two leave days are counted as leave.',
  },
  send_notification_emails: {
    kind: 'yesno', label: 'Send email notifications',
    extra: 'Email employees and approvers about requests for this leave type.',
  },
  notes: {
    kind: 'textarea', label: 'Internal notes',
    extra: 'Notes for admins only. Employees won’t see this.',
  },
};

// Sensible defaults for a brand-new leave type (merged under getDefaultValues()).
const NEW_DEFAULTS = {
  employee_can_apply: 'Yes',
  supervisor_leave_assign: 'Yes',
  apply_beyond_current: 'No',
  attachment_mandatory: 'No',
  reason_required: 'No',
  notice_period: '0',
  leave_lock_period: '0',
  employee_leave_period: 'No',
  leave_accrue: 'No',
  propotionate_on_joined_date: 'No',
  carried_forward: 'No',
  carried_forward_percentage: '100',
  max_carried_forward_amount: '0',
  carried_forward_leave_availability: '0',
  sandwich_leave: '0',
  send_notification_emails: 'Yes',
};

const STEPS = [
  {
    key: 'basics',
    title: 'Basics',
    icon: <InfoCircleOutlined />,
    heading: 'Name & allowance',
    blurb: 'Give the leave a clear name and decide how many days it grants.',
    fields: ['name', 'default_per_year'],
  },
  {
    key: 'access',
    title: 'Access',
    icon: <TeamOutlined />,
    heading: 'Access & requirements',
    blurb: 'Choose who can request this leave and what they need to provide.',
    fields: ['employee_can_apply', 'supervisor_leave_assign', 'apply_beyond_current', 'attachment_mandatory', 'reason_required'],
  },
];

// Columns still present in the DB but no longer edited in the simplified wizard
// (leave groups, per-employee leave periods, carry-forward, the timing/accrual
// "Rules" and the "Extras" options were all removed). Their stored values are
// preserved untouched on save.
const HIDDEN_COLUMNS = [
  'leave_group',
  'employee_leave_period',
  'carried_forward',
  'carried_forward_percentage',
  'max_carried_forward_amount',
  'carried_forward_leave_availability',
  // former "Rules" step
  'notice_period',
  'leave_lock_period',
  'leave_accrue',
  'propotionate_on_joined_date',
  // former "Extras" step
  'sandwich_leave',
  'send_notification_emails',
  'notes',
];

const isBlank = (v) => v === '' || v === null || v === undefined || v === 'NULL';

const toFormValue = (kind, raw) => {
  switch (kind) {
    case 'yesno': return raw === 'Yes' || raw === true;
    case 'bool01': return raw === '1' || raw === 1 || raw === true;
    case 'number': return isBlank(raw) ? undefined : Number(raw);
    // Coerce to string so the value matches the Select option values.
    case 'select': return isBlank(raw) ? undefined : String(raw);
    case 'group': return isBlank(raw) ? undefined : String(raw);
    default: return raw;
  }
};

const toServerValue = (kind, val) => {
  switch (kind) {
    case 'yesno': return val ? 'Yes' : 'No';
    case 'bool01': return val ? '1' : '0';
    case 'number': return isBlank(val) ? '0' : String(val);
    // Empty optional FK -> the string 'NULL', which the server stores as SQL NULL.
    case 'group': return isBlank(val) ? 'NULL' : String(val);
    default: return isBlank(val) ? '' : val;
  }
};

class LeaveTypeWizard extends React.Component {
  constructor(props) {
    super(props);
    this.formRef = React.createRef();
    this.editingObject = null;
    this.state = {
      visible: false,
      current: 0,
      viewOnly: false,
      saving: false,
    };
  }

  // Imperative API used by the adapter's renderForm().
  setViewOnly(viewOnly) {
    this.setState({ viewOnly: !!viewOnly });
  }

  show(object) {
    const base = { ...NEW_DEFAULTS, ...(object || {}) };
    this.editingObject = base;
    const values = {};
    Object.keys(FIELD_DEFS).forEach((name) => {
      values[name] = toFormValue(FIELD_DEFS[name].kind, base[name]);
    });
    this.setState({ visible: true, current: 0, saving: false }, () => {
      if (this.formRef.current) {
        this.formRef.current.resetFields();
        this.formRef.current.setFieldsValue(values);
      }
    });
  }

  close() {
    this.setState({ visible: false });
  }

  stepForField(fieldName) {
    return STEPS.findIndex((s) => s.fields.includes(fieldName));
  }

  next() {
    const fields = STEPS[this.state.current].fields;
    this.formRef.current.validateFields(fields)
      .then(() => this.setState((s) => ({ current: Math.min(s.current + 1, STEPS.length - 1) })))
      .catch(() => { /* stay on the step; antd highlights the offending field */ });
  }

  prev() {
    this.setState((s) => ({ current: Math.max(s.current - 1, 0) }));
  }

  async save() {
    const { adapter } = this.props;
    let values;
    try {
      values = await this.formRef.current.validateFields();
    } catch (err) {
      // Jump to the first step that contains an invalid field.
      const first = err && err.errorFields && err.errorFields[0];
      if (first && first.name && first.name[0]) {
        const stepIdx = this.stepForField(first.name[0]);
        if (stepIdx >= 0) this.setState({ current: stepIdx });
      }
      return;
    }

    const params = {};
    Object.keys(FIELD_DEFS).forEach((name) => {
      if (HIDDEN_COLUMNS.includes(name)) {
        // No longer edited in the UI — keep the stored value untouched (the
        // record already carries its server-format value, defaults for a new one).
        const stored = (this.editingObject || {})[name];
        params[name] = (stored === undefined || stored === null)
          ? toServerValue(FIELD_DEFS[name].kind, undefined)
          : String(stored);
        return;
      }
      params[name] = toServerValue(FIELD_DEFS[name].kind, values[name]);
    });

    // Preserve identity + the auto-assigned colour (column kept, not user-edited).
    if (this.editingObject && this.editingObject.id) {
      params.id = this.editingObject.id;
    }
    params.leave_color = (this.editingObject && this.editingObject.leave_color)
      || (adapter.getRandomColor ? adapter.getRandomColor(500, Math.floor(Math.random() * 500)) : '');

    const injected = adapter.forceInjectValuesBeforeSave
      ? adapter.forceInjectValuesBeforeSave(params) : params;
    const validationMsg = adapter.doCustomValidation ? adapter.doCustomValidation(injected) : null;
    if (validationMsg) {
      message.error(validationMsg, 5);
      return;
    }

    this.setState({ saving: true });
    adapter.add(
      injected,
      [],
      () => adapter.get([]),
      () => {
        this.setState({ saving: false });
        message.success(`Leave type ${params.id ? 'updated' : 'created'}`);
        this.close();
      },
      () => this.setState({ saving: false }),
    );
  }

  renderField(name) {
    const def = FIELD_DEFS[name];
    const rules = def.required
      ? [{ required: true, message: `${def.label} is required` }] : undefined;
    const common = {
      key: name, name, label: def.label, extra: def.extra, rules,
    };

    switch (def.kind) {
      case 'text':
        return <Form.Item {...common}><Input placeholder={def.placeholder} allowClear /></Form.Item>;
      case 'textarea':
        return <Form.Item {...common}><Input.TextArea rows={3} placeholder={def.placeholder} /></Form.Item>;
      case 'number':
        return (
          <Form.Item {...common}>
            <InputNumber
              min={def.min}
              max={def.max}
              step={def.step || 1}
              addonAfter={def.addonAfter}
              style={{ width: def.addonAfter ? 200 : 160 }}
            />
          </Form.Item>
        );
      case 'select':
        return <Form.Item {...common}><Select options={def.options} style={{ maxWidth: 320 }} /></Form.Item>;
      case 'yesno':
      case 'bool01':
        return (
          <Form.Item {...common} valuePropName="checked">
            <Switch checkedChildren="Yes" unCheckedChildren="No" />
          </Form.Item>
        );
      default:
        return null;
    }
  }

  renderStepBody(step) {
    return step.fields.map((f) => this.renderField(f));
  }

  render() {
    const {
      visible, current, viewOnly, saving,
    } = this.state;
    const { adapter } = this.props;
    const gt = adapter && adapter.gt ? (s) => adapter.gt(s) : (s) => s;
    const step = STEPS[current];
    const isLast = current === STEPS.length - 1;
    const editing = !!(this.editingObject && this.editingObject.id);

    return (
      <Modal
        open={visible}
        width={680}
        maskClosable={false}
        title={gt(viewOnly ? 'Leave Type' : (editing ? 'Edit Leave Type' : 'New Leave Type'))}
        onCancel={() => this.close()}
        footer={null}
        destroyOnClose={false}
      >
        <Steps
          size="small"
          current={current}
          onChange={(c) => this.setState({ current: c })}
          items={STEPS.map((s) => ({ title: s.title, icon: s.icon }))}
          style={{ marginBottom: 20 }}
        />

        <div style={{ marginBottom: 12 }}>
          <Title level={5} style={{ marginBottom: 2 }}>{gt(step.heading)}</Title>
          <Text type="secondary">{gt(step.blurb)}</Text>
        </div>

        <Form
          ref={this.formRef}
          layout="vertical"
          disabled={viewOnly}
          requiredMark="optional"
        >
          {/* All steps stay mounted so every value is collected on save; only
              the active step is visible. */}
          {STEPS.map((s, idx) => (
            <div key={s.key} style={{ display: idx === current ? 'block' : 'none' }}>
              {this.renderStepBody(s)}
            </div>
          ))}
        </Form>

        <div style={{
          display: 'flex', justifyContent: 'space-between', marginTop: 8,
        }}
        >
          <div>
            {current > 0 && <Button onClick={() => this.prev()}>{gt('Back')}</Button>}
          </div>
          <Space>
            <Button onClick={() => this.close()}>{gt('Cancel')}</Button>
            {!isLast && (
              <Button type="primary" onClick={() => this.next()}>{gt('Next')}</Button>
            )}
            {isLast && !viewOnly && (
              <Button type="primary" loading={saving} onClick={() => this.save()}>
                {editing ? gt('Save changes') : gt('Create leave type')}
              </Button>
            )}
          </Space>
        </div>
      </Modal>
    );
  }
}

export default LeaveTypeWizard;
