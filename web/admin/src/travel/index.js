import {
  ImmigrationDocumentAdapter,
  EmployeeImmigrationAdapter,
  TravelProjectAdapter,
  EmployeeTravelRecordAdminAdapter,
  CustomFieldAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.ImmigrationDocumentAdapter = ImmigrationDocumentAdapter;
window.EmployeeImmigrationAdapter = EmployeeImmigrationAdapter;
window.TravelProjectAdapter = TravelProjectAdapter;
window.EmployeeTravelRecordAdminAdapter = EmployeeTravelRecordAdminAdapter;
window.CustomFieldAdapter = CustomFieldAdapter;
window.IceDataPipe = IceDataPipe;

// Native (SPA shell) init — replicates core/admin/travel/index.php so the module
// mounts without the legacy page. See NativeModuleRegistry (admin/travel).
function init(data) {
  const modJsList = {};
  const access = (data && data.permissions) || ['get', 'element', 'save', 'delete'];

  modJsList.tabTravelProject = new TravelProjectAdapter('TravelProject', 'TravelProject', '', '');
  modJsList.tabTravelProject.setObjectTypeName('Travel Project');
  modJsList.tabTravelProject.setDataPipe(new IceDataPipe(modJsList.tabTravelProject));
  modJsList.tabTravelProject.setAccess(access);
  modJsList.tabTravelProject.setRemoteTable(true);

  modJsList.tabEmployeeTravelRecord = new EmployeeTravelRecordAdminAdapter('EmployeeTravelRecord', 'EmployeeTravelRecord', '', '');
  modJsList.tabEmployeeTravelRecord.setObjectTypeName('Travel Request');
  modJsList.tabEmployeeTravelRecord.setDataPipe(new IceDataPipe(modJsList.tabEmployeeTravelRecord));
  modJsList.tabEmployeeTravelRecord.setAccess(access);
  modJsList.tabEmployeeTravelRecord.setRemoteTable(true);
  modJsList.tabEmployeeTravelRecord.setCustomFields((data && data.customFields) || {});
  modJsList.tabEmployeeTravelRecord.setModalType('Steps');

  window.modJs = modJsList.tabTravelProject;
  window.modJsList = modJsList;
}

window.initAdminTravel = init;
