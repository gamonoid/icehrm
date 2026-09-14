import React, { useEffect, useState } from 'react';
import { Alert, Button, Space } from 'antd';

/**
 * News — shows an announcement from icehrm.com on the dashboard, mirroring the
 * legacy dashboard "news" feature. The backend (appshell/news) fetches and
 * caches icehrm.com's /sapi/news for the current version + user level and only
 * returns an item when it's flagged to show and the user hasn't dismissed it.
 * Dismissing suppresses it per user for `dismiss_period` seconds via the shared
 * dismiss-news endpoint (UserMeta), the same store the legacy UI used.
 */
export default function News({ config }) {
  const [news, setNews] = useState(null);

  useEffect(() => {
    let cancelled = false;
    fetch(`${config.restApiBase}appshell/news`, {
      headers: { Authorization: `Bearer ${config.token}` },
      credentials: 'same-origin',
    })
      .then((r) => (r.ok ? r.json() : null))
      .then((item) => { if (!cancelled) setNews(item && item.id ? item : null); })
      .catch(() => { /* no news on failure — same as legacy */ });
    return () => { cancelled = true; };
  }, [config.restApiBase, config.token]);

  if (!news || !news.id) {
    return null;
  }

  const dismiss = () => {
    setNews(null);
    fetch(`${config.restApiBase}dismiss-news`, {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${config.token}`,
        'Content-Type': 'application/json',
      },
      credentials: 'same-origin',
      body: JSON.stringify({ id: news.id, period: news.dismiss_period || 86400 }),
    }).catch(() => { /* dismissal is best-effort */ });
  };

  return (
    <Alert
      type="info"
      showIcon
      style={{ margin: '16px 24px 0' }}
      message={news.title}
      description={news.message}
      action={(
        <Space>
          {news.url && news.button_text ? (
            <Button
              size="small"
              type="primary"
              onClick={() => window.open(news.url, '_blank', 'noopener,noreferrer')}
            >
              {news.button_text}
            </Button>
          ) : null}
          <Button size="small" onClick={dismiss}>Dismiss</Button>
        </Space>
      )}
    />
  );
}
