/**
 * ApprovalsPanel — the "Approvals" tab of the employee profile, shown when any
 * module has multi-level approvals enabled.
 *
 * The Manager (supervisor) is the read-only initial approver (edited via the
 * employee form). approver1/2/3 form the rest of the chain and are managed here
 * one level at a time via a dedicated API:
 *   GET  employees/{id}/approvers  -> { manager, approver1, approver2, approver3 }
 *                                      each level resolved to { id, name, image }
 *   POST employees/{id}/approvers  -> { approver1, approver2, approver3 } ids
 *
 * UX rules:
 *  - Empty chain shows an "Add first approver" button; each set level shows the
 *    approver's name + avatar with Edit / Remove actions; the next "Add …"
 *    button only appears once the level above it is set.
 *  - Removal is blocked when it would orphan the level directly below it — a
 *    level can be removed only when the level beneath it is empty, EXCEPT when
 *    the level above it is already empty (a legacy gap), which allows cleaning
 *    up orphaned middle approvers.
 *  - An employee cannot be their own approver, the Manager cannot be repeated as
 *    an approver, and the same employee cannot appear twice — these are filtered
 *    out of the picker and re-validated server side.
 */
import React from 'react';
import {
  Card, Button, message, Space, Typography, Alert, Select, Tag, Spin, Popconfirm, Tooltip,
} from 'antd';
import {
  SolutionOutlined, PlusOutlined, EditOutlined, DeleteOutlined, CrownOutlined,
} from '@ant-design/icons';
import LazyAvatar from '../../../../components/LazyAvatar';

const { Text } = Typography;

const LEVELS = [
  { field: 'approver1', label: 'First Level Approver', addLabel: 'Add first approver' },
  { field: 'approver2', label: 'Second Level Approver', addLabel: 'Add second approver' },
  { field: 'approver3', label: 'Third Level Approver', addLabel: 'Add third approver' },
];

class ApprovalsPanel extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true,
      saving: false,
      searching: false,
      manager: null,
      approvers: [null, null, null],
      editingLevel: -1, // index of the level currently being edited, -1 = none
      options: [], // search options for the open picker
      pickValue: undefined,
    };
  }

  componentDidMount() {
    this.load();
  }

  apiClient = () => (this.props.adapter && this.props.adapter.apiClient) || this.props.apiClient;

  gt = (t) => (this.props.adapter && this.props.adapter.gt ? this.props.adapter.gt(t) : t);

  employeeId = () => this.props.employee && this.props.employee.id;

  applyChain = (data, extra) => {
    this.setState({
      manager: data.manager || null,
      approvers: [data.approver1 || null, data.approver2 || null, data.approver3 || null],
      ...(extra || {}),
    });
  };

  load = () => {
    const client = this.apiClient();
    const id = this.employeeId();
    if (!client || !id) {
      this.setState({ loading: false });
      return;
    }
    this.setState({ loading: true });
    client.get(`employees/${id}/approvers`)
      .then((res) => {
        // The url-based API wrapper returns the raw IceResponse data (no
        // {status,data} envelope), so the chain is at res.data directly.
        const data = (res && res.data) || {};
        this.applyChain(data, { loading: false });
      })
      .catch(() => this.setState({ loading: false }));
  };

  // ids that may not be picked as the approver for `level`: the employee
  // themselves, the Manager, and any other level's approver.
  excludedIds = (level) => {
    const ids = new Set();
    const self = this.employeeId();
    if (self) ids.add(String(self));
    if (this.state.manager && this.state.manager.id) ids.add(String(this.state.manager.id));
    this.state.approvers.forEach((a, i) => {
      if (i !== level && a && a.id) ids.add(String(a.id));
    });
    return ids;
  };

  search = (term, level) => {
    const client = this.apiClient();
    if (!client || !term) return;
    this.setState({ searching: true });
    client.get(`employees&limit=25&search=${encodeURIComponent(term)}`)
      .then((res) => {
        const list = (res && res.data && res.data.data) || [];
        const excluded = this.excludedIds(level);
        const opts = list
          .filter((x) => !excluded.has(String(x.id)))
          .map((x) => ({
            value: String(x.id),
            label: `${x.first_name || ''} ${x.last_name || ''}`.trim() || `#${x.id}`,
            image: x.image || null,
          }));
        this.setState({ options: opts, searching: false });
      })
      .catch(() => this.setState({ searching: false }));
  };

  startEdit = (level) => {
    const a = this.state.approvers[level];
    // Seed the picker with the current approver so it shows while editing.
    const options = a && a.id && !a.missing
      ? [{ value: String(a.id), label: a.name, image: a.image || null }]
      : [];
    this.setState({ editingLevel: level, options, pickValue: undefined });
  };

  cancelEdit = () => this.setState({ editingLevel: -1, options: [], pickValue: undefined });

  // Build the {approver1,approver2,approver3} payload from current state, with
  // an optional { levelIndex: id|null } override for the level being changed.
  buildPayload = (overrides) => {
    const payload = {};
    LEVELS.forEach((lv, i) => {
      let id = this.state.approvers[i] && this.state.approvers[i].id
        ? this.state.approvers[i].id : null;
      if (overrides && Object.prototype.hasOwnProperty.call(overrides, i)) {
        id = overrides[i];
      }
      payload[lv.field] = id || null;
    });
    return payload;
  };

  persist = (overrides, successMsg) => {
    const client = this.apiClient();
    const id = this.employeeId();
    if (!client || !id) return;
    this.setState({ saving: true });
    client.post(`employees/${id}/approvers`, this.buildPayload(overrides))
      .then((res) => {
        // A 200 with an embedded error payload (some clients don't throw).
        if (res && res.data && res.data.error) {
          message.error(res.data.error[0]?.[0]?.message || this.gt('Could not save approvers'), 5);
          this.setState({ saving: false });
          return;
        }
        // Raw IceResponse data (no {status,data} envelope) — the chain is res.data.
        const data = (res && res.data) || {};
        message.success(this.gt(successMsg));
        this.applyChain(data, {
          saving: false, editingLevel: -1, options: [], pickValue: undefined,
        });
        if (this.props.onSaved) this.props.onSaved();
      })
      .catch((error) => {
        message.error(
          error?.response?.data?.error?.[0]?.[0]?.message || this.gt('Could not save approvers'),
          5,
        );
        this.setState({ saving: false });
      });
  };

  choose = (level, value) => {
    if (!value) return;
    this.persist({ [level]: parseInt(value, 10) }, 'Approver updated.');
  };

  remove = (level) => {
    this.persist({ [level]: null }, 'Approver removed.');
  };

  isSet = (i) => (i >= 0 && i < 3 ? !!this.state.approvers[i] : false);

  // A level can be removed only when the level directly below it is empty,
  // except when the level above it is already empty (legacy gap cleanup).
  canDelete = (level) => {
    if (level === 0) return !this.isSet(1);
    if (level === 1) return !this.isSet(2) || !this.isSet(0);
    return true; // level 2 (last) — nothing below it
  };

  // Show an "Add" button for an empty level only when it is reachable: the top
  // level, or when a neighbouring level is set (so gaps can be filled).
  canAdd = (level) => {
    if (this.isSet(level)) return false;
    if (level === 0) return true;
    return this.isSet(level - 1) || this.isSet(level + 1);
  };

  deleteReason = (level) => {
    if (level === 0) return this.gt('Remove the Second Level Approver first.');
    if (level === 1) return this.gt('Remove the Third Level Approver first.');
    return '';
  };

  renderManager() {
    const m = this.state.manager;
    return (
      <Card size="small" style={{ marginBottom: 16 }} bodyStyle={{ padding: '10px 14px' }}>
        <Space align="center" size={12}>
          <CrownOutlined style={{ fontSize: 18, color: '#faad14' }} />
          <div>
            <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>
              {this.gt('Initial Approver (Manager)')}
            </Text>
            {m
              ? (
                <Space size={8} style={{ marginTop: 4 }}>
                  <LazyAvatar src={m.image} name={m.name} />
                  <Text strong>{m.name}</Text>
                </Space>
              )
              : <Text type="secondary">{this.gt('Not set — set the Manager via Edit Employee')}</Text>}
          </div>
        </Space>
      </Card>
    );
  }

  renderPicker(i) {
    return (
      <Space wrap>
        <Select
          showSearch
          autoFocus
          style={{ width: 320 }}
          placeholder={this.gt('Type to search employees…')}
          value={this.state.pickValue}
          onChange={(v) => this.setState({ pickValue: v })}
          onSelect={(v) => this.choose(i, v)}
          onSearch={(term) => this.search(term, i)}
          filterOption={false}
          loading={this.state.searching || this.state.saving}
          disabled={this.state.saving}
          optionLabelProp="label"
          notFoundContent={this.state.searching ? this.gt('Searching…') : this.gt('Type to search employees')}
        >
          {this.state.options.map((o) => (
            <Select.Option key={o.value} value={o.value} label={o.label}>
              <Space size={8}>
                <LazyAvatar src={o.image} name={o.label} />
                {o.label}
              </Space>
            </Select.Option>
          ))}
        </Select>
        <Button onClick={this.cancelEdit} disabled={this.state.saving}>{this.gt('Cancel')}</Button>
      </Space>
    );
  }

  renderApproverCard(i, a) {
    const deletable = this.canDelete(i);
    return (
      <Card size="small" bodyStyle={{ padding: '8px 12px' }}>
        <Space style={{ width: '100%', justifyContent: 'space-between' }}>
          <Space size={8}>
            <LazyAvatar src={a.image} name={a.name} />
            <span>
              <Text strong>{a.name}</Text>
              {a.missing && (
                <Tag color="warning" style={{ marginLeft: 8 }}>{this.gt('Employee not found')}</Tag>
              )}
            </span>
          </Space>
          <Space size={4}>
            <Button
              size="small"
              icon={<EditOutlined />}
              onClick={() => this.startEdit(i)}
              disabled={this.state.saving}
            >
              {this.gt('Edit')}
            </Button>
            {deletable ? (
              <Popconfirm
                title={this.gt('Remove this approver?')}
                onConfirm={() => this.remove(i)}
                okText={this.gt('Remove')}
                cancelText={this.gt('Cancel')}
              >
                <Button size="small" danger icon={<DeleteOutlined />} disabled={this.state.saving} />
              </Popconfirm>
            ) : (
              <Tooltip title={this.deleteReason(i)}>
                {/* wrapper span so the tooltip works on a disabled button */}
                <span><Button size="small" danger icon={<DeleteOutlined />} disabled /></span>
              </Tooltip>
            )}
          </Space>
        </Space>
      </Card>
    );
  }

  renderAddButton(i) {
    return (
      <Button
        type="dashed"
        icon={<PlusOutlined />}
        onClick={() => this.startEdit(i)}
        disabled={this.state.saving}
      >
        {this.gt(LEVELS[i].addLabel)}
      </Button>
    );
  }

  renderLevel(i) {
    const lv = LEVELS[i];
    const a = this.state.approvers[i];
    const editing = this.state.editingLevel === i;

    let body;
    if (editing) {
      body = this.renderPicker(i);
    } else if (a) {
      body = this.renderApproverCard(i, a);
    } else if (this.canAdd(i)) {
      body = this.renderAddButton(i);
    } else {
      body = (
        <Text type="secondary" style={{ fontSize: 12 }}>
          {this.gt('Add the previous approver first.')}
        </Text>
      );
    }

    return (
      <div key={lv.field} style={{ marginBottom: 14 }}>
        <Text type="secondary" style={{ fontSize: 12, display: 'block', marginBottom: 4 }}>
          {this.gt(lv.label)}
        </Text>
        {body}
      </div>
    );
  }

  render() {
    const title = <Space><SolutionOutlined /> {this.gt('Multi Level Approvers')}</Space>;
    if (this.state.loading) {
      return (
        <Card title={title} style={{ width: '100%' }}>
          <div style={{ textAlign: 'center', padding: 24 }}><Spin /></div>
        </Card>
      );
    }
    return (
      <Card title={title} style={{ width: '100%' }}>
        <Alert
          type="info"
          showIcon
          style={{ marginBottom: 16 }}
          message={this.gt('The Manager is the initial approver and is set from the employee form (Edit Employee → Manager). The approvers below form the rest of the approval chain.')}
        />
        {this.renderManager()}
        <div style={{ maxWidth: 520 }}>
          {LEVELS.map((lv, i) => this.renderLevel(i))}
        </div>
      </Card>
    );
  }
}

export default ApprovalsPanel;
