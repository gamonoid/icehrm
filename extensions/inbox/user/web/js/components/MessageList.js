import React, { useEffect, useRef, useState } from 'react';
import { Button, Input, Popconfirm, Space, Tooltip, Typography, message, theme } from 'antd';
import { DeleteOutlined, EditOutlined, FileOutlined } from '@ant-design/icons';
import { api, errorMessage } from '../api';
import { timeOfDay, dayLabel, dayKey, full, minutesBetween } from '../time';
import { PersonAvatar } from './Person';

import { t } from '../../../../../../web/api/i18n';
const { Text } = Typography;
const { TextArea } = Input;

function DayDivider({ day, token }) {
  return (
    <div style={{ display: 'flex', alignItems: 'center', gap: 10, margin: '14px 0 6px' }}>
      <div style={{ flex: 1, height: 1, background: token.colorBorderSecondary }} />
      <Text type="secondary" style={{ fontSize: 11, fontWeight: 600 }}>{day}</Text>
      <div style={{ flex: 1, height: 1, background: token.colorBorderSecondary }} />
    </div>
  );
}

function Message({ msg, mine, showHeader, onChanged, token }) {
  const [editing, setEditing] = useState(false);
  const [draft, setDraft] = useState(msg.body);
  const [busy, setBusy] = useState(false);

  const save = async () => {
    const body = draft.trim();
    if (!body) return;
    setBusy(true);
    try {
      await api().post(`inbox/messages/${msg.id}`, { body });
      setEditing(false);
      onChanged();
    } catch (e) {
      message.error(errorMessage(e, t('Could not edit the message')));
    } finally {
      setBusy(false);
    }
  };
  const remove = async () => {
    try {
      await api().delete(`inbox/messages/${msg.id}`);
      onChanged();
    } catch (e) {
      message.error(errorMessage(e, t('Could not delete the message')));
    }
  };

  return (
    <div
      className="inbox-message"
      style={{ display: 'flex', gap: 10, padding: showHeader ? '6px 8px 2px' : '1px 8px 1px', borderRadius: 6, position: 'relative' }}
      onMouseEnter={(e) => { e.currentTarget.style.background = token.colorFillQuaternary; }}
      onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}
    >
      <div style={{ width: 34, flex: 'none' }}>
        {showHeader ? <PersonAvatar person={msg.sender} size={34} /> : (
          <Tooltip title={full(msg.created)}>
            <Text type="secondary" className="inbox-time" style={{ fontSize: 10, display: 'block', textAlign: 'center', lineHeight: '22px', opacity: 0 }}>{timeOfDay(msg.created)}</Text>
          </Tooltip>
        )}
      </div>
      <div style={{ flex: 1, minWidth: 0 }}>
        {showHeader && (
          <Space size={8}>
            <Text strong>{msg.sender.name}</Text>
            <Tooltip title={full(msg.created)}><Text type="secondary" style={{ fontSize: 12 }}>{timeOfDay(msg.created)}</Text></Tooltip>
          </Space>
        )}
        {editing ? (
          <div style={{ marginTop: 4 }}>
            <TextArea
              autoFocus
              value={draft}
              onChange={(e) => setDraft(e.target.value)}
              autoSize={{ minRows: 1, maxRows: 8 }}
              maxLength={5000}
              onKeyDown={(e) => {
                if (e.key === 'Escape') { setEditing(false); setDraft(msg.body); }
                if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); save(); }
              }}
            />
            <Space size={6} style={{ marginTop: 6 }}>
              <Button size="small" onClick={() => { setEditing(false); setDraft(msg.body); }}>{t('Cancel')}</Button>
              <Button size="small" type="primary" loading={busy} onClick={save}>{t('Save')}</Button>
              <Text type="secondary" style={{ fontSize: 11 }}>{t('Enter to save · Shift+Enter for a new line')}</Text>
            </Space>
          </div>
        ) : (
          <>
            {msg.body ? (
              <div style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word', lineHeight: 1.55, fontSize: 14 }}>
                {msg.body}
                {msg.edited ? <Text type="secondary" style={{ fontSize: 11 }}> (edited)</Text> : null}
              </div>
            ) : null}
            {msg.attachments && msg.attachments.length > 0 && (
              <Space wrap size={[8, 6]} style={{ marginTop: msg.body ? 6 : 2 }}>
                {msg.attachments.map((a) => (
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
                    <span style={{ maxWidth: 240, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{a.original || a.filename}</span>
                    {a.size_text ? <Text type="secondary" style={{ fontSize: 12 }}>{a.size_text}</Text> : null}
                  </a>
                ))}
              </Space>
            )}
          </>
        )}
      </div>
      {mine && !editing && (
        <div className="inbox-actions" style={{ position: 'absolute', right: 8, top: showHeader ? 4 : -8, opacity: 0, background: token.colorBgElevated, border: `1px solid ${token.colorBorderSecondary}`, borderRadius: 6, padding: 1 }}>
          <Tooltip title={t('Edit')}><Button size="small" type="text" icon={<EditOutlined />} onClick={() => setEditing(true)} /></Tooltip>
          <Popconfirm title={t('Delete this message?')} okText={t('Delete')} okButtonProps={{ danger: true }} onConfirm={remove}>
            <Tooltip title={t('Delete')}><Button size="small" type="text" danger icon={<DeleteOutlined />} /></Tooltip>
          </Popconfirm>
        </div>
      )}
    </div>
  );
}

/**
 * Oldest at the top, newest at the bottom, pinned to the bottom on load and when a
 * message is sent — the way a chat reads. Messages from the same sender within a
 * few minutes are grouped under one header, as Slack does.
 */
export default function MessageList({ messages, me, hasMore, loadingOlder, onLoadOlder, onChanged, stickToBottom }) {
  const { token } = theme.useToken();
  const scroller = useRef(null);
  const lastId = messages.length ? messages[messages.length - 1].id : 0;

  useEffect(() => {
    if (stickToBottom && scroller.current) {
      scroller.current.scrollTop = scroller.current.scrollHeight;
    }
  }, [lastId, stickToBottom]); // eslint-disable-line react-hooks/exhaustive-deps

  let prev = null;
  return (
    <div ref={scroller} style={{ flex: 1, overflowY: 'auto', padding: '8px 12px' }}>
      {hasMore && (
        <div style={{ textAlign: 'center', marginBottom: 8 }}>
          <Button size="small" loading={loadingOlder} onClick={onLoadOlder}>{t('Load earlier messages')}</Button>
        </div>
      )}
      {messages.map((msg) => {
        const day = dayLabel(msg.created);
        const newDay = !prev || dayKey(prev.created) !== dayKey(msg.created);
        const grouped = prev && !newDay && prev.sender.id === msg.sender.id
          && minutesBetween(msg.created, prev.created) < 5;
        prev = msg;
        return (
          <React.Fragment key={msg.id}>
            {newDay && <DayDivider day={day} token={token} />}
            <Message msg={msg} mine={me && msg.sender.id === me.id} showHeader={!grouped} onChanged={onChanged} token={token} />
          </React.Fragment>
        );
      })}
    </div>
  );
}
