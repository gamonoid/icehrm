import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
  Button, Empty, Grid, Result, Spin, Typography, message, theme,
} from 'antd';
import { ArrowLeftOutlined, ReloadOutlined } from '@ant-design/icons';
import { api, errorMessage } from './api';
import ConversationList from './components/ConversationList';
import MessageList from './components/MessageList';
import Composer from './components/Composer';
import Person from './components/Person';
import useFillHeight from './useFillHeight';
import { setUserTimeZone } from './time';

import { t } from '../../../../../web/api/i18n';
const { Text } = Typography;
const { useBreakpoint } = Grid;

/** How often the unread bubbles are refreshed while the page is open. */
const POLL_MS = 30000;

/** Most recent message first, whoever sent it; never-messaged conversations last. */
const byRecency = (list) => [...list].sort((a, b) => {
  const ta = a.last_message_at ? new Date(a.last_message_at).getTime() : 0;
  const tb = b.last_message_at ? new Date(b.last_message_at).getTime() : 0;
  return tb - ta || b.id - a.id;
});

/**
 * Inbox — one-to-one messages, Slack style: conversations on the left, the thread on
 * the right with the composer at its foot. Not live: unread counts are polled every
 * 30 s (a tiny endpoint), and the open conversation reloads when that poll shows
 * something new in it; everything else refreshes on click.
 *
 * Below the shell's mobile breakpoint (antd md, 768px) the two panes do not fit side
 * by side, so it shows one at a time: the list, or the open thread with a Back button.
 */
export default function InboxUserView() {
  const { token } = theme.useToken();
  const [me, setMe] = useState(null);
  const [fatal, setFatal] = useState(null);
  const [conversations, setConversations] = useState([]);
  const [listLoading, setListLoading] = useState(true);
  const [selected, setSelected] = useState(null);
  const [thread, setThread] = useState({ messages: [], hasMore: false });
  const [threadLoading, setThreadLoading] = useState(false);
  const [olderLoading, setOlderLoading] = useState(false);
  const [stick, setStick] = useState(true);
  const selectedRef = useRef(null);
  selectedRef.current = selected;
  const rootRef = useRef(null);
  const fillHeight = useFillHeight(rootRef, 16);
  // `md === false`, not `!md`: breakpoints are unknown on the very first render and
  // that must not flash the one-pane layout on a desktop.
  const narrow = useBreakpoint().md === false;

  const loadConversations = useCallback(async () => {
    setListLoading(true);
    try {
      const res = await api().get('inbox/conversations');
      setConversations(byRecency(Array.isArray(res.data) ? res.data : []));
    } catch (e) {
      message.error(errorMessage(e, t('Could not load conversations')));
    } finally {
      setListLoading(false);
    }
  }, []);

  const loadThread = useCallback(async (conv) => {
    if (!conv) { setThread({ messages: [], hasMore: false }); return; }
    setThreadLoading(true);
    setStick(true);
    try {
      const res = await api().get(`inbox/conversations/${conv.id}/messages`);
      setThread({ messages: res.data.messages || [], hasMore: !!res.data.has_more });
      // Reading clears the bubble for this conversation.
      setConversations((prev) => prev.map((c) => (c.id === conv.id ? { ...c, unread: 0 } : c)));
    } catch (e) {
      message.error(errorMessage(e, t('Could not load the conversation')));
    } finally {
      setThreadLoading(false);
    }
  }, []);

  const loadOlder = async () => {
    if (!selected || !thread.messages.length) return;
    setOlderLoading(true);
    setStick(false);
    try {
      const first = thread.messages[0].id;
      const res = await api().get(`inbox/conversations/${selected.id}/messages/before/${first}`);
      setThread((th) => ({ messages: [...(res.data.messages || []), ...th.messages], hasMore: !!res.data.has_more }));
    } catch (e) {
      message.error(errorMessage(e, t('Could not load earlier messages')));
    } finally {
      setOlderLoading(false);
    }
  };

  // Boot: who am I, then my conversations.
  useEffect(() => {
    (async () => {
      try {
        const res = await api().get('inbox/me');
        setUserTimeZone(res.data.timezone);
        setMe(res.data);
        // "Message" on someone elsewhere (e.g. Home's My team) opens here through
        // the shell's deep-link contract: window.__iceShellIntent =
        // { action: 'message', params: { employee } }. Consumed once so a later
        // visit just opens the inbox.
        const intent = window.__iceShellIntent;
        let dmWith = null;
        if (intent && intent.action === 'message' && intent.params && intent.params.employee) {
          window.__iceShellIntent = null;
          dmWith = intent.params.employee;
        }
        // Open the DM after the list loads, so the list can't replace it.
        await loadConversations();
        if (dmWith) openWith({ id: dmWith });
      } catch (e) {
        setFatal(errorMessage(e, t('Inbox is not available')));
        setListLoading(false);
      }
    })();
  }, [loadConversations]);

  useEffect(() => { loadThread(selected); }, [selected ? selected.id : 0]); // eslint-disable-line react-hooks/exhaustive-deps

  // Unread poll: update bubbles; if the open conversation gained messages, reload it.
  useEffect(() => {
    if (!me) return undefined;
    const tick = async () => {
      try {
        const res = await api().get('inbox/unread');
        const by = res.data.by_conversation || {};
        const open = selectedRef.current;
        // Anything newly unread means a message arrived: re-fetch the list so the
        // conversation moves to the top with its new preview (and any conversation
        // someone just started appears). Otherwise just refresh the bubbles.
        let arrived = false;
        setConversations((prev) => {
          const known = {};
          prev.forEach((c) => { known[c.id] = c.unread || 0; });
          Object.keys(by).forEach((id) => { if (!(id in known) || by[id] > known[id]) arrived = true; });
          return prev.map((c) => ({ ...c, unread: (open && open.id === c.id) ? 0 : (by[c.id] || 0) }));
        });
        if (arrived) loadConversations();
        if (open && by[open.id] > 0) loadThread(open); // new messages in the one I'm reading
      } catch (e) { /* next tick */ }
    };
    const id = setInterval(tick, POLL_MS);
    return () => clearInterval(id);
  }, [me, loadConversations, loadThread]);

  const openWith = async (emp) => {
    try {
      const res = await api().post('inbox/conversations', { employee: emp.id });
      const conv = res.data;
      // Opening someone's conversation puts it at the top of the list for now.
      setConversations((prev) => (prev.some((c) => c.id === conv.id) ? prev : [conv, ...prev]));
      setSelected(conv);
    } catch (e) {
      message.error(errorMessage(e, t('Could not open the conversation')));
    }
  };

  const hide = async (conv) => {
    try {
      await api().post(`inbox/conversations/${conv.id}/hide`, {});
      setConversations((prev) => prev.filter((c) => c.id !== conv.id));
      if (selected && selected.id === conv.id) setSelected(null);
    } catch (e) {
      message.error(errorMessage(e, t('Could not hide the conversation')));
    }
  };

  const onSent = (msg) => {
    setStick(true);
    setThread((th) => ({ ...th, messages: [...th.messages, msg] }));
    // Bump it to the top of the list with the new preview.
    setConversations((prev) => {
      const cur = prev.find((c) => c.id === msg.conversation);
      const updated = { ...(cur || selected), last_message_at: msg.created, preview: { mine: true, text: (msg.body || '').slice(0, 80) } };
      return byRecency([updated, ...prev.filter((c) => c.id !== msg.conversation)]);
    });
  };

  const refresh = () => { loadConversations(); if (selected) loadThread(selected); };

  if (fatal) {
    return <div style={{ padding: 32 }}><Result status="warning" title={t('Inbox unavailable')} subTitle={fatal} /></div>;
  }

  return (
    <div ref={rootRef} style={{ display: 'flex', height: fillHeight || 'calc(100vh - 64px - 32px)', minHeight: 360, margin: narrow ? 8 : 16, borderRadius: 10, overflow: 'hidden', border: `1px solid ${token.colorBorderSecondary}`, background: token.colorBgLayout }}>
      {/* Hover-only affordances. Lives at the root so it applies whether or not a
          thread is open (the hide ✕ is in the list, not the thread). */}
      <style>{`
        .inbox-message:hover .inbox-actions { opacity: 1 !important; }
        .inbox-message:hover .inbox-time { opacity: 1 !important; }
        .inbox-conversation .inbox-hide { opacity: 0 !important; }
        .inbox-conversation:hover .inbox-hide { opacity: 0.7 !important; }
      `}</style>
      <div style={{
        width: narrow ? '100%' : 280,
        flex: 'none',
        display: narrow && selected ? 'none' : 'block',
        background: token.colorBgContainer,
        borderRight: narrow ? 'none' : `1px solid ${token.colorBorderSecondary}`,
      }}
      >
        <ConversationList
          conversations={conversations}
          selectedId={selected ? selected.id : null}
          onSelect={(c) => { if (selected && selected.id === c.id) loadThread(c); else setSelected(c); }}
          onHide={hide}
          onRefresh={refresh}
          onPick={openWith}
          loading={listLoading}
        />
      </div>

      <div style={{ flex: 1, minWidth: 0, display: narrow && !selected ? 'none' : 'flex', flexDirection: 'column' }}>
        {!selected && (
          <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            {listLoading ? <Spin size="large" /> : (
              <Empty description={conversations.length ? t('Pick a conversation on the left') : t('Find a colleague on the left to start a conversation')} />
            )}
          </div>
        )}
        {selected && (
          <>
            <div style={{ padding: '10px 16px', background: token.colorBgContainer, borderBottom: `1px solid ${token.colorBorderSecondary}`, display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12 }}>
              <div style={{ display: 'flex', alignItems: 'center', gap: 8, minWidth: 0 }}>
                {narrow && (
                  <Button type="text" icon={<ArrowLeftOutlined />} aria-label={t('Back')} onClick={() => setSelected(null)} />
                )}
                <Person person={selected.other} size={36} />
              </div>
              <Button size="small" icon={<ReloadOutlined spin={threadLoading} />} onClick={() => loadThread(selected)}>{t('Refresh')}</Button>
            </div>
            {threadLoading && thread.messages.length === 0 ? (
              <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center' }}><Spin /></div>
            ) : (
              thread.messages.length === 0 ? (
                <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
                  <Empty description={<Text type="secondary">This is the start of your conversation with {selected.other.name}.</Text>} />
                </div>
              ) : (
                <MessageList
                  messages={thread.messages}
                  me={me}
                  hasMore={thread.hasMore}
                  loadingOlder={olderLoading}
                  onLoadOlder={loadOlder}
                  onChanged={() => loadThread(selected)}
                  stickToBottom={stick}
                />
              )
            )}
            <Composer conversation={selected} me={me} onSent={onSent} />
          </>
        )}
      </div>
    </div>
  );
}
