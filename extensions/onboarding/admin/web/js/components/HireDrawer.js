import React, { useEffect, useState } from 'react';
import {
  Alert, Button, Descriptions, Drawer, Input, Modal, Popconfirm, Progress, Select, Skeleton, Space, Tag, Timeline, Typography, message,
} from 'antd';
import {
  CheckOutlined, CloseCircleOutlined, DeleteOutlined, UserSwitchOutlined, EditOutlined, FlagOutlined, MailOutlined, RedoOutlined, StopOutlined,
} from '@ant-design/icons';
import { t, tp } from '../../../../../../web/api/i18n';
import { errorMessage } from '../shared/api';
import {
  formatDate, formatDateTime, hireStatusColor, hireStatusLabel, invitationLabel,
} from '../shared/constants';
import StepList from '../shared/StepList';
import SetManagerModal from './SetManagerModal';

const { Text, Title, Paragraph } = Typography;

// Each entry gets the activity's details; `warn` marks entries shown as a problem.
const ACTIVITY = {
  invited: (d) => (d.sent === false ? t('Invited, but the invitation email could not be sent') : t('Invited')),
  invite_resent: (d) => (d.sent === false ? t('New invitation link created, but the email could not be sent') : t('Invitation sent again')),
  accepted: () => t('Accepted the invitation'),
  step_started: () => t('Step started'),
  step_start_failed: () => t('Step could not be started'),
  step_done: () => t('Step done'),
  acknowledged: () => t('Acknowledged'),
  submitted: () => t('Submitted for review'),
  approved: () => t('Approved'),
  changes_requested: () => t('Changes requested'),
  skipped: () => t('Step skipped'),
  activated: () => t('Became an active employee'),
  completed: () => t('Onboarding completed'),
  cancelled: () => t('Onboarding cancelled'),
  manager_set: (d) => (d.manager ? t('Manager set to {name}', { name: d.manager }) : t('Manager set')),
  open_steps_skipped: (d) => tp(Number(d.count) || 0, '{n} open step skipped', '{n} open steps skipped', { n: Number(d.count) || 0 }),
};

function activityWarns(a) {
  return a.action === 'step_start_failed'
    || ((a.action === 'invited' || a.action === 'invite_resent') && a.details && a.details.sent === false);
}

/** Steps nobody has done yet (completing the onboarding skips them). */
export function openStepCount(data) {
  return (data.steps || []).filter((s) => s.status === 'Pending' || s.status === 'ChangesRequested').length;
}

/** One new hire's onboarding, for HR (all actions) and managers (read only). */
export function HireDetail({
  api, data, isHr, onChanged, compact,
}) {
  const [busy, setBusy] = useState(null);
  const [noteFor, setNoteFor] = useState(null);
  const [note, setNote] = useState('');
  const [assetFor, setAssetFor] = useState(null);
  const [assets, setAssets] = useState([]);
  const [asset, setAsset] = useState(null);
  const [managerFor, setManagerFor] = useState(null);


  const act = (path, body, success) => {
    setBusy(path);
    return api.post(path, body)
      .then((res) => {
        if (success) message.success(success);
        if (onChanged) onChanged(res);
        return res;
      })
      .catch((e) => {
        message.error(errorMessage(e, t('That did not work. Please try again.')));
        throw e;
      })
      .finally(() => setBusy(null));
  };

  const openAsset = (step) => {
    setAssetFor(step);
    setAsset(null);
    setAssets([]);
    api.get('onboarding/assets', { type: step.asset_type || '' }).then(setAssets).catch(() => setAssets([]));
  };

  const renderActions = (step) => {
    const buttons = [];
    if (step.can_review) {
      buttons.push(
        <Button key="approve" size="small" type="primary" icon={<CheckOutlined />} loading={busy === `onboarding/steps/${step.id}/approve`} onClick={() => act(`onboarding/steps/${step.id}/approve`, {}, t('Approved'))}>{t('Approve')}</Button>,
        <Button key="changes" size="small" icon={<EditOutlined />} onClick={() => { setNote(''); setNoteFor({ step, kind: 'changes' }); }}>{t('Request changes')}</Button>,
      );
    }
    if (step.can_complete && step.type === 'task') {
      buttons.push(<Button key="done" size="small" type="primary" ghost icon={<CheckOutlined />} onClick={() => { setNote(''); setNoteFor({ step, kind: 'done' }); }}>{t('Mark as done')}</Button>);
    }
    if (step.can_complete && step.type === 'set_manager') {
      buttons.push(<Button key="manager" size="small" type="primary" ghost icon={<UserSwitchOutlined />} onClick={() => setManagerFor(step)}>{t('Set manager')}</Button>);
    }
    if (step.can_complete && step.type === 'asset') {
      buttons.push(<Button key="asset" size="small" type="primary" ghost icon={<CheckOutlined />} onClick={() => openAsset(step)}>{t('Hand over')}</Button>);
    }
    if (step.can_retry) {
      buttons.push(<Button key="retry" size="small" icon={<RedoOutlined />} loading={busy === `onboarding/steps/${step.id}/retry`} onClick={() => act(`onboarding/steps/${step.id}/retry`, {}, t('Step started'))}>{t('Try again')}</Button>);
    }
    if (step.can_skip) {
      buttons.push(
        <Popconfirm key="skip" title={t('Skip this step?')} okText={t('Skip')} cancelText={t('Cancel')} onConfirm={() => act(`onboarding/steps/${step.id}/skip`, {}, t('Step skipped'))}>
          <Button size="small" type="text" icon={<StopOutlined />}>{t('Skip')}</Button>
        </Popconfirm>,
      );
    }
    return buttons.length ? <Space wrap size={6}>{buttons}</Space> : null;
  };

  const pct = data.progress.total ? Math.round((data.progress.done / data.progress.total) * 100) : 0;
  // Existing staff (Company Onboarding) have a joined date and no buddy.
  const workforce = data.track === 'workforce';

  return (
    <div>
      {!compact ? (
        <Descriptions size="small" column={{ xs: 1, sm: 2 }} style={{ marginBottom: 12 }}>
          {workforce ? (
            data.joined_date ? <Descriptions.Item label={t('Joined')}>{formatDate(data.joined_date)}</Descriptions.Item> : null
          ) : (
            <Descriptions.Item label={t('Start date')}>{formatDate(data.start_date)}</Descriptions.Item>
          )}
          <Descriptions.Item label={t('Workflow')}>{data.workflow}</Descriptions.Item>
          <Descriptions.Item label={t('Email')}>{data.email}</Descriptions.Item>
          <Descriptions.Item label={t('Job title')}>{data.job_title_name || '—'}</Descriptions.Item>
          <Descriptions.Item label={t('Department')}>{data.department_name || '—'}</Descriptions.Item>
          <Descriptions.Item label={t('Manager')}>{data.manager_name || '—'}</Descriptions.Item>
          {workforce ? null : <Descriptions.Item label={t('Buddy')}>{data.buddy_name || '—'}</Descriptions.Item>}
          <Descriptions.Item label={t('Accepted')}>{data.accepted_at ? formatDateTime(data.accepted_at) : '—'}</Descriptions.Item>
        </Descriptions>
      ) : null}
      <div style={{ marginBottom: 16 }}>
        <Progress percent={pct} size="small" />
        <Space size={12} wrap>
          <Text type="secondary">{t('{done} of {total} steps done', { done: data.progress.done, total: data.progress.total })}</Text>
          {data.progress.overdue ? <Text type="danger">{t('{n} overdue', { n: data.progress.overdue })}</Text> : null}
          {data.progress.review ? <Text style={{ color: '#1677ff' }}>{t('{n} waiting for review', { n: data.progress.review })}</Text> : null}
        </Space>
      </div>
      {data.status === 'Invited' ? (
        <Alert
          type={data.invitation_state === 'valid' ? 'info' : 'warning'}
          showIcon
          style={{ marginBottom: 16 }}
          message={invitationLabel(data.invitation_state) || (workforce ? t('Waiting for the employee to accept the invitation') : t('Waiting for the new hire to accept the invitation'))}
          description={workforce
            ? t('Steps for the employee start once they accept. HR and manager tasks can be done now.')
            : t('Steps for the new hire start once they accept. HR, manager and buddy tasks can be done now.')}
        />
      ) : null}

      <StepList steps={data.steps} renderActions={renderActions} track={data.track} />

      {!compact && data.activity && data.activity.length ? (
        <div style={{ marginTop: 8 }}>
          <Title level={5}>{t('Activity')}</Title>
          <Timeline
            items={data.activity.map((a) => ({
              children: (
                <div>
                  <Text type={activityWarns(a) ? 'warning' : undefined}>{ACTIVITY[a.action] ? ACTIVITY[a.action](a.details || {}) : a.action}</Text>
                  {a.step ? <Text type="secondary">{` · ${a.step}`}</Text> : null}
                  {a.by ? <Text type="secondary">{` · ${a.by}`}</Text> : null}
                  <div><Text type="secondary" style={{ fontSize: 12 }}>{formatDateTime(a.at)}</Text></div>
                  {a.details && a.details.note ? <div><Text italic>{a.details.note}</Text></div> : null}
                  {a.details && a.details.error ? <div><Text type="danger">{a.details.error}</Text></div> : null}
                </div>
              ),
            }))}
          />
        </div>
      ) : null}

      <SetManagerModal
        api={api}
        step={managerFor}
        donePath={managerFor ? `onboarding/steps/${managerFor.id}/done` : ''}
        onClose={() => setManagerFor(null)}
        onDone={() => { setManagerFor(null); if (onChanged) onChanged(); }}
      />
      <Modal
        open={!!noteFor}
        title={noteFor && noteFor.kind === 'changes' ? t('Request changes') : t('Mark as done')}
        okText={noteFor && noteFor.kind === 'changes' ? t('Send back') : t('Mark as done')}
        okButtonProps={{ disabled: noteFor && noteFor.kind === 'changes' && !note.trim() }}
        onCancel={() => setNoteFor(null)}
        onOk={() => {
          const { step, kind } = noteFor;
          const path = kind === 'changes' ? `onboarding/steps/${step.id}/request-changes` : `onboarding/steps/${step.id}/done`;
          const sentBack = workforce ? t('Sent back to the employee') : t('Sent back to the new hire');
          act(path, { note }, kind === 'changes' ? sentBack : t('Step done')).then(() => setNoteFor(null)).catch(() => {});
        }}
      >
        <Text type="secondary">
          {noteFor && noteFor.kind === 'changes'
            ? (workforce
              ? t('Tell the employee what to change. They will see this note with the step.')
              : t('Tell the new hire what to change. They will see this note with the step.'))
            : t('Add a note if it helps (optional).')}
        </Text>
        <Input.TextArea style={{ marginTop: 8 }} value={note} onChange={(e) => setNote(e.target.value)} autoSize={{ minRows: 3, maxRows: 8 }} maxLength={2000} />
      </Modal>


      <Modal
        open={!!assetFor}
        title={t('Hand over an asset')}
        okText={t('Hand over')}
        okButtonProps={{ disabled: !asset }}
        onCancel={() => setAssetFor(null)}
        onOk={() => act(`onboarding/steps/${assetFor.id}/done`, { asset, note }, t('Asset handed over')).then(() => setAssetFor(null)).catch(() => {})}
      >
        <Paragraph type="secondary">
          {workforce
            ? t('Choose an available asset. It is assigned to the employee in Company Assets.')
            : t('Choose an available asset. It is assigned to the new hire in Company Assets.')}
        </Paragraph>
        <Select
          style={{ width: '100%' }}
          showSearch
          value={asset}
          onChange={setAsset}
          options={assets}
          placeholder={t('Choose an asset')}
          notFoundContent={t('No available assets of this type')}
          filterOption={(input, option) => String(option.label).toLowerCase().includes(input.toLowerCase())}
        />
      </Modal>
    </div>
  );
}

export default function HireDrawer({
  api, id, open, onClose, onChanged, isHr,
}) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [busy, setBusy] = useState(null);
  const [link, setLink] = useState(null);

  const load = () => {
    if (!id) return;
    setLoading(true);
    api.get(`onboarding/hires/${id}`)
      .then(setData)
      .catch((e) => message.error(errorMessage(e, t('The onboarding could not be loaded'))))
      .finally(() => setLoading(false));
  };
  useEffect(() => {
    if (open) {
      setData(null);
      setLink(null);
      load();
    }
  }, [open, id]);

  const act = (action, success) => {
    setBusy(action);
    api.post(`onboarding/hires/${id}/${action}`, {})
      .then((res) => {
        if (action === 'resend' && res && res.sent === false) {
          message.warning(t('A new invitation link was created, but the email could not be sent. Share the link yourself.'));
        } else {
          message.success(success);
        }
        if (action === 'resend' && res && res.url) setLink(res);
        load();
        if (onChanged) onChanged();
      })
      .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))))
      .finally(() => setBusy(null));
  };

  const remove = () => {
    setBusy('delete');
    api.del(`onboarding/hires/${id}`)
      .then(() => {
        message.success(t('Onboarding deleted'));
        if (onChanged) onChanged();
        onClose();
      })
      .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))))
      .finally(() => setBusy(null));
  };

  const openStatus = data && ['Invited', 'InProgress'].includes(data.status);
  // Once the person is an active employee (a new hire after activation, existing staff
  // once they accept), the onboarding can no longer be cancelled.
  const canCancel = openStatus && !data.activated_at;
  const openSteps = data ? openStepCount(data) : 0;
  const workforceDrawer = data && data.track === 'workforce';
  const cancelledFooter = data && isHr && data.status === 'Cancelled' ? (
    <Space wrap style={{ justifyContent: 'flex-end', width: '100%' }}>
      <Popconfirm
        title={t('Delete this onboarding?')}
        description={data.employee
          ? t('The employee record and everything stored for them, including documents, form answers and bank details, are permanently deleted.')
          : t('Its steps, activity and invitation are removed.')}
        okText={t('Delete')}
        okButtonProps={{ danger: true }}
        cancelText={t('Cancel')}
        onConfirm={remove}
      >
        <Button danger icon={<DeleteOutlined />} loading={busy === 'delete'}>{t('Delete onboarding')}</Button>
      </Popconfirm>
    </Space>
  ) : null;

  return (
    <Drawer
      open={open}
      onClose={onClose}
      width={Math.min(760, typeof window !== 'undefined' ? window.innerWidth : 760)}
      title={data ? data.name : t('Onboarding')}
      extra={data ? <Tag color={hireStatusColor(data.status)}>{hireStatusLabel(data.status)}</Tag> : null}
      footer={data && isHr && openStatus ? (
        <Space wrap style={{ justifyContent: 'flex-end', width: '100%' }}>
          {data.status === 'Invited' ? (
            <Button icon={<MailOutlined />} loading={busy === 'resend'} onClick={() => act('resend', t('Invitation sent again'))}>{t('Resend invitation')}</Button>
          ) : null}
          {canCancel ? (
            <Popconfirm
              title={t('Cancel this onboarding?')}
              description={data.status === 'Invited'
                ? t('The invitation link will stop working.')
                : t('The new hire\'s login is removed and their record is marked as left. Their uploads are kept.')}
              okText={t('Cancel onboarding')}
              okButtonProps={{ danger: true }}
              cancelText={t('Keep')}
              onConfirm={() => act('cancel', t('Onboarding cancelled'))}
            >
              <Button danger icon={<CloseCircleOutlined />} loading={busy === 'cancel'}>{t('Cancel onboarding')}</Button>
            </Popconfirm>
          ) : null}
          {data.status === 'InProgress' ? (
            <Popconfirm
              title={t('Complete this onboarding now?')}
              overlayStyle={{ maxWidth: 360 }}
              description={(
                <div>
                  {openSteps > 0 ? (
                    <div style={{ marginBottom: 4 }}>
                      {tp(openSteps, '1 step is not done yet. It will be marked as skipped.', '{n} steps are not done yet. They will be marked as skipped.', { n: openSteps })}
                    </div>
                  ) : null}
                  {!data.activated_at && !workforceDrawer ? <div>{t('The employee becomes active straight away, even before the start date.')}</div> : null}
                </div>
              )}
              okText={t('Complete')}
              cancelText={t('Cancel')}
              onConfirm={() => act('complete', t('Onboarding completed'))}
            >
              <Button type="primary" icon={<FlagOutlined />} loading={busy === 'complete'}>{t('Complete onboarding')}</Button>
            </Popconfirm>
          ) : null}
        </Space>
      ) : cancelledFooter}
    >
      {loading && !data ? <Skeleton active /> : null}
      {link ? (
        <Alert
          type={link.sent ? 'success' : 'warning'}
          showIcon
          closable
          onClose={() => setLink(null)}
          style={{ marginBottom: 16 }}
          message={link.sent ? t('A new link was emailed. The previous link no longer works.') : t('The email could not be sent. Share this link yourself.')}
          description={<Paragraph copyable code style={{ wordBreak: 'break-all', margin: 0 }}>{link.url}</Paragraph>}
        />
      ) : null}
      {data ? <HireDetail api={api} data={data} isHr={isHr} onChanged={() => { load(); if (onChanged) onChanged(); }} /> : null}
    </Drawer>
  );
}
