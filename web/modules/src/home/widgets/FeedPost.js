import React, { useEffect, useState } from 'react';
import {
  Card, Avatar, Typography, Space, Button, Input, Tooltip, Dropdown, message, theme,
} from 'antd';
import {
  MessageOutlined, LikeOutlined, DeleteOutlined, PaperClipOutlined,
  GlobalOutlined, MoreOutlined, ExportOutlined,
} from '@ant-design/icons';
import {
  timeAgo, blocksToText, toggleReaction, getReactions, addReply, deletePost, errorText,
} from '../feedApi';
import { personColor, initials } from '../palette';
import PostBlocks from './PostBlocks';
import { t, tp } from '../../../../api/i18n';

const { Text } = Typography;

const QUICK_EMOJI = ['👍', '🎉', '❤️', '👏', '😄', '😮'];
const EXCERPT_CHARS = 420;
// Longer posts collapse to their first few blocks behind a "See more".
const COLLAPSE_BLOCKS = 6;

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
 * One post in the Home feed, laid out the way a social feed reads: an author line
 * with a "…" menu, the content, a reaction summary, a full-width action bar, then
 * comments as rounded bubbles.
 *
 * Reactions use the shared core Reactions API (target_type "DiscussionPost");
 * comments post real discussion replies, so anything written here is the same
 * thread the Discussions module shows.
 */
export default function FeedPost({ post, apiClient, me, onDeleted, onOpen }) {
  const { token } = theme.useToken();
  const author = post.author || {};
  const blockCount = ((post.content && post.content.blocks) || []).length;
  const text = blocksToText(post.content);
  const long = text.length > EXCERPT_CHARS || blockCount > COLLAPSE_BLOCKS;

  const [expanded, setExpanded] = useState(false);
  const [reactions, setReactions] = useState({ counts: {}, mine: [] });
  const [picking, setPicking] = useState(false);
  const [showComments, setShowComments] = useState(false);
  const [comment, setComment] = useState('');
  const [replies, setReplies] = useState(post.replies || []);
  const [sending, setSending] = useState(false);

  useEffect(() => {
    if (!apiClient) return;
    getReactions(apiClient, 'DiscussionPost', post.id)
      .then((r) => setReactions({ counts: (r && r.counts) || {}, mine: (r && r.mine) || [] }))
      .catch(() => { /* reactions are decoration — never block the post */ });
  }, [apiClient, post.id]);

  const react = (emoji) => {
    if (!apiClient) return;
    setPicking(false);
    toggleReaction(apiClient, 'DiscussionPost', post.id, emoji)
      .then((r) => setReactions({ counts: (r && r.counts) || {}, mine: (r && r.mine) || [] }))
      .catch((e) => message.error(errorText(e, t('Could not react'))));
  };

  const submitComment = () => {
    const body = comment.trim();
    if (!body || !apiClient) return;
    setSending(true);
    addReply(apiClient, post.id, body)
      .then((reply) => {
        setReplies((prev) => prev.concat([reply || {
          id: Math.random(), body, author: me, created: new Date().toISOString(),
        }]));
        setComment('');
      })
      .catch((e) => message.error(errorText(e, t('Could not comment'))))
      .finally(() => setSending(false));
  };

  const remove = () => {
    deletePost(apiClient, post.id)
      .then(() => { message.success(t('Post deleted')); if (onDeleted) onDeleted(post.id); })
      .catch((e) => message.error(errorText(e, t('Could not delete'))));
  };

  const emojis = Object.keys(reactions.counts || {});
  const totalReactions = emojis.reduce((n, e) => n + (reactions.counts[e] || 0), 0);
  const myEmoji = (reactions.mine && reactions.mine.length) ? reactions.mine[0] : null;
  const commentCount = replies.length || post.reply_count || 0;
  const attachments = Array.isArray(post.attachments) ? post.attachments : [];

  // Equal-width buttons filling the action bar, as a social feed does.
  const actionBtn = (icon, label, onClick, active) => (
    <Button
      type="text"
      onClick={onClick}
      icon={icon}
      style={{
        flex: 1,
        height: 36,
        fontWeight: 600,
        color: active ? token.colorPrimary : token.colorTextSecondary,
      }}
    >
      {label}
    </Button>
  );

  const menuItems = [];
  if (onOpen) menuItems.push({ key: 'open', icon: <ExportOutlined />, label: t('Open in Discussions') });
  if (post.can_delete) {
    menuItems.push({ key: 'delete', icon: <DeleteOutlined />, label: t('Delete post'), danger: true });
  }

  return (
    <Card
      size="small"
      style={{ marginBottom: 16, borderRadius: 10 }}
      styles={{ body: { padding: '12px 0 4px' } }}
    >
      {/* author line */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 10, padding: '0 14px' }}>
        <PersonAvatar person={author} size={40} />
        <div style={{ flex: 1, minWidth: 0 }}>
          <Text strong style={{ fontSize: 14 }}>{author.name || t('Someone')}</Text>
          <div style={{ display: 'flex', alignItems: 'center', gap: 5 }}>
            <Text type="secondary" style={{ fontSize: 12 }}>{timeAgo(post.created)}</Text>
            <Text type="secondary" style={{ fontSize: 11 }}>·</Text>
            <Tooltip title={t('Shared with everyone')}>
              <GlobalOutlined style={{ fontSize: 11, color: token.colorTextSecondary }} />
            </Tooltip>
            {post.edited ? <Text type="secondary" style={{ fontSize: 11 }}>· {t('edited')}</Text> : null}
          </div>
        </div>
        {menuItems.length > 0 && (
          <Dropdown
            trigger={['click']}
            menu={{
              items: menuItems,
              onClick: ({ key }) => {
                if (key === 'open' && onOpen) onOpen(post);
                if (key === 'delete') remove();
              },
            }}
          >
            <Button type="text" shape="circle" icon={<MoreOutlined />} />
          </Dropdown>
        )}
      </div>

      {/* body */}
      {blockCount > 0 ? (
        <div style={{ padding: '10px 14px 2px' }}>
          <PostBlocks content={post.content} limit={expanded || !long ? null : COLLAPSE_BLOCKS} />
          {long && !expanded && (
            <Button type="link" size="small" style={{ paddingLeft: 0 }} onClick={() => setExpanded(true)}>
              {t('See more')}
            </Button>
          )}
        </div>
      ) : null}

      {attachments.length > 0 && (
        <div style={{ padding: '0 14px 8px' }}>
          <Space wrap size={8}>
            {attachments.map((f, i) => (
              // eslint-disable-next-line react/no-array-index-key
              <a key={f.id || f.name || i} href={f.url} target="_blank" rel="noopener noreferrer">
                <Space size={4}><PaperClipOutlined />{f.name || t('Attachment')}</Space>
              </a>
            ))}
          </Space>
        </div>
      )}

      {/* reaction summary — overlapping emoji bubbles + comment count */}
      {(totalReactions > 0 || commentCount > 0) && (
        <div style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          padding: '4px 14px 8px',
          fontSize: 13,
        }}
        >
          {totalReactions > 0 ? (
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
              <span>{totalReactions}</span>
            </button>
          ) : <span />}
          {commentCount > 0 && (
            <button
              type="button"
              onClick={() => setShowComments((s) => !s)}
              style={{
                background: 'none',
                border: 0,
                padding: 0,
                cursor: 'pointer',
                color: token.colorTextSecondary,
              }}
            >
              {tp(commentCount, '{count} comment', '{count} comments')}
            </button>
          )}
        </div>
      )}

      {/* action bar */}
      <div style={{ borderTop: `1px solid ${token.colorBorderSecondary}`, margin: '0 14px' }} />
      <div style={{ display: 'flex', gap: 4, padding: '4px 10px 6px' }}>
        <Tooltip
          open={picking}
          onOpenChange={setPicking}
          trigger="click"
          color={token.colorBgElevated}
          title={(
            <Space size={2}>
              {QUICK_EMOJI.map((e) => (
                <Button
                  key={e}
                  type="text"
                  onClick={() => react(e)}
                  style={{ fontSize: 18, padding: '0 6px' }}
                >
                  {e}
                </Button>
              ))}
            </Space>
          )}
        >
          {actionBtn(
            myEmoji ? <span style={{ fontSize: 15 }}>{myEmoji}</span> : <LikeOutlined />,
            myEmoji ? t('Reacted') : t('React'),
            () => {},
            !!myEmoji,
          )}
        </Tooltip>
        {actionBtn(<MessageOutlined />, t('Comment'), () => setShowComments((s) => !s))}
      </div>

      {/* comments */}
      {showComments && (
        <div style={{ padding: '2px 14px 10px' }}>
          {replies.map((r) => (
            <div key={r.id} style={{ display: 'flex', gap: 8, marginBottom: 8 }}>
              <PersonAvatar person={r.author} size={28} />
              <div style={{ minWidth: 0 }}>
                <div style={{
                  background: token.colorFillTertiary,
                  borderRadius: 16,
                  padding: '6px 12px',
                  display: 'inline-block',
                  maxWidth: '100%',
                }}
                >
                  <div>
                    <Text strong style={{ fontSize: 13 }}>
                      {(r.author && r.author.name) || t('Someone')}
                    </Text>
                  </div>
                  <span style={{ whiteSpace: 'pre-wrap', fontSize: 13.5 }}>{r.body}</span>
                </div>
                <div style={{ paddingLeft: 12 }}>
                  <Text type="secondary" style={{ fontSize: 11 }}>{timeAgo(r.created)}</Text>
                </div>
              </div>
            </div>
          ))}

          <div style={{ display: 'flex', gap: 8, alignItems: 'flex-start', marginTop: 4 }}>
            <PersonAvatar person={me} size={28} />
            <div style={{ flex: 1, minWidth: 0 }}>
              <Input.TextArea
                value={comment}
                onChange={(e) => setComment(e.target.value)}
                // Enter sends, Shift+Enter makes a new line — the box is multi-line,
                // so a plain Enter must not insert a newline as well as send.
                onPressEnter={(e) => { if (!e.shiftKey) { e.preventDefault(); submitComment(); } }}
                placeholder={t('Write a comment…  (Shift+Enter for a new line)')}
                autoSize={{ minRows: 1, maxRows: 8 }}
                maxLength={2000}
                disabled={sending}
                style={{ borderRadius: 16, background: token.colorFillTertiary, padding: '6px 12px' }}
              />
              {comment.trim() ? (
                <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: 6 }}>
                  <Button size="small" type="primary" onClick={submitComment} loading={sending}>
                    {t('Send')}
                  </Button>
                </div>
              ) : null}
            </div>
          </div>
        </div>
      )}
    </Card>
  );
}
