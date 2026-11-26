import React from 'react';
import { Avatar, Button, Empty, Spin, Tag, Tooltip, Typography, theme } from 'antd';
import { CloseOutlined, MessageOutlined, FileTextOutlined, UserOutlined } from '@ant-design/icons';
import { ago, full } from '../time';

import { t } from '../../../../../../web/api/i18n';
const { Text } = Typography;

/** The matched term, emphasised inside a plain-text snippet (no HTML involved). */
function Highlight({ text, term, token }) {
  if (!term) return text;
  const parts = [];
  const lower = text.toLowerCase();
  const needle = term.toLowerCase();
  let i = 0;
  let idx = lower.indexOf(needle, i);
  while (idx >= 0) {
    if (idx > i) parts.push(text.slice(i, idx));
    parts.push(<mark key={idx} style={{ background: token.colorWarningBg, color: 'inherit', padding: '0 2px', borderRadius: 2 }}>{text.slice(idx, idx + term.length)}</mark>);
    i = idx + term.length;
    idx = lower.indexOf(needle, i);
  }
  if (i < text.length) parts.push(text.slice(i));
  return parts;
}

export default function SearchResults({
  q, results, truncated, loading, onOpen, onClose,
}) {
  const { token } = theme.useToken();
  return (
    <div style={{ padding: 16 }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 12 }}>
        <Text strong>
          {loading ? 'Searching…' : `${results.length}${truncated ? '+' : ''} result${results.length === 1 ? '' : 's'} for “${q}”`}
        </Text>
        <Button size="small" type="text" icon={<CloseOutlined />} onClick={onClose}>{t('Close')}</Button>
      </div>

      {loading && <div style={{ textAlign: 'center', padding: 40 }}><Spin /></div>}
      {!loading && results.length === 0 && (
        <div style={{ padding: 40 }}><Empty description={t('Nothing matched. Try a different word.')} /></div>
      )}

      {!loading && results.map((r) => (
        <div
          key={`${r.type}-${r.reply_id || r.post_id}`}
          role="button"
          tabIndex={0}
          onClick={() => onOpen(r)}
          onKeyDown={(e) => { if (e.key === 'Enter') onOpen(r); }}
          style={{
            display: 'flex', gap: 12, padding: '12px 14px', marginBottom: 8, cursor: 'pointer',
            background: token.colorBgContainer, border: `1px solid ${token.colorBorderSecondary}`, borderRadius: 10,
          }}
        >
          <Avatar size={30} src={r.author && r.author.image ? r.author.image : undefined} icon={<UserOutlined />}>
            {r.author && r.author.name ? r.author.name.charAt(0).toUpperCase() : null}
          </Avatar>
          <div style={{ flex: 1, minWidth: 0 }}>
            <div style={{ display: 'flex', gap: 8, alignItems: 'center', flexWrap: 'wrap', marginBottom: 3 }}>
              <Text strong style={{ fontSize: 13 }}>{r.author ? r.author.name : ''}</Text>
              <Tooltip title={full(r.created)}><Text type="secondary" style={{ fontSize: 12 }}>{ago(r.created)}</Text></Tooltip>
              <Tag style={{ marginInlineEnd: 0 }} icon={r.type === 'reply' ? <MessageOutlined /> : <FileTextOutlined />}>
                {r.type === 'reply' ? 'reply' : 'post'} in #{r.channel.name}
              </Tag>
            </div>
            <div style={{ fontSize: 14, lineHeight: 1.5, color: token.colorText, wordBreak: 'break-word' }}>
              <Highlight text={r.snippet || ''} term={q} token={token} />
            </div>
          </div>
        </div>
      ))}

      {!loading && truncated && (
        <Text type="secondary" style={{ display: 'block', textAlign: 'center', fontSize: 12, marginTop: 8 }}>
          Showing the first {results.length}. Narrow the search to see the rest.
        </Text>
      )}
    </div>
  );
}
