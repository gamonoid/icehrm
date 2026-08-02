// Dark-mode text styling for @antv/g2plot charts rendered inside the SPA shell.
//
// g2plot's default legend/axis/label text is a dark grey that disappears on the
// shell's dark surfaces. The shell publishes the active colour mode on
// window.__shellColorMode ('dark' | 'light'); call chartDark() when building a
// chart config and spread/merge the returned fragments so the text stays legible
// in both modes. Returns empty fragments in light mode (safe to spread).
export default function chartDark() {
  const isDark = typeof window !== 'undefined' && window.__shellColorMode === 'dark';
  const strong = 'rgba(255,255,255,0.85)';
  const muted = 'rgba(255,255,255,0.55)';
  return {
    isDark,
    // Spread into a legend config: `legend: { position: 'bottom', ...dk.legend }`
    legend: isDark ? { itemName: { style: { fill: strong } } } : {},
    // Spread into an axis label `style`: `style: { fontSize: 11, ...dk.axisLabelStyle }`
    axisLabelStyle: isDark ? { fill: muted } : {},
    // Spread into an axis title `style`.
    axisTitleStyle: isDark ? { fill: muted } : {},
    // Use as a data-label / pie-label fill (falls back to the usual grey in light mode).
    labelFill: isDark ? strong : '#595959',
  };
}
