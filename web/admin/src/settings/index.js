import { SettingAdapter } from './lib';

// Export SettingAdapter for ModuleBuilder to use
window.SettingAdapter = SettingAdapter;

export { SettingAdapter };
export default { SettingAdapter };

import IceDataPipe from '../../../api/IceDataPipe';

// Native (SPA shell) init — one Setting adapter per category tab; the registry
// (NativeModuleRegistry::settingsTabs) decides which tabs actually show, using
// the same visibility rules as the legacy page.
const SETTING_TABS = [
  ['CompanySetting', { category: 'Company' }],
  ['SystemSetting', { category: 'System' }],
  ['EmailSetting', { category: 'Email' }],
  ['LeaveSetting', { category: 'Leave' }],
  ['AttendanceSetting', { category: 'Attendance' }],
  ['FilesSetting', { category: 'Files' }],
  ['LDAPSetting', { category: 'LDAP' }],
  ['SAMLSetting', { category: 'SAML' }],
  ['OtherSetting', {
    category: ['Projects', 'Recruitment', 'Notifications', 'Expense', 'Travel', 'Api', 'Overtime', 'Microsoft', 'Google'],
  }],
];

function init(data) {
  const modJsList = {};
  const permissions = (data && data.permissions) || {};

  SETTING_TABS.forEach(([name, filter]) => {
    const m = new SettingAdapter('Setting', name, filter, 'name');
    m.setObjectTypeName('Setting');
    m.setDataPipe(new IceDataPipe(m));
    m.setAccess(permissions.Setting || ['get', 'element', 'save']);
    m.setRemoteTable(true);
    m.setShowAddNew(false);
    modJsList[`tab${name}`] = m;
  });

  window.modJs = modJsList.tabCompanySetting;
  window.modJsList = modJsList;
}

window.initAdminSettings = init;
