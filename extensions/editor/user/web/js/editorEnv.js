import React from 'react';

/**
 * Central accessor for the editor's runtime environment.
 *
 * Legacy (the full-page index.php) sets `window.*` globals and navigates via the
 * browser; the SPA mount (mountEditor.js) sets `window.__editor*` overrides so the
 * very same sidebar components work inside the shell with no page reloads.
 *
 * Every default here reproduces the legacy behaviour exactly, so nothing changes
 * for the legacy page or for any consumer (learn, projects, esign, …).
 */
export const editorEnv = {
  isReadOnly: () => !!window.editor_readonly,
  canSelectChecks: () => !!window.editor_can_select_checks,
  employees: () => window.editorEmployees || [],
  controller: () => window.editorExtensionController,

  // Navigate to another document_link. Legacy: full-page navigation. SPA: an
  // override that re-fetches + re-mounts the document in the same modal.
  navigate: (url) => {
    if (!url) return;
    if (typeof window.__editorNavigate === 'function') {
      window.__editorNavigate(url);
    } else {
      window.location = url;
    }
  },

  // Close / go back. Legacy: history.back(). SPA: close the modal.
  close: () => {
    if (typeof window.__editorClose === 'function') {
      window.__editorClose();
    } else {
      window.history.back();
    }
  },

  colorMode: () => window.__editorColorMode || window.__shellColorMode || 'light',
};

/**
 * Drop-in replacement for `<a href={documentLink}>…</a>` inside the sidebars.
 * Keeps the href (so it still looks/right-clicks like a link) but routes the
 * left-click through editorEnv.navigate so the SPA can intercept it.
 */
export function EditorLink({ href, children }) {
  return (
    <a
      href={href || '#'}
      onClick={(e) => {
        if (!href) return;
        // Let modified clicks (new tab, etc.) behave normally.
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.button === 1) return;
        e.preventDefault();
        editorEnv.navigate(href);
      }}
    >
      {children}
    </a>
  );
}

export default editorEnv;
