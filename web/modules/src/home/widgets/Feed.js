import React, { useCallback, useEffect, useState } from 'react';
import { Card, Empty, Spin, Button, Typography } from 'antd';
import { registerWidget } from '../registry';
import { openIntent } from '../intent';
import { listPosts, listKudos, errorText } from '../feedApi';
import FeedPost from './FeedPost';
import KudosFeedCard from './KudosFeedCard';
import { t } from '../../../../api/i18n';

const { Text } = Typography;

/**
 * The company feed: posts from the built-in "Public" channel and the kudos people
 * have given, interleaved newest-first, with reactions and comments inline.
 *
 * The two come from different modules with their own pagination, so each page pulls
 * a page of both and the merged list is re-sorted by time. Kudos are optional — if
 * that extension is absent the feed is simply posts.
 *
 * Lazy-loaded after first paint (it is not part of the appshell/home aggregation).
 * `newPost` carries a post the composer just created so it appears immediately.
 */
function Feed({ data, ctx, apiClient, newPost }) {
  const feed = (data && data.feed) || null;
  const channelId = feed && feed.channelId;
  const me = (data && data.me) || {};
  const kudosApp = (ctx.apps || []).find((a) => a.name === 'kudos');
  const discussionsApp = (ctx.apps || []).find((a) => a.name === 'discussions');

  const [state, setState] = useState({ loading: true, items: [], error: null });
  const [page, setPage] = useState(1);
  const [more, setMore] = useState(false);
  const [loadingMore, setLoadingMore] = useState(false);

  // Newest first. Anything without a usable timestamp sinks to the bottom rather
  // than jumping to the top.
  //
  // NB: use the Date constructor, never Date.parse — the app loads Date.js, which
  // replaces Date.parse with one returning a Date|null instead of a number. With
  // Date.parse this comparator silently returned 0 for everything and the merge
  // came out unsorted.
  const timeOf = (x) => {
    const ms = new Date(x && x.created ? x.created : 0).getTime();
    return Number.isNaN(ms) ? 0 : ms;
  };
  const byNewest = (a, b) => timeOf(b) - timeOf(a);

  const listOf = (payload) => {
    if (Array.isArray(payload)) return payload;
    return (payload && (payload.posts || payload.items || payload.list)) || [];
  };

  const load = useCallback((p, append) => {
    if (!apiClient || !channelId) { setState({ loading: false, items: [], error: null }); return; }
    if (append) setLoadingMore(true); else setState((s) => ({ ...s, loading: true }));

    // Kudos are a bonus: a failure there must not empty the feed.
    const kudosReq = kudosApp
      ? listKudos(apiClient, p).catch(() => [])
      : Promise.resolve([]);

    Promise.all([listPosts(apiClient, channelId, p), kudosReq])
      .then(([postPayload, kudosPayload]) => {
        const posts = listOf(postPayload).map((x) => ({ ...x, kind: 'post' }));
        const kudos = listOf(kudosPayload).map((x) => ({ ...x, kind: 'kudos' }));
        const merged = posts.concat(kudos).sort(byNewest);
        setMore(posts.length > 0 || kudos.length > 0
          ? (posts.length >= 10 || kudos.length >= 10)
          : false);
        setState((s) => ({
          loading: false,
          error: null,
          items: append ? s.items.concat(merged) : merged,
        }));
      })
      .catch((e) => setState({ loading: false, items: [], error: errorText(e, t('Could not load the feed')) }))
      .finally(() => setLoadingMore(false));
  }, [apiClient, channelId, kudosApp]);

  useEffect(() => { load(1, false); }, [load]);

  // A post created by the composer goes straight to the top.
  useEffect(() => {
    if (newPost && newPost.id) {
      setState((s) => (s.items.some((x) => x.kind === 'post' && x.id === newPost.id)
        ? s
        : { ...s, items: [{ ...newPost, kind: 'post' }].concat(s.items) }));
    }
  }, [newPost]);

  const dropItem = (kind, id) => setState((s) => ({
    ...s,
    items: s.items.filter((x) => !(x.kind === kind && x.id === id)),
  }));

  if (!feed) return null;

  if (state.loading) {
    return <Card size="small"><div style={{ textAlign: 'center', padding: 24 }}><Spin /></div></Card>;
  }

  if (state.error) {
    return (
      <Card size="small">
        <Empty description={state.error}>
          <Button onClick={() => load(1, false)}>{t('Retry')}</Button>
        </Empty>
      </Card>
    );
  }

  if (state.items.length === 0) {
    return (
      <Card size="small">
        <Empty
          image={Empty.PRESENTED_IMAGE_SIMPLE}
          description={feed.canPost ? t('Nothing here yet — say hello to the team') : t('Nothing has been posted yet')}
        />
      </Card>
    );
  }

  return (
    <div>
      {state.items.map((item) => (item.kind === 'kudos' ? (
        <KudosFeedCard
          key={`k${item.id}`}
          kudos={item}
          apiClient={apiClient}
          onDeleted={(id) => dropItem('kudos', id)}
          onOpen={kudosApp ? () => openIntent({ module: kudosApp.key }) : null}
        />
      ) : (
        <FeedPost
          key={`p${item.id}`}
          post={item}
          me={me}
          apiClient={apiClient}
          onDeleted={(id) => dropItem('post', id)}
          onOpen={discussionsApp ? (p) => openIntent({ module: discussionsApp.key, id: p.id }) : null}
        />
      )))}
      <div style={{ textAlign: 'center', paddingBottom: 8 }}>
        {more ? (
          <Button
            loading={loadingMore}
            onClick={() => { const next = page + 1; setPage(next); load(next, true); }}
          >
            {t('Load more')}
          </Button>
        ) : (
          <Text type="secondary" style={{ fontSize: 12 }}>{t("You're all caught up")}</Text>
        )}
      </div>
    </div>
  );
}

registerWidget({
  id: 'feed', order: 10, column: 'main', requires: { dataKey: 'feed' }, component: Feed,
});

export default Feed;
