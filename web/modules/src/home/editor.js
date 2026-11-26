/**
 * The rich-text editor for Home's composer.
 *
 * Loads EditorJS + a subset of its tools at runtime from the scripts shipped under
 * web/js/editorjs — the same ones the Discussions composer uses — so a post written
 * on Home has exactly the same block content, renders identically in Discussions,
 * and stays editable there. Nothing is bundled: these are plain <script> tags, and
 * the module bundle stays the same size.
 *
 * Home is core while the editor assets are shipped with the web app, so a missing
 * or failed script must never break the landing page — loadEditorJs() rejects and
 * the composer falls back to a plain textarea.
 *
 * The tool list is deliberately the SAME SUBSET the discussions server accepts:
 * anything outside it is refused by validateContent() on save.
 */
import { t } from '../../../api/i18n';

const TOOL_SCRIPTS = [
  'js/editorjs/public/js/header.js',
  'js/editorjs/public/js/list.js',
  'js/editorjs/public/js/checklist.js',
  'js/editorjs/public/js/quote.js',
  'js/editorjs/public/js/code.js',
  'js/editorjs/public/js/table.js',
  'js/editorjs/public/js/marker.js',
  'js/editorjs/public/js/inline-code.js',
  'js/editorjs/public/js/delimiter.js',
  'js/editorjs/public/js/editorjs.js', // core last
];

/**
 * Where the editor assets live. The SPA does not set window.BASE_URL (legacy pages
 * do), so fall back to the baseUrl the shell handed the module's adapter.
 */
function assetBase() {
  if (typeof window === 'undefined') return '';
  if (window.BASE_URL) return window.BASE_URL;
  const m = window.modJs;
  if (m && m.baseUrl) return m.baseUrl;
  return '';
}

const loaded = {};
function loadScript(url) {
  if (loaded[url]) return loaded[url];
  loaded[url] = new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = url;
    s.async = false;
    s.onload = () => resolve();
    s.onerror = () => reject(new Error(`Failed to load ${url}`));
    document.head.appendChild(s);
  });
  return loaded[url];
}

export function loadEditorJs() {
  if (typeof window !== 'undefined' && window.EditorJS && window.Header) {
    return Promise.resolve();
  }
  const base = assetBase();
  return TOOL_SCRIPTS
    .map((s) => `${base}${s}`)
    .reduce((prev, u) => prev.then(() => loadScript(u)), Promise.resolve());
}

/**
 * A minimal inline tool whose only job is to get the mention span past EditorJS's
 * save-time sanitizer.
 *
 * EditorJS reads a block's allow-list from the tool CLASS (`constructable.sanitize`)
 * and ignores any `sanitize` passed in tool settings — but it merges the allow-list
 * of every INLINE tool enabled for that block. That is why <mark> (Marker) and
 * <code> (InlineCode) survive while a bare <span> does not. Registering mentions as
 * an inline tool is therefore the supported way to keep them.
 *
 * It renders no button: mentions are inserted by the "@" picker, not from the
 * inline toolbar, so there is nothing for a user to click here.
 */
function buildMentionTool() {
  return class MentionInlineTool {
    static get isInline() { return true; }

    static get title() { return 'Mention'; }

    // The whole point: keep <span class="ice-mention" data-employee="…">.
    static get sanitize() {
      return { span: { class: true, 'data-employee': true } };
    }

    render() {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.style.display = 'none';
      return btn;
    }

    surround() {}

    checkState() { return false; }
  };
}

function buildTools() {
  return {
    paragraph: { config: { preserveBlank: true } },
    header: {
      class: window.Header,
      inlineToolbar: ['marker', 'inlineCode', 'mention'],
      config: { placeholder: t('Heading'), levels: [2, 3, 4], defaultLevel: 3 },
    },
    list: { class: window.List, inlineToolbar: true },
    checklist: { class: window.Checklist, inlineToolbar: true },
    quote: {
      class: window.Quote,
      inlineToolbar: true,
      config: { quotePlaceholder: t('Quote'), captionPlaceholder: t('Who said it') },
    },
    code: { class: window.CodeTool },
    table: { class: window.Table, inlineToolbar: true },
    mention: { class: buildMentionTool() },
    marker: { class: window.Marker },
    inlineCode: { class: window.InlineCode },
    delimiter: window.Delimiter,
  };
}

/**
 * EditorJS centres every block in a fixed 650px column, which leaves a wide blank
 * margin inside a composer box. Let the content fill the width, keeping a left
 * gutter for the block handles (+ and ⠿ sit at right:100% of the toolbar row).
 * In EditorJS's narrow mode the handles move to the right edge, so the gutter goes.
 * Scoped to .home-editor so nothing else is affected.
 */
const EDITOR_CSS = `
.home-editor .ce-block__content,
.home-editor .ce-toolbar__content {
  max-width: none;
  margin-left: 44px;
  margin-right: 0;
}
.home-editor .codex-editor--narrow .ce-block__content,
.home-editor .codex-editor--narrow .ce-toolbar__content {
  margin-left: 0;
}
.home-editor .ce-paragraph[data-placeholder]:empty::before {
  opacity: 0.55;
}
.home-editor .codex-editor {
  padding-bottom: 0;
}
.home-editor .ce-block:first-child .ce-paragraph {
  padding-top: 0;
}

/* --- theming -------------------------------------------------------------
 * EditorJS 2.30 drives its chrome (popovers, toolbar, inline toolbar) from CSS
 * custom properties, and the composer sets those from the antd theme tokens —
 * so the editor follows light/dark automatically. What the variables do NOT
 * cover is the individual tools (code, quote, checklist, table, header), which
 * ship hard-coded light colours; those are neutralised here to inherit the
 * surrounding text colour instead of painting white-on-white or dark-on-dark.
 */
.home-editor .ce-toolbar__plus,
.home-editor .ce-toolbar__settings-btn {
  color: var(--home-ed-text-2);
  background: transparent;
}
.home-editor .ce-toolbar__plus:hover,
.home-editor .ce-toolbar__settings-btn:hover {
  background: var(--home-ed-hover);
  color: var(--home-ed-text);
}
/* The popover (block toolbox, block settings, inline toolbar) paints a hard-coded
 * white panel with black text in this EditorJS build — the --color-* variables do
 * not reach it — so it is restyled explicitly. Without this the whole menu is
 * invisible-on-white in dark mode. */
.home-editor .ce-popover__container {
  background: var(--home-ed-pop-bg);
  border: 1px solid var(--home-ed-border);
  box-shadow: 0 6px 24px rgba(0, 0, 0, 0.35);
}
.home-editor .ce-popover-item__title,
.home-editor .ce-popover-item__secondary-title,
.home-editor .ce-popover-item__icon,
.home-editor .ce-popover__nothing-found-message,
.home-editor .ce-popover-header__text {
  color: var(--home-ed-text);
}
.home-editor .ce-popover-item__icon {
  background: var(--home-ed-soft);
  box-shadow: none;
}
.home-editor .ce-popover-item:hover:not(.ce-popover-item--no-hover) {
  background: var(--home-ed-hover);
}
.home-editor .ce-popover-item--active,
.home-editor .ce-popover-item--focused {
  background: var(--home-ed-active-bg) !important;
}
.home-editor .ce-popover-item--active .ce-popover-item__title,
.home-editor .ce-popover-item--active .ce-popover-item__icon {
  color: var(--home-ed-active);
}
.home-editor .ce-popover__search,
.home-editor .cdx-search-field {
  background: var(--home-ed-hover);
  color: var(--home-ed-text);
  border: 1px solid var(--home-ed-border);
}
.home-editor .cdx-search-field__input {
  color: var(--home-ed-text);
}
.home-editor .cdx-search-field__input::placeholder {
  color: var(--home-ed-text-2);
}
/* tools with baked-in light styling */
.home-editor .ce-code__textarea,
.home-editor .cdx-input,
.home-editor .cdx-quote__text,
.home-editor .cdx-quote__caption {
  background: var(--home-ed-soft);
  color: var(--home-ed-text);
  border: 1px solid var(--home-ed-border);
  box-shadow: none;
}
.home-editor .ce-header,
.home-editor .ce-paragraph,
.home-editor .cdx-checklist__item-text,
.home-editor .cdx-list__item {
  color: var(--home-ed-text);
}
.home-editor .cdx-checklist__item-checkbox-check {
  border-color: var(--home-ed-border);
}
.home-editor .tc-table,
.home-editor .tc-cell,
.home-editor .tc-row,
.home-editor .tc-wrap {
  border-color: var(--home-ed-border) !important;
  color: var(--home-ed-text);
}
.home-editor .tc-toolbox__toggler,
.home-editor .tc-add-column,
.home-editor .tc-add-row {
  color: var(--home-ed-text-2);
}
.home-editor .tc-add-column:hover,
.home-editor .tc-add-row:hover {
  background: var(--home-ed-hover);
}
`;

let cssInjected = false;
function ensureEditorCss() {
  if (cssInjected || typeof document === 'undefined') return;
  const style = document.createElement('style');
  style.id = 'home-composer-css';
  style.textContent = EDITOR_CSS;
  document.head.appendChild(style);
  cssInjected = true;
}

let counter = 0;

/**
 * @param {object} opts { holder, data, placeholder, autofocus }
 * @returns {Promise<EditorJS>}
 */
export function createEditor(opts) {
  return loadEditorJs().then(() => {
    ensureEditorCss();
    const {
      holder, data, placeholder, autofocus,
    } = opts;
    if (!holder.id) {
      counter += 1;
      holder.id = `home-editor-${counter}`;
    }
    const editor = new window.EditorJS({
      holder: holder.id,
      tools: buildTools(),
      data: data || { blocks: [] },
      placeholder: placeholder || t('Share something with the team…'),
      autofocus: !!autofocus,
      minHeight: 80,
      logLevel: 'ERROR',
    });
    return editor.isReady.then(() => editor);
  });
}

/**
 * The CSS custom properties that theme an EditorJS instance, derived from the
 * antd theme tokens. Applied as inline styles on the editor holder, so the editor
 * matches the app in both light and dark mode — and follows the theme toggle
 * without having to rebuild the editor.
 *
 * The `--color-*` names are EditorJS's own (2.30); the `--home-ed-*` ones are
 * read by the tool overrides in EDITOR_CSS above.
 *
 * @param  {object} token antd theme token (theme.useToken())
 * @return {object} style object for the holder element
 */
export function editorThemeVars(token) {
  return {
    // EditorJS chrome: popovers, toolbar, inline toolbar
    '--color-background': token.colorBgElevated,
    '--color-text-primary': token.colorText,
    '--color-text-secondary': token.colorTextSecondary,
    '--color-border': token.colorBorder,
    '--color-background-item-hover': token.colorFillTertiary,
    '--color-background-item-focus': token.colorFillSecondary,
    '--color-background-icon-active': token.colorPrimaryBg,
    '--color-text-icon-active': token.colorPrimary,
    '--color-shadow': 'rgba(0, 0, 0, 0.25)',
    // consumed by the tool + popover overrides
    '--home-ed-text': token.colorText,
    '--home-ed-text-2': token.colorTextSecondary,
    '--home-ed-border': token.colorBorder,
    '--home-ed-hover': token.colorFillTertiary,
    '--home-ed-soft': token.colorFillQuaternary,
    '--home-ed-pop-bg': token.colorBgElevated,
    '--home-ed-active': token.colorPrimary,
    '--home-ed-active-bg': token.colorPrimaryBg,
  };
}

/** True when the content has at least one block carrying text. */
export function hasText(content) {
  const blocks = (content && content.blocks) || [];
  return blocks.some((b) => {
    const d = (b && b.data) || {};
    if (typeof d.text === 'string' && d.text.replace(/<[^>]*>/g, '').trim()) return true;
    if (Array.isArray(d.items) && d.items.length) return true;
    if (typeof d.code === 'string' && d.code.trim()) return true;
    if (b && b.type === 'delimiter') return true;
    if (d.content && Array.isArray(d.content) && d.content.length) return true;
    return false;
  });
}
