import React, { useEffect, useState } from 'react';
import {
  Avatar, Button, Card, Col, Empty, Input, Progress, Row, Segmented, Skeleton, Space, Tag, Typography, message,
} from 'antd';
import { CalendarOutlined, UserAddOutlined } from '@ant-design/icons';
import { t } from '../../../../../../web/api/i18n';
import { errorMessage } from '../shared/api';
import {
  formatDate, hireStatusColor, hireStatusLabel, invitationLabel,
} from '../shared/constants';
import HireDrawer from './HireDrawer';
import InviteDialog from './InviteDialog';

const { Text } = Typography;

function startText(hire) {
  if (hire.track === 'workforce') {
    return hire.joined_date ? t('Joined {date}', { date: formatDate(hire.joined_date) }) : t('Existing employee');
  }
  if (hire.status === 'Completed' || hire.status === 'Cancelled') return formatDate(hire.start_date);
  const d = hire.days_to_start;
  if (d > 1) return t('Starts in {n} days', { n: d });
  if (d === 1) return t('Starts tomorrow');
  if (d === 0) return t('Starts today');
  return t('Started {date}', { date: formatDate(hire.start_date) });
}

/** New hires: who is being onboarded, how far they are, and what needs attention. */
export default function HiresTab({
  api, ctx, refreshKey, onChanged,
}) {
  const [status, setStatus] = useState('open');
  const [search, setSearch] = useState('');
  const [rows, setRows] = useState(null);
  const [openId, setOpenId] = useState(null);
  const [inviteOpen, setInviteOpen] = useState(false);
  const track = ctx.track || 'new_hire';
  const inviteLabel = track === 'workforce' ? t('Invite an employee') : t('Invite a new hire');

  const load = () => {
    setRows(null);
    api.get('onboarding/hires', { status, search, track })
      .then(setRows)
      .catch((e) => {
        message.error(errorMessage(e, t('New hires could not be loaded')));
        setRows([]);
      });
  };
  useEffect(load, [status, search, refreshKey]);

  const counts = ctx.counts || {};
  const options = [
    { value: 'open', label: `${t('Open')} (${counts.open || 0})` },
    { value: 'Invited', label: t('Invited') },
    { value: 'InProgress', label: t('In progress') },
    { value: 'Completed', label: t('Completed') },
    { value: 'Cancelled', label: t('Cancelled') },
  ];

  return (
    <div>
      <div style={{
        display: 'flex', gap: 12, flexWrap: 'wrap', justifyContent: 'space-between', marginBottom: 16,
      }}
      >
        <Space wrap>
          <Segmented options={options} value={status} onChange={setStatus} />
          <Input.Search allowClear placeholder={t('Search by name or email')} onSearch={setSearch} style={{ width: 240 }} />
        </Space>
        {ctx.is_hr ? (
          <Button type="primary" icon={<UserAddOutlined />} onClick={() => setInviteOpen(true)}>{inviteLabel}</Button>
        ) : null}
      </div>

      {rows === null ? <Skeleton active /> : null}
      {rows && rows.length === 0 ? (
        <Empty
          description={status === 'open' ? t('No one is being onboarded right now.') : t('Nothing here.')}
        >
          {ctx.is_hr && status === 'open' ? (
            <Button type="primary" icon={<UserAddOutlined />} onClick={() => setInviteOpen(true)}>{inviteLabel}</Button>
          ) : null}
        </Empty>
      ) : null}
      <Row gutter={[16, 16]}>
        {(rows || []).map((hire) => {
          const pct = hire.progress.total ? Math.round((hire.progress.done / hire.progress.total) * 100) : 0;
          const inv = hire.status === 'Invited' ? invitationLabel(hire.invitation_state) : null;
          return (
            <Col key={hire.id} xs={24} md={12} xl={8}>
              <Card hoverable onClick={() => setOpenId(hire.id)} styles={{ body: { padding: 16 } }}>
                <div style={{ display: 'flex', gap: 12, alignItems: 'center' }}>
                  <Avatar size={44} style={{ background: '#1677ff', flex: '0 0 auto' }}>
                    {`${(hire.first_name || '?')[0]}${(hire.last_name || '')[0] || ''}`.toUpperCase()}
                  </Avatar>
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', gap: 8 }}>
                      <Text strong ellipsis>{hire.name}</Text>
                      <Tag color={hireStatusColor(hire.status)} style={{ marginRight: 0 }}>{hireStatusLabel(hire.status)}</Tag>
                    </div>
                    <Text type="secondary" ellipsis style={{ display: 'block', fontSize: 13 }}>
                      {[hire.job_title_name, hire.department_name].filter(Boolean).join(' · ') || hire.email}
                    </Text>
                  </div>
                </div>
                <div style={{ marginTop: 12 }}>
                  <Text type="secondary" style={{ fontSize: 13 }}>
                    <CalendarOutlined />
                    {' '}
                    {startText(hire)}
                    {' · '}
                    {hire.workflow}
                  </Text>
                  <Progress percent={pct} size="small" style={{ margin: '6px 0 4px' }} />
                  <Space size={6} wrap>
                    {inv ? <Tag color={{ valid: 'gold', email_failed: 'orange' }[hire.invitation_state] || 'red'}>{inv}</Tag> : null}
                    {hire.progress.overdue ? <Tag color="red">{t('{n} overdue', { n: hire.progress.overdue })}</Tag> : null}
                    {hire.progress.review ? <Tag color="blue">{t('{n} waiting for review', { n: hire.progress.review })}</Tag> : null}
                    {hire.manager_name ? <Text type="secondary" style={{ fontSize: 12 }}>{t('Manager: {name}', { name: hire.manager_name })}</Text> : null}
                  </Space>
                </div>
              </Card>
            </Col>
          );
        })}
      </Row>

      <HireDrawer
        api={api}
        id={openId}
        open={!!openId}
        isHr={ctx.is_hr}
        onClose={() => setOpenId(null)}
        onChanged={() => { load(); if (onChanged) onChanged(); }}
      />
      <InviteDialog
        api={api}
        track={track}
        open={inviteOpen}
        onClose={() => setInviteOpen(false)}
        onInvited={() => { load(); if (onChanged) onChanged(); }}
      />
    </div>
  );
}
