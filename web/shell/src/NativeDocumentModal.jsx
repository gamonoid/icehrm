import React, { useEffect, useRef } from 'react';
import { Modal } from 'antd';

// Lazy, idempotent loader for an extension bundle that exposes a native
// document-mount function (e.g. the editor's window.mountEditorDocument). Mirrors
// loadNativeBundle in NativeCardList — kept local so this modal has no coupling to
// the card list.
const DOC_CB = String(Date.now());
const docBundles = {};
function loadDocBundle(url) {
  if (docBundles[url]) return docBundles[url];
  const p = new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = `${url}${url.indexOf('?') >= 0 ? '&' : '?'}cb=${DOC_CB}`;
    s.async = false;
    s.onload = () => resolve();
    s.onerror = () => { s.remove(); reject(new Error(`Failed to load ${url}`)); };
    document.head.appendChild(s);
  });
  // Evict on failure so the next open retries instead of replaying the rejection.
  docBundles[url] = p.catch((e) => { delete docBundles[url]; throw e; });
  return docBundles[url];
}

/**
 * Shell-level modal that mounts a native document editor for an arbitrary
 * document URL (a legacy `g=extension&n=editor|user&...&hash=…` link). Used when
 * something OUTSIDE a card list needs to open a document — e.g. clicking a task
 * notification. It loads the editor bundle and calls its mountFn, snapshotting +
 * restoring the shared window.modJs/modJsList globals the bundle clobbers (same
 * contract as NativeCardList's native documentAction).
 */
// Load a list of bundle URLs strictly in order (each waits for the previous), so
// dependency bundles register their modules before dependents run.
function loadDocBundlesInOrder(urls) {
  return urls.reduce((p, url) => p.then(() => loadDocBundle(url)), Promise.resolve());
}

export default function NativeDocumentModal({
  documentUrl, bundle, mountFn, title, deps, shellConfig, onClose,
}) {
  const elRef = useRef(null);

  useEffect(() => {
    if (!documentUrl) return undefined;
    const el = elRef.current;
    if (!el) return undefined;
    // The editor bundle (EditorDocument / mountEditor) reads these globals. When
    // the modal opens from a context that hasn't mounted a native module yet (e.g.
    // the dashboard), they may be unset — seed them from the shell config so the
    // bundle URL + REST calls resolve correctly.
    if (shellConfig.baseUrl) window.BASE_URL = window.BASE_URL || shellConfig.baseUrl;
    if (shellConfig.clientBaseUrl) window.CLIENT_BASE_URL = window.CLIENT_BASE_URL || shellConfig.clientBaseUrl;
    // Legacy adapter code (AdapterBase) reads a bare global `baseUrl` (the
    // service.php endpoint) at construction. NativeModuleHost sets it per module;
    // seed it here for the standalone case.
    if (!window.baseUrl && shellConfig.clientBaseUrl) window.baseUrl = `${shellConfig.clientBaseUrl}service.php`;
    const savedModJsList = window.modJsList;
    const savedModJs = window.modJs;
    let cancelled = false;
    const mountName = mountFn || 'mountEditorDocument';
    const unmountName = mountName.replace('mount', 'unmount');
    const webBase = window.BASE_URL || shellConfig.baseUrl || '';
    const extBase = webBase.replace('/web/', '/extensions/');
    const bundleUrl = bundle ? `${extBase}${bundle}` : null;
    // Vendor/dep bundles the extension bundle expects to already be registered
    // (npm externals like moment). NativeModuleHost loads these before any module;
    // when the modal opens standalone (from a notification) we must load them too.
    const depUrls = (deps || []).map((d) => (/^https?:\/\//.test(d) ? d : `${webBase}${d}`));

    const run = () => {
      const fn = window[mountName];
      if (typeof fn === 'function') {
        fn(el, {
          documentUrl,
          restApiBase: shellConfig.restApiBase,
          token: shellConfig.token,
          colorMode: window.__shellColorMode || 'light',
          onClose,
        });
      }
    };

    loadDocBundlesInOrder(depUrls)
      .then(() => (bundleUrl ? loadDocBundle(bundleUrl) : Promise.resolve()))
      .then(() => { if (!cancelled) run(); })
      .catch(() => { /* ignore */ });

    return () => {
      cancelled = true;
      const ufn = window[unmountName];
      if (typeof ufn === 'function') { try { ufn(el); } catch (e) { /* ignore */ } }
      window.modJsList = savedModJsList;
      window.modJs = savedModJs;
    };
  }, [documentUrl]); // eslint-disable-line react-hooks/exhaustive-deps

  if (!documentUrl) return null;
  return (
    <Modal
      open
      width="92%"
      style={{ top: 16, maxWidth: 1280 }}
      footer={null}
      title={title || 'Document'}
      onCancel={onClose}
      styles={{ body: { padding: 16, minHeight: '60vh', maxHeight: '86vh', overflowY: 'auto' } }}
      destroyOnClose
    >
      <div ref={elRef} />
    </Modal>
  );
}
