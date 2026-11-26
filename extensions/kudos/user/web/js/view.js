import React, { useCallback, useEffect, useState } from 'react';
import {
  Button, Result, Segmented, Space, Typography, theme,
} from 'antd';
import { PlusOutlined, ReloadOutlined, TrophyOutlined } from '@ant-design/icons';
import { api, errorMessage } from './api';
import KudosFeed from './components/KudosFeed';
import KudosComposer from './components/KudosComposer';
import { t } from '../../../../../web/api/i18n';

const { Title, Text } = Typography;

const TABS = [
  { value: 'feed', label: 'Feed' },
  { value: 'received', label: 'Received' },
  { value: 'given', label: 'Given' },
];

/**
 * Kudos — peer recognition. Three lists over the same cards (the company feed the
 * caller may see, kudos they received, kudos they gave) and the composer.
 *
 * A "compose" intent may arrive from elsewhere (e.g. Home's "Give Kudos" or a
 * birthday "Send wishes") as window.__kudosComposeIntent = { recipient }; it is
 * consumed once on mount so the composer opens pre-filled.
 */
export default function KudosUserView() {
  const { token } = theme.useToken();
  const [me, setMe] = useState(null);
  const [values, setValues] = useState([]);
  const [tab, setTab] = useState('feed');
  const [items, setItems] = useState([]);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(false);
  const [loading, setLoading] = useState(true);
  const [fatal, setFatal] = useState(null);
  const [composing, setComposing] = useState(false);
  const [initialRecipient, setInitialRecipient] = useState(null);

  const load = useCallback(async (which, pageNo, append) => {
    setLoading(true);
    try {
      const res = await api().get(`kudos/${which}/${pageNo}`);
      const data = res.data || {};
      const list = Array.isArray(data.items) ? data.items : [];
      setItems((prev) => (append ? prev.concat(list) : list));
      setPage(pageNo);
      setHasMore(!!data.has_more);
    } catch (e) {
      setFatal(errorMessage(e, t('Kudos could not be loaded')));
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    (async () => {
      let meData = null;
      try {
        const [meRes, valRes] = await Promise.all([api().get('kudos/me'), api().get('kudos/values')]);
        setMe(meRes.data);
        meData = meRes.data;
        setValues(Array.isArray(valRes.data) ? valRes.data : []);
      } catch (e) {
        setFatal(errorMessage(e, t('Kudos could not be loaded')));
        return;
      }
      load('feed', 1, false);
      // Consume a compose intent left by another screen — either the module's own
      // global or the shell's deep-link contract (window.iceShellOpenIntent sets
      // window.__iceShellIntent = { action, params }; Home's "Give Kudos" and
      // "Send wishes" use it). Consumed once so a later revisit opens the feed.
      try {
        let intent = window.__kudosComposeIntent || null;
        if (intent) {
          window.__kudosComposeIntent = null;
        } else {
          const shellIntent = window.__iceShellIntent;
          if (shellIntent && shellIntent.action === 'compose') {
            window.__iceShellIntent = null;
            intent = shellIntent.params || {};
          }
        }
        // With nothing left this month the page itself explains why; opening the
        // composer would only end in a server refusal.
        const left = meData && meData.quota ? meData.quota.remaining : 1;
        if (intent && left > 0) {
          setInitialRecipient(intent.recipient || null);
          setComposing(true);
        }
      } catch (e) { /* ignore */ }
    })();
  }, [load]);

  const switchTab = (value) => {
    setTab(value);
    setItems([]);
    load(value, 1, false);
  };

  const onPosted = (kudos) => {
    // A new kudos always belongs at the top of Feed and Given.
    if (tab === 'feed' || tab === 'given') {
      setItems((prev) => [kudos].concat(prev));
    }
    refreshMe();
  };

  // Posting or deleting changes this month's count; re-read it from the server,
  // which owns the limit and the month boundary.
  const refreshMe = () => {
    api().get('kudos/me').then((res) => setMe(res.data)).catch(() => {});
  };

  const onDeleted = (id) => {
    setItems((prev) => prev.filter((k) => k.id !== id));
    refreshMe();
  };

  if (fatal) {
    return <Result status="error" title={fatal} extra={<Button onClick={() => window.location.reload()}>{t('Reload')}</Button>} />;
  }

  const canGive = !!(me && me.can_give);
  const quota = (me && me.quota) || null;
  const outOfKudos = !!(quota && quota.remaining <= 0);

  return (
    <div style={{ maxWidth: 820, margin: '0 auto', padding: 16 }}>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginBottom: 16, gap: 12, flexWrap: 'wrap' }}>
        <Space align="center">
          <TrophyOutlined style={{ fontSize: 22, color: token.colorWarning }} />
          <Title level={4} style={{ margin: 0 }}>{t('Kudos')}</Title>
        </Space>
        <Space>
          <Segmented options={TABS.map((o) => ({ ...o, label: t(o.label) }))} value={tab} onChange={switchTab} />
          <Button icon={<ReloadOutlined />} onClick={() => load(tab, 1, false)} />
          <Button
            type="primary"
            icon={<PlusOutlined />}
            disabled={!canGive || outOfKudos}
            onClick={() => { setInitialRecipient(null); setComposing(true); }}
          >
            {t('Give kudos')}
          </Button>
        </Space>
      </div>

      {me && !canGive && (
        <Text type="secondary" style={{ display: 'block', marginBottom: 12 }}>
          {t('Your account has no employee profile, so you can read kudos but not give any.')}
        </Text>
      )}

      {canGive && quota && (
        <Text type={outOfKudos ? 'warning' : 'secondary'} style={{ display: 'block', marginBottom: 12 }}>
          {outOfKudos
            ? t("You've given all {limit} kudos for this month. More become available on the 1st.", { limit: quota.limit })
            : t('{remaining} of {limit} kudos left to give this month', { remaining: quota.remaining, limit: quota.limit })}
        </Text>
      )}

      <KudosFeed
        items={items}
        loading={loading}
        hasMore={hasMore}
        onLoadMore={() => load(tab, page + 1, true)}
        onDeleted={onDeleted}
        emptyText={tab === 'received' ? t('Nobody has given you kudos yet') : (tab === 'given' ? t('You have not given kudos yet') : t('No kudos yet — be the first'))}
      />

      <KudosComposer
        open={composing}
        onClose={() => setComposing(false)}
        onPosted={onPosted}
        values={values}
        initialRecipient={initialRecipient}
      />
    </div>
  );
}
