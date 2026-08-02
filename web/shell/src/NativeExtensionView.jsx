import React from 'react';
import { Empty } from 'antd';

/**
 * Mounts a bespoke extension's own React view component natively (no iframe).
 *
 * Many extensions (team, esign, directory, …) are already full React UIs whose
 * `module.js` does `ReactDOM.render(<TheView/>, #content)` — a SEPARATE React
 * root that wouldn't inherit the shell theme. Instead, the extension exposes its
 * view component on `window` (e.g. `window.TeamsAdminView`) and declares
 * `viewGlobal` in its meta.json native tab; we render it INSIDE the shell tree so
 * it inherits the active (light/dark) ConfigProvider for free.
 *
 * The extension's init() (run by NativeModuleHost before tabs render) has already
 * created window.modJs + its controller and wired the API client, so the view's
 * data fetching works exactly as in the legacy page.
 */
export default function NativeExtensionView({ viewGlobal, viewProps }) {
  const Comp = viewGlobal && typeof window !== 'undefined' ? window[viewGlobal] : null;
  if (!Comp) {
    return <div style={{ padding: 32 }}><Empty description="View not available" /></div>;
  }
  // Pass the props an extension's module.js typically passes its view. Defaults
  // cover the common ones; `viewProps` (from the meta tab) maps any extra prop
  // name -> window global, e.g. { adapter: 'modJs', controller: 'learnExtensionController' }.
  const modJs = (typeof window !== 'undefined') ? window.modJs : null;
  const props = { apiClient: modJs && modJs.apiClient, ice: modJs, modJs };
  if (viewProps && typeof viewProps === 'object') {
    Object.keys(viewProps).forEach((k) => {
      const g = viewProps[k];
      props[k] = (g === 'modJs') ? modJs : (typeof window !== 'undefined' ? window[g] : undefined);
    });
  }
  return <Comp {...props} />;
}
