import React, { useEffect, useState } from 'react';
import { Checkbox, Form, Input, Modal, Space, message } from 'antd';
import { api, errorMessage } from '../api';

import { t } from '../../../../../../web/api/i18n';
const CONFIGURABLE_LEVELS = ['Manager', 'Employee'];
const { TextArea } = Input;

/**
 * Create a channel without leaving the conversation view. Admin-only: the button
 * that opens this is hidden for everyone else, and the request goes to the admin
 * endpoint (discussions-admin/channels), which refuses non-admins server-side
 * whatever the client shows. Editing an existing channel stays on the Manage page.
 */
export default function NewChannelModal({ open, onClose, onCreated }) {
  const [form] = Form.useForm();
  const [saving, setSaving] = useState(false);

  useEffect(() => {
    if (open) {
      form.setFieldsValue({
        name: '', description: '',
        read_levels: CONFIGURABLE_LEVELS, post_levels: CONFIGURABLE_LEVELS, reply_levels: CONFIGURABLE_LEVELS,
      });
    }
  }, [open]); // eslint-disable-line react-hooks/exhaustive-deps

  const save = async () => {
    let values;
    try { values = await form.validateFields(); } catch (e) { return; }
    setSaving(true);
    try {
      const res = await api().post('discussions-admin/channels', {
        name: values.name,
        description: values.description,
        read_levels: ['Admin', ...(values.read_levels || [])],
        post_levels: ['Admin', ...(values.post_levels || [])],
        reply_levels: ['Admin', ...(values.reply_levels || [])],
      });
      message.success(`#${res.data.name} created`);
      onCreated(res.data);
    } catch (e) {
      message.error(errorMessage(e, t('Could not create the channel')));
    } finally {
      setSaving(false);
    }
  };

  return (
    <Modal
      title={t('New channel')}
      open={open}
      onCancel={onClose}
      onOk={save}
      okText={t('Create')}
      confirmLoading={saving}
      destroyOnClose
    >
      <Form form={form} layout="vertical" style={{ marginTop: 16 }}>
        <Form.Item name="name" label={t('Name')} rules={[{ required: true, message: t('Give the channel a name') }, { max: 100 }]}>
          <Input prefix="#" placeholder="general" maxLength={100} autoFocus />
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
              <Checkbox checked disabled>{t('Admin')}</Checkbox>
              <Form.Item name={name} noStyle>
                <Checkbox.Group options={CONFIGURABLE_LEVELS} />
              </Form.Item>
            </Space>
          </Form.Item>
        ))}
      </Form>
    </Modal>
  );
}
