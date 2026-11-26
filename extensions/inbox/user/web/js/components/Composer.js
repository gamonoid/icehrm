import React, { useState } from 'react';
import { Button, Input, Space, Tag, Typography, Upload, message, theme } from 'antd';
import { PaperClipOutlined, SendOutlined } from '@ant-design/icons';
import { api, errorMessage } from '../api';

import { t } from '../../../../../../web/api/i18n';
const { Text } = Typography;
const { TextArea } = Input;

/**
 * The message box at the foot of a conversation. Enter sends, Shift+Enter breaks a
 * line. Files are uploaded as they are picked and sent along with the message.
 */
export default function Composer({ conversation, me, onSent }) {
  const { token } = theme.useToken();
  const [text, setText] = useState('');
  const [attachments, setAttachments] = useState([]);
  const [busy, setBusy] = useState(false);

  const allowed = (me && me.allowed_extensions) || [];
  const maxBytes = (me && me.max_file_bytes) || 10485760;
  const maxFiles = (me && me.max_attachments) || 5;

  const beforeUpload = (file) => {
    const ext = String(file.name.split('.').pop() || '').toLowerCase();
    if (allowed.length && allowed.indexOf(ext) < 0) { message.error(`.${ext} files are not allowed`); return Upload.LIST_IGNORE; }
    if (file.size > maxBytes) { message.error(`Files are limited to ${Math.round(maxBytes / 1048576)} MB`); return Upload.LIST_IGNORE; }
    if (attachments.length >= maxFiles) { message.error(`At most ${maxFiles} files per message`); return Upload.LIST_IGNORE; }
    return true;
  };

  const customRequest = async ({ file, onSuccess, onError }) => {
    const fd = new FormData();
    fd.append('file', file);
    try {
      const res = await api().post(`inbox/conversations/${conversation.id}/upload`, fd);
      setAttachments((prev) => [...prev, res.data]);
      onSuccess(res.data);
    } catch (e) {
      message.error(errorMessage(e, t('Upload failed')));
      onError(e);
    }
  };

  const canSend = !!text.trim() || attachments.length > 0;

  const send = async () => {
    if (!canSend || busy) return;
    setBusy(true);
    try {
      const res = await api().post(`inbox/conversations/${conversation.id}/messages`, {
        body: text.trim(),
        attachments: attachments.map((a) => ({ name: a.name, original: a.original })),
      });
      setText('');
      setAttachments([]);
      onSent(res.data);
    } catch (e) {
      message.error(errorMessage(e, t('Could not send the message')));
    } finally {
      setBusy(false);
    }
  };

  return (
    <div style={{ padding: '10px 12px 8px', borderTop: `1px solid ${token.colorBorderSecondary}`, background: token.colorBgContainer }}>
      <TextArea
        value={text}
        onChange={(e) => setText(e.target.value)}
        placeholder={t('Message {name}', { name: conversation.other.name })}
        autoSize={{ minRows: 3, maxRows: 10 }}
        maxLength={5000}
        onKeyDown={(e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); } }}
      />
      {attachments.length > 0 && (
        <Space wrap size={[6, 6]} style={{ marginTop: 8 }}>
          {attachments.map((a) => (
            <Tag key={a.name} closable onClose={() => setAttachments((prev) => prev.filter((x) => x.name !== a.name))} icon={<PaperClipOutlined />}>
              {a.original || a.filename}{a.size_text ? <Text type="secondary"> · {a.size_text}</Text> : null}
            </Tag>
          ))}
        </Space>
      )}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 8 }}>
        <Space size={12}>
          <Upload showUploadList={false} beforeUpload={beforeUpload} customRequest={customRequest} multiple>
            <Button size="small" type="text" icon={<PaperClipOutlined />}>{t('Attach')}</Button>
          </Upload>
          <Text type="secondary" style={{ fontSize: 11 }}>{t('Enter to send · Shift+Enter for a new line')}</Text>
        </Space>
        <Button type="primary" icon={<SendOutlined />} loading={busy} disabled={!canSend} onClick={send}>{t('Send')}</Button>
      </div>
    </div>
  );
}
