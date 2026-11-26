import React, { useEffect, useState } from 'react';
import {
  Button, Card, Checkbox, Form, Grid, Input, Modal, Popconfirm, Space, Table, Tag,
  Tooltip, Typography, message, theme,
} from 'antd';
import {
  DeleteOutlined, EditOutlined, LockOutlined, NotificationOutlined, PlusOutlined,
  ReloadOutlined,
} from '@ant-design/icons';
import moment from 'moment';

import { t } from '../../../../../web/api/i18n';
const { Text } = Typography;
const { TextArea } = Input;
const { useBreakpoint } = Grid;

// Admin is always allowed everything; only these two are configurable.
const CONFIGURABLE_LEVELS = ['Manager', 'Employee'];

const api = () => window.modJs.apiClient;

function errorMessage(err, fallback) {
  const r = err && err.response && err.response.data;
  if (r && r.error && r.error[0] && r.error[0][0] && r.error[0][0].message) return r.error[0][0].message;
  return fallback;
}

function LevelTags({ levels }) {
  const all = ['Admin', ...CONFIGURABLE_LEVELS];
  return (
    <Space size={4} wrap>
      {all.map((l) => (
        <Tag key={l} color={levels.indexOf(l) >= 0 ? (l === 'Admin' ? 'geekblue' : 'green') : 'default'}
          style={{ marginInlineEnd: 0, opacity: levels.indexOf(l) >= 0 ? 1 : 0.45 }}>
          {l}
        </Tag>
      ))}
    </Space>
  );
}

export default function DiscussionsAdminView() {
  const { token } = theme.useToken();
  const [channels, setChannels] = useState([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [modalOpen, setModalOpen] = useState(false);
  const [editing, setEditing] = useState(null);
  const [form] = Form.useForm();
  // Below the shell's mobile breakpoint (antd md, 768px) the six-column table cannot
  // fit; it folds into one Channel column (access + post count stacked under the
  // name) plus actions. `md === false` so the first render never flashes it.
  const narrow = useBreakpoint().md === false;

  const load = async () => {
    setLoading(true);
    try {
      const res = await api().get('discussions-admin/channels');
      setChannels(Array.isArray(res.data) ? res.data : []);
    } catch (e) {
      message.error(errorMessage(e, t('Could not load channels')));
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => { load(); }, []);

  const openCreate = () => {
    setEditing(null);
    form.setFieldsValue({
      name: '', description: '',
      read_levels: CONFIGURABLE_LEVELS, post_levels: CONFIGURABLE_LEVELS, reply_levels: CONFIGURABLE_LEVELS,
    });
    setModalOpen(true);
  };

  const openEdit = (ch) => {
    setEditing(ch);
    form.setFieldsValue({
      name: ch.name,
      description: ch.description || '',
      read_levels: ch.read_levels.filter((l) => l !== 'Admin'),
      post_levels: ch.post_levels.filter((l) => l !== 'Admin'),
      reply_levels: ch.reply_levels.filter((l) => l !== 'Admin'),
    });
    setModalOpen(true);
  };

  const save = async () => {
    let values;
    try { values = await form.validateFields(); } catch (e) { return; }
    setSaving(true);
    try {
      const payload = {
        name: values.name,
        description: values.description,
        read_levels: ['Admin', ...(values.read_levels || [])],
        post_levels: ['Admin', ...(values.post_levels || [])],
        reply_levels: ['Admin', ...(values.reply_levels || [])],
      };
      if (editing) {
        // IceApiClient has no put(); updates are POSTed to the channel.
        await api().post(`discussions-admin/channels/${editing.id}`, payload);
        message.success(t('Channel updated'));
      } else {
        await api().post('discussions-admin/channels', payload);
        message.success(t('Channel created'));
      }
      setModalOpen(false);
      load();
    } catch (e) {
      message.error(errorMessage(e, t('Could not save the channel')));
    } finally {
      setSaving(false);
    }
  };

  const remove = async (ch) => {
    try {
      await api().delete(`discussions-admin/channels/${ch.id}`);
      message.success(`Deleted #${ch.name}`);
      load();
    } catch (e) {
      message.error(errorMessage(e, t('Could not delete the channel')));
    }
  };

  const columns = [
    {
      title: t('Channel'),
      dataIndex: 'name',
      key: 'name',
      render: (name, ch) => (
        <Space direction="vertical" size={0}>
          <Space size={6}>
            {ch.is_system
              ? <NotificationOutlined style={{ color: token.colorPrimary }} />
              : <Text type="secondary">#</Text>}
            <Text strong>{name}</Text>
            {ch.is_system && (
              <Tooltip title={t('Built in — cannot be deleted')}>
                <Tag icon={<LockOutlined />} color="blue" style={{ marginInlineEnd: 0 }}>{t('Built in')}</Tag>
              </Tooltip>
            )}
          </Space>
          {ch.description ? <Text type="secondary" style={{ fontSize: 12 }}>{ch.description}</Text> : null}
          {narrow && (
            <div style={{ marginTop: 8, display: 'grid', gridTemplateColumns: 'auto 1fr', gap: '6px 8px', alignItems: 'center' }}>
              <Text type="secondary" style={{ fontSize: 12 }}>{t('Can read')}</Text>
              <LevelTags levels={ch.read_levels} />
              <Text type="secondary" style={{ fontSize: 12 }}>{t('Can post')}</Text>
              <LevelTags levels={ch.post_levels} />
              <Text type="secondary" style={{ fontSize: 12 }}>{t('Can reply')}</Text>
              <LevelTags levels={ch.reply_levels} />
              <Text type="secondary" style={{ fontSize: 12 }}>{t('Posts')}</Text>
              <Text style={{ fontSize: 12 }}>
                {ch.posts}
                {ch.last_post ? <Text type="secondary" style={{ fontSize: 12 }}>{` · last ${moment(ch.last_post).fromNow()}`}</Text> : null}
              </Text>
            </div>
          )}
        </Space>
      ),
    },
    { title: t('Can read'), dataIndex: 'read_levels', key: 'read', render: (v) => <LevelTags levels={v} /> },
    { title: t('Can post'), dataIndex: 'post_levels', key: 'post', render: (v) => <LevelTags levels={v} /> },
    { title: t('Can reply'), dataIndex: 'reply_levels', key: 'reply', render: (v) => <LevelTags levels={v} /> },
    {
      title: t('Posts'),
      dataIndex: 'posts',
      key: 'posts',
      width: 140,
      render: (n, ch) => (
        <Space direction="vertical" size={0}>
          <Text>{n}</Text>
          {ch.last_post
            ? <Text type="secondary" style={{ fontSize: 12 }}>last {moment(ch.last_post).fromNow()}</Text>
            : null}
        </Space>
      ),
    },
    {
      title: '',
      key: 'actions',
      width: 90,
      render: (_, ch) => (
        <Space>
          <Tooltip title={t('Edit')}>
            <Button size="small" type="text" icon={<EditOutlined />} onClick={() => openEdit(ch)} />
          </Tooltip>
          {ch.is_system ? (
            <Tooltip title={t('The built-in channel cannot be deleted')}>
              <Button size="small" type="text" icon={<DeleteOutlined />} disabled />
            </Tooltip>
          ) : (
            <Popconfirm
              title={t('Delete #{name}?', { name: ch.name })}
              description={t('Every post, reply and attachment in it is removed too.')}
              okText={t('Delete')}
              okButtonProps={{ danger: true }}
              onConfirm={() => remove(ch)}
            >
              <Button size="small" type="text" danger icon={<DeleteOutlined />} />
            </Popconfirm>
          )}
        </Space>
      ),
    },
  ];

  const NARROW_KEYS = ['name', 'actions'];
  const shownColumns = narrow ? columns.filter((c) => NARROW_KEYS.indexOf(c.key) >= 0) : columns;

  return (
    <div style={{ padding: narrow ? 8 : 16 }}>
      <Card
        title={t('Channels')}
        styles={narrow ? { body: { padding: 12 } } : undefined}
        extra={(
          <Space>
            <Button icon={<ReloadOutlined />} onClick={load} aria-label={t('Refresh')}>{narrow ? null : t('Refresh')}</Button>
            <Button type="primary" icon={<PlusOutlined />} onClick={openCreate} aria-label={t('New channel')}>{narrow ? null : t('New channel')}</Button>
          </Space>
        )}
      >
        <Text type="secondary" style={{ display: 'block', marginBottom: 16 }}>
          {t('Choose which user levels can read each channel, start new posts in it, and reply to posts. Administrators can always do all three.')}
        </Text>
        <Table
          rowKey="id"
          loading={loading}
          columns={shownColumns}
          dataSource={channels}
          pagination={false}
          size="middle"
        />
      </Card>

      <Modal
        title={editing ? t('Edit #{name}', { name: editing.name }) : t('New channel')}
        open={modalOpen}
        onCancel={() => setModalOpen(false)}
        onOk={save}
        okText={editing ? t('Save') : t('Create')}
        confirmLoading={saving}
        destroyOnClose
      >
        <Form form={form} layout="vertical" style={{ marginTop: 16 }}>
          <Form.Item
            name="name"
            label={t('Name')}
            rules={[{ required: true, message: t('Give the channel a name') }, { max: 100 }]}
          >
            <Input prefix="#" placeholder="general" maxLength={100} />
          </Form.Item>
          <Form.Item name="description" label={t('Description')}>
            <TextArea rows={2} placeholder={t('What is this channel for?')} maxLength={500} />
          </Form.Item>

          {[
            ['read_levels', t('Who can read this channel')],
            ['post_levels', t('Who can start new posts')],
            ['reply_levels', t('Who can reply to posts')],
          ].map(([name, label]) => (
            <Form.Item key={name} label={label} style={{ marginBottom: 12 }}>
              <Space wrap>
                {/* Admin is always allowed; shown ticked and fixed, outside the group so
                    the group's value cannot untick it. */}
                <Checkbox checked disabled>{t('Admin')}</Checkbox>
                <Form.Item name={name} noStyle>
                  <Checkbox.Group options={CONFIGURABLE_LEVELS} />
                </Form.Item>
              </Space>
            </Form.Item>
          ))}
        </Form>
      </Modal>
    </div>
  );
}
