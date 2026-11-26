/**
 * Home widget registry (docs/HOME_AND_KUDOS_PLAN.md §4.2).
 *
 * A widget is { id, order, column, size, requires, component }. Home renders only
 * widgets whose `requires` pass for the current user, so admins and employees share
 * one page and each sees their own view. Modules/extensions can contribute widgets
 * by calling registerWidget() — Home hard-codes nothing about them.
 *
 * `column` places the widget in the feed-style layout:
 *   'main' — the wide centre column (the composer and the feed)
 *   'side' — the narrow right rail (profile, attendance, celebrations, apps)
 * Defaults to 'side', so a contributed widget never disrupts the feed.
 *
 *   requires: {
 *     manager: true,            // Admin/Manager only
 *     level:   ['Admin'],       // one of these user levels
 *     app:     'kudos',         // the user can open this module (present in apps[])
 *     dataKey: 'myTeam',        // appshell/home returned this key non-null
 *   }
 *   size: 'full' | 'half' | 'third'
 */
const widgets = [];

export function registerWidget(widget) {
  if (!widget || !widget.id || !widget.component) return;
  if (widgets.some((w) => w.id === widget.id)) return;
  widgets.push(widget);
}

function passes(req, ctx) {
  if (!req) return true;
  if (req.manager && !ctx.isManager) return false;
  if (req.level && req.level.indexOf(ctx.userLevel) === -1) return false;
  if (req.app && !(ctx.apps || []).some((a) => a.name === req.app)) return false;
  if (req.dataKey && (ctx.data == null || ctx.data[req.dataKey] == null)) return false;
  return true;
}

export function getWidgets(ctx) {
  return widgets
    .filter((w) => passes(w.requires, ctx))
    .sort((a, b) => (a.order || 0) - (b.order || 0));
}

/** Widgets for one column of the feed layout, in order. */
export function getColumnWidgets(ctx, column) {
  return getWidgets(ctx).filter((w) => (w.column || 'side') === column);
}

export const SPAN = { full: 24, half: 12, third: 8 };
