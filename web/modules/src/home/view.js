import React, { useEffect, useState } from 'react';
import { Row, Col, Spin, Empty, Button } from 'antd';
import { getColumnWidgets } from './registry';
import { t } from '../../../api/i18n';
import ExtensionBanners from '../../../components/ExtensionBanners';
import './widgets';

/**
 * Home — the landing page / entry point for every user level.
 *
 * Laid out as a feed: a greeting band across the top, the company feed (composer +
 * posts from the built-in Public channel) down the wide centre column, and the
 * glanceable widgets — what needs you, my team, celebrations, kudos, your
 * apps — in the right rail.
 *
 * One aggregation call (appshell/home) feeds every lightweight widget; the widget
 * registry renders only what this user's role allows, so admins and employees share
 * the page and each sees their own view. The feed itself lazy-loads from the
 * discussions endpoints. Props come from NativeExtensionView:
 * { apiClient, ice, modJs }.
 */
export default function HomeView({ apiClient, modJs }) {
  const client = apiClient || (modJs && modJs.apiClient)
    || (typeof window !== 'undefined' && window.modJs && window.modJs.apiClient) || null;
  const [state, setState] = useState({ loading: true, data: null, error: null });
  // A post the composer just created, handed to the feed so it appears at once.
  const [newPost, setNewPost] = useState(null);

  const load = () => {
    if (!client) { setState({ loading: false, data: null, error: t('No API client') }); return; }
    setState((s) => ({ ...s, loading: true, error: null }));
    client.get('appshell/home')
      .then((r) => {
        const body = r && r.data;
        const d = (body && body.data && body.status) ? body.data : body;
        if (!d || typeof d !== 'object' || d.error) {
          setState({ loading: false, data: null, error: t('Could not load home') });
          return;
        }
        setState({ loading: false, data: d, error: null });
      })
      .catch(() => setState({ loading: false, data: null, error: t('Could not load home') }));
  };

  useEffect(() => { load(); }, []); // eslint-disable-line react-hooks/exhaustive-deps

  if (state.loading && !state.data) {
    return <div style={{ padding: 48, textAlign: 'center' }}><Spin size="large" /></div>;
  }
  if (!state.data) {
    return (
      <div style={{ padding: 48 }}>
        <Empty description={state.error || t('Nothing to show')}>
          <Button onClick={load}>{t('Retry')}</Button>
        </Empty>
      </div>
    );
  }

  const data = state.data;
  const ctx = {
    data,
    apps: data.apps || [],
    isManager: !!data.isManager,
    userLevel: data.userLevel,
    apiClient: client,
  };

  const render = (w) => {
    const Comp = w.component;
    return (
      <Comp
        key={w.id}
        data={data}
        ctx={ctx}
        apiClient={client}
        reload={load}
        newPost={newPost}
        onPosted={setNewPost}
      />
    );
  };

  const hero = getColumnWidgets(ctx, 'hero');
  const main = getColumnWidgets(ctx, 'main');
  const side = getColumnWidgets(ctx, 'side');
  // With no feed (discussions absent), the side rail becomes the whole page rather
  // than leaving a wide empty column.
  const hasMain = main.length > 0;

  return (
    // A social-feed shape: the hero spans the page, then a narrow centred feed
    // column with the glanceable widgets in a right rail beside it. The feed is
    // deliberately capped (~640px) so lines stay readable rather than stretching
    // across a wide screen.
    <div style={{ padding: 16, maxWidth: 1180, margin: '0 auto' }}>
      {/* Banners from extensions, e.g. "Complete your onboarding". */}
      <ExtensionBanners banners={data.banners} />
      {hero.length > 0 && (
        <div style={{ marginBottom: 16 }}>{hero.map(render)}</div>
      )}
      <Row gutter={20} justify="center">
        {hasMain && (
          <Col xs={24} lg={14} xl={14} style={{ maxWidth: 680 }}>
            {main.map(render)}
          </Col>
        )}
        <Col xs={24} lg={hasMain ? 10 : 24} xl={hasMain ? 10 : 24} style={hasMain ? { maxWidth: 420 } : undefined}>
          {hasMain ? (
            side.map((w) => <div key={w.id} style={{ marginBottom: 16 }}>{render(w)}</div>)
          ) : (
            <Row gutter={[16, 16]}>
              {side.map((w) => (
                <Col key={w.id} xs={24} md={12} xl={8}>{render(w)}</Col>
              ))}
            </Row>
          )}
        </Col>
      </Row>
    </div>
  );
}
