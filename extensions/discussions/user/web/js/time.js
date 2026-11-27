import moment from 'moment';
import { getLocale } from '../../../../../web/api/i18n';

/**
 * Times in the reader's own zone.
 *
 * The API sends ISO-8601 instants (with offset). The reader's zone comes from
 * /me — the same resolution the profile's "Time now" uses: the employee's zone,
 * else their department's. Formatting goes through the browser's Intl API, which
 * knows every IANA zone, so no timezone library has to be bundled. Falls back to
 * the browser's zone when none is set or the name is one Intl rejects.
 */
let zone = null;

export function setUserTimeZone(tz) {
  zone = tz || null;
}

export function userTimeZone() {
  return zone;
}

function fmt(iso, opts) {
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '';
  try {
    return new Intl.DateTimeFormat(getLocale(), { ...opts, timeZone: zone || undefined }).format(d);
  } catch (e) {
    return new Intl.DateTimeFormat(getLocale(), opts).format(d);
  }
}

/** "14:05" */
export const timeOfDay = (iso) => fmt(iso, { hour: '2-digit', minute: '2-digit', hour12: false });
/** "Wednesday, 16 September" */
export const dayLabel = (iso) => fmt(iso, { weekday: 'long', day: 'numeric', month: 'long' });
/** "2026-09-16" in the reader's zone — for grouping by day */
export const dayKey = (iso) => fmt(iso, { year: 'numeric', month: '2-digit', day: '2-digit' });
/** Tooltip: full date and time, with the zone it is shown in */
export const full = (iso) => `${fmt(iso, { dateStyle: 'full', timeStyle: 'short' })}${zone ? ` (${zone.replace(/_/g, ' ')})` : ''}`;
/** "5 minutes ago" — relative, so zone-independent; worded in the UI language */
export const ago = (iso) => {
  const secs = (new Date(iso).getTime() - Date.now()) / 1000;
  if (Number.isNaN(secs)) return '';
  const abs = Math.abs(secs);
  const steps = [[60, 'second', 1], [3600, 'minute', 60], [86400, 'hour', 3600],
    [2592000, 'day', 86400], [31536000, 'month', 2592000], [Infinity, 'year', 31536000]];
  const [, unit, size] = steps.find(([limit]) => abs < limit);
  try {
    return new Intl.RelativeTimeFormat(getLocale(), { numeric: 'auto' }).format(Math.round(secs / size), unit);
  } catch (e) {
    return moment(iso).fromNow();
  }
};
/** Minutes between two instants */
export const minutesBetween = (a, b) => Math.abs(moment(a).diff(moment(b), 'minutes'));
