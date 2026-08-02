import { ConnectionAdapter } from './lib';

function init(data) {
  const modJsList = {};
  modJsList.tabConnection = new ConnectionAdapter(data);
  window.modJs = modJsList.tabConnection;
  window.modJsList = modJsList;

}

window.initAdminConnection = init;

// SPA native mount: the system-status view reads the server-computed report
// from the adapter created by init (modJs.data.components).
// eslint-disable-next-line global-require
const ReactLib = require('react');
// eslint-disable-next-line global-require
const ConnectionTabView = (require('./components/ConnectionTab').default
  || require('./components/ConnectionTab'));

window.ConnectionSystemView = function ConnectionSystemView() {
  const components = (window.modJs && window.modJs.data && window.modJs.data.components) || {};
  return ReactLib.createElement(ConnectionTabView, components);
};

// SPA native mount: the Backups tab (create/download encrypted DB backups).
// eslint-disable-next-line global-require
const ConnectionBackupsComponent = (require('./components/ConnectionBackups').default
  || require('./components/ConnectionBackups'));

window.ConnectionBackupsView = function ConnectionBackupsView() {
  return ReactLib.createElement(ConnectionBackupsComponent, {});
};
