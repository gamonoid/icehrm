/**
 * Dark-mode overrides for the EditorJS canvas + its tool popovers.
 *
 * Scoped under `body.ice-editor-dark`, which the SPA mount adds ONLY when it
 * renders in dark mode. The legacy full-page editor never adds that class, so its
 * light styling is completely untouched. (Editor popovers/toolboxes portal to
 * <body>, so the scope is on body rather than the editor container.)
 */
const EDITOR_DARK_CSS = `
body.ice-editor-dark .codex-editor,
body.ice-editor-dark .ce-block,
body.ice-editor-dark .ce-paragraph,
body.ice-editor-dark .ce-header,
body.ice-editor-dark .cdx-block { color: #e6e6e6; }

body.ice-editor-dark .codex-editor__redactor { caret-color: #e6e6e6; }
body.ice-editor-dark a { color: #6ea8ff; }

body.ice-editor-dark .ce-toolbar__plus,
body.ice-editor-dark .ce-toolbar__settings-btn { color: #cfd3dc; background: transparent; }
body.ice-editor-dark .ce-toolbar__plus:hover,
body.ice-editor-dark .ce-toolbar__settings-btn:hover { background: rgba(255,255,255,.08); }

body.ice-editor-dark .ce-popover,
body.ice-editor-dark .ce-popover__items,
body.ice-editor-dark .ce-inline-toolbar,
body.ice-editor-dark .ce-conversion-toolbar,
body.ice-editor-dark .ce-settings,
body.ice-editor-dark .ce-toolbox {
  background: #2a3441;
  color: #e6e6e6;
  border: 1px solid rgba(255,255,255,.12);
  box-shadow: 0 6px 24px rgba(0,0,0,.45);
}

body.ice-editor-dark .ce-popover-item__title,
body.ice-editor-dark .ce-popover__item-label,
body.ice-editor-dark .cdx-settings-button,
body.ice-editor-dark .ce-inline-tool { color: #e6e6e6; }

body.ice-editor-dark .ce-popover-item:hover,
body.ice-editor-dark .ce-popover__item:hover,
body.ice-editor-dark .ce-inline-tool:hover,
body.ice-editor-dark .cdx-settings-button:hover { background: rgba(255,255,255,.10); }

body.ice-editor-dark .cdx-input,
body.ice-editor-dark .cdx-checklist__item-text,
body.ice-editor-dark input,
body.ice-editor-dark textarea {
  color: #e6e6e6;
  border-color: rgba(255,255,255,.18);
  background: transparent;
}
body.ice-editor-dark .cdx-checklist__item-checkbox { border-color: rgba(255,255,255,.4); }
body.ice-editor-dark .ce-block--selected .ce-block__content { background: rgba(120,150,255,.12); }

body.ice-editor-dark .tc-table,
body.ice-editor-dark .tc-row,
body.ice-editor-dark .tc-cell { border-color: rgba(255,255,255,.18); color: #e6e6e6; }
`;

const STYLE_ID = 'ice-editor-dark-style';

/** Add/remove the scoped dark stylesheet + the body scope class. */
export function setEditorDark(on) {
  if (typeof document === 'undefined') return;
  if (on) {
    if (!document.getElementById(STYLE_ID)) {
      const style = document.createElement('style');
      style.id = STYLE_ID;
      style.textContent = EDITOR_DARK_CSS;
      document.head.appendChild(style);
    }
    document.body.classList.add('ice-editor-dark');
  } else {
    document.body.classList.remove('ice-editor-dark');
  }
}

export default EDITOR_DARK_CSS;
