
import { t } from '../../../../../../web/api/i18n';/**
 * Loads the EditorJS core + the tools a post may use, from the same public tool
 * scripts the editor extension ships under web/js/editorjs (so nothing new has to
 * be packaged), and builds editor instances from them.
 *
 * Deliberately a SUBSET of the editor extension's toolset: nothing that fetches
 * (link preview, embeds, image upload) and nothing tied to a Content document
 * (employee checklists, quizzes). Every type here has a matching renderer in
 * BlockRenderer.js — and the server refuses any block type outside this list.
 */

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
  if (window.EditorJS && window.Header) return Promise.resolve();
  const base = window.BASE_URL || '';
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
 * EditorJS lays every block out in a 650px column centred in its holder — right for
 * a document page, wrong for a message box, where it leaves a wide blank margin on
 * both sides. Let the content fill the composer, keeping a gutter on the left for
 * the block toolbar (the + and ⠿ handles sit at right:100% of the toolbar row, i.e.
 * in that gutter). Scoped to .discussions-editor so the editor extension's own pages
 * are untouched. In EditorJS's narrow mode (holder under 650px) the handles move to
 * the right edge, so the gutter is dropped there.
 */
const COMPOSER_CSS = `
.discussions-editor .ce-block__content,
.discussions-editor .ce-toolbar__content {
  max-width: none;
  margin-left: 56px;
  margin-right: 0;
}
.discussions-editor .codex-editor--narrow .ce-block__content,
.discussions-editor .codex-editor--narrow .ce-toolbar__content {
  margin-left: 0;
}
.discussions-editor .ce-paragraph[data-placeholder]:empty::before {
  opacity: 0.55;
}
`;

let cssInjected = false;
function ensureComposerCss() {
  if (cssInjected || typeof document === 'undefined') return;
  const style = document.createElement('style');
  style.id = 'discussions-composer-css';
  style.textContent = COMPOSER_CSS;
  document.head.appendChild(style);
  cssInjected = true;
}

let counter = 0;

/**
 * @param {object} opts { holder: HTMLElement, data, placeholder, readOnly, onReady }
 * @returns {Promise<EditorJS>}
 */
export async function createEditor(opts) {
  await loadEditorJs();
  ensureComposerCss();
  const { holder, data, placeholder, readOnly, autofocus } = opts;
  if (!holder.id) {
    counter += 1;
    holder.id = `discussions-editor-${counter}`;
  }
  const editor = new window.EditorJS({
    holder: holder.id,
    tools: buildTools(),
    data: data || { blocks: [] },
    placeholder: placeholder || t('Write something…'),
    readOnly: !!readOnly,
    autofocus: !!autofocus,
    minHeight: 60,
    logLevel: 'ERROR',
  });
  await editor.isReady;
  return editor;
}
