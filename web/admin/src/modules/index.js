import { ModuleAdapter } from './lib';

window.ModuleAdapter = ModuleAdapter;

import IceDataPipe from '../../../api/IceDataPipe';

// Shared init — used by both the legacy page and the SPA shell.
function initModulesAdmin(data) {
  const modJsList = {};
  const permissions = (data && data.permissions) || {};

  modJsList.tabModule = new ModuleAdapter('Module', 'Module');
  modJsList.tabModule.setShowAddNew(false);
  modJsList.tabModule.setObjectTypeName('Module');
  modJsList.tabModule.setDataPipe(new IceDataPipe(modJsList.tabModule));
  modJsList.tabModule.setAccess(permissions.Module || []);

  window.modJs = modJsList.tabModule;
  window.modJsList = modJsList;
}

window.initAdminModules = initModulesAdmin;
