import moment from 'moment';
import { getLocale } from '../../../../../web/api/i18n';

/**
 * Times for the feed. The API sends ISO-8601 instants; the browser's Intl API
 * renders them in the reader's own zone, and moment gives the relative form.
 */
function fmt(iso, opts) {
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '';
  try {
    return new Intl.DateTimeFormat(getLocale(), opts).format(d);
  } catch (e) {
    return d.toLocaleString();
  }
}

/** Tooltip: full date and time */
export const full = (iso) => fmt(iso, { dateStyle: 'full', timeStyle: 'short' });
/** "5 minutes ago" */
export const ago = (iso) => moment(iso).fromNow();
