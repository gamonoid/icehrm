/**
 * Home's colour vocabulary.
 *
 * Each widget owns an accent colour, used for its header icon, its tinted icon
 * chip and any highlight inside it. Keeping them here (rather than inline per
 * widget) means the page reads as one deliberate palette instead of a dozen
 * unrelated choices, and a contributed widget can pick an accent off the shelf.
 *
 * The colours are antd's palette, which works on both the light and dark shell
 * themes; anything tinted uses the ACCENT_SOFT alpha so it sits on either ground.
 */
export const ACCENT = {
  feed: '#1677ff',
  needsYou: '#fa8c16',
  team: '#13c2c2',
  timeOff: '#2f54eb',
  announcements: '#fa541c',
  kudos: '#faad14',
  birthday: '#eb2f96',
  anniversary: '#722ed1',
  org: '#52c41a',
  neutral: '#8c8c8c',
};

/** A soft tint of an accent — for icon chips and highlight backgrounds. */
export function soft(hex, alpha) {
  const a = alpha === undefined ? 0.14 : alpha;
  const h = hex.replace('#', '');
  const r = parseInt(h.slice(0, 2), 16);
  const g = parseInt(h.slice(2, 4), 16);
  const b = parseInt(h.slice(4, 6), 16);
  return `rgba(${r}, ${g}, ${b}, ${a})`;
}

/**
 * A stable colour for a person, derived from their name — so the same colleague
 * is always the same colour wherever they appear (avatar fallbacks, initials).
 */
const AVATAR_COLORS = [
  '#1677ff', '#52c41a', '#fa8c16', '#eb2f96', '#722ed1',
  '#13c2c2', '#f5222d', '#2f54eb', '#fa541c', '#a0d911',
];
export function personColor(name) {
  const s = String(name || '');
  let sum = 0;
  for (let i = 0; i < s.length; i += 1) sum += s.charCodeAt(i);
  return AVATAR_COLORS[sum % AVATAR_COLORS.length];
}

export function initials(name) {
  const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
  if (parts.length === 0) return '?';
  if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
  return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

/** Props for a coloured circular icon chip used in card headers. */
export function chip(color) {
  return {
    width: 26,
    height: 26,
    borderRadius: 8,
    display: 'inline-flex',
    alignItems: 'center',
    justifyContent: 'center',
    background: soft(color),
    color,
    fontSize: 14,
    flex: '0 0 auto',
  };
}
