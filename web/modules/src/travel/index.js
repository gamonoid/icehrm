import {
  EmployeeImmigrationAdapter,
  EmployeeTravelRecordAdapter,
  EmployeeTravelRecordApproverAdapter,
  SubordinateEmployeeTravelRecordAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.EmployeeImmigrationAdapter = EmployeeImmigrationAdapter;
window.EmployeeTravelRecordAdapter = EmployeeTravelRecordAdapter;
window.EmployeeTravelRecordApproverAdapter = EmployeeTravelRecordApproverAdapter;
window.SubordinateEmployeeTravelRecordAdapter = SubordinateEmployeeTravelRecordAdapter;
window.IceDataPipe = IceDataPipe;

// Native (SPA shell) init — replicates core/modules/travel/index.php. The
// Approval tab (multi-level) is gated via requiresSetting in the registry.
function init(data) {
  const modJsList = {};
  const access = (data && data.permissions) || ['get', 'element', 'save', 'delete'];
  const customFields = (data && data.customFields) || {};

  // My travel requests — apply/edit/delete + request cancellation.
  modJsList.tabEmployeeTravelRecord = new EmployeeTravelRecordAdapter('EmployeeTravelRecord', 'EmployeeTravelRecord', '', '');
  modJsList.tabEmployeeTravelRecord.setObjectTypeName('Travel Request');
  modJsList.tabEmployeeTravelRecord.setDataPipe(new IceDataPipe(modJsList.tabEmployeeTravelRecord));
  modJsList.tabEmployeeTravelRecord.setAccess(access);
  modJsList.tabEmployeeTravelRecord.setCustomFields(customFields);
  modJsList.tabEmployeeTravelRecord.setModalType('Steps');

  // Direct reports' travel requests — view (with status change) + edit.
  modJsList.tabSubordinateEmployeeTravelRecord = new SubordinateEmployeeTravelRecordAdapter('EmployeeTravelRecord', 'SubordinateEmployeeTravelRecord', '', '');
  modJsList.tabSubordinateEmployeeTravelRecord.setObjectTypeName('Travel Request');
  modJsList.tabSubordinateEmployeeTravelRecord.setDataPipe(new IceDataPipe(modJsList.tabSubordinateEmployeeTravelRecord));
  modJsList.tabSubordinateEmployeeTravelRecord.setAccess(['get', 'element']);
  modJsList.tabSubordinateEmployeeTravelRecord.setRemoteTable(true);
  modJsList.tabSubordinateEmployeeTravelRecord.setShowAddNew(false);
  modJsList.tabSubordinateEmployeeTravelRecord.setShowDelete(false);
  modJsList.tabSubordinateEmployeeTravelRecord.setShowEdit(true);

  // Multi-level approval queue (only shown when enabled).
  modJsList.tabEmployeeTravelRecordApprove = new EmployeeTravelRecordApproverAdapter('EmployeeTravelRecordApprove', 'EmployeeTravelRecordApprove', '', '');
  modJsList.tabEmployeeTravelRecordApprove.setObjectTypeName('Travel Request');
  modJsList.tabEmployeeTravelRecordApprove.setDataPipe(new IceDataPipe(modJsList.tabEmployeeTravelRecordApprove));
  modJsList.tabEmployeeTravelRecordApprove.setAccess(['get', 'element']);
  modJsList.tabEmployeeTravelRecordApprove.setShowAddNew(false);
  modJsList.tabEmployeeTravelRecordApprove.setShowDelete(false);
  modJsList.tabEmployeeTravelRecordApprove.setShowEdit(false);

  window.modJs = modJsList.tabEmployeeTravelRecord;
  window.modJsList = modJsList;
}

window.initUserTravel = init;
