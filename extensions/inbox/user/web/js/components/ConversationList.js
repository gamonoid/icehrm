import React from 'react';
import { Badge, Button, Tooltip, Typography, theme } from 'antd';
import { CloseOutlined, ReloadOutlined } from '@ant-design/icons';
import Person from './Person';
import EmployeeSearch from './EmployeeSearch';

import { t } from '../../../../../../web/api/i18n';
const { Text } = Typography;

export default function ConversationList({
  conversations, selectedId, onSelect, onHide, onRefresh, onPick, loading,
}) {
  const { token } = theme.useToken();
  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
      <div style={{ padding: '10px 10px 2px' }}>
        <EmployeeSearch onPick={onPick} />
      </div>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px 12px 6px' }}>
        <Text strong style={{ fontSize: 12, letterSpacing: 0.6, textTransform: 'uppercase', color: token.colorTextSecondary }}>{t('Direct messages')}</Text>
        <Tooltip title={t('Refresh')}>
          <Button size="small" type="text" icon={<ReloadOutlined spin={loading} />} onClick={onRefresh} />
        </Tooltip>
      </div>
      <div style={{ flex: 1, overflowY: 'auto', padding: '0 6px 8px' }}>
        {conversations.map((c) => {
          const active = c.id === selectedId;
          return (
            <div
              key={c.id}
              role="button"
              tabIndex={0}
              onClick={() => onSelect(c)}
              onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') onSelect(c); }}
              className="inbox-conversation"
              style={{
                display: 'flex', alignItems: 'center', gap: 6,
                padding: '7px 8px', marginBottom: 2, borderRadius: 8, cursor: 'pointer',
                background: active ? token.colorPrimary : 'transparent',
                color: active ? token.colorWhite : token.colorText,
                userSelect: 'none',
              }}
            >
              <div style={{ flex: 1, minWidth: 0 }}>
                <Person person={c.other} size={34} muted={active} nameStyle={{ fontWeight: c.unread > 0 ? 700 : 500 }} />
              </div>
              {c.unread > 0 && (
                <Badge count={c.unread} size="small" style={{ boxShadow: 'none' }} />
              )}
              <Tooltip title={t('Hide from list')}>
                <Button
                  size="small"
                  type="text"
                  className="inbox-hide"
                  icon={<CloseOutlined style={{ fontSize: 11 }} />}
                  style={{ color: 'inherit', opacity: 0.55, flex: 'none', width: 22, height: 22, minWidth: 22, padding: 0 }}
                  onClick={(e) => { e.stopPropagation(); onHide(c); }}
                />
              </Tooltip>
            </div>
          );
        })}
        {conversations.length === 0 && !loading && (
          <Text type="secondary" style={{ display: 'block', padding: '12px 10px', fontSize: 13, lineHeight: 1.5 }}>
            {t('No conversations yet. Find a colleague above to start one.')}
          </Text>
        )}
      </div>
    </div>
  );
}
