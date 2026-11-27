import React, {
  useEffect, useMemo, useRef, useState,
} from 'react';
import {
  Layout, Menu, Drawer, Grid, Avatar, Dropdown, Button, theme, Spin, Alert, Tooltip, ConfigProvider, Badge,
} from 'antd';
import {
  MenuOutlined, UserOutlined, LogoutOutlined, AppstoreOutlined,
  SettingOutlined, TeamOutlined, BarChartOutlined, ControlOutlined,
  FundOutlined, DollarOutlined, SolutionOutlined, ShopOutlined,
  CalendarOutlined, ClockCircleOutlined, CheckSquareOutlined,
  FileTextOutlined, ReadOutlined, TrophyOutlined, CarOutlined, HomeOutlined,
  BulbOutlined, BulbFilled, SafetyCertificateOutlined, DownOutlined,
  BuildOutlined,
} from '@ant-design/icons';
import { SIDEBAR_BG, buildTheme } from './theme';
import Notifications from './Notifications';
import News from './News';
import Dashboard from './Dashboard';
import EmployeeDashboard from './EmployeeDashboard';
import LicenseRenewalBanner from './LicenseRenewalBanner';
import UpdateAvailableBanner from './UpdateAvailableBanner';
import NativeModuleHost from './NativeModuleHost';
import NativeDocumentModal from './NativeDocumentModal';
import ModuleSearch from './ModuleSearch';
import { resolveLegacyRoute, resolveLegacyTab } from './legacyLinks';

const { Header, Sider, Content } = Layout;
const { useBreakpoint } = Grid;

// Map menu-group names to antd SVG icons (always render, unlike the FA webfont
// which is not fully deployed). Falls back to a generic icon.
const GROUP_ICONS = {
  Admin: <SettingOutlined />,
  Employees: <TeamOutlined />,
  Manage: <AppstoreOutlined />,
  Reports: <BarChartOutlined />,
  'My Reports': <BarChartOutlined />,
  System: <ControlOutlined />,
  Insights: <FundOutlined />,
  Payroll: <DollarOutlined />,
  Finance: <DollarOutlined />,
  Recruitment: <SolutionOutlined />,
  Marketplace: <ShopOutlined />,
  'About You': <UserOutlined />,
  Collaboration: <TeamOutlined />,
  Leave: <CalendarOutlined />,
  Time: <ClockCircleOutlined />,
  'My Tasks': <CheckSquareOutlined />,
  Documents: <FileTextOutlined />,
  Training: <ReadOutlined />,
  Performance: <TrophyOutlined />,
  Travel: <CarOutlined />,
};

// Area `icon` name (from the server area registry) -> antd icon. Falls back to a
// generic icon for extension-defined areas.
const AREA_ICONS = {
  home: <HomeOutlined />,
  people: <TeamOutlined />,
  time: <ClockCircleOutlined />,
  leave: <CalendarOutlined />,
  pay: <DollarOutlined />,
  recruitment: <SolutionOutlined />,
  learning: <ReadOutlined />,
  performance: <TrophyOutlined />,
  documents: <FileTextOutlined />,
  reports: <BarChartOutlined />,
  configuration: <BuildOutlined />,
  system: <ControlOutlined />,
  more: <AppstoreOutlined />,
};
const areaIcon = (name) => AREA_ICONS[name] || <AppstoreOutlined />;

// A section header label inside the area menu (e.g. "MANAGE" / "PERSONAL"). Styled
// to read clearly as a heading, NOT a clickable menu item: small uppercase,
// letter-spaced, muted, with a leading icon and a hairline rule above.
function sectionHeader(icon, text) {
  return (
    <span style={{
      display: 'flex', alignItems: 'center', gap: 7,
      paddingTop: 12, marginTop: 2,
      borderTop: '1px solid rgba(255,255,255,0.08)',
      fontSize: 10.5, fontWeight: 700, letterSpacing: 1.3, textTransform: 'uppercase',
      color: 'rgba(255,255,255,0.42)', cursor: 'default', userSelect: 'none',
    }}>
      {React.cloneElement(icon, { style: { fontSize: 12, opacity: 0.85 } })}
      <span>{text}</span>
    </span>
  );
}

// CSS injected into each module iframe (same-origin) to hide the legacy chrome
// (top bar + sidebar) so only the module content shows inside the new shell.
// No legacy files are modified — this is applied from the parent shell on load.
const EMBED_CSS = `
  header.header { display: none !important; }
  aside.left-side, .sidebar-offcanvas, .skeletonSideMenu { display: none !important; }
  .right-side { margin-left: 0 !important; left: 0 !important; }
  .wrapper, body, html { padding-top: 0 !important; margin-top: 0 !important; background: #f0f2f5 !important; }
  body { min-width: 0 !important; }
  #DemoModeNotice, #IceHrmConnectionNotice { display: none !important; }
`;

const KEY_SEP = '::';
const HOME_KEY = `modules${KEY_SEP}home`;

// Build antd Menu `items` from a view's group list. Each item already carries its
// resolved { g, n } route from the server (MenuService.getViewMenus).
function buildItems(groups) {
  if (!Array.isArray(groups)) return [];
  return groups.map((group) => ({
    key: `grp:${group.name}`,
    label: group.name,
    icon: GROUP_ICONS[group.name] || <AppstoreOutlined />,
    children: (group.items || []).map((it) => ({
      key: `${it.g}${KEY_SEP}${it.n}`,
      label: it.label || it.name,
      data: { g: it.g, n: it.n, label: it.label || it.name },
    })),
  }));
}

function keyFor(g, n) {
  return `${g}${KEY_SEP}${n}`;
}

// Merge several group lists into one, de-duplicating groups by name (their items
// are concatenated, items de-duped by g::n) — used to give non-switch privileged
// users (e.g. Managers) a single combined menu of their admin + employee modules.
function mergeGroups(...lists) {
  const byName = new Map();
  const order = [];
  lists.forEach((list) => (Array.isArray(list) ? list : []).forEach((group) => {
    if (!byName.has(group.name)) {
      byName.set(group.name, { name: group.name, items: [] });
      order.push(group.name);
    }
    const tgt = byName.get(group.name);
    const seen = new Set(tgt.items.map((it) => keyFor(it.g, it.n)));
    (group.items || []).forEach((it) => {
      const k = keyFor(it.g, it.n);
      if (!seen.has(k)) { seen.add(k); tgt.items.push(it); }
    });
  }));
  return order.map((n) => byName.get(n));
}

/**
 * Links in notification emails and system to-dos still use the legacy form
 * "ui/?g=modules&n=overtime#tabSubordinateEmployeeOvertime" (app/index.php forwards
 * the old "app/?g=…" links here, and the login page keeps the tab). Turn that into
 * the shell route "#modules::overtime" and the start tab, before the first render.
 */
function consumeLegacyRoute() {
  try {
    const sp = new URLSearchParams(window.location.search);
    const g = sp.get('g');
    const n = sp.get('n');
    if (!g || !n) return;
    const frag = (window.location.hash || '').replace(/^#/, '');
    let tab = /^[A-Za-z][A-Za-z0-9_]*$/.test(frag) ? frag : null;
    if (!tab) tab = window.sessionStorage.getItem('ice-legacy-tab');
    window.sessionStorage.removeItem('ice-legacy-tab');
    const route = resolveLegacyRoute(g, n, tab);
    if (route.tab) window.__iceShellStartTab = route.tab;
    window.history.replaceState(null, '', `${window.location.pathname}#${encodeURIComponent(`${route.g}::${route.n}`)}`);
  } catch (e) {
    // Leave the URL as it is: the shell then opens Home.
  }
}

if (typeof window !== 'undefined') consumeLegacyRoute();

export default function AppShell({
  bootstrap, config, colorMode = 'light', onToggleColorMode,
}) {
  const isDark = colorMode === 'dark';
  const screens = useBreakpoint();
  const isMobile = !screens.md; // < 768px
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [current, setCurrent] = useState(null); // { g, n, label } | null — drives the iframe src
  // When a module navigates the iframe internally to ANOTHER module, this tracks
  // what the iframe is actually showing (for title/selection/hash) without
  // changing the iframe src (which would reload it).
  const [override, setOverride] = useState(null);
  const [iframeLoading, setIframeLoading] = useState(false);
  const iframeRef = useRef(null);
  const { token } = theme.useToken();

  const company = bootstrap.company || {};
  const user = bootstrap.user || {};
  const profile = bootstrap.profile || {};
  const displayName = profile.firstName || user.first_name || user.email;
  const views = bootstrap.views || { admin: [], employee: [] };

  // "View as employee" (profile switch) — initiated from inside a module. The
  // switch state lives in the legacy (cookie) session, which the stateless REST
  // API can't see, so the shell derives the banner directly from the loaded
  // module page (the .switched-name marker) on every iframe load.
  // Seed from bootstrap (set when an admin has switched into an employee profile
  // and is viewing a natively-mounted module — no iframe marker to derive from).
  const [switchedProfile, setSwitchedProfile] = useState(bootstrap.switchedProfile || null);
  const [reloadNonce, setReloadNonce] = useState(0);
  // A document (e.g. an editor task list) opened from a notification — mounted in
  // a shell-level modal rather than navigated to (the editor isn't a route).
  const [doc, setDoc] = useState(null);
  const setDocUrl = (url) => setDoc(url ? { url, title: 'Task List' } : null);

  // Seam for bespoke extension views (NativeExtensionView trees, e.g. the learn
  // user "Open Course" button): a legacy document_link would navigate the SPA away
  // (and bounce to the dashboard), so views call this global to open the document
  // in the shell's native editor modal instead. Cleared on unmount so a stale
  // handler never outlives the shell.
  useEffect(() => {
    window.iceShellOpenDocument = (url, title) => setDoc(url ? { url, title: title || 'Document' } : null);
    return () => { delete window.iceShellOpenDocument; };
  }, []);

  const reloadIframe = () => {
    setIframeLoading(true);
    setReloadNonce((n) => n + 1);
  };

  // AdapterBase.setAdminProfile posts this (instead of breaking the top window
  // out to the legacy app) after a switch/switch-back. Reload the current module;
  // the banner is then re-derived from the reloaded page in onIframeLoad.
  useEffect(() => {
    const onMsg = (e) => {
      if (!e.data || e.data.iceShell !== 'profile-switched') return;
      reloadIframe();
    };
    window.addEventListener('message', onMsg);
    return () => window.removeEventListener('message', onMsg);
  }, []);

  // Switch back. Inside an iframe, delegate to the module's (session-correct)
  // handler. For a natively-mounted module there is no iframe, so post the
  // switch-back to the legacy session endpoint ourselves and reload the shell.
  const switchBack = () => {
    try {
      const win = iframeRef.current && iframeRef.current.contentWindow;
      if (win && win.modJs && typeof win.modJs.setAdminProfile === 'function') {
        win.modJs.setAdminProfile('-1');
        return;
      }
    } catch (e) { /* fall through to native */ }
    const body = new URLSearchParams({ a: 'setAdminEmp', empid: '-1' });
    fetch(`${config.clientBaseUrl}service.php`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: body.toString(),
    }).then(() => { window.location.reload(); })
      .catch(() => { window.location.reload(); });
  };

  // There is no Admin/Employee switch any more — the menu is sliced by high-level
  // AREA (Home/People/Time and Work/…). Within an area, items are split into two
  // sections: "Manage" (admin/management modules, from the admin view) and "Mine"
  // (the user's own self-service modules, from the employee view).
  const areaRegistry = bootstrap.areas || [];

  // Build, per area, the Manage + Mine item lists (de-duped by route across both
  // views), plus a flat route -> data map and route -> area map. We must NOT dedupe
  // by label: an admin module and a self-service module can share a label but be
  // different destinations (e.g. "Expenses" = manage all vs "My Expenses" = apply
  // as an employee), disambiguated by the "My …" rename.
  const { itemMap, areaOfKey, itemsByArea } = useMemo(() => {
    const map = {};
    const aok = {};
    const byArea = {}; // area -> { manage:[], mine:[], keys:Set }
    const add = (groups, section) => (Array.isArray(groups) ? groups : []).forEach(
      (grp) => (grp.items || []).forEach((it) => {
        const area = it.area || 'more';
        const key = keyFor(it.g, it.n);
        const label = it.label || it.name;
        if (!byArea[area]) byArea[area] = { manage: [], mine: [], keys: new Set() };
        const b = byArea[area];
        if (b.keys.has(key)) return; // same route in both views: keep the first (Manage)
        b.keys.add(key);
        // areaOrder lets meta.json position an item within its section; unset
        // items default to 50 and keep their natural order via a stable sort.
        const order = (it.areaOrder === 0 || it.areaOrder) ? it.areaOrder : 50;
        b[section].push({ key, label, order });
        if (!map[key]) { map[key] = { g: it.g, n: it.n, label, area, section }; aok[key] = area; }
      }),
    );
    add(views.admin, 'manage');
    add(views.employee, 'mine');
    // Stable sort each section by areaOrder (ties keep insertion/menu order).
    Object.keys(byArea).forEach((a) => {
      const cmp = (x, y) => x.order - y.order;
      byArea[a].manage.sort(cmp);
      byArea[a].mine.sort(cmp);
    });
    return { itemMap: map, areaOfKey: aok, itemsByArea: byArea };
  }, [views]);

  const areaCount = (id) => {
    const b = itemsByArea[id];
    return b ? b.manage.length + b.mine.length : 0;
  };
  const firstKeyOf = (id) => {
    const b = itemsByArea[id];
    if (!b) return null;
    const it = b.manage[0] || b.mine[0];
    return it ? it.key : null;
  };

  // The module to land on when there is no hash: the user's home module, else a
  // dashboard. Must exist in the menu.
  const defaultKey = () => {
    const hl = bootstrap.homeLink;
    const cands = [];
    if (hl && hl.group && hl.name) cands.push(keyFor(hl.group, hl.name));
    cands.push('admin::dashboard', 'modules::dashboard');
    return cands.find((k) => itemMap[k]) || null;
  };

  // Areas that actually have ≥1 accessible item, in registry order.
  const availableAreas = useMemo(
    () => areaRegistry.filter((a) => areaCount(a.id) > 0),
    [areaRegistry, itemsByArea], // eslint-disable-line react-hooks/exhaustive-deps
  );

  const [selectedArea, setSelectedArea] = useState(() => {
    const saved = localStorage.getItem('shell-area');
    if (saved && areaCount(saved) > 0) return saved;
    const dk = defaultKey();
    const a = dk && areaOfKey[dk];
    if (a && areaCount(a) > 0) return a;
    const first = areaRegistry.find((ar) => areaCount(ar.id) > 0);
    return first ? first.id : null;
  });
  useEffect(() => {
    if (selectedArea) localStorage.setItem('shell-area', selectedArea);
  }, [selectedArea]);

  // The left menu = the selected area's items. When both Manage and Mine sections
  // have items, render labelled groups separated by a divider; if only one section
  // exists, render it flat (no redundant header).
  const menuItems = useMemo(() => {
    const b = itemsByArea[selectedArea];
    if (!b) return [];
    // A bubble from bootstrap.menuBadges (route -> count), e.g. setup steps left.
    const badges = bootstrap.menuBadges || {};
    const toItem = (it) => ({
      key: it.key,
      label: badges[it.key] > 0 ? (
        <span style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 8 }}>
          <span style={{ overflow: 'hidden', textOverflow: 'ellipsis' }}>{it.label}</span>
          <Badge count={badges[it.key]} size="small" />
        </span>
      ) : it.label,
    });
    // Home has its own pinned entry above; drop it here so it is not listed twice.
    const notHome = (it) => it.key !== HOME_KEY;
    const manage = b.manage.filter(notHome);
    const mine = b.mine.filter(notHome);
    if (manage.length && mine.length) {
      return [
        {
          type: 'group',
          key: 'grp-manage',
          label: sectionHeader(<ControlOutlined />, 'Manage'),
          children: manage.map(toItem),
        },
        {
          type: 'group',
          key: 'grp-mine',
          label: sectionHeader(<UserOutlined />, 'Personal'),
          children: mine.map(toItem),
        },
      ];
    }
    return [...manage, ...mine].map(toItem);
  }, [itemsByArea, selectedArea]);

  const onAreaChange = (areaId) => {
    setSelectedArea(areaId);
    const first = firstKeyOf(areaId);
    if (first) openModule(first);
  };

  // --- routing: hash <-> selected module -----------------------------------

  const applyHash = () => {
    const raw = (window.location.hash || '').replace(/^#\/?/, '');
    const key = raw ? decodeURIComponent(raw) : defaultKey();
    const data = key && itemMap[key];
    setOverride(null);
    if (data) {
      setCurrent(data);
      setIframeLoading(true);
    } else {
      setCurrent(null);
    }
  };

  // Go to the landing (Home). Clearing the hash re-runs applyHash, which resolves
  // defaultKey() — so the URL stays clean and Home mounts like any other module.
  const goHome = () => {
    if (window.location.hash) {
      window.location.hash = '';
    } else {
      applyHash();
    }
  };

  useEffect(() => {
    applyHash();
    const onHash = () => applyHash();
    window.addEventListener('hashchange', onHash);
    return () => window.removeEventListener('hashchange', onHash);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [itemMap]);

  const openModule = (key) => {
    const data = itemMap[key];
    if (!data) return;
    if (isMobile) setDrawerOpen(false);
    const hash = `#${encodeURIComponent(key)}`;
    if (window.location.hash === hash) {
      // same module: force reload of the iframe
      setIframeLoading(true);
      setCurrent({ ...data });
    } else {
      window.location.hash = hash; // triggers applyHash via hashchange
    }
  };

  // What the iframe is actually showing (may differ from `current` after an
  // in-iframe navigation) — drives the title, sidebar selection and hash.
  const displayed = override || current;
  // Navigate to a module by group/name (used by notifications). Uses the menu
  // route if known, else loads the page directly.
  const navigateTo = (g, n) => {
    if (!g || !n) return;
    const key = keyFor(g, n);
    if (isMobile) setDrawerOpen(false);
    if (itemMap[key]) {
      openModule(key);
    } else {
      setOverride(null);
      setCurrent({ g, n, label: n });
      setIframeLoading(true);
      try { window.history.replaceState(null, '', `#${encodeURIComponent(key)}`); } catch (z) { /* */ }
    }
  };

  // Deep-link / intent contract (docs/HOME_AND_KUDOS_PLAN.md §4.4) for Home widgets,
  // notifications and other modules:
  //   window.iceShellOpenIntent({ group, module, tab, id, action, params })
  // Sets the one-shot start-tab hint the module host already honours, stashes the
  // record/action intent for the mounted module to consume (window.__iceShellIntent),
  // then navigates. `module` may be a name, a "group::name" key, or {group,name};
  // 'home' / no module goes to the landing. Purely additive over the existing hash
  // forms. Re-registered every render so it always closes over the latest navigateTo.
  // (No promises/async here on purpose — this bundle has no regenerator runtime.)
  useEffect(() => {
    window.iceShellOpenIntent = (intent) => {
      const it = intent || {};
      const mod = it.module;
      if (!mod || mod === 'home') {
        goHome();
        return;
      }
      let g = it.group;
      let n = mod;
      if (typeof mod === 'string' && mod.indexOf(KEY_SEP) !== -1) {
        const parts = mod.split(KEY_SEP);
        g = parts[0];
        n = parts[1];
      } else if (mod && typeof mod === 'object') {
        g = mod.group;
        n = mod.name;
      }
      if (!g) g = 'modules';
      // it.tab may be a legacy anchor (from a system to-do link); map it to the SPA key.
      try { window.__iceShellStartTab = resolveLegacyTab(it.tab); } catch (e) { /* ignore */ }
      try {
        window.__iceShellIntent = (it.id || it.action || it.params)
          ? { id: it.id || null, action: it.action || null, params: it.params || null }
          : null;
      } catch (e) { /* ignore */ }
      navigateTo(g, n);
    };
    return () => { delete window.iceShellOpenIntent; };
  });

  const selectedKey = displayed ? keyFor(displayed.g, displayed.n) : null;

  // Keep the area selector in sync with whatever module is actually showing
  // (deep links, in-iframe navigation to another area's module, etc.).
  useEffect(() => {
    const a = selectedKey && areaOfKey[selectedKey];
    if (a && a !== selectedArea) setSelectedArea(a);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [selectedKey, areaOfKey]);

  const onIframeLoad = (e) => {
    try {
      const doc = e.target.contentDocument;
      if (doc && doc.head) {
        const style = doc.createElement('style');
        style.setAttribute('data-shell-embed', '1');
        style.textContent = EMBED_CSS;
        doc.head.appendChild(style);
      }
      // Derive the "viewing as employee" banner from the page's own switched
      // marker, so it stays correct on load, after a switch, and after switch-back.
      const nameEl = doc && doc.querySelector('.switched-name');
      setSwitchedProfile(nameEl ? { name: (nameEl.textContent || '').trim() } : null);

      // If the module navigated the iframe to ANOTHER module (e.g. a dashboard
      // widget link), sync the title/selection/hash to it WITHOUT reloading.
      const loc = e.target.contentWindow && e.target.contentWindow.location;
      const sp = loc && new URLSearchParams(loc.search);
      const g = sp && sp.get('g');
      const n = sp && sp.get('n');
      if (g && n) {
        const k = keyFor(g, n);
        if (current && k === keyFor(current.g, current.n)) {
          setOverride(null);
        } else if (itemMap[k]) {
          setOverride(itemMap[k]);
          try { window.history.replaceState(null, '', `#${encodeURIComponent(k)}`); } catch (z) { /* */ }
        }
      }
    } catch (err) {
      // cross-origin (shouldn't happen — same origin) — ignore
    }
    setIframeLoading(false);
  };

  // The admin dashboard is rendered natively (React + charts), not via the iframe.
  const isNativeDashboard = current && current.g === 'admin' && current.n === 'dashboard';
  // The employee/manager personal dashboard is also native.
  const isNativeEmployeeDashboard = current && current.g === 'modules' && current.n === 'dashboard';
  // Modules registered for native in-shell mounting (no iframe).
  const nativeKey = current ? `${current.g}/${current.n}` : null;
  const isNativeModule = !!nativeKey && !isNativeDashboard && !isNativeEmployeeDashboard
    && (bootstrap.nativeModules || []).indexOf(nativeKey) !== -1;
  const iframeSrc = current && !isNativeDashboard && !isNativeEmployeeDashboard && !isNativeModule
    ? `${config.clientBaseUrl}?g=${encodeURIComponent(current.g)}&n=${encodeURIComponent(current.n)}&_embed=1`
    : null;

  // --- chrome --------------------------------------------------------------
  // The pinned Home entry, directly under the area switcher and present in every
  // area. Rendered as a one-item Menu so it inherits the exact styling and
  // selected state of the module menu below it. Hidden when this user cannot
  // reach Home at all (e.g. a restricted user without a role on it).
  const homeNav = itemMap[HOME_KEY] ? (
    <Menu
      mode="inline"
      theme="dark"
      selectedKeys={selectedKey === HOME_KEY ? [HOME_KEY] : []}
      style={{ borderInlineEnd: 0 }}
      items={[{ key: HOME_KEY, icon: <HomeOutlined />, label: 'Home' }]}
      onClick={() => { if (isMobile) setDrawerOpen(false); goHome(); }}
    />
  ) : null;

  const sideMenu = (
    <Menu
      mode="inline"
      theme="dark"
      selectedKeys={selectedKey ? [selectedKey] : []}
      style={{ borderInlineEnd: 0 }}
      items={menuItems}
      onClick={({ key }) => openModule(key)}
    />
  );

  const userMenu = {
    items: [
      { key: 'name', label: `${profile.firstName || user.first_name || ''} ${profile.lastName || user.last_name || ''}`.trim() || user.email, disabled: true },
      { type: 'divider' },
      { key: 'home', icon: <HomeOutlined />, label: 'Home' },
      { key: 'logout', icon: <LogoutOutlined />, label: 'Logout' },
    ],
    onClick: ({ key }) => {
      if (key === 'logout') window.location.href = `${config.clientBaseUrl}logout.php`;
      if (key === 'home') { goHome(); }
    },
  };

  const [logoFailed, setLogoFailed] = useState(false);
  // Prefer a company-uploaded logo (white-label); otherwise the compact IceHrm
  // mark. Left-aligned to line up with the menu items.
  const defaultLogo = `${config.baseUrl || ''}images/logo-sq.png`;
  const logoSrc = company.logoUrl || defaultLogo;
  // Pinned brand footer at the bottom of the sidebar: just version + copyright
  // (the logo now lives in the top bar).
  const logo = (
    <div style={{
      flex: '0 0 auto', display: 'flex', alignItems: 'center', justifyContent: 'space-between',
      gap: 8, padding: '10px 16px', overflow: 'hidden', borderTop: '1px solid rgba(255,255,255,0.08)',
      lineHeight: 1.35,
    }}>
      <span style={{ color: 'rgba(255,255,255,0.4)', fontSize: 10.5, whiteSpace: 'nowrap' }}>
        {`© ${new Date().getFullYear()} IceHrm.com`}
      </span>
      {bootstrap.version && (
        <span style={{ color: 'rgba(255,255,255,0.72)', fontSize: 11, fontWeight: 600, whiteSpace: 'nowrap' }}>
          {`v${bootstrap.version}`}
        </span>
      )}
    </div>
  );

  // Every reachable module, for the top-bar global search (route map -> list).
  const allModules = useMemo(
    () => Object.keys(itemMap).map((key) => ({ key, ...itemMap[key] })),
    [itemMap],
  );

  // Area selector — replaces the old Admin/Employee switch. Picking an area
  // filters the left menu to that functional domain. Styled as a dropdown (icon
  // tile + name + chevron) so it clearly reads as switchable.
  const currentArea = availableAreas.find((a) => a.id === selectedArea) || availableAreas[0] || null;
  const areaSwitch = (availableAreas.length > 1 && currentArea) ? (
    <div style={{ padding: '12px 16px 8px' }}>
      <div style={{
        fontSize: 10.5, letterSpacing: 1.2, fontWeight: 700,
        color: 'rgba(255,255,255,0.38)', marginBottom: 8, paddingLeft: 2,
      }}>
        AREA
      </div>
      {/* The sidebar is always dark, but the Dropdown's popup renders in a body
          portal with the ambient (light) theme — force dark so it matches the
          sidebar, same as the header dropdowns. */}
      <ConfigProvider theme={buildTheme('dark')}>
      <Dropdown
        trigger={['click']}
        menu={{
          selectedKeys: [selectedArea],
          items: availableAreas.map((a) => ({ key: a.id, icon: areaIcon(a.icon), label: a.label })),
          onClick: ({ key }) => onAreaChange(key),
        }}
      >
        <button
          type="button"
          className="ice-role-switch"
          title="Switch area"
          style={{
            width: '100%', display: 'flex', alignItems: 'center', gap: 10,
            padding: '8px 10px', borderRadius: 12, cursor: 'pointer', color: '#fff',
            background: 'rgba(255,255,255,0.06)', border: '1px solid rgba(255,255,255,0.10)',
            textAlign: 'left',
          }}
        >
          <span style={{
            width: 30, height: 30, borderRadius: 9, flex: '0 0 auto', fontSize: 15,
            background: '#4c9aff26', color: '#4c9aff',
            display: 'flex', alignItems: 'center', justifyContent: 'center',
          }}>
            {areaIcon(currentArea.icon)}
          </span>
          <span style={{ flex: 1, minWidth: 0 }}>
            <span style={{
              display: 'block', fontSize: 13.5, fontWeight: 600, lineHeight: 1.2,
              overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
            }}>
              {currentArea.label}
            </span>
            <span style={{ display: 'block', fontSize: 11, color: 'rgba(255,255,255,0.45)', lineHeight: 1.2 }}>
              Switch area
            </span>
          </span>
          <DownOutlined style={{ fontSize: 12, color: 'rgba(255,255,255,0.5)', flex: '0 0 auto' }} />
        </button>
      </Dropdown>
      </ConfigProvider>
    </div>
  ) : null;

  // --- top bar (GA-style): brand · area switch · module search ----------------
  const headerBrand = (
    <div
      role="button"
      tabIndex={0}
      onClick={() => goHome()}
      title="Home"
      style={{
        display: 'flex', alignItems: 'center', gap: 10, cursor: 'pointer',
        paddingRight: 18, marginRight: 4, borderRight: '1px solid rgba(255,255,255,0.14)',
        height: 48,
      }}
    >
      {!logoFailed ? (
        <img
          src={logoSrc}
          alt={company.name || 'IceHrm'}
          style={{ height: 46, maxWidth: 200, objectFit: 'contain' }}
          onError={() => setLogoFailed(true)}
        />
      ) : (
        <span style={{ fontWeight: 700, fontSize: 22, color: '#fff', whiteSpace: 'nowrap' }}>
          {company.name || 'IceHrm'}
        </span>
      )}
    </div>
  );

  return (
    <Layout style={{ minHeight: '100vh' }}>
      {/* Top bar spans the FULL width, above the sidebar (GA-style). It is fixed
          dark chrome (like the sidebar) in BOTH colour modes, so its contents are
          wrapped in a forced-dark ConfigProvider. */}
      <ConfigProvider theme={buildTheme('dark')}>
      <Header style={{
        display: 'flex', alignItems: 'center', justifyContent: 'space-between',
        paddingInline: 16, background: SIDEBAR_BG,
        position: 'sticky', top: 0, zIndex: 20,
        boxShadow: '0 1px 4px rgba(0,0,0,0.25)',
      }}>
          <div style={{ display: 'flex', alignItems: 'center', gap: 10, flex: '0 0 auto' }}>
            {isMobile && (
              <Button type="text" icon={<MenuOutlined style={{ color: '#fff' }} />} onClick={() => setDrawerOpen(true)} />
            )}
            {!isMobile && headerBrand}
            <span style={{
              fontSize: 16, fontWeight: 600, color: 'rgba(255,255,255,0.92)',
              maxWidth: 260, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap',
            }}>
              {displayed ? displayed.label : 'Home'}
            </span>
          </div>
          <div style={{ flex: 1, display: 'flex', justifyContent: 'center', minWidth: 0, padding: '0 16px' }}>
            <div style={{ width: '100%', maxWidth: 560 }}>
              <ModuleSearch
                items={allModules}
                areas={areaRegistry}
                onSelect={(key) => navigateTo(itemMap[key].g, itemMap[key].n)}
                dark
              />
            </div>
          </div>
          <div style={{ display: 'flex', alignItems: 'center', gap: 4, flex: '0 0 auto' }}>
            <Tooltip title={isDark ? 'Switch to light mode' : 'Switch to dark mode'}>
              <Button
                type="text"
                aria-label="Toggle colour mode"
                icon={isDark ? <BulbFilled style={{ color: '#fbc02d' }} /> : <BulbOutlined style={{ color: '#fff' }} />}
                onClick={onToggleColorMode}
              />
            </Tooltip>
            <Notifications clientBaseUrl={config.clientBaseUrl} onNavigate={navigateTo} onOpenDocument={setDocUrl} />
            <Dropdown menu={userMenu} trigger={['click']}>
              <Button type="text" style={{ display: 'flex', alignItems: 'center', gap: 8, color: 'rgba(255,255,255,0.92)' }}>
                <Avatar size="small" src={profile.image || undefined} icon={<UserOutlined />} />
                {!isMobile && <span>{displayName}</span>}
              </Button>
            </Dropdown>
          </div>
        </Header>
      </ConfigProvider>

      <Layout>
        {!isMobile && (
          <Sider
            width={240}
            style={{
              height: 'calc(100vh - 64px)', position: 'sticky', top: 64, insetInlineStart: 0,
              background: SIDEBAR_BG,
            }}
          >
            <div style={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
              <div style={{ flex: 1, minHeight: 0, overflow: 'auto', paddingTop: 8 }}>
                {areaSwitch}
                {homeNav}
                {sideMenu}
              </div>
              {logo}
            </div>
          </Sider>
        )}

        {isMobile && (
          <Drawer
            placement="left"
            open={drawerOpen}
            onClose={() => setDrawerOpen(false)}
            width={260}
            styles={{
              body: { padding: 0, background: SIDEBAR_BG },
              header: { display: 'none' },
            }}
          >
            <div style={{ height: '100%', display: 'flex', flexDirection: 'column' }}>
              <div style={{ flex: 1, minHeight: 0, overflow: 'auto', paddingTop: 8 }}>
                {areaSwitch}
                {homeNav}
                {sideMenu}
              </div>
              {logo}
            </div>
          </Drawer>
        )}

      <Layout>
        {switchedProfile && (
          <Alert
            type="warning"
            banner
            showIcon
            message={(
              <span>
                Viewing as <strong>{switchedProfile.name}</strong>
              </span>
            )}
            action={(
              <Button size="small" onClick={switchBack}>Switch back</Button>
            )}
          />
        )}

        {bootstrap.showConnectBanner && (
          <Alert
            type="warning"
            banner
            showIcon
            style={{ padding: '18px 28px', alignItems: 'center' }}
            message={(
              <div style={{ lineHeight: 1.35 }}>
                <div style={{ fontSize: 17, fontWeight: 700, marginBottom: 3 }}>
                  Connect to icehrm.com to unlock the marketplace
                </div>
                <div style={{ fontSize: 14, opacity: 0.9 }}>
                  This installation isn&apos;t connected yet. Connect it to install marketplace
                  {' '}extensions and receive product updates.
                </div>
              </div>
            )}
            action={(
              <Button
                size="large"
                type="primary"
                style={{ fontWeight: 600 }}
                onClick={() => navigateTo('extension', 'marketplace|admin')}
              >
                Connect now
              </Button>
            )}
          />
        )}

        <Content style={{
          background: token.colorBgLayout,
          position: 'relative',
          height: `calc(100vh - 64px${switchedProfile ? ' - 40px' : ''}${bootstrap.showConnectBanner ? ' - 84px' : ''})`,
        }}>
          {isNativeDashboard ? (
            <div style={{ height: '100%', overflow: 'auto' }}>
              <UpdateAvailableBanner updateAvailable={bootstrap.updateAvailable} />
              <LicenseRenewalBanner licenseRenewal={bootstrap.licenseRenewal} />
              <News config={config} />
              <Dashboard config={config} onNavigate={navigateTo} />
            </div>
          ) : isNativeEmployeeDashboard ? (
            <div style={{ height: '100%', overflow: 'auto' }}>
              <UpdateAvailableBanner updateAvailable={bootstrap.updateAvailable} />
              <LicenseRenewalBanner licenseRenewal={bootstrap.licenseRenewal} />
              <EmployeeDashboard config={config} onNavigate={navigateTo} onOpenDocument={setDocUrl} />
            </div>
          ) : isNativeModule ? (
            <div style={{ height: '100%', overflow: 'auto' }}>
              <NativeModuleHost key={nativeKey} group={current.g} name={current.n} shellConfig={config} />
            </div>
          ) : iframeSrc ? (
            <>
              {iframeLoading && (
                <div style={{
                  position: 'absolute', inset: 0, display: 'flex',
                  alignItems: 'center', justifyContent: 'center', zIndex: 2,
                  background: token.colorBgLayout,
                }}>
                  <Spin size="large" />
                </div>
              )}
              <iframe
                ref={iframeRef}
                key={`${iframeSrc}#${reloadNonce}`}
                title={current ? current.label : ''}
                src={iframeSrc}
                onLoad={onIframeLoad}
                style={{
                  width: '100%', height: '100%', border: 0, display: 'block',
                  visibility: iframeLoading ? 'hidden' : 'visible',
                }}
              />
            </>
          ) : (
            <div style={{ padding: 24 }}>
              <div style={{
                background: token.colorBgContainer, borderRadius: 8, padding: 32, maxWidth: 720,
                boxShadow: '0 1px 4px rgba(0,0,0,0.06)', color: token.colorText,
              }}>
                <h2 style={{ marginTop: 0 }}>Welcome to the new IceHrm</h2>
                <p style={{ color: token.colorTextSecondary }}>
                  This is the new React + Ant Design interface. Pick a module from the
                  menu to get started. The sidebar reflects your permissions and
                  collapses into a drawer on mobile.
                </p>
                <p style={{ color: token.colorTextSecondary, fontSize: 13 }}>
                  Signed in as <strong>{user.email}</strong> ({bootstrap.userLevel}).
                </p>
              </div>
            </div>
          )}
        </Content>
      </Layout>
      </Layout>
      <NativeDocumentModal
        documentUrl={doc && doc.url}
        bundle="editor/user/dist/editor.js"
        mountFn="mountEditorDocument"
        deps={['dist/vendorOther.js', 'dist/third-party.js', 'dist/common.js']}
        title={(doc && doc.title) || 'Document'}
        shellConfig={config}
        onClose={() => setDoc(null)}
      />
    </Layout>
  );
}
