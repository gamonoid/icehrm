import React, { useEffect, useState } from 'react';
import {
  Modal, Descriptions, Tag, Avatar, Button, Typography, Divider, Spin, Image, Timeline,
  Form, Input, Select, InputNumber, Dropdown, message, theme,
} from 'antd';
import {
  UserOutlined, FileImageOutlined, DownloadOutlined, HistoryOutlined, DownOutlined,
} from '@ant-design/icons';
import { MUI_DARK } from './theme';

const { Text, Title } = Typography;

const STATUS_COLOR = {
  Pending: 'gold', Approved: 'green', Rejected: 'red', Paid: 'blue', Cancelled: 'default',
};

// Right-hand, vertically scrollable status-change log. Entries arrive newest-first
// from the backend (expense/{id}/logs), so the latest change is at the top.
function LogSidebar({ logs, loading, token }) {
  return (
    <div style={{
      width: 260,
      flexShrink: 0,
      borderLeft: `1px solid ${token.colorBorderSecondary}`,
      paddingLeft: 16,
      display: 'flex',
      flexDirection: 'column',
      minHeight: 0,
    }}
    >
      <Text strong style={{ marginBottom: 12 }}>
        <HistoryOutlined style={{ marginRight: 6 }} />
        Status History
      </Text>
      <div style={{
        overflowY: 'auto', flex: 1, maxHeight: 460, paddingRight: 4, paddingTop: 6,
      }}
      >
        {loading ? (
          <div style={{ textAlign: 'center', padding: 24 }}><Spin size="small" /></div>
        ) : (!logs || logs.length === 0 ? (
          <Text type="secondary" style={{ fontSize: 13 }}>No status changes yet.</Text>
        ) : (
          <Timeline
            items={logs.map((l) => ({
              children: (
                <div>
                  <div style={{ fontWeight: 600, fontSize: 13 }}>
                    {`${l.status_from || ''} → ${l.status_to || ''}`}
                  </div>
                  <div style={{ color: token.colorTextTertiary, fontSize: 12 }}>{l.time}</div>
                  {l.note && l.note.trim() ? (
                    <div style={{ fontSize: 12, marginTop: 2 }}>{l.note.trim()}</div>
                  ) : null}
                </div>
              ),
            }))}
          />
        ))}
      </div>
    </div>
  );
}

/**
 * Unified expense dialog with a status-history sidebar. Three modes:
 *  - 'view'     : read-only detail (admin/manager, and employees on non-editable rows)
 *  - 'edit'     : editable form + Save (owner / admin)
 *  - 'resubmit' : editable form + Re-submit -> sets a rejected expense back to Pending
 * A new, expense-only component — it does not touch the shared form/modal components.
 */
export default function ExpenseDialog({
  open, onClose, rec, shellConfig, mode = 'view', onSaved, statusOptionsFor,
}) {
  const { token } = theme.useToken();
  const isDark = token.colorBgContainer === MUI_DARK.paper;
  const [detail, setDetail] = useState(null);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [values, setValues] = useState({});
  // Status-change (view dialog dropdown): the chosen target + its reason prompt.
  const [statusTarget, setStatusTarget] = useState(null);
  const [statusReason, setStatusReason] = useState('');
  const [statusSaving, setStatusSaving] = useState(false);

  const editable = mode === 'edit' || mode === 'resubmit';

  const loadDetail = (withSpinner) => {
    if (withSpinner) { setLoading(true); }
    return fetch(`${shellConfig.restApiBase}expense/${rec.id}/detail`, {
      headers: { Authorization: `Bearer ${shellConfig.token}` },
      credentials: 'same-origin',
    })
      .then((r) => r.json())
      .then((d) => {
        if (d && !d.error) {
          setDetail(d);
          setValues({
            category: d.category,
            expense_date: d.expense_date,
            payment_method: d.payment_method,
            payee: d.payee,
            currency: d.currency,
            amount: d.amount,
            transaction_no: d.transaction_no,
            notes: d.notes,
          });
        } else { setDetail({}); }
      })
      .catch(() => setDetail({}))
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    if (!open || !rec) { setDetail(null); setValues({}); return; }
    setDetail(null); setStatusTarget(null); setStatusReason('');
    loadDetail(true);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [open, rec, shellConfig]);

  if (!rec) return null;

  const status = (detail && detail.status) || rec.status || '—';
  const attachments = (detail && detail.attachments) || [];
  const logs = (detail && detail.logs) || [];
  const options = (detail && detail.options) || {};
  const employeeName = (detail && detail.employee_name) || rec.employee || 'Employee';
  const heroBg = isDark ? 'rgba(25,118,210,0.14)' : 'rgba(25,118,210,0.06)';
  const setV = (k, v) => setValues((prev) => ({ ...prev, [k]: v }));

  const doSave = () => {
    const endpoint = mode === 'resubmit' ? 'resubmit' : 'update';
    setSaving(true);
    fetch(`${shellConfig.restApiBase}expense/${rec.id}/${endpoint}`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${shellConfig.token}`, 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(values),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d && d.error) {
          const msg = d.error[0] && d.error[0][0] ? d.error[0][0].message : 'Could not save';
          message.error(msg, 5);
          return;
        }
        message.success(mode === 'resubmit' ? 'Expense re-submitted' : 'Expense saved');
        if (onSaved) onSaved();
        onClose();
      })
      .catch(() => message.error('Could not save', 5))
      .finally(() => setSaving(false));
  };

  // Status change from the view dialog (admin/manager). A reason is mandatory and
  // all rules are enforced by the backend; on success we refresh in place so the
  // new status + log entry show immediately.
  const submitStatusChange = () => {
    if (!statusTarget || !statusReason.trim()) return;
    setStatusSaving(true);
    fetch(`${shellConfig.restApiBase}expense/${rec.id}/status`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${shellConfig.token}`, 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ status: statusTarget.value, reason: statusReason }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d && d.error) {
          const msg = d.error[0] && d.error[0][0] ? d.error[0][0].message : 'Could not change status';
          message.error(msg, 5);
          return;
        }
        message.success('Status updated');
        setStatusTarget(null); setStatusReason('');
        if (onSaved) onSaved();
        loadDetail(false);
      })
      .catch(() => message.error('Could not change status', 5))
      .finally(() => setStatusSaving(false));
  };

  const statusOpts = (!editable && typeof statusOptionsFor === 'function' && detail && detail.status)
    ? statusOptionsFor(detail.status) : [];

  const footer = editable
    ? [
      <Button key="cancel" onClick={onClose}>Cancel</Button>,
      <Button key="save" type="primary" loading={saving} onClick={doSave}>
        {mode === 'resubmit' ? 'Re-submit' : 'Save'}
      </Button>,
    ]
    : [
      <Button key="close" onClick={onClose}>Close</Button>,
      statusOpts.length > 0 ? (
        <Dropdown
          key="status"
          trigger={['click']}
          menu={{
            items: statusOpts.map((o) => ({ key: String(o.value), label: o.label })),
            onClick: ({ key }) => {
              setStatusReason('');
              setStatusTarget(statusOpts.find((o) => String(o.value) === String(key)) || null);
            },
          }}
        >
          <Button type="primary">
            Change Status
            <DownOutlined />
          </Button>
        </Dropdown>
      ) : null,
    ];

  const currencyLabel = (detail && detail.currency_name) || rec.currency || '';

  return (
    <>
    <Modal
      open={open}
      onCancel={onClose}
      title={null}
      width={840}
      footer={footer}
      styles={{ body: { paddingTop: 8 } }}
    >
      <div style={{ display: 'flex', gap: 20 }}>
        {/* Left: details / form */}
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 14, marginBottom: 16 }}>
            <Avatar size={52} src={rec.image || undefined} icon={<UserOutlined />} style={{ flexShrink: 0 }} />
            <div style={{ flex: 1, minWidth: 0 }}>
              <Title level={5} style={{ margin: 0 }} ellipsis>{employeeName}</Title>
              <Text type="secondary" style={{ fontSize: 13 }}>
                {(detail && detail.category_name) || rec.category || 'Expense'}
              </Text>
            </div>
            <Tag color={STATUS_COLOR[status] || 'default'} style={{ fontWeight: 600, marginInlineEnd: 0 }}>{status}</Tag>
          </div>

          {loading ? (
            <div style={{ textAlign: 'center', padding: 40 }}><Spin /></div>
          ) : editable ? (
            <Form layout="vertical" size="small">
              <div style={{ display: 'flex', gap: 12 }}>
                <Form.Item label="Amount" style={{ flex: 1 }} required>
                  <InputNumber
                    style={{ width: '100%' }}
                    value={values.amount}
                    onChange={(v) => setV('amount', v)}
                    stringMode
                  />
                </Form.Item>
                <Form.Item label="Currency" style={{ width: 140 }}>
                  <Select
                    showSearch
                    optionFilterProp="label"
                    value={values.currency}
                    onChange={(v) => setV('currency', v)}
                    options={(options.currencies || []).map((o) => ({ value: o.id, label: o.name }))}
                  />
                </Form.Item>
              </div>
              <div style={{ display: 'flex', gap: 12 }}>
                <Form.Item label="Category" style={{ flex: 1 }}>
                  <Select
                    showSearch
                    optionFilterProp="label"
                    value={values.category}
                    onChange={(v) => setV('category', v)}
                    options={(options.categories || []).map((o) => ({ value: o.id, label: o.name }))}
                  />
                </Form.Item>
                <Form.Item label="Payment Method" style={{ flex: 1 }}>
                  <Select
                    showSearch
                    optionFilterProp="label"
                    value={values.payment_method}
                    onChange={(v) => setV('payment_method', v)}
                    options={(options.paymentMethods || []).map((o) => ({ value: o.id, label: o.name }))}
                  />
                </Form.Item>
              </div>
              <div style={{ display: 'flex', gap: 12 }}>
                <Form.Item label="Expense Date" style={{ flex: 1 }}>
                  <input
                    type="date"
                    value={values.expense_date || ''}
                    onChange={(e) => setV('expense_date', e.target.value)}
                    style={{
                      width: '100%',
                      height: 24,
                      padding: '0 8px',
                      borderRadius: 6,
                      border: `1px solid ${token.colorBorder}`,
                      background: token.colorBgContainer,
                      color: token.colorText,
                      colorScheme: isDark ? 'dark' : 'light',
                    }}
                  />
                </Form.Item>
                <Form.Item label="Payee / Merchant" style={{ flex: 1 }}>
                  <Input value={values.payee || ''} onChange={(e) => setV('payee', e.target.value)} />
                </Form.Item>
              </div>
              <Form.Item label="Transaction / Ref No" style={{ marginBottom: 12 }}>
                <Input value={values.transaction_no || ''} onChange={(e) => setV('transaction_no', e.target.value)} />
              </Form.Item>
              <Form.Item label="Notes" style={{ marginBottom: 4 }}>
                <Input.TextArea rows={2} value={values.notes || ''} onChange={(e) => setV('notes', e.target.value)} />
              </Form.Item>
            </Form>
          ) : (
            <>
              <div style={{
                background: heroBg, borderRadius: 12, padding: '16px 20px', marginBottom: 18,
                display: 'flex', alignItems: 'baseline', gap: 8,
              }}
              >
                <span style={{ fontSize: 30, fontWeight: 700, color: token.colorText, lineHeight: 1 }}>
                  {rec.amount != null ? rec.amount : (detail && detail.amount) || '—'}
                </span>
                <span style={{ fontSize: 15, fontWeight: 600, color: token.colorTextSecondary }}>{currencyLabel}</span>
              </div>
              <Descriptions
                column={1}
                size="small"
                bordered
                labelStyle={{ width: 150, color: token.colorTextSecondary }}
                items={[
                  { key: 'date', label: 'Expense Date', children: (detail && detail.expense_date) || rec.expense_date || '—' },
                  { key: 'pm', label: 'Payment Method', children: (detail && detail.payment_method_name) || rec.payment_method || '—' },
                  { key: 'payee', label: 'Payee', children: (detail && detail.payee) || rec.payee || '—' },
                  { key: 'txn', label: 'Transaction No', children: (detail && detail.transaction_no) || '—' },
                  { key: 'submitted', label: 'Submitted', children: (detail && detail.created) || '—' },
                ]}
              />
              {detail && detail.notes ? (
                <div style={{ marginTop: 16 }}>
                  <Text strong style={{ display: 'block', marginBottom: 6 }}>Notes</Text>
                  <div style={{
                    background: token.colorFillQuaternary, borderRadius: 8, padding: '10px 12px',
                    whiteSpace: 'pre-wrap', color: token.colorText,
                  }}
                  >
                    {detail.notes}
                  </div>
                </div>
              ) : null}
            </>
          )}

          {/* Receipts (read-only in every mode) */}
          {attachments.length > 0 && (
            <>
              <Divider style={{ margin: '16px 0 12px' }} orientation="left" plain>
                <FileImageOutlined style={{ marginRight: 6 }} />
                Receipts
              </Divider>
              <div style={{ display: 'flex', flexWrap: 'wrap', gap: 12 }}>
                {attachments.map((a) => (a.isImage ? (
                  <div key={a.field} style={{ textAlign: 'center' }}>
                    <Image
                      src={a.url}
                      alt={a.label}
                      width={82}
                      height={82}
                      style={{ objectFit: 'cover', borderRadius: 8, border: `1px solid ${token.colorBorderSecondary}` }}
                    />
                    <div style={{ fontSize: 12, color: token.colorTextSecondary, marginTop: 4 }}>{a.label}</div>
                  </div>
                ) : (
                  <Button key={a.field} icon={<DownloadOutlined />} href={a.url} target="_blank" rel="noreferrer">
                    {a.label}
                  </Button>
                )))}
              </div>
            </>
          )}
        </div>

        {/* Right: status history sidebar */}
        <LogSidebar logs={logs} loading={loading} token={token} />
      </div>
    </Modal>

    {/* Reason prompt for a status change (mandatory reason, enforced server-side too) */}
    <Modal
      open={!!statusTarget}
      title="Change Status"
      okText="Update"
      confirmLoading={statusSaving}
      okButtonProps={{ disabled: !statusReason.trim() }}
      onOk={submitStatusChange}
      onCancel={() => setStatusTarget(null)}
      width={440}
    >
      <div style={{ marginBottom: 10 }}>
        Set status to{' '}
        <Tag color={STATUS_COLOR[statusTarget && statusTarget.value] || 'default'} style={{ fontWeight: 600 }}>
          {statusTarget && statusTarget.label}
        </Tag>
      </div>
      <div style={{ marginBottom: 6, color: token.colorTextSecondary }}>
        <span style={{ color: token.colorError, marginRight: 4 }}>*</span>
        Reason
      </div>
      <Input.TextArea
        rows={3}
        value={statusReason}
        onChange={(e) => setStatusReason(e.target.value)}
        status={!statusReason.trim() ? 'error' : undefined}
        placeholder="Reason for this status change…"
      />
    </Modal>
    </>
  );
}
