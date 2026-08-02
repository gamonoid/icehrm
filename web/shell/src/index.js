import React from 'react';
import ReactDOM from 'react-dom';
import { ConfigProvider, message } from 'antd';
import AppShell from './AppShell';
import { buildTheme, MUI, MUI_DARK } from './theme';

/**
 * App shell entry (SPA migration Phase 1).
 * Reads bootstrap config injected by core/spa-shell.php, fetches /appshell/bootstrap
 * with the JWT, and renders the persistent shell.
 */
function readConfig() {
  const el = document.getElementById('app-shell-config');
  if (!el) return {};
  try {
    return JSON.parse(el.textContent || '{}');
  } catch (e) {
    return {};
  }
}

const COLOR_MODE_KEY = 'shell-color-mode';

function readColorMode() {
  try {
    const saved = localStorage.getItem(COLOR_MODE_KEY);
    if (saved === 'dark' || saved === 'light') return saved;
  } catch (e) { /* ignore */ }
  // Fall back to the OS preference the first time.
  try {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) return 'dark';
  } catch (e) { /* ignore */ }
  return 'light';
}

// Keep the document background (visible during loads / outside React) in sync
// with the active mode, so there is no light flash in dark mode.
function applyBodyBackground(mode) {
  const bg = mode === 'dark' ? MUI_DARK.bg : MUI.bg;
  const fg = mode === 'dark' ? MUI_DARK.text : MUI.text;
  try {
    document.documentElement.style.background = bg;
    document.body.style.background = bg;
    document.body.style.color = fg;
    document.body.setAttribute('data-color-mode', mode);
    // Exposed so legacy adapter modals (rendered in their own React roots,
    // outside this ConfigProvider) can theme themselves to match — see
    // ReactModalAdapterBase.shellThemeWrap().
    window.__shellColorMode = mode;
  } catch (e) { /* ignore */ }
}

// A tiny stateful wrapper so the in-app toggle can re-theme the whole shell.
function ThemedShell({ bootstrap, config }) {
  const [mode, setMode] = React.useState(readColorMode);

  React.useEffect(() => { applyBodyBackground(mode); }, [mode]);

  const toggleColorMode = React.useCallback(() => {
    setMode((prev) => {
      const next = prev === 'dark' ? 'light' : 'dark';
      try { localStorage.setItem(COLOR_MODE_KEY, next); } catch (e) { /* ignore */ }
      return next;
    });
  }, []);

  return React.createElement(
    ConfigProvider,
    { theme: buildTheme(mode) },
    React.createElement(AppShell, {
      bootstrap, config, colorMode: mode, onToggleColorMode: toggleColorMode,
    }),
  );
}

function renderRaw(children) {
  ReactDOM.render(
    React.createElement(ConfigProvider, { theme: buildTheme(readColorMode()) }, children),
    document.getElementById('app-shell-root'),
  );
}

// NOTE: intentionally using Promise chains (not async/await) so the bundle does
// not depend on regeneratorRuntime, which is not provided by the vendor bundles.
function boot() {
  applyBodyBackground(readColorMode());
  const config = readConfig();
  fetch(`${config.restApiBase}appshell/bootstrap`, {
    headers: { Authorization: `Bearer ${config.token}` },
    credentials: 'same-origin',
  })
    .then((res) => {
      if (!res.ok) {
        throw new Error(`bootstrap failed: ${res.status}`);
      }
      return res.json();
    })
    .then((bootstrap) => {
      ReactDOM.render(
        React.createElement(ThemedShell, { bootstrap, config }),
        document.getElementById('app-shell-root'),
      );
    })
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error('[app-shell] boot failed', err);
      renderRaw(React.createElement(
        'div',
        { style: { padding: 24 } },
        `Failed to load the app shell: ${err.message}`,
      ));
      try { message.error('Failed to load the app shell'); } catch (e) { /* noop */ }
    });
}

boot();
