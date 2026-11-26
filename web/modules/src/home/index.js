import { HomeAdapter } from './lib';

// Native (SPA shell) init — the shell calls initModulesHome(data) with the module
// context, then wires window.modJs (api client, urls, translations) like every
// other natively mounted module. The Home tab itself is a bespoke React view
// (NativeExtensionView + viewGlobal 'HomeView'); see NativeModuleRegistry.
function init(data) {
  const modJsList = {};
  modJsList.tabHome = new HomeAdapter(data);
  window.modJs = modJsList.tabHome;
  window.modJsList = modJsList;
}

window.initModulesHome = init;

// eslint-disable-next-line global-require
const ReactLib = require('react');
// eslint-disable-next-line global-require
const HomeViewComponent = (require('./view').default || require('./view'));

window.HomeView = function HomeView(props) {
  return ReactLib.createElement(HomeViewComponent, props || {});
};
