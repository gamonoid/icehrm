import React, { useEffect, useRef, useState } from 'react';
import { Spin } from 'antd';

/**
 * Mounts a module tab whose ADAPTER draws its own view — e.g. the settings
 * module, whose initTable() renders the bespoke SettingsPage (grouped inline
 * controls) instead of a table. The shell provides the Table/Form/FilterForm
 * containers (via setContainers) and triggers the adapter's get(), exactly as
 * the legacy footer would; everything else is the module's own code.
 */
export default function NativeAdapterView({ tabKey }) {
  const tableRef = useRef(null);
  const formRef = useRef(null);
  const filterRef = useRef(null);
  const [ready, setReady] = useState(false);

  useEffect(() => {
    let cancelled = false;
    let tries = 0;
    const tick = () => {
      if (cancelled) return;
      const m = (window.modJsList || {})[tabKey];
      if (m && tableRef.current) {
        if (typeof m.setContainers === 'function') {
          m.setContainers({
            Table: tableRef.current,
            Form: formRef.current,
            FilterForm: filterRef.current,
          });
        }
        // The instance persists across remounts — force a re-render into the
        // current containers.
        m.tableInitialized = false;
        m.formInitialized = false;
        window.modJs = m;
        try {
          if (m.masterDataReader && m.masterDataReader.updateAllMasterData) {
            m.masterDataReader.updateAllMasterData();
          }
        } catch (e) { /* ignore */ }
        try { m.get([]); } catch (e) { /* ignore */ }
        setReady(true);
        return;
      }
      tries += 1;
      if (tries <= 100) setTimeout(tick, 100);
    };
    tick();
    return () => { cancelled = true; };
  }, [tabKey]);

  return (
    <div>
      {!ready && (
        <div style={{ display: 'flex', justifyContent: 'center', padding: 40 }}><Spin size="large" /></div>
      )}
      <div ref={tableRef} />
      <div ref={formRef} />
      <div ref={filterRef} />
    </div>
  );
}
