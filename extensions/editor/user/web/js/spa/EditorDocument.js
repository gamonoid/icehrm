import React, {
  useCallback, useEffect, useRef, useState,
} from 'react';
import { Spin, Empty, ConfigProvider, theme } from 'antd';
import { mountEditorDocument, unmountEditorDocument } from '../mountEditor';

/**
 * Native (no-iframe) editor for the SPA shell. Fetches the document via the
 * editor/document REST endpoint (the same data the legacy page computes), renders
 * the editor canvas + sidebar, and mounts EditorJS through mountEditor.js.
 *
 * In-document navigation (clicking another lesson/task link in a sidebar) is
 * intercepted via the editorEnv navigate seam and handled here by re-fetching +
 * re-mounting in place — so the user never leaves the SPA.
 */

// Pull the editor/document query params out of a legacy document_link URL.
function paramsFromDocumentUrl(documentUrl) {
  try {
    const qs = documentUrl.indexOf('?') >= 0 ? documentUrl.split('?')[1] : documentUrl;
    const sp = new URLSearchParams(qs);
    const out = {};
    ['hash', 'object', 'id', 'field', 'view', 'checks', 'title'].forEach((k) => {
      const v = sp.get(k);
      if (v !== null && v !== undefined && v !== '') out[k] = v;
    });
    return out;
  } catch (e) {
    return {};
  }
}

export default function EditorDocument({
  documentUrl, restApiBase, token, colorMode, onClose,
}) {
  const [url, setUrl] = useState(documentUrl);
  const [state, setState] = useState({ loading: true, error: null, payload: null });

  const editorRef = useRef(null);
  const sidebarRef = useRef(null);
  const employeeSelectRef = useRef(null);
  const handleRef = useRef(null);

  const mode = colorMode || window.__shellColorMode || 'light';
  const baseUrl = window.BASE_URL || '';
  const clientBaseUrl = window.CLIENT_BASE_URL || '';

  // Fetch the document payload whenever the target url changes.
  useEffect(() => {
    let alive = true;
    setState({ loading: true, error: null, payload: null });
    const params = paramsFromDocumentUrl(url);
    const qs = Object.keys(params).map((k) => `${k}=${encodeURIComponent(params[k])}`).join('&');
    fetch(`${restApiBase}editor/document?${qs}`, {
      headers: { Authorization: `Bearer ${token}` },
      credentials: 'same-origin',
    })
      .then((r) => r.json())
      .then((d) => {
        if (!alive) return;
        // SUCCESS returns the resolved payload directly ({allowed, data, …});
        // errors come back wrapped as { error: [[{message}]] }.
        if (d && d.allowed) {
          setState({ loading: false, error: null, payload: d });
        } else {
          const msg = (d && d.error && d.error[0] && d.error[0][0] && d.error[0][0].message)
            || (d && typeof d.error === 'string' ? d.error : null)
            || 'Could not open this document';
          setState({ loading: false, error: msg, payload: null });
        }
      })
      .catch(() => { if (alive) setState({ loading: false, error: 'Could not open this document', payload: null }); });
    return () => { alive = false; };
  }, [url, restApiBase, token]);

  const navigate = useCallback((nextUrl) => { setUrl(nextUrl); }, []);

  // Mount EditorJS once the payload + containers are ready; tear down on change.
  useEffect(() => {
    if (!state.payload || !editorRef.current) return undefined;
    let cancelled = false;

    // Replace any previous mount (e.g. after in-place navigation).
    if (handleRef.current) {
      unmountEditorDocument(handleRef.current);
      handleRef.current = null;
    }

    mountEditorDocument({
      editorHolder: editorRef.current,
      sidebarContainer: sidebarRef.current,
      employeeSelectContainer: employeeSelectRef.current,
      payload: state.payload,
      controllerUrl: `${clientBaseUrl}service.php`,
      clientBaseUrl,
      baseUrl,
      restApiBase,
      token,
      colorMode: mode,
      navigate,
      onClose,
    }).then((handle) => {
      if (cancelled) { unmountEditorDocument(handle); return; }
      handleRef.current = handle;
    });

    return () => { cancelled = true; };
  }, [state.payload]); // eslint-disable-line react-hooks/exhaustive-deps

  // Final teardown.
  useEffect(() => () => {
    if (handleRef.current) {
      unmountEditorDocument(handleRef.current);
      handleRef.current = null;
    }
  }, []);

  const dark = mode === 'dark';
  const content = (
    <div style={{ position: 'relative', minHeight: 420 }}>
      {state.loading && (
        <div style={{ display: 'flex', justifyContent: 'center', padding: 80 }}>
          <Spin size="large" />
        </div>
      )}
      {!state.loading && state.error && (
        <div style={{ padding: 48 }}><Empty description={state.error} /></div>
      )}
      <div style={{
        display: state.loading || state.error ? 'none' : 'flex',
        gap: 20,
        alignItems: 'flex-start',
        flexWrap: 'wrap',
      }}>
        <div style={{ flex: '1 1 540px', minWidth: 320 }}>
          <div ref={editorRef} />
        </div>
        <div style={{ flex: '0 0 340px', maxWidth: '100%' }}>
          <div ref={sidebarRef} />
          <div ref={employeeSelectRef} />
        </div>
      </div>
    </div>
  );

  if (!dark) return content;
  return (
    <ConfigProvider theme={{
      algorithm: theme.darkAlgorithm,
      token: { colorBgContainer: '#222b36', colorBgElevated: '#2a3441', colorBgLayout: '#1a212b' },
    }}>
      {content}
    </ConfigProvider>
  );
}
