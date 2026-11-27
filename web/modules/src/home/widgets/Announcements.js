import React from 'react';
import {
  Card, Avatar, List, Typography,
} from 'antd';
import { NotificationOutlined } from '@ant-design/icons';
import { registerWidget } from '../registry';
import { openIntent } from '../intent';
import {
  ACCENT, chip, personColor, initials,
} from '../palette';
import { t, tp, formatDate } from '../../../../api/i18n';

const { Text } = Typography;

const clamp = (lines) => ({
  display: '-webkit-box', WebkitLineClamp: lines, WebkitBoxOrient: 'vertical', overflow: 'hidden',
});

/** "Today" / "Yesterday" / "3 days ago" / "Sep 2" from a server 'Y-m-d H:i:s'. */
function ago(created) {
  const p = String(created || '').split(/[- :]/).map(Number);
  if (p.length < 3) return '';
  const d = new Date(p[0], p[1] - 1, p[2]);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const days = Math.round((today - d) / 86400000);
  if (days <= 0) return t('Today');
  if (days === 1) return t('Yesterday');
  if (days < 7) return tp(days, '{count} day ago', '{count} days ago');
  return formatDate(d, { month: 'short', day: 'numeric' });
}

/**
 * Announcements ("Glance"): the latest posts in the built-in Announcements
 * discussions channel — shown only when something was announced recently
 * (appshell/home sends no `announcements` otherwise). A post opens in Discussions.
 */
function Announcements({ data, ctx }) {
  const ann = (data && data.announcements) || {};
  const posts = Array.isArray(ann.posts) ? ann.posts : [];
  const discussionsApp = (ctx.apps || []).find((a) => a.key === 'extension::discussions|user');
  const open = (intent) => discussionsApp && openIntent({ module: discussionsApp.key, ...intent });

  return (
    <Card
      size="small"
      style={{ height: '100%' }}
      title={(
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 8 }}>
          <span style={chip(ACCENT.announcements)}><NotificationOutlined /></span>
          {t('Announcements')}
        </span>
      )}
      extra={discussionsApp ? (
        <a onClick={() => open({ action: 'openChannel', params: { channel: ann.channelId } })}>{t('View all')}</a>
      ) : null}
    >
      <List
        size="small"
        dataSource={posts}
        renderItem={(p) => (
          <List.Item
            style={{
              paddingLeft: 6, paddingRight: 6, borderRadius: 8, cursor: discussionsApp ? 'pointer' : 'default',
            }}
            onClick={() => open({ action: 'openPost', params: { post: p.id } })}
          >
            <div style={{ minWidth: 0, width: '100%' }}>
              <div style={{ fontWeight: 600, ...clamp(2) }}>{p.title}</div>
              {p.excerpt && (
                <Text type="secondary" style={{ fontSize: 12, marginTop: 2, ...clamp(2) }}>{p.excerpt}</Text>
              )}
              <div style={{
                display: 'flex', alignItems: 'center', gap: 6, marginTop: 6, minWidth: 0,
              }}
              >
                {p.author && (
                  <>
                    <Avatar
                      size={18}
                      src={p.author.image || undefined}
                      style={p.author.image ? undefined : { background: personColor(p.author.name), fontSize: 9 }}
                    >
                      {p.author.image ? null : initials(p.author.name)}
                    </Avatar>
                    <Text type="secondary" style={{ fontSize: 12, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                      {p.author.name}
                    </Text>
                    <Text type="secondary" style={{ fontSize: 12 }}>·</Text>
                  </>
                )}
                <Text type="secondary" style={{ fontSize: 12, flex: '0 0 auto' }}>{ago(p.created)}</Text>
              </div>
            </div>
          </List.Item>
        )}
      />
    </Card>
  );
}

registerWidget({
  id: 'announcements', order: 32, column: 'side', size: 'full', requires: { dataKey: 'announcements' }, component: Announcements,
});

export default Announcements;
