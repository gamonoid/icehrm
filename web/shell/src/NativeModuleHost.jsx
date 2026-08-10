import React, { useEffect, useRef, useState } from 'react';
import ReactDOM from 'react-dom';
import {
  Tabs, Spin, Empty, theme, Badge, Button,
} from 'antd';
import { MUI_SHADOW, MUI_DARK } from './theme';
import OrgChart from './OrgChart';
import CompanyStructureCards from './CompanyStructureCards';
import NativeCardList from './NativeCardList';
import NativeExtensionView from './NativeExtensionView';
import MobileApp from './MobileApp';
import ApiAccess from './ApiAccess';
import TimeSheets from './TimeSheets';
import LeaveEntitlement from './LeaveEntitlement';
import LeaveCalendar from './LeaveCalendar';
import NativeAdapterView from './NativeAdapterView';
import LicenseBlocked from './LicenseBlocked';

// Tabs may declare a custom React component instead of mounting a legacy adapter.
const TAB_COMPONENTS = {
  OrgChart, CompanyStructureCards, NativeCardList, NativeExtensionView, MobileApp, ApiAccess, TimeSheets, LeaveEntitlement, LeaveCalendar,
  NativeAdapterView,
};

/**
 * Natively mounts a registered legacy/React module inside the shell — no iframe.
 * It fetches /appshell/module-context, loads the module's JS bundles once, calls
 * its global init() into shell-provided containers (the legacy `#<tab><suffix>`
 * div ids), applies the context the legacy footer.php injects, then drives each
 * tab's adapter (get / field master data) like the footer does. On unmount it
 * tears the adapters down. SPA migration Phase 3.
 */

const loadedScripts = {};
function loadScript(url) {
  if (loadedScripts[url]) return loadedScripts[url];
  const p = new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = url;
    s.async = false;
    s.onload = () => resolve();
    s.onerror = () => { s.remove(); reject(new Error(`Failed to load ${url}`)); };
    document.head.appendChild(s);
  });
  // Evict on failure — a cached rejection would otherwise brick the module for
  // the lifetime of the page (every navigation replays it until a full refresh).
  loadedScripts[url] = p.catch((e) => { delete loadedScripts[url]; throw e; });
  return loadedScripts[url];
}
function loadScriptsSequential(urls) {
  return urls.reduce((prev, u) => prev.then(() => loadScript(u)), Promise.resolve());
}

export default function NativeModuleHost({ group, name, shellConfig }) {
  const [mc, setMc] = useState(null);
  const [failed, setFailed] = useState(false);
  const [booting, setBooting] = useState(true);
  const [activeTab, setActiveTab] = useState(null);
  const [licenseBlocked, setLicenseBlocked] = useState(false);
  const [retryTick, setRetryTick] = useState(0);
  const started = useRef({});
  const { token } = theme.useToken();
  const isDark = token.colorBgContainer === MUI_DARK.paper;

  // Legacy adapter tabs render in their own React root (wrapped in shellThemeWrap,
  // which captures the colour mode at render time), so they don't follow a live
  // theme toggle. Re-render them when the mode flips.
  const skipFirstThemeRun = useRef(true);
  useEffect(() => {
    if (skipFirstThemeRun.current) { skipFirstThemeRun.current = false; return; }
    if (!mc || !mc.config) return;
    // Ensure the global shellThemeWrap reads matches this render's mode — the
    // index.js updater runs in a parent effect (after this child effect).
    window.__shellColorMode = isDark ? 'dark' : 'light';
    const list = window.modJsList || {};
    (mc.config.tabs || []).forEach((t) => {
      // Adapter-drawn component tabs (NativeAdapterView, e.g. settings) render
      // their own root too — re-render those on a flip as well.
      const adapterDrawn = t.component === 'NativeAdapterView';
      if (t.component && !adapterDrawn) return;
      const m = list[t.key];
      if (!m || typeof m.initTable !== 'function') return;
      const mounted = adapterDrawn
        ? !!(m.containerOverrides && m.containerOverrides.Table)
        : started.current[t.key];
      if (mounted) {
        try { m.tableInitialized = false; m.initTable(); } catch (e) { /* ignore */ }
      }
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [isDark]);

  // 1) fetch the module context
  useEffect(() => {
    let alive = true;
    setMc(null); setFailed(false); setBooting(true); setLicenseBlocked(false); started.current = {};
    // try/catch because instrumented fetch (e.g. an injected monitoring agent's
    // wrapper) can throw synchronously — without it that exception escapes the
    // effect and the pane goes blank instead of showing the failure state.
    try {
      fetch(`${shellConfig.restApiBase}appshell/module-context?group=${encodeURIComponent(group)}&name=${encodeURIComponent(name)}`, {
        headers: { Authorization: `Bearer ${shellConfig.token}` },
        credentials: 'same-origin',
      })
        .then((r) => r.json())
        .then((d) => { if (alive) { if (d && d.config) setMc(d); else setFailed(true); } })
        .catch(() => { if (alive) setFailed(true); });
    } catch (e) {
      if (alive) setFailed(true);
    }
    return () => { alive = false; };
  }, [group, name, shellConfig, retryTick]);

  const startTab = (key) => {
    // Component tabs (e.g. the org chart) are pure React — no legacy adapter to drive.
    const cfg = (mc && mc.config && mc.config.tabs ? mc.config.tabs : []).find((t) => t.key === key);
    if (cfg && cfg.component) return;
    const list = window.modJsList || {};
    const m = list[key];
    if (!m) return;
    window.modJs = m;
    if (started.current[key]) return;
    started.current[key] = true;
    try {
      m.get([]);
      if (!m.isV2) {
        if (m.initialFilter != null) m.initFieldMasterData(null, m.setFilterExternal);
        else m.initFieldMasterData();
      }
    } catch (e) { /* ignore */ }
  };

  // 2) once context is in and the tab divs are rendered: load scripts, init, wire
  useEffect(() => {
    if (!mc || !mc.config) return undefined;
    let destroyed = false;
    const ctx = mc.context || {};
    window.CLIENT_BASE_URL = ctx.clientUrl;
    window.BASE_URL = ctx.webBaseUrl;
    window.baseUrl = ctx.baseUrl;

    const urls = (mc.config.scripts || [])
      .map((s) => (/^https?:\/\//.test(s) || s.startsWith('//') ? s : (ctx.webBaseUrl || '') + s));
    loadScriptsSequential(urls).then(() => {
      if (destroyed) return;
      const initFn = window[mc.config.initFn];
      if (typeof initFn !== 'function') { setFailed(true); return; }

      const isProModule = !!(window.iceProModules && window.iceProModules[mc.config.initFn]);
      const lic = mc.license;
      if (isProModule && lic && (!lic.has_license || lic.is_expired)) {
        setLicenseBlocked(true);
        setBooting(false);
        return;
      }

      initFn(mc.data || {});

      const list = window.modJsList || {};
      Object.keys(list).forEach((k) => {
        const m = list[k];
        try {
          // Tag each adapter with the module it belongs to, so its data.php
          // requests declare scope explicitly (fixes the shared-session
          // modulePath data-scope bug — see docs/DATA_SCOPE_ISSUE.md).
          m.spaModuleGroup = group;
          m.spaModuleName = name;
          m.setTranslations(ctx.translations || {});
          m.setFieldTemplates(ctx.fieldTemplates || {});
          m.setTemplates(ctx.templates || {});
          m.setCustomTemplates(ctx.customTemplates || {});
          m.setUser(ctx.user || {});
          m.initSourceMappings();
          m.setBaseUrl(ctx.baseUrl);
          m.setClientUrl(ctx.clientUrl);
          m.setCurrentProfile(null);
          m.setInstanceId(ctx.instanceId || '');
          m.setApiUrl(ctx.restApiBase);
          m.setupApiClient(shellConfig.token);
        } catch (e) { /* ignore */ }
      });

      setBooting(false);
      // Default to the first tab, unless a caller requested a specific start tab
      // (e.g. a dashboard tile deep-linking into a module's tab). The hint is
      // consumed once and only if it matches a tab this module actually has.
      let first = mc.config.tabs[0].key;
      try {
        const desired = window.__iceShellStartTab;
        if (desired && mc.config.tabs.some((t) => t.key === desired)) {
          first = desired;
        }
        window.__iceShellStartTab = null;
      } catch (e) { /* ignore */ }
      setActiveTab(first);
      startTab(first);

      // Seam for views INSIDE a module (e.g. the employee profile's
      // Qualifications edit buttons) to switch this host's active tab —
      // the legacy global switchTab() clicks a legacy tab anchor that does
      // not exist in the SPA. Optionally applies a filter to the target
      // tab's adapter (e.g. {employee: id}) before it loads/reloads.
      window.iceShellSwitchModuleTab = (key, filter) => {
        const list = window.modJsList || {};
        const m = list[key];
        if (m && filter && typeof m.setFilter === 'function') {
          m.setFilter(filter);
          m.filtersAlreadySet = true;
        }
        setActiveTab(key);
        startTab(key);
        // Tab already mounted (e.g. a wired card list): refresh so the new
        // filter takes effect and the filter tag shows.
        try {
          if (m && m.tableContainer && m.tableContainer.current) {
            if (filter) m.tableContainer.current.setFilterData(filter);
            m.tableContainer.current.reload();
          }
        } catch (e) { /* ignore */ }
        try { window.scrollTo({ top: 0 }); } catch (e) { /* ignore */ }
      };
    }).catch(() => { if (!destroyed) setFailed(true); });

    return () => {
      destroyed = true;
      try {
        mc.config.tabs.forEach((t) => (t.ids || []).forEach((id) => {
          const el = document.getElementById(id);
          if (el) { try { ReactDOM.unmountComponentAtNode(el); } catch (e) { /* */ } }
        }));
      } catch (e) { /* */ }
      delete window.iceShellSwitchModuleTab;
      window.modJs = undefined;
      window.modJsList = undefined;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [mc]);

  if (failed) {
    return (
      <div style={{ padding: 32, textAlign: 'center' }}>
        <Empty description="Could not load this module">
          <Button type="primary" onClick={() => setRetryTick((t) => t + 1)}>Retry</Button>
        </Empty>
      </div>
    );
  }
  if (!mc) {
    return <div style={{ display: 'flex', justifyContent: 'center', padding: 80 }}><Spin size="large" /></div>;
  }
  if (licenseBlocked) {
    return <LicenseBlocked license={mc.license} />;
  }

  // A "group" (e.g. leaves' "For Approval" tabs about OTHER employees) is set
  // off from the preceding tabs by a divider + caption on its first tab, and
  // each grouped tab can carry a pending-count bubble.
  const reviewTabs = mc.config.tabs.filter((t) => t.group === 'review');
  const firstReviewKey = reviewTabs.length ? reviewTabs[0].key : null;
  const groupLabel = reviewTabs.length ? (reviewTabs[0].groupLabel || '') : '';

  const renderTabLabel = (t) => {
    const badge = (typeof t.count === 'number' && t.count > 0)
      ? <Badge count={t.count} size="small" overflowCount={99} style={{ marginLeft: 6 }} />
      : null;
    const inner = <span>{t.label}{badge}</span>;
    if (t.key && t.key === firstReviewKey) {
      return (
        <span style={{ display: 'inline-flex', alignItems: 'center' }}>
          <span style={{
            display: 'inline-block', width: 1, height: 18,
            background: token.colorSplit, marginRight: 14,
          }} />
          {groupLabel && (
            <span style={{
              fontSize: 11, textTransform: 'uppercase', letterSpacing: 0.6,
              color: token.colorTextTertiary, marginRight: 10, fontWeight: 600,
            }}>{groupLabel}</span>
          )}
          {inner}
        </span>
      );
    }
    return inner;
  };

  const items = mc.config.tabs.map((t) => {
    if (t.component) {
      const Comp = TAB_COMPONENTS[t.component];
      // Only mount the native component AFTER init has run (booting === false),
      // so window.modJs + its apiClient are in place. On SPA re-entry the bundle
      // is already cached, so without this gate a bespoke view would mount and
      // fetch before initFn re-ran (window.modJs was nulled on the prior unmount),
      // showing an empty state. The booting overlay covers this brief gap.
      return {
        key: t.key,
        label: renderTabLabel(t),
        children: (Comp && !booting)
          ? (
            <Comp
              shellConfig={shellConfig}
              tabKey={t.key}
              entity={t.entity}
              cardConfig={t.card}
              viewGlobal={t.viewGlobal}
              viewProps={t.props}
            />
          )
          : null,
      };
    }
    // Legacy adapter tabs render via their own React root; the adapter wraps that
    // render in shellThemeWrap so it follows the shell's colour mode. Use the
    // themed container surface so the legacy view sits on the right background.
    return {
      key: t.key,
      label: renderTabLabel(t),
      forceRender: true,
      children: (
        <div style={{
          background: token.colorBgContainer,
          color: token.colorText,
          borderRadius: 10,
          padding: 12,
          minHeight: 320,
          ...(t.scroll ? { overflowX: 'auto' } : {}),
        }}>
          {(t.ids || []).map((id) => <div id={id} key={id} className="reviewBlock" />)}
        </div>
      ),
    };
  });

  const singleTab = items.length <= 1;

  return (
    <div style={{ padding: 24, position: 'relative' }}>
      {booting && (
        <div style={{
          position: 'absolute', inset: 0, display: 'flex', alignItems: 'center',
          justifyContent: 'center', zIndex: 2, background: token.colorBgLayout, opacity: 0.6,
        }}>
          <Spin size="large" />
        </div>
      )}
      <div style={{
        background: token.colorBgContainer,
        borderRadius: 14,
        boxShadow: MUI_SHADOW,
        padding: singleTab ? 20 : '4px 20px 20px',
      }}>
        <Tabs
          activeKey={activeTab || (items[0] && items[0].key)}
          onChange={(k) => { setActiveTab(k); startTab(k); }}
          items={items}
          tabBarStyle={singleTab ? { display: 'none' } : { marginBottom: 16 }}
        />
      </div>
    </div>
  );
}
