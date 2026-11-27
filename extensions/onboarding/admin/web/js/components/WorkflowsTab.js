import React, { useEffect, useState } from 'react';
import {
  Button, Card, Col, Drawer, Empty, Popconfirm, Row, Segmented, Skeleton, Space, Tag, Typography, message,
} from 'antd';
import {
  CopyOutlined, DeleteOutlined, EditOutlined, EyeOutlined, InboxOutlined, RollbackOutlined, UserAddOutlined,
} from '@ant-design/icons';
import { t, tp } from '../../../../../../web/api/i18n';
import { errorMessage } from '../shared/api';
import { formatDate } from '../shared/constants';
import StepList from '../shared/StepList';
import WorkflowBuilder from './WorkflowBuilder';
import InviteDialog from './InviteDialog';

const { Text, Paragraph, Title } = Typography;

/**
 * Workflows: the standard ones shipped with IceHrm (read-only, clone to change) and
 * the custom ones HR made.
 */
export default function WorkflowsTab({ api, ctx, onChanged }) {
  const [view, setView] = useState('standard');
  const [library, setLibrary] = useState(null);
  const [custom, setCustom] = useState(null);
  const [preview, setPreview] = useState(null);
  const [editId, setEditId] = useState(null);
  const [inviteWith, setInviteWith] = useState(null);

  const load = () => {
    if (view === 'standard') {
      setLibrary(null);
      api.get('onboarding/library', { track: ctx.track || 'new_hire' }).then(setLibrary).catch(() => setLibrary([]));
    } else {
      setCustom(null);
      api.get('onboarding/workflows', { status: view === 'archived' ? 'Archived' : 'Active', track: ctx.track || 'new_hire' }).then(setCustom).catch(() => setCustom([]));
    }
  };
  useEffect(load, [view]);

  const clone = (key) => {
    api.post(`onboarding/library/${key}/clone`, {})
      .then((res) => {
        message.success(t('A copy was added to your custom workflows'));
        setPreview(null);
        setView('custom');
        setEditId(res.id);
      })
      .catch((e) => message.error(errorMessage(e, t('The workflow could not be copied'))));
  };

  const act = (id, action, success) => api.post(`onboarding/workflows/${id}/${action}`, {})
    .then((res) => {
      message.success(success);
      load();
      if (action === 'duplicate' && res && res.id) setEditId(res.id);
    })
    .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))));

  const remove = (id) => api.del(`onboarding/workflows/${id}`)
    .then(() => { message.success(t('Workflow deleted')); load(); })
    .catch((e) => message.error(errorMessage(e, t('The workflow could not be deleted'))));

  const openPreview = (key) => {
    setPreview({ loading: true });
    api.get(`onboarding/library/${key}`)
      .then((w) => setPreview({
        ...w,
        steps: (w.steps || []).map((s, i) => ({
          ...s, id: i + 1, status: 'Pending', due_date: null, owner_name: null,
        })),
      }))
      .catch((e) => { message.error(errorMessage(e, t('The workflow could not be loaded'))); setPreview(null); });
  };

  const items = view === 'standard' ? library : custom;

  return (
    <div>
      <div style={{ marginBottom: 16 }}>
        <Segmented
          value={view}
          onChange={setView}
          options={[
            { value: 'standard', label: t('Standard workflows') },
            { value: 'custom', label: t('Custom workflows') },
            { value: 'archived', label: t('Archived') },
          ]}
        />
        <Paragraph type="secondary" style={{ marginTop: 8, marginBottom: 0 }}>
          {view === 'standard'
            ? t('Ready-made workflows that are kept up to date with IceHrm. Use one as it is, or copy it to make your own version.')
            : (ctx.track === 'workforce'
              ? t('Workflows your company made. Changes apply to people invited after you save; onboardings already under way keep their steps.')
              : t('Workflows your company made. Changes apply to new hires invited after you save; onboardings already under way keep their steps.'))}
        </Paragraph>
      </div>

      {items === null ? <Skeleton active /> : null}
      {items && items.length === 0 ? (
        <Empty description={view === 'custom' ? t('No custom workflows yet. Copy a standard workflow to start.') : t('Nothing here.')} />
      ) : null}

      <Row gutter={[16, 16]}>
        {(items || []).map((w) => (
          <Col key={w.value} xs={24} md={12} xl={8} style={{ display: 'flex' }}>
            {/* Same height in a row, so the action bars line up. */}
            <Card
              style={{ width: '100%', display: 'flex', flexDirection: 'column' }}
              styles={{ body: { padding: 16, flex: 1 } }}
              actions={view === 'standard' ? [
                <Button key="preview" type="text" icon={<EyeOutlined />} onClick={() => openPreview(w.key)}>{t('Preview')}</Button>,
                <Button key="clone" type="text" icon={<CopyOutlined />} onClick={() => clone(w.key)}>{t('Copy and edit')}</Button>,
                <Button key="use" type="text" icon={<UserAddOutlined />} onClick={() => setInviteWith(w.value)}>{t('Invite')}</Button>,
              ] : [
                <Button key="edit" type="text" icon={<EditOutlined />} onClick={() => setEditId(w.id)}>{t('Edit')}</Button>,
                view === 'custom'
                  ? <Button key="use" type="text" icon={<UserAddOutlined />} onClick={() => setInviteWith(w.value)}>{t('Invite')}</Button>
                  : <Button key="restore" type="text" icon={<RollbackOutlined />} onClick={() => act(w.id, 'restore', t('Workflow restored'))}>{t('Restore')}</Button>,
                <Space key="more" size={0}>
                  <Button type="text" icon={<CopyOutlined />} title={t('Duplicate')} onClick={() => act(w.id, 'duplicate', t('Workflow duplicated'))} />
                  {view === 'custom' ? <Button type="text" icon={<InboxOutlined />} title={t('Archive')} onClick={() => act(w.id, 'archive', t('Workflow archived'))} /> : null}
                  {w.used_count === 0 ? (
                    <Popconfirm title={t('Delete this workflow?')} okText={t('Delete')} okButtonProps={{ danger: true }} cancelText={t('Cancel')} onConfirm={() => remove(w.id)}>
                      <Button type="text" danger icon={<DeleteOutlined />} title={t('Delete')} />
                    </Popconfirm>
                  ) : null}
                </Space>,
              ]}
            >
              <Title level={5} style={{ marginTop: 0 }}>{w.name}</Title>
              {w.audience ? <Text type="secondary" style={{ display: 'block', fontSize: 12, marginBottom: 6 }}>{w.audience}</Text> : null}
              <Paragraph type="secondary" ellipsis={{ rows: 3 }} style={{ minHeight: 44 }}>{w.description}</Paragraph>
              <Space size={6} wrap>
                <Tag>{tp(w.step_count, '{n} step', '{n} steps', { n: w.step_count })}</Tag>
                {w.used_count ? <Tag color="blue">{tp(w.used_count, 'Used for {n} hire', 'Used for {n} hires', { n: w.used_count })}</Tag> : null}
                {w.unavailable_steps ? <Tag color="orange">{tp(w.unavailable_steps, '{n} step needs an extension', '{n} steps need an extension', { n: w.unavailable_steps })}</Tag> : null}
                {w.updated ? <Text type="secondary" style={{ fontSize: 12 }}>{t('Updated {date}', { date: formatDate(w.updated) })}</Text> : null}
              </Space>
            </Card>
          </Col>
        ))}
      </Row>

      <Drawer
        open={!!preview}
        onClose={() => setPreview(null)}
        width={Math.min(720, typeof window !== 'undefined' ? window.innerWidth : 720)}
        title={preview && preview.name ? preview.name : t('Workflow')}
        extra={preview && preview.key ? <Button type="primary" icon={<CopyOutlined />} onClick={() => clone(preview.key)}>{t('Copy and edit')}</Button> : null}
      >
        {preview && preview.loading ? <Skeleton active /> : null}
        {preview && preview.steps ? (
          <>
            {preview.description ? <Paragraph>{preview.description}</Paragraph> : null}
            {preview.welcome_message ? (
              <Card size="small" style={{ marginBottom: 16 }} title={t('Welcome message')}>
                <Paragraph style={{ whiteSpace: 'pre-line', margin: 0 }}>{preview.welcome_message}</Paragraph>
              </Card>
            ) : null}
            <StepList steps={preview.steps} track={ctx.track} showProgress={false} />
          </>
        ) : null}
      </Drawer>

      <WorkflowBuilder
        api={api}
        ctx={ctx}
        id={editId}
        open={!!editId}
        onClose={() => { setEditId(null); load(); }}
        onSaved={() => { load(); if (onChanged) onChanged(); }}
      />
      <InviteDialog
        api={api}
        track={ctx.track || 'new_hire'}
        open={!!inviteWith}
        presetWorkflow={inviteWith}
        onClose={() => setInviteWith(null)}
        onInvited={() => { if (onChanged) onChanged(); }}
      />
    </div>
  );
}
