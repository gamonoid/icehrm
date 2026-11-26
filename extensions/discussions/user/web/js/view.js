import React, { useCallback, useEffect, useRef, useState } from 'react';
import {
  Button, Empty, Grid, Result, Space, Spin, Tag, Typography, message, theme,
} from 'antd';
import { ArrowLeftOutlined, LockOutlined, NotificationOutlined, ReloadOutlined } from '@ant-design/icons';
import { api, errorMessage } from './api';
import ChannelSidebar from './components/ChannelSidebar';
import PostComposer from './components/PostComposer';
import PostCard from './components/PostCard';
import SearchResults from './components/SearchResults';
import NewChannelModal from './components/NewChannelModal';
import useFillHeight from './useFillHeight';
import { setUserTimeZone } from './time';

import { t } from '../../../../../web/api/i18n';
const { Text, Title } = Typography;
const { useBreakpoint } = Grid;

/**
 * Discussions — a Slack-like channel view without the live part. Nothing here
 * polls or listens: the feed is fetched when a channel is opened, when Refresh is
 * pressed, and after the reader's own post / reply lands.
 *
 * Below the shell's mobile breakpoint (antd md, 768px) the sidebar and the feed do
 * not fit side by side, so it shows one at a time: the channel list first, then
 * whatever the reader opens (a channel, search results, a post) with a way back.
 */
export default function DiscussionsUserView() {
  const { token } = theme.useToken();
  const [me, setMe] = useState(null);
  const [channels, setChannels] = useState([]);
  const [channelsLoading, setChannelsLoading] = useState(true);
  const [selected, setSelected] = useState(null);
  const [feed, setFeed] = useState(null);
  const [feedLoading, setFeedLoading] = useState(false);
  const [fatal, setFatal] = useState(null);

  // Search across channels; while `search` is set the results replace the feed.
  const [search, setSearch] = useState(null); // { q, results, truncated, loading }
  // A single post opened from a result: { postId, replyId, data:{channel,post}|null, loading, fromSearch }
  const [focused, setFocused] = useState(null);
  const [creating, setCreating] = useState(false);
  const rootRef = useRef(null);
  const fillHeight = useFillHeight(rootRef, 16);
  // `md === false`, not `!md`: breakpoints are unknown on the very first render and
  // that must not flash the one-pane layout on a desktop.
  const narrow = useBreakpoint().md === false;
  // One-pane mode only: true while the feed side is showing instead of the list.
  const [showMain, setShowMain] = useState(false);

  const loadChannels = useCallback(async (keepSelection) => {
    setChannelsLoading(true);
    try {
      const [meRes, chRes] = await Promise.all([api().get('discussions/me'), api().get('discussions/channels')]);
      setUserTimeZone(meRes.data.timezone);
      setMe(meRes.data);
      const list = Array.isArray(chRes.data) ? chRes.data : [];
      setChannels(list);
      setSelected((prev) => {
        if (keepSelection && prev) {
          const still = list.find((c) => c.id === prev.id);
          if (still) return still;
        }
        return list.length ? list[0] : null;
      });
    } catch (e) {
      setFatal(errorMessage(e, t('Discussions could not be loaded')));
    } finally {
      setChannelsLoading(false);
    }
  }, []);

  const loadFeed = useCallback(async (channel, page) => {
    if (!channel) { setFeed(null); return; }
    setFeedLoading(true);
    try {
      // Page in the path: IceApiClient.get() takes a bare endpoint, no params/query.
      const n = page || 1;
      const res = await api().get(`discussions/channels/${channel.id}/posts${n > 1 ? `/${n}` : ''}`);
      const data = res.data;
      setFeed((prev) => (page && page > 1 && prev && prev.channel.id === channel.id
        ? { ...data, posts: [...prev.posts, ...data.posts] }
        : data));
      // The server's view of what this reader may do is the one that counts.
      setSelected((prev) => (prev && prev.id === data.channel.id ? { ...prev, ...data.channel } : prev));
    } catch (e) {
      message.error(errorMessage(e, t('Could not load this channel')));
    } finally {
      setFeedLoading(false);
    }
  }, []);

  useEffect(() => { loadChannels(false); }, [loadChannels]);
  useEffect(() => { loadFeed(selected, 1); }, [selected ? selected.id : 0]); // eslint-disable-line react-hooks/exhaustive-deps

  const refresh = () => { loadChannels(true); if (selected) loadFeed(selected, 1); };
  const selectChannel = (ch) => {
    // Leaving search / a focused post; clicking the open channel again refreshes it.
    setSearch(null);
    setFocused(null);
    setShowMain(true);
    if (selected && selected.id === ch.id) loadFeed(ch, 1); else setSelected(ch);
  };

  const lastSearch = useRef(null);
  const runSearch = async (q) => {
    setFocused(null);
    setShowMain(true);
    setSearch({ q, results: [], truncated: false, loading: true });
    try {
      const res = await api().post('discussions/search', { q });
      const next = { q, results: res.data.results || [], truncated: !!res.data.truncated, loading: false };
      lastSearch.current = next;
      setSearch(next);
    } catch (e) {
      message.error(errorMessage(e, t('Search failed')));
      setSearch(null);
    }
  };

  // Open one post (optionally landing on one of its replies), keeping the search
  // results around so Back returns to them.
  const openPost = async (postId, replyId, fromSearch) => {
    setFocused({ postId, replyId, data: null, loading: true, fromSearch: !!fromSearch });
    setShowMain(true);
    try {
      const res = await api().get(`discussions/posts/${postId}`);
      const ch = channels.find((c) => c.id === res.data.channel.id);
      if (ch) setSelected(ch);
      setFocused({ postId, replyId, data: res.data, loading: false, fromSearch: !!fromSearch });
    } catch (e) {
      message.error(errorMessage(e, t('Could not open that post')));
      setFocused(null);
    }
  };
  const openResult = (r) => { setSearch(null); openPost(r.post_id, r.reply_id, true); };

  // Deep links from elsewhere (e.g. Home's Announcements) through the shell's
  // contract: window.__iceShellIntent = { action: 'openPost', params: { post } }
  // or { action: 'openChannel', params: { channel } }. Waits for the channel list
  // (openPost selects the post's channel from it); consumed once.
  useEffect(() => {
    if (!channels.length) return;
    const intent = window.__iceShellIntent;
    if (!intent || !intent.params) return;
    if (intent.action === 'openPost' && intent.params.post) {
      window.__iceShellIntent = null;
      openPost(intent.params.post, null, false);
    } else if (intent.action === 'openChannel' && intent.params.channel) {
      window.__iceShellIntent = null;
      const ch = channels.find((c) => c.id === Number(intent.params.channel));
      if (ch) { setSelected(ch); setShowMain(true); }
    }
  }, [channels]); // eslint-disable-line react-hooks/exhaustive-deps

  // A channel created here is selected as soon as the list is reloaded.
  const onChannelCreated = async (ch) => {
    setCreating(false);
    setSearch(null);
    setFocused(null);
    setShowMain(true);
    await loadChannels(true);
    setSelected((prev) => (prev && prev.id === ch.id ? prev : { ...ch, can_post: true, can_reply: true }));
  };
  // Editing lives on the Manage page; hand admins over to it.
  const goToManage = () => { window.location.hash = 'extension::discussions|admin'; };
  const reloadFocused = () => { if (focused) openPost(focused.postId, focused.replyId, focused.fromSearch); };

  if (fatal) {
    return <div style={{ padding: 32 }}><Result status="warning" title={t('Discussions unavailable')} subTitle={fatal} extra={<Button onClick={() => { setFatal(null); loadChannels(false); }}>{t('Try again')}</Button>} /></div>;
  }

  const channel = selected;
  const posts = feed && feed.channel.id === (channel ? channel.id : -1) ? feed.posts : [];
  const hasMore = !!(feed && feed.has_more && feed.channel.id === (channel ? channel.id : -1));
  const listHidden = narrow && showMain;
  const mainHidden = narrow && !showMain;

  return (
    <div ref={rootRef} style={{ display: 'flex', height: fillHeight || 'calc(100vh - 64px - 32px)', minHeight: 360, margin: narrow ? 8 : 16, gap: 0, borderRadius: 10, overflow: 'hidden', border: `1px solid ${token.colorBorderSecondary}`, background: token.colorBgLayout }}>
      {/* Channel list */}
      <div style={{
        width: narrow ? '100%' : 240,
        flex: 'none',
        display: listHidden ? 'none' : 'block',
        background: token.colorBgContainer,
        borderRight: narrow ? 'none' : `1px solid ${token.colorBorderSecondary}`,
      }}
      >
        <ChannelSidebar
          channels={channels}
          selectedId={channel ? channel.id : null}
          onSelect={selectChannel}
          onRefresh={refresh}
          loading={channelsLoading || feedLoading}
          onSearch={runSearch}
          searching={!!(search && search.loading)}
          canManage={!!(me && me.is_admin)}
          onCreate={() => setCreating(true)}
          onManage={goToManage}
        />
        <NewChannelModal open={creating} onClose={() => setCreating(false)} onCreated={onChannelCreated} />
      </div>

      {/* Feed */}
      <div style={{ flex: 1, minWidth: 0, display: mainHidden ? 'none' : 'flex', flexDirection: 'column' }}>
        {channelsLoading && !channel && (
          <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center' }}><Spin size="large" /></div>
        )}
        {!channelsLoading && !channel && !search && (
          <div style={{ flex: 1, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <Empty description={t('There are no channels you can read yet')} />
          </div>
        )}
        {search && (
          <div style={{ flex: 1, overflowY: 'auto' }}>
            <SearchResults
              q={search.q}
              results={search.results}
              truncated={search.truncated}
              loading={search.loading}
              onOpen={openResult}
              onClose={() => setSearch(null)}
            />
          </div>
        )}

        {!search && focused && (
          <>
            <div style={{ padding: narrow ? '10px 12px' : '10px 20px', background: token.colorBgContainer, borderBottom: `1px solid ${token.colorBorderSecondary}`, display: 'flex', alignItems: 'center', gap: 12, flexWrap: 'wrap' }}>
              <Button
                size="small"
                icon={<ArrowLeftOutlined />}
                onClick={() => { setFocused(null); if (focused.fromSearch && search === null) { /* results were kept in `search` only while open */ } }}
              >
                {focused.data ? t('Back to #{name}', { name: focused.data.channel.name }) : t('Back')}
              </Button>
              {focused.fromSearch && (
                <Button size="small" type="link" onClick={() => { setFocused(null); setSearch((s) => s || lastSearch.current); }}>
                  {t('Back to search results')}
                </Button>
              )}
              <Text type="secondary" style={{ fontSize: 13 }}>{t('Viewing one post')}</Text>
            </div>
            <div style={{ flex: 1, overflowY: 'auto', padding: narrow ? 8 : 16 }}>
              {focused.loading && <div style={{ textAlign: 'center', padding: 40 }}><Spin /></div>}
              {!focused.loading && focused.data && me && (
                <PostCard
                  post={focused.data.post}
                  channel={focused.data.channel}
                  me={me}
                  defaultShowReplies
                  highlightReplyId={focused.replyId || null}
                  onChanged={reloadFocused}
                  onDeleted={() => { setFocused(null); if (selected) loadFeed(selected, 1); }}
                />
              )}
            </div>
          </>
        )}

        {!search && !focused && channel && (
          <>
            <div style={{ padding: narrow ? '10px 12px' : '12px 20px', background: token.colorBgContainer, borderBottom: `1px solid ${token.colorBorderSecondary}`, display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 12 }}>
              {narrow && (
                <Button type="text" icon={<ArrowLeftOutlined />} aria-label={t('Back')} onClick={() => setShowMain(false)} style={{ flex: 'none' }} />
              )}
              <div style={{ minWidth: 0, flex: 1 }}>
                <Title level={5} style={{ margin: 0, display: 'flex', alignItems: 'center', gap: 8 }}>
                  {channel.is_system ? <NotificationOutlined style={{ color: token.colorPrimary }} /> : <Text type="secondary">#</Text>}
                  <span style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{channel.name}</span>
                </Title>
                {channel.description ? <Text type="secondary" style={{ fontSize: 13 }}>{channel.description}</Text> : null}
              </div>
              <Space size={6} wrap style={{ justifyContent: 'flex-end' }}>
                {!channel.can_post && (
                  <Tag icon={<LockOutlined />} style={{ marginInlineEnd: 0 }}>
                    Posting: {channel.post_levels.join(', ')}
                  </Tag>
                )}
                {!channel.can_reply && (
                  <Tag icon={<LockOutlined />} style={{ marginInlineEnd: 0 }}>{t('Replies off for you')}</Tag>
                )}
                <Button size="small" icon={<ReloadOutlined spin={feedLoading} />} onClick={() => loadFeed(channel, 1)}>{t('Refresh')}</Button>
              </Space>
            </div>

            <div style={{ flex: 1, overflowY: 'auto', padding: narrow ? 8 : 16 }}>
              {channel.can_post && me && (
                <div style={{ marginBottom: 16 }}>
                  <PostComposer channel={channel} me={me} onSaved={() => loadFeed(channel, 1)} />
                </div>
              )}

              {feedLoading && posts.length === 0 && (
                <div style={{ textAlign: 'center', padding: 40 }}><Spin /></div>
              )}
              {!feedLoading && posts.length === 0 && (
                <div style={{ padding: 40 }}>
                  <Empty description={channel.can_post ? t('Nothing here yet — start the conversation.') : t('Nothing has been posted here yet.')} />
                </div>
              )}

              <Space direction="vertical" size={12} style={{ width: '100%' }}>
                {posts.map((p) => (
                  <PostCard
                    key={p.id}
                    post={p}
                    channel={channel}
                    me={me}
                    onChanged={() => loadFeed(channel, 1)}
                    onDeleted={() => loadFeed(channel, 1)}
                  />
                ))}
              </Space>

              {hasMore && (
                <div style={{ textAlign: 'center', marginTop: 16 }}>
                  <Button loading={feedLoading} onClick={() => loadFeed(channel, (feed.page || 1) + 1)}>{t('Show older posts')}</Button>
                </div>
              )}
            </div>
          </>
        )}
      </div>
    </div>
  );
}
