import React from 'react';
import ReactDOM from 'react-dom';
import { ConfigProvider, theme } from 'antd';
import EditorUserExtensionView from './view';
import buildEditorTools from './buildEditorTools';
import { setEditorDark } from './editorDarkCss';

/**
 * Imperatively mounts a single EditorJS document (editor canvas + sidebar) into
 * caller-provided DOM containers. Used by the native SPA component
 * (EditorDocument.jsx). The legacy full-page index.php keeps its own inline
 * bootstrap; both share buildEditorTools + the sidebar components, so they can't
 * drift.
 */

// EditorJS tool scripts (globals: Header, ImageTool, List, …). Same set + order
// as the legacy index.php; core editorjs.js loads last. Relative to BASE_URL.
const TOOL_SCRIPTS = [
  'js/editorjs/public/js/header.js',
  'js/editorjs/public/js/simple-image.js',
  'js/editorjs/public/js/delimiter.js',
  'js/editorjs/public/js/list.js',
  'js/editorjs/public/js/quote.js',
  'js/editorjs/public/js/code.js',
  'js/editorjs/public/js/embed.js',
  'js/editorjs/public/js/table.js',
  'js/editorjs/public/js/link.js',
  'js/editorjs/public/js/warning.js',
  'js/editorjs/public/js/checklist.js',
  'js/editorjs/public/js/marker.js',
  'js/editorjs/public/js/inline-code.js',
  'js/editorjs/public/js/image.js',
  'js/editorjs/public/js/editorjs.js',
];

const loadedScripts = {};
function loadScript(url) {
  if (loadedScripts[url]) return loadedScripts[url];
  loadedScripts[url] = new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = url;
    s.async = false;
    s.onload = () => resolve();
    s.onerror = () => reject(new Error(`Failed to load ${url}`));
    document.head.appendChild(s);
  });
  return loadedScripts[url];
}
function loadToolScripts(baseUrl) {
  return TOOL_SCRIPTS
    .map((s) => `${baseUrl || ''}${s}`)
    .reduce((prev, u) => prev.then(() => loadScript(u)), Promise.resolve());
}

function themed(node, colorMode) {
  if (colorMode !== 'dark') return node;
  return (
    <ConfigProvider theme={{
      algorithm: theme.darkAlgorithm,
      token: {
        colorBgContainer: '#222b36',
        colorBgElevated: '#2a3441',
        colorBgLayout: '#1a212b',
      },
    }}>
      {node}
    </ConfigProvider>
  );
}

/**
 * @param {object} opts
 *   editorHolder, sidebarContainer, employeeSelectContainer  (DOM elements)
 *   payload      resolved document (EditorService::resolveDocument output)
 *   controllerUrl, clientBaseUrl, baseUrl, restApiBase, token
 *   colorMode    'dark' | 'light'
 *   navigate(url), onClose()                                 (callbacks)
 * @returns {Promise<object>} handle for unmountEditorDocument
 */
export async function mountEditorDocument(opts) {
  const {
    editorHolder, sidebarContainer, employeeSelectContainer,
    payload, controllerUrl, clientBaseUrl, baseUrl, restApiBase, token,
    colorMode, navigate, onClose,
  } = opts;

  // The editor.js custom tools render into these well-known ids.
  if (editorHolder) editorHolder.id = 'editorjs';
  if (employeeSelectContainer) employeeSelectContainer.id = 'EmployeeSelect';

  // Per-open globals the sidebars / SaveButton / tools read (same names the
  // legacy page sets). editorEnv reads these with safe defaults.
  window.object_type = payload.objectType;
  window.object_id = payload.objectId;
  window.object_field = payload.objectField;
  window.content_id = payload.contentId;
  window.hash = payload.hash;
  window.editor_readonly = !!payload.readOnly;
  window.editor_can_select_checks = !!payload.canSelectChecks;
  window.editorEmployees = payload.employees || [];
  window.sideBarObject = payload.sideBarObject || {};

  // SPA navigation overrides (editorEnv falls back to window.location / back()).
  window.__editorNavigate = typeof navigate === 'function' ? navigate : null;
  window.__editorClose = typeof onClose === 'function' ? onClose : null;
  window.__editorColorMode = colorMode || 'light';
  window.CLIENT_BASE_URL = clientBaseUrl;
  window.BASE_URL = baseUrl;

  // initEditorUser resets the shared window.modJsList/modJs globals. The HOST
  // (NativeCardList) snapshots + restores them around the whole mount lifecycle —
  // it can't be done here because the editor re-mounts on in-document navigation
  // and wouldn't know the original host value.
  if (typeof window.initEditorUser === 'function') {
    window.initEditorUser({ controller_url: controllerUrl });
  }
  // Wire the freshly-created module's API client to the shell session.
  try {
    if (window.modJs) {
      window.modJs.translations = window.modJs.translations || {};
      if (restApiBase) window.modJs.setApiUrl(restApiBase);
      if (typeof window.modJs.setupApiClient === 'function') window.modJs.setupApiClient(token);
    }
  } catch (e) { /* ignore */ }

  await loadToolScripts(baseUrl);

  const tools = buildEditorTools({
    contentId: payload.contentId,
    clientBaseUrl,
    objectType: payload.objectType,
  });

  // eslint-disable-next-line no-undef
  const editor = new window.EditorJS({
    holder: 'editorjs',
    readOnly: !!payload.readOnly,
    tools,
    data: payload.data || {},
  });
  window.editor = editor;

  if (sidebarContainer) {
    ReactDOM.render(themed(<EditorUserExtensionView />, colorMode), sidebarContainer);
  }

  setEditorDark(colorMode === 'dark');

  return {
    editor, sidebarContainer, employeeSelectContainer,
  };
}

export function unmountEditorDocument(handle) {
  if (!handle) return;
  try { if (handle.editor && handle.editor.destroy) handle.editor.destroy(); } catch (e) { /* */ }
  try {
    if (handle.sidebarContainer) ReactDOM.unmountComponentAtNode(handle.sidebarContainer);
  } catch (e) { /* */ }
  try {
    if (handle.employeeSelectContainer) ReactDOM.unmountComponentAtNode(handle.employeeSelectContainer);
  } catch (e) { /* */ }
  setEditorDark(false);
  // NOTE: window.modJs/modJsList are restored by the host (NativeCardList), not
  // here — see mountEditorDocument. We only clear our own editor-scoped globals.
  window.editor = undefined;
  window.__editorNavigate = null;
  window.__editorClose = null;
}

export default mountEditorDocument;
