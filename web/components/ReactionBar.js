import React, { useState } from 'react';
import { Button, Popover, Space, Tooltip, message } from 'antd';
import { SmileOutlined } from '@ant-design/icons';
import { t } from '../api/i18n';

/**
 * Emoji reactions for any target (core Reactions table).
 *
 * Renders one chip per emoji that has at least one reaction (highlighted when the
 * viewer is among them) and a "+" picker. Clicking a chip or a picker entry toggles
 * the viewer's own reaction via POST reactions/toggle and replaces the summary with
 * the server's answer, so counts never drift from the database.
 *
 * Props:
 *   targetType  e.g. 'Kudos' (must be in ReactionService::TARGET_TYPES)
 *   targetId    the row id
 *   value       {counts:{emoji:n}, mine:[emoji]} — the summary the list endpoint sent
 *   onChange    (summary) => void — called with the new summary after a toggle
 *   apiClient   optional; defaults to window.modJs.apiClient (the shell-wired axios)
 *   size        'small' | 'middle' (default 'small')
 */
const DEFAULT_EMOJIS = ['👍', '🎉', '❤️', '👏', '🔥', '💯'];

function summaryOf(value) {
  const v = value || {};
  const counts = (v.counts && typeof v.counts === 'object' && !Array.isArray(v.counts)) ? v.counts : {};
  const mine = Array.isArray(v.mine) ? v.mine : [];
  return { counts, mine };
}

export default function ReactionBar({
  targetType, targetId, value, onChange, apiClient, size, emojis,
}) {
  const [busy, setBusy] = useState(false);
  const [open, setOpen] = useState(false);
  const { counts, mine } = summaryOf(value);
  const choices = Array.isArray(emojis) && emojis.length ? emojis : DEFAULT_EMOJIS;
  const client = apiClient || (window.modJs && window.modJs.apiClient);

  const toggle = async (emoji) => {
    if (busy || !client) return;
    setBusy(true);
    setOpen(false);
    try {
      const res = await client.post('reactions/toggle', {
        target_type: targetType, target_id: targetId, emoji,
      });
      if (onChange) onChange(summaryOf(res.data));
    } catch (e) {
      const r = e && e.response && e.response.data;
      const msg = (r && r.error && r.error[0] && r.error[0][0] && r.error[0][0].message) || t('Could not save your reaction');
      message.error(msg);
    } finally {
      setBusy(false);
    }
  };

  const present = Object.keys(counts).filter((k) => counts[k] > 0);
  const btnSize = size || 'small';

  return (
    <Space size={4} wrap>
      {present.map((emoji) => {
        const isMine = mine.indexOf(emoji) >= 0;
        return (
          <Tooltip key={emoji} title={isMine ? t('You reacted — click to remove') : t('React')}>
            <Button
              size={btnSize}
              type={isMine ? 'primary' : 'default'}
              ghost={isMine}
              shape="round"
              disabled={busy}
              onClick={() => toggle(emoji)}
              style={{ paddingLeft: 8, paddingRight: 8 }}
            >
              <span style={{ marginRight: 4 }}>{emoji}</span>
              <span style={{ fontVariantNumeric: 'tabular-nums' }}>{counts[emoji]}</span>
            </Button>
          </Tooltip>
        );
      })}
      <Popover
        trigger="click"
        open={open}
        onOpenChange={setOpen}
        content={(
          <Space size={2}>
            {choices.map((emoji) => (
              <Button
                key={emoji}
                type="text"
                size="middle"
                disabled={busy}
                onClick={() => toggle(emoji)}
                style={{ fontSize: 18, padding: '0 6px' }}
              >
                {emoji}
              </Button>
            ))}
          </Space>
        )}
      >
        <Button size={btnSize} shape="round" icon={<SmileOutlined />} disabled={busy}>
          {present.length ? null : t('React')}
        </Button>
      </Popover>
    </Space>
  );
}
