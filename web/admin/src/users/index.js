import {
  UserAdapter,
  UserRoleAdapter,
  UserInvitationAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.UserAdapter = UserAdapter;
window.UserRoleAdapter = UserRoleAdapter;
window.UserInvitationAdapter = UserInvitationAdapter;
window.IceDataPipe = IceDataPipe;

// Hidden helper divs the legacy users page provided via index.php so the
// adapters keep working without that page:
//  - #UserForm carries the CSRF token the User adapter reads
//    ($('#UserForm').data('csrf')) before posting saveUser.
//  - #UserPasswordChangeForm is the mount point showPasswordChangeForm renders
//    its modal into.
// (The adapter form/filter modals themselves mount into the detached containers
// NativeCardList provides via setContainers — these are separate.)
function ensureHelperDiv(id, csrf) {
  let el = document.getElementById(id);
  if (!el) {
    el = document.createElement('div');
    el.id = id;
    el.style.display = 'none';
    document.body.appendChild(el);
  }
  if (csrf) el.setAttribute('data-csrf', csrf);
  return el;
}

// Native (SPA shell) init — mirrors the inline <script> in core/admin/users/index.php
// so the module can be mounted without the legacy page. See NativeModuleRegistry.
function init(data) {
  const modJsList = [];
  const perm = data.permissions || {};

  ensureHelperDiv('UserForm', data.csrf);
  ensureHelperDiv('UserPasswordChangeForm', data.csrf);

  modJsList.tabUser = new UserAdapter('User');
  modJsList.tabUser.setCSRFRequired(true);
  modJsList.tabUser.setRemoteTable(true);
  modJsList.tabUser.setObjectTypeName('User');
  modJsList.tabUser.setDataPipe(new IceDataPipe(modJsList.tabUser));
  // Mirrors the legacy index.php (the Users tab is gated on UserRole access).
  modJsList.tabUser.setAccess(perm.UserRole || []);

  modJsList.tabUserRole = new UserRoleAdapter('UserRole');
  modJsList.tabUserRole.setTables(data.modelClasses || []);
  modJsList.tabUserRole.setObjectTypeName('User Role');
  modJsList.tabUserRole.setDataPipe(new IceDataPipe(modJsList.tabUserRole));
  modJsList.tabUserRole.setAccess(perm.UserRole || []);

  modJsList.tabUserInvitation = new UserInvitationAdapter('UserInvitation');
  modJsList.tabUserInvitation.setObjectTypeName('User Invitation');
  modJsList.tabUserInvitation.setRemoteTable(true);
  modJsList.tabUserInvitation.setDataPipe(new IceDataPipe(modJsList.tabUserInvitation));
  modJsList.tabUserInvitation.setAccess(perm.UserInvitation || []);
  modJsList.tabUserInvitation.setModalType(UserInvitationAdapter.MODAL_TYPE_STEPS);
  // Legacy hides the invitation Edit button (only Delete/Copy); match that here
  // since the native card list derives its buttons from the access flags.
  modJsList.tabUserInvitation.setShowEdit(false);

  window.modJs = modJsList.tabUser;
  window.modJsList = modJsList;
}

window.initAdminUsers = init;
