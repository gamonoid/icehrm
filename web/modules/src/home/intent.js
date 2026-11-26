/**
 * Home's side of the shell's deep-link / intent contract
 * (docs/HOME_AND_KUDOS_PLAN.md §4.4). Every widget routes through this so a
 * click lands on the exact module / tab / record / action.
 */
export function openIntent(intent) {
  const it = intent || {};
  if (typeof window !== 'undefined' && typeof window.iceShellOpenIntent === 'function') {
    window.iceShellOpenIntent(it);
    return;
  }
  // Outside the shell contract (should not happen in the SPA): plain hash nav.
  if (!it.module || it.module === 'home') {
    window.location.hash = '';
    return;
  }
  const g = it.group || 'modules';
  window.location.hash = `#${encodeURIComponent(`${g}::${it.module}`)}`;
}

/** A legacy absolute/relative link (e.g. a system to-do's link). */
export function openLink(url) {
  if (url) window.location.href = url;
}

/**
 * Pull the module route out of a legacy task link ("…?g=modules&n=attendance").
 * The system to-dos (module TaskCreators) still produce those pre-SPA URLs, and
 * following one directly navigates the browser out of the shell — so they are
 * resolved to a route and opened in-app instead. Same approach as the personal
 * dashboard's to-do list. The link's anchor names the tab to land on (e.g.
 * "#tabSubEmployeeLeaveAll"); the shell maps legacy anchors to its tab keys.
 */
export function routeFromLink(link) {
  try {
    const hashIdx = link.indexOf('#');
    const tab = hashIdx >= 0 ? link.slice(hashIdx + 1) : null;
    const noFrag = hashIdx >= 0 ? link.slice(0, hashIdx) : link;
    const qs = noFrag.indexOf('?') >= 0 ? noFrag.split('?')[1] : noFrag;
    const sp = new URLSearchParams(qs);
    const g = sp.get('g');
    const n = sp.get('n');
    return (g && n) ? { g, n, tab: tab || null } : null;
  } catch (e) {
    return null;
  }
}

/** Whether an intent actually leads somewhere (so a row can be made clickable). */
export function hasDestination(intent) {
  if (!intent) return false;
  return !!(intent.link || intent.module || intent.group);
}

/** Follow an action item's intent: a legacy link, or a shell intent. */
export function followIntent(intent) {
  if (!intent) return;
  if (intent.link) {
    const route = routeFromLink(intent.link);
    if (route) {
      openIntent({ group: route.g, module: route.n, tab: route.tab });
      return;
    }
    // Not a module link (an external or file URL): leave the SPA deliberately.
    openLink(intent.link);
    return;
  }
  openIntent(intent);
}
