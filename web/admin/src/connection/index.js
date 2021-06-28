import { ConnectionAdapter } from './lib';

function init(data) {
  const modJsList = {};
  modJsList.tabConnection = new ConnectionAdapter(data);
  window.modJs = modJsList.tabConnection;
  window.modJsList = modJsList;

}

window.initAdminConnection = init;
