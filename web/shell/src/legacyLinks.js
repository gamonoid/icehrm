// Legacy (pre-SPA) links — "…?g=modules&n=leaves#tabSubEmployeeLeaveAll" — still
// come out of the backend in notification actions and system to-dos. These
// helpers resolve one to an in-app route plus the tab its anchor names.

// Legacy tab anchors → native SPA tab keys, where they differ. Anchors that
// already match a SPA tab key pass through.
export const LEGACY_TAB_ALIASES = {
  // Leave module: the SPA rebuild shortened the tab keys.
  tabSubEmployeeLeaveAll: 'tabSubLeaveAll', // "applied for a leave" / pending-leave to-do → Leave Requests
  tabEmployeeLeaveApproval: 'tabLeaveApproval', // "assigned … for approval" → Approval Requests
  tabEmployeeLeaveApproved: 'tabMyLeaveApproved', // "leave approved" → Approved Leave
  tabSubEmployeeLeaveCancel: 'tabSubLeaveCancel', // cancellation request → Cancellation Requests
  // Time sheets: one tab in the SPA.
  tabSubEmployeeTimeSheetAll: 'tabTimeSheets',
  tabEmployeeTimeSheetApproved: 'tabTimeSheets',
  // Renamed approval tabs.
  tabEmployeeTravelRecordApproval: 'tabEmployeeTravelRecordApprove',
  tabEmployeeExpenseApproval: 'tabEmployeeExpenseApprove',
  // An overtime link was sent without the "tab" prefix.
  SubordinateEmployeeOvertime: 'tabSubordinateEmployeeOvertime',
};

// Legacy links whose page moved to another module ("g::n#tab" → [g, n, tab]), so
// links in notification emails already sent still land in the right place.
export const LEGACY_ROUTE_ALIASES = {
  // Managers review their reports' expenses in Expenses (admin) > Employee Expenses.
  'extension::expenses|user#tabSubordinateEmployeeExpense': ['extension', 'expenses|admin', 'tabEmployeeExpense'],
  // Coordinated performance reviews are in Performance (admin).
  'modules::performance#tabCoordinatedPerformanceReview': ['admin', 'performance', 'tabPerformanceReview'],
};

/** { g, n, tab } for a legacy route, following moves and tab renames. */
export function resolveLegacyRoute(g, n, tab) {
  const moved = LEGACY_ROUTE_ALIASES[`${g}::${n}#${tab || ''}`];
  if (moved) return { g: moved[0], n: moved[1], tab: moved[2] };
  return { g, n, tab: resolveLegacyTab(tab) };
}

export function resolveLegacyTab(tab) {
  if (!tab) return null;
  return LEGACY_TAB_ALIASES[tab] || tab;
}

// { g, n, tab, sp } for a legacy module link, or null when it isn't one. `sp` is
// the parsed query for callers that need more than the route.
export function routeFromLegacyLink(link) {
  try {
    const hashIdx = link.indexOf('#');
    const frag = hashIdx >= 0 ? link.slice(hashIdx + 1) : null;
    const noFrag = hashIdx >= 0 ? link.slice(0, hashIdx) : link;
    // May be a full URL or just a query string; parse the part after '?' so the
    // first key isn't swallowed by the scheme/path.
    const qs = noFrag.indexOf('?') >= 0 ? noFrag.split('?')[1] : noFrag;
    const sp = new URLSearchParams(qs);
    const g = sp.get('g');
    const n = sp.get('n');
    return (g && n) ? { ...resolveLegacyRoute(g, n, frag), sp } : null;
  } catch (e) {
    return null;
  }
}

// Navigate to a module, landing on `tab` when it has one. NativeModuleHost
// consumes the start-tab hint at boot; if that module is already showing, it
// won't re-boot, so switch its tab directly.
export function navigateToTab(onNavigate, g, n, tab) {
  if (tab) {
    try {
      window.__iceShellStartTab = tab;
      const cur = decodeURIComponent(window.location.hash.replace(/^#/, ''));
      if (cur === `${g}::${n}` && window.iceShellSwitchModuleTab) {
        window.iceShellSwitchModuleTab(tab);
      }
    } catch (e) { /* ignore */ }
  }
  onNavigate(g, n);
}
