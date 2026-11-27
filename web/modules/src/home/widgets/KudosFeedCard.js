import React, { useState } from 'react';
import {
  Card, Avatar, Typography, Space, Button, Tooltip, Dropdown, Tag, message, theme,
} from 'antd';
import {
  TrophyOutlined, LikeOutlined, DeleteOutlined, MoreOutlined, ExportOutlined,
} from '@ant-design/icons';
import { timeAgo, toggleReaction, deleteKudos, errorText } from '../feedApi';
import { ACCENT, personColor, initials, soft } from '../palette';
import { t } from '../../../../api/i18n';

const { Text } = Typography;

const QUICK_EMOJI = ['👍', '🎉', '❤️', '👏', '😄', '😮'];

function PersonAvatar({ person, size }) {
  const p = person || {};
  return (
    <Avatar
      size={size}
      src={p.image || undefined}
      style={p.image ? undefined : { background: personColor(p.name) }}
    >
      {p.image ? null : initials(p.name)}
    </Avatar>
  );
}

/**
 * A kudos rendered as a feed item, alongside ordinary posts.
 *
 * Given the same card anatomy as a post (author line, body, reaction summary,
 * action bar) so the stream reads consistently, but with a celebratory banner and
 * the recipient front and centre — a kudos is about who received it.
 *
 * Reactions go through the shared core Reactions API with target_type "Kudos", and
 * arrive already attached to the payload, so no extra fetch is needed. Kudos have
 * no replies in the data model, so there is no Comment action.
 */
export default function KudosFeedCard({ kudos, apiClient, onDeleted, onOpen }) {
  const { token } = theme.useToken();
  const sender = kudos.sender || {};
  const recipient = kudos.recipient || {};

  const initial = kudos.reactions || { counts: {}, mine: [] };
  const [reactions, setReactions] = useState({
    counts: (initial.counts && typeof initial.counts === 'object') ? initial.counts : {},
    mine: initial.mine || [],
  });
  const [picking, setPicking] = useState(false);

  const react = (emoji) => {
    if (!apiClient) return;
    setPicking(false);
    toggleReaction(apiClient, 'Kudos', kudos.id, emoji)
      .then((r) => setReactions({ counts: (r && r.counts) || {}, mine: (r && r.mine) || [] }))
      .catch((e) => message.error(errorText(e, t('Could not react'))));
  };

  const remove = () => {
    deleteKudos(apiClient, kudos.id)
      .then(() => { message.success(t('Kudos deleted')); if (onDeleted) onDeleted(kudos.id); })
      .catch((e) => message.error(errorText(e, t('Could not delete'))));
  };

  const emojis = Object.keys(reactions.counts || {});
  const total = emojis.reduce((n, e) => n + (reactions.counts[e] || 0), 0);
  const myEmoji = (reactions.mine && reactions.mine.length) ? reactions.mine[0] : null;

  const menuItems = [];
  if (onOpen) menuItems.push({ key: 'open', icon: <ExportOutlined />, label: t('Open in Kudos') });
  if (kudos.can_delete) {
    menuItems.push({ key: 'delete', icon: <DeleteOutlined />, label: t('Delete kudos'), danger: true });
  }

  return (
    <Card
      size="small"
      style={{
        marginBottom: 16,
        borderRadius: 10,
        borderColor: soft(ACCENT.kudos, 0.35),
      }}
      styles={{ body: { padding: '12px 0 4px' } }}
    >
      {/* who gave it */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '0 14px' }}>
        <PersonAvatar person={sender} size={40} />
        <div style={{ flex: 1, minWidth: 0 }}>
          <Text style={{ fontSize: 14 }}>
            {/* Translated as one sentence; the names are slotted into its placeholders. */}
            {t('{sender} gave kudos to {recipient}').split(/(\{sender\}|\{recipient\})/).map((part, i) => {
              if (part === '{sender}') {
                // eslint-disable-next-line react/no-array-index-key
                return <Text strong key={i}>{sender.name || t('Someone')}</Text>;
              }
              if (part === '{recipient}') {
                // eslint-disable-next-line react/no-array-index-key
                return <Text strong key={i}>{recipient.name || t('a colleague')}</Text>;
              }
              // eslint-disable-next-line react/no-array-index-key
              return part ? <Text type="secondary" key={i}>{part}</Text> : null;
            })}
          </Text>
          <div style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
            <Text type="secondary" style={{ fontSize: 12 }}>{timeAgo(kudos.created)}</Text>
            <Text type="secondary" style={{ fontSize: 11 }}>·</Text>
            <Tooltip title={t('Kudos')}>
              <TrophyOutlined style={{ fontSize: 11, color: ACCENT.kudos }} />
            </Tooltip>
          </div>
        </div>
        {menuItems.length > 0 && (
          <Dropdown
            trigger={['click']}
            menu={{
              items: menuItems,
              onClick: ({ key }) => {
                if (key === 'open' && onOpen) onOpen(kudos);
                if (key === 'delete') remove();
              },
            }}
          >
            <Button type="text" shape="circle" icon={<MoreOutlined />} />
          </Dropdown>
        )}
      </div>

      {/* the celebratory panel: recipient + message */}
      <div style={{ padding: '10px 14px 4px' }}>
        <div style={{
          borderRadius: 12,
          padding: '18px 16px',
          textAlign: 'center',
          background: `linear-gradient(135deg, ${soft(ACCENT.kudos, 0.16)} 0%, ${soft(ACCENT.birthday, 0.14)} 100%)`,
          border: `1px solid ${soft(ACCENT.kudos, 0.25)}`,
        }}
        >
          <PersonAvatar person={recipient} size={56} />
          <div style={{
            marginTop: 8,
            fontSize: 18,
            fontWeight: 800,
            letterSpacing: 1,
            color: ACCENT.kudos,
          }}
          >
            {t('KUDOS!')}
          </div>
          <div style={{ marginTop: 4, fontSize: 14 }}>{kudos.message}</div>
          {kudos.value_tag ? (
            <Tag
              bordered={false}
              style={{ marginTop: 10, color: ACCENT.kudos, background: soft(ACCENT.kudos) }}
            >
              {`#${kudos.value_tag}`}
            </Tag>
          ) : null}
        </div>
      </div>

      {/* reaction summary */}
      {total > 0 && (
        <div style={{ padding: '4px 14px 8px', fontSize: 13 }}>
          <button
            type="button"
            onClick={() => setPicking(true)}
            style={{
              display: 'flex',
              alignItems: 'center',
              gap: 6,
              background: 'none',
              border: 0,
              padding: 0,
              cursor: 'pointer',
              color: token.colorTextSecondary,
            }}
          >
            <span style={{ display: 'inline-flex' }}>
              {emojis.slice(0, 3).map((e, i) => (
                <span
                  key={e}
                  style={{
                    width: 20,
                    height: 20,
                    borderRadius: '50%',
                    background: token.colorBgElevated,
                    border: `1.5px solid ${token.colorBgContainer}`,
                    display: 'inline-flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    fontSize: 11,
                    marginLeft: i === 0 ? 0 : -6,
                    zIndex: 3 - i,
                  }}
                >
                  {e}
                </span>
              ))}
            </span>
            <span>{total}</span>
          </button>
        </div>
      )}

      {/* action bar */}
      <div style={{ borderTop: `1px solid ${token.colorBorderSecondary}`, margin: '0 14px' }} />
      <div style={{ display: 'flex', padding: '4px 10px 6px' }}>
        <Tooltip
          open={picking}
          onOpenChange={setPicking}
          trigger="click"
          color={token.colorBgElevated}
          title={(
            <Space size={2}>
              {QUICK_EMOJI.map((e) => (
                <Button key={e} type="text" onClick={() => react(e)} style={{ fontSize: 18, padding: '0 6px' }}>
                  {e}
                </Button>
              ))}
            </Space>
          )}
        >
          <Button
            type="text"
            icon={myEmoji ? <span style={{ fontSize: 15 }}>{myEmoji}</span> : <LikeOutlined />}
            style={{
              flex: 1,
              height: 36,
              fontWeight: 600,
              color: myEmoji ? token.colorPrimary : token.colorTextSecondary,
            }}
          >
            {myEmoji ? t('Reacted') : t('React')}
          </Button>
        </Tooltip>
      </div>
    </Card>
  );
}
