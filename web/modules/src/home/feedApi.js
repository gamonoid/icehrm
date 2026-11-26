/**
 * Home's view of the company feed.
 *
 * The feed is the built-in "Public" discussions channel (is_system, undeletable),
 * so every call here goes through the discussions extension's own REST endpoints —
 * which enforce the per-channel read/post rules. Home never touches the tables.
 *
 * Reactions come from the shared core Reactions API, the same one Kudos uses.
 */
import { t, getLocale, formatDate } from '../../../api/i18n';

/** Unwrap {status, data} envelopes; some endpoints return the payload directly. */
export function unwrap(res) {
  const body = res && res.data;
  if (body && body.status && body.data !== undefined) return body.data;
  return body;
}

export function errorText(e, fallback) {
  const msg = e && e.response && e.response.data
    && e.response.data.error && e.response.data.error[0]
    && e.response.data.error[0][0] && e.response.data.error[0][0].message;
  return msg || fallback;
}

export function listPosts(api, channelId, page) {
  return api.get(`discussions/channels/${channelId}/posts/${page || 1}`).then(unwrap);
}

/**
 * Post plain text as a single paragraph block — the same EditorJS-shaped content
 * the discussions composer produces, so a post written on Home renders identically
 * in the Discussions module (and stays editable there).
 */
export function createPost(api, channelId, text, attachments) {
  const content = {
    blocks: [{ type: 'paragraph', data: { text: String(text || '') } }],
  };
  return createPostContent(api, channelId, content, attachments);
}

/** Post already-built block content (what the rich editor produces). */
export function createPostContent(api, channelId, content, attachments) {
  const body = { content: content || { blocks: [] } };
  if (attachments && attachments.length) body.attachments = attachments;
  return api.post(`discussions/channels/${channelId}/posts`, body).then(unwrap);
}

export function addReply(api, postId, text) {
  return api.post(`discussions/posts/${postId}/replies`, { body: String(text || '') }).then(unwrap);
}

export function deletePost(api, postId) {
  return api.delete(`discussions/posts/${postId}`).then(unwrap);
}

/**
 * People who can be @mentioned, matching a typed query. The term travels in the
 * PATH — IceApiClient proxies through api/index.php?url=/<endpoint>, so a "?" in
 * the endpoint breaks the route match.
 */
export function searchPeople(api, term) {
  const q = String(term || '').trim();
  const url = q ? `discussions/people/${encodeURIComponent(q)}` : 'discussions/people';
  return api.get(url).then(unwrap).then((r) => (Array.isArray(r) ? r : []));
}

// --- kudos ------------------------------------------------------------------

/** Recent kudos, merged into the feed alongside posts. */
export function listKudos(api, page) {
  return api.get(`kudos/feed/${page || 1}`).then(unwrap);
}

/** The caller's kudos profile, incl. quota {limit, used, remaining} for this month. */
export function getKudosMe(api) {
  return api.get('kudos/me').then(unwrap);
}

export function deleteKudos(api, id) {
  return api.delete(`kudos/${id}`).then(unwrap);
}

// --- reactions (shared core API) -------------------------------------------
// Generic over the target so the same bar serves feed posts and kudos.

export function toggleReaction(api, targetType, targetId, emoji) {
  return api.post('reactions/toggle', {
    target_type: targetType, target_id: targetId, emoji,
  }).then(unwrap);
}

export function getReactions(api, targetType, targetId) {
  return api.get(`reactions/${targetType}/${targetId}`).then(unwrap);
}

// --- rendering helpers ------------------------------------------------------

/**
 * Flatten a post's block content to display text. Home shows a plain, readable
 * excerpt; the full rich rendering (tables, checklists, code) lives in the
 * Discussions module, which a "See more" link opens.
 */
export function blocksToText(content) {
  const blocks = (content && content.blocks) || [];
  const parts = [];
  blocks.forEach((b) => {
    const d = b && b.data ? b.data : {};
    if (typeof d.text === 'string') parts.push(d.text);
    else if (Array.isArray(d.items)) {
      d.items.forEach((it) => {
        if (typeof it === 'string') parts.push(it);
        else if (it && typeof it.text === 'string') parts.push(it.text);
      });
    }
  });
  // The editor stores inline markup as HTML; strip tags and decode the few
  // entities it emits so the excerpt reads as plain text.
  return parts.join('\n')
    .replace(/<[^>]*>/g, '')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .trim();
}

/** "just now" / "3h ago" / "2d ago" (in the UI locale) / a date once it is older than a week. */
export function timeAgo(iso) {
  if (!iso) return '';
  const then = new Date(iso).getTime();
  if (Number.isNaN(then)) return '';
  const secs = Math.max(0, Math.floor((Date.now() - then) / 1000));
  if (secs < 60) return t('just now');
  const ago = (n, unit) => {
    try {
      return new Intl.RelativeTimeFormat(getLocale(), { numeric: 'always', style: 'narrow' }).format(-n, unit);
    } catch (e) {
      return `${n}${unit.charAt(0)}`;
    }
  };
  if (secs < 3600) return ago(Math.floor(secs / 60), 'minute');
  if (secs < 86400) return ago(Math.floor(secs / 3600), 'hour');
  if (secs < 604800) return ago(Math.floor(secs / 86400), 'day');
  return formatDate(then, { day: 'numeric', month: 'short' });
}
