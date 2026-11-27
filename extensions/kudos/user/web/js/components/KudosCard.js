import React, { useState } from 'react';
import {
  Avatar, Button, Popconfirm, Space, Tag, Tooltip, Typography, message, theme,
} from 'antd';
import {
  ArrowRightOutlined, DeleteOutlined, TrophyOutlined, UserOutlined,
} from '@ant-design/icons';
import ReactionBar from '../../../../../../web/components/ReactionBar';
import { api, errorMessage } from '../api';
import { ago, full } from '../time';
import { t } from '../../../../../../web/api/i18n';

const { Text, Paragraph } = Typography;

const AUDIENCE_LABEL = { all: 'Everyone', department: 'Department', team: 'Team' };

function Person({ person, size, bold }) {
  const p = person || {};
  return (
    <Space size={8} align="center">
      <Avatar size={size || 36} src={p.image || undefined} icon={<UserOutlined />}>
        {p.name ? p.name.charAt(0).toUpperCase() : null}
      </Avatar>
      <Text strong={!!bold}>{p.name || t('Unknown')}</Text>
    </Space>
  );
}

/**
 * One kudos: sender → recipient, the celebratory banner, the message, the value tag,
 * and the shared ReactionBar. `onDeleted(id)` is called after a successful delete;
 * the parent removes the card.
 */
export default function KudosCard({ kudos, onDeleted, onReactions }) {
  const { token } = theme.useToken();
  const [deleting, setDeleting] = useState(false);
  const [reactions, setReactions] = useState(kudos.reactions);

  const remove = async () => {
    setDeleting(true);
    try {
      await api().delete(`kudos/${kudos.id}`);
      message.success(t('Kudos removed'));
      if (onDeleted) onDeleted(kudos.id);
    } catch (e) {
      message.error(errorMessage(e, t('Could not remove the kudos')));
      setDeleting(false);
    }
  };

  const onReactionChange = (summary) => {
    setReactions(summary);
    if (onReactions) onReactions(kudos.id, summary);
  };

  return (
    <div
      style={{
        background: token.colorBgContainer,
        border: `1px solid ${token.colorBorderSecondary}`,
        borderRadius: 12,
        overflow: 'hidden',
      }}
    >
      {/* header: who gave whom */}
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '12px 16px' }}>
        <Space size={10} align="center" wrap>
          <Person person={kudos.sender} size={32} />
          <ArrowRightOutlined style={{ color: token.colorTextTertiary }} />
          <Person person={kudos.recipient} size={32} bold />
        </Space>
        <Space size={8}>
          <Tooltip title={full(kudos.created)}>
            <Text type="secondary" style={{ fontSize: 12 }}>{ago(kudos.created)}</Text>
          </Tooltip>
          {kudos.audience && kudos.audience !== 'all' && (
            <Tag style={{ margin: 0 }}>{AUDIENCE_LABEL[kudos.audience] ? t(AUDIENCE_LABEL[kudos.audience]) : kudos.audience}</Tag>
          )}
          {kudos.can_delete && (
            <Popconfirm
              title={t('Remove this kudos?')}
              okText={t('Remove')}
              okButtonProps={{ danger: true }}
              onConfirm={remove}
            >
              <Button size="small" type="text" danger icon={<DeleteOutlined />} loading={deleting} />
            </Popconfirm>
          )}
        </Space>
      </div>

      {/* celebratory banner */}
      <div
        style={{
          position: 'relative',
          margin: '0 16px',
          borderRadius: 10,
          padding: '22px 20px',
          background: 'linear-gradient(135deg, #fde68a 0%, #fbcfe8 45%, #c7d2fe 100%)',
          color: '#1f2937',
          overflow: 'hidden',
        }}
      >
        {[['8%', '18%', 10], ['82%', '22%', 14], ['20%', '78%', 8], ['70%', '72%', 12], ['48%', '12%', 6]].map(([l, t, s]) => (
          <span
            key={`${l}-${t}`}
            style={{
              position: 'absolute', left: l, top: t, width: s, height: s, borderRadius: '50%',
              background: 'rgba(255,255,255,0.7)',
            }}
          />
        ))}
        <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
          <Avatar size={56} src={kudos.recipient && kudos.recipient.image ? kudos.recipient.image : undefined} icon={<UserOutlined />} style={{ border: '3px solid #fff', flex: 'none' }}>
            {kudos.recipient && kudos.recipient.name ? kudos.recipient.name.charAt(0).toUpperCase() : null}
          </Avatar>
          <div style={{ minWidth: 0 }}>
            <div style={{ fontSize: 26, fontWeight: 800, letterSpacing: 1, lineHeight: 1.1 }}>
              <TrophyOutlined style={{ marginRight: 8 }} />
              KUDOS!
            </div>
            <div style={{ fontSize: 13, opacity: 0.85 }}>
              to {kudos.recipient ? kudos.recipient.name : 'a colleague'}
            </div>
          </div>
        </div>
        {kudos.value_tag && (
          <Tag color="magenta" style={{ position: 'absolute', right: 14, top: 14, margin: 0, fontWeight: 600 }}>
            #{kudos.value_tag}
          </Tag>
        )}
      </div>

      {kudos.image && (
        <div style={{ padding: '12px 16px 0' }}>
          <img src={kudos.image} alt="" style={{ maxWidth: '100%', borderRadius: 8 }} />
        </div>
      )}

      {/* message */}
      <div style={{ padding: '14px 16px 6px' }}>
        <Paragraph style={{ marginBottom: 0, whiteSpace: 'pre-wrap' }}>{kudos.message}</Paragraph>
      </div>

      {/* reactions */}
      <div style={{ padding: '6px 16px 12px' }}>
        <ReactionBar
          targetType="Kudos"
          targetId={kudos.id}
          value={reactions}
          onChange={onReactionChange}
        />
      </div>
    </div>
  );
}
