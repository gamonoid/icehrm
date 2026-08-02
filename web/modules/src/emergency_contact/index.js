import { EmergencyContactAdapter } from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.EmergencyContactAdapter = EmergencyContactAdapter;
window.IceDataPipe = IceDataPipe;

// Native SPA init (mounted by the shell via NativeModuleRegistry).
function init(data) {
  const modJsList = {};
  const a = new EmergencyContactAdapter('EmergencyContact', 'EmergencyContact', '', '');
  a.setObjectTypeName('Emergency Contact');
  a.setDataPipe(new IceDataPipe(a));
  a.setAccess((data && data.permissions && data.permissions.EmergencyContact) || ['get', 'element', 'save', 'delete']);
  modJsList.tabEmergencyContact = a;
  window.modJs = a;
  window.modJsList = modJsList;
}
window.initModulesEmergencyContact = init;
