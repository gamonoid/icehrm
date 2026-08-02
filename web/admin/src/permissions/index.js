import { PermissionAdapter } from './lib';

window.PermissionAdapter = PermissionAdapter;

import IceDataPipe from '../../../api/IceDataPipe';

// Shared init — used by both the legacy page and the SPA shell.
function initPermissionsAdmin(data) {
  const modJsList = {};
  const permissions = (data && data.permissions) || {};

  modJsList.tabPermission = new PermissionAdapter('Permission', 'Permission');
  modJsList.tabPermission.setShowAddNew(false);
  modJsList.tabPermission.setObjectTypeName('Permission');
  modJsList.tabPermission.setDataPipe(new IceDataPipe(modJsList.tabPermission));
  modJsList.tabPermission.setAccess(permissions.Permission || ['get', 'element', 'save']);
  modJsList.tabPermission.setShowDelete(false);
  modJsList.tabPermission.setRemoteTable(true);

  window.modJs = modJsList.tabPermission;
  window.modJsList = modJsList;
}

window.initAdminPermissions = initPermissionsAdmin;
