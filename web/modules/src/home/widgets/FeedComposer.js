import React, { useEffect, useRef, useState } from 'react';
import { Card, Input, Button, Avatar, Space, message, theme } from 'antd';
import { TrophyOutlined, SendOutlined } from '@ant-design/icons';
import { registerWidget } from '../registry';
import { openIntent } from '../intent';
import { createPostContent, errorText } from '../feedApi';
import { createEditor, hasText, editorThemeVars } from '../editor';
import { personColor, initials } from '../palette';
import { attachMentions } from '../../../../components/MentionAutocomplete';
import { searchPeople } from '../feedApi';
import { t } from '../../../../api/i18n';

/**
 * The composer at the top of the feed. Collapsed it is a single prompt line; click
 * it and it opens into a full rich-text editor (EditorJS — headings, lists,
 * checklists, quotes, code, tables), the same editor and the same block format the
 * Discussions composer uses, so a post written here is a first-class post there.
 *
 * The editor scripts load at runtime; if they cannot be fetched the composer falls
 * back to a plain multi-line textarea rather than leaving the user with no way to
 * post — Home is core and must not depend on those assets being present.
 *
 * Only rendered when the server said this user may post (data.feed.canPost).
 */
function FeedComposer({ data, ctx, apiClient, onPosted }) {
  const { token } = theme.useToken();
  const feed = (data && data.feed) || null;
  const me = (data && data.me) || {};
  const kudosApp = (ctx.apps || []).find((a) => a.name === 'kudos');

  const [open, setOpen] = useState(false);
  const [posting, setPosting] = useState(false);
  const [plain, setPlain] = useState('');       // fallback editor text
  const [rich, setRich] = useState(true);       // false once the editor fails to load
  const holderRef = useRef(null);
  const editorRef = useRef(null);
  const mentionsRef = useRef(null);

  // Build the editor when the composer opens; tear it down when it closes so a
  // cancelled draft never lingers in a detached instance.
  useEffect(() => {
    if (!open || !rich || !holderRef.current || editorRef.current) return undefined;
    let cancelled = false;
    createEditor({ holder: holderRef.current, autofocus: true })
      .then((ed) => {
        if (cancelled) { try { ed.destroy(); } catch (e) { /* ignore */ } return; }
        editorRef.current = ed;
        // "@" tagging inside the editor. Attached to the holder (EditorJS owns the
        // contenteditable), and torn down with it.
        if (holderRef.current && apiClient) {
          mentionsRef.current = attachMentions({
            root: holderRef.current,
            search: (q) => searchPeople(apiClient, q),
            getTheme: () => token,
          });
        }
      })
      .catch(() => { if (!cancelled) setRich(false); });
    return () => { cancelled = true; };
  }, [open, rich, apiClient]); // eslint-disable-line react-hooks/exhaustive-deps

  const destroyEditor = () => {
    if (mentionsRef.current) {
      try { mentionsRef.current.destroy(); } catch (e) { /* ignore */ }
      mentionsRef.current = null;
    }
    if (editorRef.current) {
      try { editorRef.current.destroy(); } catch (e) { /* ignore */ }
      editorRef.current = null;
    }
  };

  const close = () => { destroyEditor(); setPlain(''); setOpen(false); };

  const submit = () => {
    if (!apiClient || !feed) return;
    const send = (content) => {
      if (!hasText(content)) { message.warning(t('Write something first')); return; }
      setPosting(true);
      createPostContent(apiClient, feed.channelId, content)
        .then((post) => {
          message.success(t('Posted'));
          close();
          if (onPosted) onPosted(post);
        })
        .catch((e) => message.error(errorText(e, t('Could not post'))))
        .finally(() => setPosting(false));
    };

    if (rich && editorRef.current) {
      editorRef.current.save()
        .then(send)
        .catch(() => message.error(t('Could not read the editor')));
      return;
    }
    send({ blocks: [{ type: 'paragraph', data: { text: plain.trim() } }] });
  };

  if (!feed || !feed.canPost) return null;

  const avatar = (
    <Avatar
      src={me.image || undefined}
      style={me.image ? undefined : { background: personColor(me.name) }}
    >
      {me.image ? null : initials(me.name)}
    </Avatar>
  );

  return (
    <Card size="small" style={{ marginBottom: 16, borderRadius: 10 }}>
      <div style={{ display: 'flex', gap: 10, alignItems: 'flex-start' }}>
        {avatar}
        <div style={{ flex: 1, minWidth: 0 }}>
          {!open ? (
            <button
              type="button"
              onClick={() => setOpen(true)}
              style={{
                width: '100%',
                textAlign: 'left',
                padding: '11px 18px',
                borderRadius: 24,
                cursor: 'text',
                fontSize: 14,
                background: token.colorFillTertiary,
                border: `1px solid ${token.colorBorderSecondary}`,
                color: token.colorTextPlaceholder,
              }}
            >
              {t('Share something with the team…')}
            </button>
          ) : (
            <div style={{
              border: `1px solid ${token.colorBorder}`,
              borderRadius: 10,
              padding: '12px 14px',
              minHeight: 160,
              background: token.colorBgContainer,
            }}
            >
              {rich ? (
                <div
                  ref={holderRef}
                  className="home-editor"
                  // EditorJS chrome (toolbar, popovers, inline toolbar) is driven by
                  // these CSS variables, so it follows the app's light/dark theme.
                  style={{ minHeight: 130, ...editorThemeVars(token) }}
                />
              ) : (
                <Input.TextArea
                  value={plain}
                  onChange={(e) => setPlain(e.target.value)}
                  placeholder={t('Share something with the team…')}
                  autoSize={{ minRows: 6, maxRows: 18 }}
                  maxLength={5000}
                  variant="borderless"
                  style={{ padding: 0 }}
                  autoFocus
                />
              )}
            </div>
          )}

          {open && (
            <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: 10 }}>
              <Space>
                <Button onClick={close} disabled={posting}>{t('Cancel')}</Button>
                <Button type="primary" icon={<SendOutlined />} loading={posting} onClick={submit}>
                  {t('Post')}
                </Button>
              </Space>
            </div>
          )}
        </div>
      </div>

      {kudosApp && !open && (
        <div style={{
          borderTop: `1px solid ${token.colorBorderSecondary}`,
          marginTop: 12,
          paddingTop: 6,
          display: 'flex',
        }}
        >
          <Button
            type="text"
            icon={<TrophyOutlined style={{ color: '#faad14', fontSize: 17 }} />}
            onClick={() => openIntent({ module: kudosApp.key, action: 'compose' })}
            style={{ flex: 1, height: 38, fontWeight: 600, color: token.colorTextSecondary }}
          >
            {t('Give Kudos')}
          </Button>
        </div>
      )}
    </Card>
  );
}

registerWidget({
  id: 'feed-composer', order: 5, column: 'main', requires: { dataKey: 'feed' }, component: FeedComposer,
});

export default FeedComposer;
