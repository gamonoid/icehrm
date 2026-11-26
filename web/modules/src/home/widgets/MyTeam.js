import React from 'react';
import {
  Card, Avatar, Button, Popover, Space, Tooltip, Typography,
} from 'antd';
import { MessageOutlined, TeamOutlined } from '@ant-design/icons';
import { registerWidget } from '../registry';
import { openIntent } from '../intent';
import {
  ACCENT, chip, personColor, initials,
} from '../palette';
import { t } from '../../../../api/i18n';

const { Text } = Typography;

function PersonAvatar({ person, size }) {
  return (
    <Avatar
      size={size}
      src={person.image || undefined}
      style={person.image ? undefined : { background: personColor(person.name) }}
    >
      {person.image ? null : initials(person.name)}
    </Avatar>
  );
}

/** Click a face: who they are, and a button straight into a DM with them. */
function PersonCard({ person, inboxApp }) {
  const subtitle = [person.title, person.role].filter(Boolean).join(' · ');
  return (
    <div style={{ width: 220 }}>
      <Space align="center" size={12}>
        <PersonAvatar person={person} size={44} />
        <div style={{ minWidth: 0 }}>
          <div style={{ fontWeight: 600 }}>{person.name}</div>
          {subtitle && <Text type="secondary" style={{ fontSize: 12 }}>{subtitle}</Text>}
        </div>
      </Space>
      {inboxApp && (
        <Button
          type="primary"
          size="small"
          block
          icon={<MessageOutlined />}
          style={{ marginTop: 12 }}
          onClick={() => openIntent({
            module: inboxApp.key, action: 'message', params: { employee: person.id, name: person.name },
          })}
        >
          {t('Message')}
        </Button>
      )}
    </div>
  );
}

/**
 * My team ("Glance"): the faces of the people this user works with — their teams
 * (Team extension), or else their manager, peers and direct reports — each one a
 * click away from a direct message in Inbox (when the user has Inbox).
 */
function MyTeam({ data, ctx }) {
  const groups = ((data && data.myTeam && data.myTeam.groups) || []);
  const apps = ctx.apps || [];
  const inboxApp = apps.find((a) => a.name === 'inbox');
  // By route key: admins also have the Team extension's admin part.
  const teamApp = apps.find((a) => a.key === 'extension::team|user');

  return (
    <Card
      size="small"
      style={{ height: '100%' }}
      title={(
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 8 }}>
          <span style={chip(ACCENT.team)}><TeamOutlined /></span>
          {groups.length > 1 ? t('My teams') : t('My team')}
        </span>
      )}
      extra={teamApp && groups.some((g) => g.id) ? (
        <a onClick={() => openIntent({ module: teamApp.key })}>{t('View all')}</a>
      ) : null}
    >
      <Space direction="vertical" size={14} style={{ width: '100%' }}>
        {groups.map((g) => (
          <div key={g.id || g.name}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 6, marginBottom: 8 }}>
              <span style={{
                width: 8, height: 8, borderRadius: '50%', background: g.color || ACCENT.team, flex: '0 0 auto',
              }}
              />
              <Text strong style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                {/* The server's built-in fallback group; real team names are data. */}
                {(!g.id && g.name === 'Your team') ? t('Your team') : g.name}
              </Text>
              <Text type="secondary" style={{ fontSize: 12, flex: '0 0 auto' }}>{g.members.length}</Text>
            </div>
            <Space wrap size={8}>
              {g.members.map((p) => (
                <Popover
                  key={p.id}
                  trigger="click"
                  placement="left"
                  content={<PersonCard person={p} inboxApp={inboxApp} />}
                >
                  <Tooltip title={p.name} mouseEnterDelay={0.3}>
                    <span style={{ cursor: 'pointer', display: 'inline-flex' }}>
                      <PersonAvatar person={p} size={36} />
                    </span>
                  </Tooltip>
                </Popover>
              ))}
            </Space>
          </div>
        ))}
      </Space>
    </Card>
  );
}

registerWidget({
  id: 'myTeam', order: 30, column: 'side', size: 'full', requires: { dataKey: 'myTeam' }, component: MyTeam,
});

export default MyTeam;
