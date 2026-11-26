import React, { useState } from 'react';
import { Popover, Avatar, Typography, Spin, theme } from 'antd';
import { UserOutlined } from '@ant-design/icons';
import { t } from '../api/i18n';

const { Text } = Typography;

/**
 * An @mention in rendered post content: the highlighted name, plus a hover card
 * showing who that person actually is (picture, job title, department).
 *
 * Shared by the Home feed and the Discussions module so a mention looks and
 * behaves the same wherever a post is read.
 *
 * The profile is fetched only on first hover — a post can carry many mentions and
 * a feed many posts, so loading them up front would be a burst of requests for
 * something most readers never look at. Results are cached per employee for the
 * life of the page, and in-flight requests are shared, so hovering the same person
 * repeatedly (or twice in one post) costs one request.
 */

const cache = new Map();    // employeeId -> person
const inflight = new Map(); // employeeId -> Promise

function loadPerson(id) {
  if (cache.has(id)) return Promise.resolve(cache.get(id));
  if (inflight.has(id)) return inflight.get(id);
  const client = (typeof window !== 'undefined' && window.modJs) ? window.modJs.apiClient : null;
  if (!client) return Promise.resolve(null);
  const req = client.get(`discussions/person/${id}`)
    .then((r) => {
      const body = r && r.data;
      const person = (body && body.status && body.data !== undefined) ? body.data : body;
      cache.set(id, person || null);
      inflight.delete(id);
      return person || null;
    })
    .catch(() => {
      // Cache the miss too: a deleted or unreadable employee should not be
      // re-requested on every hover.
      cache.set(id, null);
      inflight.delete(id);
      return null;
    });
  inflight.set(id, req);
  return req;
}

function Card({ person, loading, fallbackName }) {
  if (loading) {
    return <div style={{ padding: '6px 4px', minWidth: 160, textAlign: 'center' }}><Spin size="small" /></div>;
  }
  if (!person) {
    return (
      <div style={{ padding: '2px 4px', minWidth: 160 }}>
        <Text strong>{fallbackName}</Text>
        <div><Text type="secondary" style={{ fontSize: 12 }}>{t('Profile unavailable')}</Text></div>
      </div>
    );
  }
  return (
    <div style={{ display: 'flex', gap: 10, alignItems: 'center', minWidth: 180, padding: '2px 4px' }}>
      <Avatar size={44} src={person.image || undefined} icon={<UserOutlined />} />
      <div style={{ minWidth: 0 }}>
        <div><Text strong>{person.name}</Text></div>
        {person.job_title ? (
          <div><Text style={{ fontSize: 12 }}>{person.job_title}</Text></div>
        ) : null}
        {person.department ? (
          <div><Text type="secondary" style={{ fontSize: 12 }}>{person.department}</Text></div>
        ) : null}
      </div>
    </div>
  );
}

export default function MentionChip({ employeeId, children }) {
  const { token } = theme.useToken();
  const id = parseInt(employeeId, 10);
  const [person, setPerson] = useState(() => (cache.has(id) ? cache.get(id) : undefined));
  const [loading, setLoading] = useState(false);

  const onOpenChange = (isOpen) => {
    if (!isOpen || person !== undefined || !id) return;
    setLoading(true);
    loadPerson(id)
      .then((p) => setPerson(p))
      .finally(() => setLoading(false));
  };

  const chip = (
    <span
      data-employee={employeeId || undefined}
      style={{
        color: token.colorPrimary,
        background: token.colorPrimaryBg,
        borderRadius: 4,
        padding: '0 4px',
        fontWeight: 600,
        whiteSpace: 'nowrap',
        cursor: 'default',
      }}
    >
      {children}
    </span>
  );

  // Without a usable id there is nothing to look up — render the plain chip.
  if (!id) return chip;

  const label = React.Children.toArray(children).join('') || t('This person');
  return (
    <Popover
      trigger="hover"
      mouseEnterDelay={0.35}
      placement="top"
      onOpenChange={onOpenChange}
      content={<Card person={person || null} loading={loading} fallbackName={label} />}
    >
      {chip}
    </Popover>
  );
}
