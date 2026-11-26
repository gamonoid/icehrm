import React, { useEffect, useRef, useState } from 'react';
import { Button, Space, Spin, Tag, Typography, Upload, message, theme } from 'antd';
import { PaperClipOutlined, SendOutlined } from '@ant-design/icons';
import { api, errorMessage } from '../api';
import { createEditor } from './editorLoader';
import { attachMentions } from '../../../../../../web/components/MentionAutocomplete';
import { searchPeople } from '../api';

import { t } from '../../../../../../web/api/i18n';
const { Text } = Typography;

/**
 * The post editor: an EditorJS canvas plus an attachment tray. Used both to write
 * a new post (channel) and to edit an existing one (post) — the same component,
 * so the two can't drift.
 */
export default function PostComposer({
  channel, me, post, onSaved, onCancel, autoFocus,
}) {
  const { token } = theme.useToken();
  const holderRef = useRef(null);
  const editorRef = useRef(null);
  const mentionsRef = useRef(null);
  const [ready, setReady] = useState(false);
  const [saving, setSaving] = useState(false);
  const [attachments, setAttachments] = useState(post ? (post.attachments || []) : []);

  useEffect(() => {
    let cancelled = false;
    setReady(false);
    createEditor({
      holder: holderRef.current,
      data: post ? post.content : { blocks: [] },
      placeholder: post ? t('Edit your post…') : t('Message #{name}', { name: channel.name }),
      autofocus: !!autoFocus,
    }).then((ed) => {
      if (cancelled) { try { ed.destroy(); } catch (e) { /* ignore */ } return; }
      editorRef.current = ed;
      // "@" tagging — the same picker Home uses, so a mention behaves identically
      // in every channel. Torn down with the editor below.
      if (holderRef.current) {
        mentionsRef.current = attachMentions({
          root: holderRef.current,
          search: searchPeople,
          getTheme: () => token,
        });
      }
      setReady(true);
    }).catch(() => message.error(t('The editor could not be loaded')));
    return () => {
      cancelled = true;
      if (mentionsRef.current) {
        try { mentionsRef.current.destroy(); } catch (e) { /* ignore */ }
        mentionsRef.current = null;
      }
      if (editorRef.current) {
        try { editorRef.current.destroy(); } catch (e) { /* ignore */ }
        editorRef.current = null;
      }
    };
  }, [channel.id, post ? post.id : 0]); // eslint-disable-line react-hooks/exhaustive-deps

  const allowed = (me && me.allowed_extensions) || [];
  const maxBytes = (me && me.max_file_bytes) || 10485760;

  const beforeUpload = (file) => {
    const ext = String(file.name.split('.').pop() || '').toLowerCase();
    if (allowed.length && allowed.indexOf(ext) < 0) {
      message.error(`.${ext} files are not allowed`);
      return Upload.LIST_IGNORE;
    }
    if (file.size > maxBytes) {
      message.error(`Files are limited to ${Math.round(maxBytes / 1048576)} MB`);
      return Upload.LIST_IGNORE;
    }
    if (attachments.length >= 10) {
      message.error(t('At most 10 files can be attached'));
      return Upload.LIST_IGNORE;
    }
    return true;
  };

  const customRequest = async ({ file, onSuccess, onError }) => {
    const fd = new FormData();
    fd.append('file', file);
    fd.append('channel', channel.id);
    try {
      const res = await api().post('discussions/upload', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
      setAttachments((prev) => [...prev, res.data]);
      onSuccess(res.data);
    } catch (e) {
      message.error(errorMessage(e, t('Upload failed')));
      onError(e);
    }
  };

  const removeAttachment = (name) => setAttachments((prev) => prev.filter((a) => a.name !== name));

  const submit = async () => {
    if (!editorRef.current) return;
    setSaving(true);
    try {
      const content = await editorRef.current.save();
      const payload = {
        content,
        attachments: attachments.map((a) => ({ name: a.name, original: a.original })),
      };
      // IceApiClient exposes get/post/delete only, so an update is a POST to the post.
      const res = post
        ? await api().post(`discussions/posts/${post.id}`, payload)
        : await api().post(`discussions/channels/${channel.id}/posts`, payload);
      if (!post) {
        await editorRef.current.clear();
        setAttachments([]);
      }
      onSaved(res.data);
    } catch (e) {
      message.error(errorMessage(e, t('Could not save the post')));
    } finally {
      setSaving(false);
    }
  };

  return (
    <div style={{
      border: `1px solid ${token.colorBorderSecondary}`,
      borderRadius: 10,
      background: token.colorBgContainer,
      padding: '8px 14px 10px',
    }}>
      {!ready && <div style={{ padding: 12, textAlign: 'center' }}><Spin size="small" /></div>}
      <div ref={holderRef} className="discussions-editor" style={{ minHeight: ready ? 60 : 0 }} />

      {attachments.length > 0 && (
        <Space wrap size={[6, 6]} style={{ marginTop: 6, marginBottom: 6 }}>
          {attachments.map((a) => (
            <Tag key={a.name} closable onClose={() => removeAttachment(a.name)} icon={<PaperClipOutlined />}>
              {a.original || a.filename}{a.size_text ? <Text type="secondary"> · {a.size_text}</Text> : null}
            </Tag>
          ))}
        </Space>
      )}

      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginTop: 6, borderTop: `1px solid ${token.colorBorderSecondary}`, paddingTop: 8 }}>
        <Upload showUploadList={false} beforeUpload={beforeUpload} customRequest={customRequest} multiple>
          <Button size="small" type="text" icon={<PaperClipOutlined />}>{t('Attach')}</Button>
        </Upload>
        <Space>
          {post && <Button size="small" onClick={onCancel}>{t('Cancel')}</Button>}
          <Button size="small" type="primary" icon={<SendOutlined />} loading={saving} disabled={!ready} onClick={submit}>
            {post ? t('Save') : t('Post')}
          </Button>
        </Space>
      </div>
    </div>
  );
}
