import React, { useState } from 'react';
import { Button, Input, Tooltip, Typography, theme } from 'antd';
import { NotificationOutlined, PlusOutlined, ReloadOutlined, SearchOutlined, SettingOutlined } from '@ant-design/icons';

import { t } from '../../../../../../web/api/i18n';
const { Text } = Typography;

export default function ChannelSidebar({
  channels, selectedId, onSelect, onRefresh, loading, onSearch, searching,
  canManage, onCreate, onManage,
}) {
  const { token } = theme.useToken();
  const [term, setTerm] = useState('');
  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
      <div style={{ padding: '10px 10px 2px' }}>
        <Input.Search
          allowClear
          size="small"
          placeholder={t('Search messages')}
          prefix={<SearchOutlined style={{ color: token.colorTextTertiary }} />}
          value={term}
          loading={!!searching}
          onChange={(e) => setTerm(e.target.value)}
          onSearch={(v) => { const q = (v || '').trim(); if (q) onSearch(q); }}
        />
      </div>
      <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '10px 12px 6px' }}>
        <Text strong style={{ fontSize: 12, letterSpacing: 0.6, textTransform: 'uppercase', color: token.colorTextSecondary }}>{t('Channels')}</Text>
        <span>
          {canManage && (
            <Tooltip title={t('New channel')}>
              <Button size="small" type="text" icon={<PlusOutlined />} onClick={onCreate} />
            </Tooltip>
          )}
          {canManage && (
            <Tooltip title={t('Manage channels')}>
              <Button size="small" type="text" icon={<SettingOutlined />} onClick={onManage} />
            </Tooltip>
          )}
          <Tooltip title={t('Refresh')}>
            <Button size="small" type="text" icon={<ReloadOutlined spin={loading} />} onClick={onRefresh} />
          </Tooltip>
        </span>
      </div>
      <div style={{ flex: 1, overflowY: 'auto', padding: '0 6px 8px' }}>
        {channels.map((ch) => {
          const active = ch.id === selectedId;
          return (
            <div
              key={ch.id}
              role="button"
              tabIndex={0}
              onClick={() => onSelect(ch)}
              onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') onSelect(ch); }}
              style={{
                display: 'flex', alignItems: 'center', gap: 8,
                padding: '7px 10px', marginBottom: 2, borderRadius: 6, cursor: 'pointer',
                background: active ? token.colorPrimary : 'transparent',
                color: active ? token.colorWhite : token.colorText,
                fontWeight: active ? 600 : 400,
                userSelect: 'none',
              }}
            >
              <span style={{ width: 16, textAlign: 'center', opacity: active ? 1 : 0.65, flex: 'none' }}>
                {ch.is_system ? <NotificationOutlined /> : '#'}
              </span>
              <span style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{ch.name}</span>
            </div>
          );
        })}
        {channels.length === 0 && !loading && (
          <Text type="secondary" style={{ display: 'block', padding: 10, fontSize: 13 }}>{t('No channels you can see yet.')}</Text>
        )}
      </div>
    </div>
  );
}
