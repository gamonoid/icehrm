/**
 * UI translations for every bundle (shell, admin, modules, extensions).
 *
 * Keys are the English text itself (gettext style), so an untranslated string
 * simply renders in English:
 *
 *   import { t, tp } from '../../api/i18n';
 *   t('Save')                                  // "Speichern"
 *   t('Welcome back, {name}', { name })        // placeholders use {braces}
 *   tp(count, '{count} day', '{count} days')   // English singular/plural pair
 *
 * The catalog lives on `window` (not in this module) because each gulp bundle
 * gets its own copy of this file — they must all read the one catalog the shell
 * installed from /appshell/bootstrap (or core/footer.php on legacy pages).
 *
 * Catalogs are maintained with `node tools/i18n/i18n.js` — see
 * docs/TRANSLATIONS.md. Only pass string LITERALS to t()/tp() so the extractor
 * can find them; runtime data (menu labels, field labels) must have its
 * literal in the source somewhere the extractor scans.
 */

const STORE_KEY = '__icehrmI18n';

function store() {
  if (typeof window === 'undefined') return { messages: {} };
  if (!window[STORE_KEY]) {
    window[STORE_KEY] = {
      lang: 'en', locale: 'en', dir: 'ltr', messages: {}, missing: {},
    };
  }
  return window[STORE_KEY];
}

/**
 * Install a catalog: { lang, locale, dir, messages: { english: translated } }.
 */
export function setI18n(payload) {
  const s = store();
  const p = payload || {};
  s.lang = p.lang || 'en';
  s.locale = p.locale || s.lang;
  s.dir = p.dir === 'rtl' ? 'rtl' : 'ltr';
  s.messages = (p.messages && typeof p.messages === 'object') ? p.messages : {};
  s.languages = Array.isArray(p.languages) ? p.languages : [];
  s.userLang = p.userLang || null;
  s.defaultLang = p.defaultLang || 'en';
  s.missing = {};
}

export function getI18n() {
  return store();
}

export function getLanguage() {
  return store().lang;
}

export function getLocale() {
  return store().locale;
}

export function isRtl() {
  return store().dir === 'rtl';
}

function interpolate(text, vars) {
  if (!vars) return text;
  return text.replace(/\{(\w+)\}/g, (match, name) => (
    Object.prototype.hasOwnProperty.call(vars, name) && vars[name] !== undefined && vars[name] !== null
      ? String(vars[name])
      : match
  ));
}

/**
 * Translated text for an English key, or the key itself when there is no
 * translation. Non-string input is returned unchanged.
 */
export function lookup(key) {
  if (typeof key !== 'string' || key === '') return key;
  const s = store();
  const hit = s.messages[key];
  if (typeof hit === 'string' && hit !== '') return hit;
  if (s.lang !== 'en' && s.missing) s.missing[key] = true;
  return key;
}

/**
 * Whether the active catalog has a translation for the key.
 */
export function has(key) {
  const hit = store().messages[key];
  return typeof hit === 'string' && hit !== '';
}

export function t(key, vars) {
  const out = lookup(key);
  return typeof out === 'string' ? interpolate(out, vars) : out;
}

/**
 * Plural helper. English has two forms; `{count}` is filled from `n`. Languages
 * with richer plural rules translate each form as a whole sentence.
 */
export function tp(n, one, other, vars) {
  return t(n === 1 ? one : other, Object.assign({ count: n }, vars || {}));
}

/**
 * Format a number / date with the UI locale (Intl), falling back quietly.
 */
export function formatNumber(value, options) {
  try {
    return new Intl.NumberFormat(getLocale(), options).format(value);
  } catch (e) {
    return String(value);
  }
}

export function formatDate(value, options) {
  try {
    const d = value instanceof Date ? value : new Date(value);
    if (Number.isNaN(d.getTime())) return String(value);
    return new Intl.DateTimeFormat(getLocale(), options).format(d);
  } catch (e) {
    return String(value);
  }
}

export default t;
