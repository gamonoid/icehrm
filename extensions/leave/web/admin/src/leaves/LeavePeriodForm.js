/*
 * LeavePeriodForm — a focused form for creating / editing a Leave Period.
 *
 * When an existing period is opened it shows how much it is used (leave days +
 * leave requests, fetched from a server action that caches the counts per
 * period for one day). If any employee leaves are linked to the period the
 * form is locked — the period's dates can no longer be changed, because doing
 * so would corrupt the leave balances already calculated against it.
 *
 * The adapter is used purely as the data/save backend (adapter.add).
 */
import React from 'react';
import {
  Modal, Form, Input, Select, Button, Space, Alert, Spin, Typography, message,
} from 'antd';
import { CalendarOutlined } from '@ant-design/icons';

const { Text } = Typography;

// Normalise a stored date ("YYYY-MM-DD" or "YYYY-MM-DD HH:MM:SS") to the value a
// native date input expects ("YYYY-MM-DD").
const toDateInput = (raw) => (raw ? String(raw).slice(0, 10) : undefined);

const plural = (n, word) => `${n} ${word}${Number(n) === 1 ? '' : 's'}`;

class LeavePeriodForm extends React.Component {
  constructor(props) {
    super(props);
    this.formRef = React.createRef();
    this.editingObject = null;
    this.state = {
      visible: false,
      viewOnly: false,
      saving: false,
      loadingStats: false,
      stats: null,
      countries: [],
    };
  }

  componentDidMount() {
    if (this.props.adapter && this.props.adapter.countryBasedLeavePeriods) {
      this.loadCountries();
    }
  }

  setViewOnly(viewOnly) {
    this.setState({ viewOnly: !!viewOnly });
  }

  show(object) {
    this.editingObject = object || {};
    const isEdit = !!(object && object.id);
    const values = {
      name: object ? object.name : undefined,
      date_start: object ? toDateInput(object.date_start) : undefined,
      date_end: object ? toDateInput(object.date_end) : undefined,
      country: object && object.country ? String(object.country) : undefined,
    };
    this.setState({
      visible: true, saving: false, stats: null, loadingStats: isEdit,
    }, () => {
      if (this.formRef.current) {
        this.formRef.current.resetFields();
        this.formRef.current.setFieldsValue(values);
      }
    });
    if (isEdit && this.props.adapter.fetchLeavePeriodStats) {
      this.props.adapter.fetchLeavePeriodStats(object.id)
        .then((stats) => this.setState({ stats, loadingStats: false }));
    }
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
        date_start: vals.date_start || '',
        date_end: vals.date_end || '',
      };
      if (adapter.countryBasedLeavePeriods) {
        params.country = vals.country || 'NULL';
      }
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
          message.success(`Leave period ${params.id ? 'updated' : 'created'}`);
          this.close();
        },
        () => this.setState({ saving: false }),
      );
    }).catch(() => { /* validation errors shown inline */ });
  }

  render() {
    const {
      visible, viewOnly, saving, stats, loadingStats,
    } = this.state;
    const { adapter } = this.props;
    const gt = adapter && adapter.gt ? (s) => adapter.gt(s) : (s) => s;
    const editing = !!(this.editingObject && this.editingObject.id);
    const hasLeaves = !!(stats && stats.hasLeaves);
    const locked = hasLeaves || viewOnly;

    return (
      <Modal
        open={visible}
        width={560}
        maskClosable={false}
        title={gt(editing ? 'Edit Leave Period' : 'New Leave Period')}
        onCancel={() => this.close()}
        footer={null}
      >
        {editing && (loadingStats ? (
          <div style={{ marginBottom: 16 }}>
            <Spin size="small" />
            <Text type="secondary" style={{ marginLeft: 8 }}>{gt('Checking leave usage…')}</Text>
          </div>
        ) : (stats && (
          <div style={{
            marginBottom: 16, padding: '10px 14px', borderRadius: 8, background: 'rgba(22,119,255,0.06)',
          }}
          >
            <Space size="large">
              <span>
                <CalendarOutlined style={{ marginRight: 6 }} />
                <Text strong>{plural(stats.days, 'leave day')}</Text>
              </span>
              <Text type="secondary">{plural(stats.requests, 'leave request')}</Text>
            </Space>
          </div>
        )))}

        {hasLeaves && (
          <Alert
            type="warning"
            showIcon
            style={{ marginBottom: 16 }}
            message={gt('This leave period can’t be edited')}
            description={gt(
              'Employee leaves are already linked to this leave period, so its details '
              + 'are locked to keep the existing leave balances accurate.',
            )}
          />
        )}

        <Form ref={this.formRef} layout="vertical" disabled={locked}>
          <Form.Item name="name" label={gt('Name')} rules={[{ required: true, message: gt('Name is required') }]}>
            <Input placeholder="e.g. Year 2026" />
          </Form.Item>
          <Form.Item name="date_start" label={gt('Period Start')} rules={[{ required: true, message: gt('Start date is required') }]}>
            <Input type="date" />
          </Form.Item>
          <Form.Item name="date_end" label={gt('Period End')} rules={[{ required: true, message: gt('End date is required') }]}>
            <Input type="date" />
          </Form.Item>
          {adapter.countryBasedLeavePeriods && (
            <Form.Item name="country" label={gt('Country')}>
              <Select
                allowClear
                showSearch
                optionFilterProp="label"
                placeholder={gt('For All Countries')}
                options={this.state.countries}
              />
            </Form.Item>
          )}
        </Form>

        <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: 8 }}>
          <Space>
            <Button onClick={() => this.close()}>{gt(locked ? 'Close' : 'Cancel')}</Button>
            {!locked && (
              <Button type="primary" loading={saving} onClick={() => this.save()}>
                {editing ? gt('Save changes') : gt('Create period')}
              </Button>
            )}
          </Space>
        </div>
      </Modal>
    );
  }
}

export default LeavePeriodForm;
