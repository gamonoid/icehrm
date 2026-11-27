import React, { useEffect, useRef, useState } from 'react';
import {
  Avatar, Button, Input, Popconfirm, Space, Tooltip, Typography, message, theme,
} from 'antd';
import {
  DeleteOutlined, EditOutlined, FileOutlined, MessageOutlined, UserOutlined,
} from '@ant-design/icons';
import { api, errorMessage } from '../api';
import { ago, full } from '../time';
import BlockRenderer from './BlockRenderer';
import PostComposer from './PostComposer';

import { t, tp } from '../../../../../../web/api/i18n';
const { Text } = Typography;
const { TextArea } = Input;

function Who({ author, size }) {
  return (
    <Avatar size={size || 32} src={author && author.image ? author.image : undefined} icon={<UserOutlined />}>
      {author && author.name ? author.name.charAt(0).toUpperCase() : null}
    </Avatar>
  );
}

function When({ at, edited }) {
  return (
    <Tooltip title={full(at)}>
      <Text type="secondary" style={{ fontSize: 12 }}>{ago(at)}{edited ? ' · edited' : ''}</Text>
    </Tooltip>
  );
}

function ReplyBox({ placeholder, onSend, onCancel, autoFocus }) {
  const [text, setText] = useState('');
  const [busy, setBusy] = useState(false);
  const send = async () => {
    const body = text.trim();
    if (!body) return;
    setBusy(true);
    try {
      await onSend(body);
      setText('');
    } finally {
      setBusy(false);
    }
  };
  return (
    <div style={{ display: 'flex', gap: 8, alignItems: 'flex-end' }}>
      <TextArea
        autoFocus={autoFocus}
        value={text}
        onChange={(e) => setText(e.target.value)}
        placeholder={placeholder}
        autoSize={{ minRows: 1, maxRows: 5 }}
        maxLength={5000}
        onPressEnter={(e) => { if (!e.shiftKey) { e.preventDefault(); send(); } }}
      />
      {onCancel && <Button size="small" onClick={onCancel}>{t('Cancel')}</Button>}
      <Button size="small" type="primary" loading={busy} disabled={!text.trim()} onClick={send}>{t('Reply')}</Button>
    </div>
  );
}

function Reply({ reply, canReply, onReply, onDelete, depth, token, highlightId }) {
  const [replying, setReplying] = useState(false);
  const highlighted = highlightId && reply.id === highlightId;
  const ref = useRef(null);
  // A reply opened from search: bring it into view once it is on screen.
  useEffect(() => {
    if (highlighted && ref.current && ref.current.scrollIntoView) {
      ref.current.scrollIntoView({ block: 'center', behavior: 'smooth' });
    }
  }, [highlighted]);
  const avatarSize = depth ? 22 : 26;
  return (
    <div ref={ref} style={{ marginTop: 10 }}>
      {/* The reply's own row. Children are rendered as a sibling below so a search
          highlight marks just the matched reply, not everything nested under it. */}
      <div style={{
        display: 'flex', gap: 10,
        ...(highlighted ? { background: token.colorWarningBg, borderRadius: 8, padding: '8px 10px', margin: '0 -10px' } : {}),
      }}>
        <Who author={reply.author} size={avatarSize} />
        <div style={{ flex: 1, minWidth: 0 }}>
          <Space size={8} wrap>
            <Text strong style={{ fontSize: 13 }}>{reply.author.name}</Text>
            <When at={reply.created} />
          </Space>
          <div style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word', lineHeight: 1.55, fontSize: 14, marginTop: 2 }}>{reply.body}</div>
          <Space size={2} style={{ marginTop: 2 }}>
            {/* Only top-level replies can be replied to: two levels, no deeper. */}
            {!depth && canReply && (
              <Button size="small" type="text" style={{ fontSize: 12, color: token.colorTextSecondary }} onClick={() => setReplying(!replying)}>{t('Reply')}</Button>
            )}
            {reply.can_delete && (
              <Popconfirm title={t('Delete this reply?')} okText={t('Delete')} okButtonProps={{ danger: true }} onConfirm={() => onDelete(reply)}>
                <Button size="small" type="text" style={{ fontSize: 12, color: token.colorTextSecondary }}>{t('Delete')}</Button>
              </Popconfirm>
            )}
          </Space>
          {replying && (
            <div style={{ marginTop: 6 }}>
              <ReplyBox
                autoFocus
                placeholder={t('Reply to {name}…', { name: reply.author.name })}
                onCancel={() => setReplying(false)}
                onSend={async (body) => { await onReply(body, reply.id); setReplying(false); }}
              />
            </div>
          )}
        </div>
      </div>
      {reply.children && reply.children.length > 0 && (
        <div style={{ marginLeft: avatarSize + 10, borderLeft: `2px solid ${token.colorBorderSecondary}`, paddingLeft: 10, marginTop: 2 }}>
          {reply.children.map((c) => (
            <Reply key={c.id} reply={c} depth={1} canReply={canReply} onReply={onReply} onDelete={onDelete} token={token} highlightId={highlightId} />
          ))}
        </div>
      )}
    </div>
  );
}

export default function PostCard({
  post, channel, me, onChanged, onDeleted, defaultShowReplies, highlightReplyId,
}) {
  const { token } = theme.useToken();
  const [editing, setEditing] = useState(false);
  const [showReplies, setShowReplies] = useState(!!defaultShowReplies);

  const canReply = !!channel.can_reply;
  const replyCount = post.reply_count || 0;

  const sendReply = async (body, parent) => {
    try {
      await api().post(`discussions/posts/${post.id}/replies`, { body, parent: parent || null });
      onChanged();
    } catch (e) {
      message.error(errorMessage(e, t('Could not send the reply')));
      throw e;
    }
  };

  const deleteReply = async (reply) => {
    try {
      await api().delete(`discussions/replies/${reply.id}`);
      onChanged();
    } catch (e) {
      message.error(errorMessage(e, t('Could not delete the reply')));
    }
  };

  const deletePost = async () => {
    try {
      await api().delete(`discussions/posts/${post.id}`);
      message.success(t('Post deleted'));
      onDeleted(post);
    } catch (e) {
      message.error(errorMessage(e, t('Could not delete the post')));
    }
  };

  return (
    <div style={{
      background: token.colorBgContainer,
      border: `1px solid ${token.colorBorderSecondary}`,
      borderRadius: 10,
      padding: '14px 16px',
    }}>
      <div style={{ display: 'flex', gap: 12 }}>
        <Who author={post.author} size={36} />
        <div style={{ flex: 1, minWidth: 0 }}>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 8 }}>
            <Space size={8} wrap>
              <Text strong>{post.author.name}</Text>
              <When at={post.created} edited={post.edited} />
            </Space>
            {!editing && (post.can_edit || post.can_delete) && (
              <Space size={0}>
                {post.can_edit && (
                  <Tooltip title={t('Edit')}><Button size="small" type="text" icon={<EditOutlined />} onClick={() => setEditing(true)} /></Tooltip>
                )}
                {post.can_delete && (
                  <Popconfirm title={t('Delete this post?')} description={t('Its replies and attachments go with it.')} okText={t('Delete')} okButtonProps={{ danger: true }} onConfirm={deletePost}>
                    <Tooltip title={t('Delete')}><Button size="small" type="text" danger icon={<DeleteOutlined />} /></Tooltip>
                  </Popconfirm>
                )}
              </Space>
            )}
          </div>

          {editing ? (
            <div style={{ marginTop: 8 }}>
              <PostComposer
                channel={channel}
                me={me}
                post={post}
                autoFocus
                onCancel={() => setEditing(false)}
                onSaved={() => { setEditing(false); onChanged(); }}
              />
            </div>
          ) : (
            <div style={{ marginTop: 6 }}>
              <BlockRenderer content={post.content} />
            </div>
          )}

          {!editing && post.attachments && post.attachments.length > 0 && (
            <Space wrap size={[8, 8]} style={{ marginTop: 6 }}>
              {post.attachments.map((a) => (
                <a
                  key={a.name}
                  href={a.url || '#'}
                  target="_blank"
                  rel="noopener noreferrer"
                  style={{
                    display: 'inline-flex', alignItems: 'center', gap: 6, padding: '4px 10px',
                    border: `1px solid ${token.colorBorderSecondary}`, borderRadius: 6,
                    background: token.colorFillQuaternary, color: token.colorText, fontSize: 13,
                  }}
                >
                  <FileOutlined style={{ color: token.colorPrimary }} />
                  <span style={{ maxWidth: 220, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{a.original || a.filename}</span>
                  {a.size_text ? <Text type="secondary" style={{ fontSize: 12 }}>{a.size_text}</Text> : null}
                </a>
              ))}
            </Space>
          )}

          {!editing && (
            <div style={{ marginTop: 8 }}>
              <Button
                size="small"
                type="text"
                icon={<MessageOutlined />}
                style={{ color: token.colorTextSecondary, paddingLeft: 4 }}
                onClick={() => setShowReplies(!showReplies)}
              >
                {replyCount === 0 ? (canReply ? t('Reply') : t('No replies')) : tp(replyCount, '{count} reply', '{count} replies')}
              </Button>
            </div>
          )}

          {!editing && showReplies && (
            <div style={{ marginTop: 4, paddingTop: 6, borderTop: `1px solid ${token.colorBorderSecondary}` }}>
              {(post.replies || []).map((r) => (
                <Reply key={r.id} reply={r} depth={0} canReply={canReply} onReply={sendReply} onDelete={deleteReply} token={token} highlightId={highlightReplyId} />
              ))}
              {canReply && (
                <div style={{ marginTop: 10 }}>
                  <ReplyBox placeholder={t('Write a reply…')} onSend={(body) => sendReply(body, null)} />
                </div>
              )}
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
