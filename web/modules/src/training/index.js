import {
  EmployeeTrainingSessionAdapter,
  TrainingSessionAdapter,
  CoordinatedTrainingSessionAdapter,
  SubEmployeeTrainingSessionAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.EmployeeTrainingSessionAdapter = EmployeeTrainingSessionAdapter;
window.TrainingSessionAdapter = TrainingSessionAdapter;
window.CoordinatedTrainingSessionAdapter = CoordinatedTrainingSessionAdapter;
window.SubEmployeeTrainingSessionAdapter = SubEmployeeTrainingSessionAdapter;

// Shared init — used by both the legacy page and the SPA shell (see
// NativeModuleRegistry modules/training). Replicates core/modules/training's
// original wiring with data pipes + per-entity permissions.
function init(data) {
  const modJsList = {};
  const permissions = (data && data.permissions) || {};

  // Open sign-up sessions (view + Sign Up).
  modJsList.tabTrainingSession = new TrainingSessionAdapter(
    'TrainingSessionWithCourse', 'TrainingSession',
    { attendanceType: 'Sign Up', status: 'Approved' }, '',
  );
  modJsList.tabTrainingSession.setObjectTypeName('Training Session');
  modJsList.tabTrainingSession.setDataPipe(new IceDataPipe(modJsList.tabTrainingSession));
  modJsList.tabTrainingSession.setAccess(permissions.TrainingSessionWithCourse || ['get', 'element']);
  modJsList.tabTrainingSession.setRemoteTable(true);
  modJsList.tabTrainingSession.setShowAddNew(false);
  modJsList.tabTrainingSession.setShowSave(false);
  modJsList.tabTrainingSession.setShowDelete(false);

  // My training sessions (feedback + mark completed).
  modJsList.tabEmployeeTrainingSession = new EmployeeTrainingSessionAdapter('EmployeeTrainingSession', 'EmployeeTrainingSession', '', '');
  modJsList.tabEmployeeTrainingSession.setObjectTypeName('Training Session');
  modJsList.tabEmployeeTrainingSession.setDataPipe(new IceDataPipe(modJsList.tabEmployeeTrainingSession));
  modJsList.tabEmployeeTrainingSession.setAccess(permissions.EmployeeTrainingSession || ['get', 'element', 'save', 'delete']);
  modJsList.tabEmployeeTrainingSession.setRemoteTable(true);
  modJsList.tabEmployeeTrainingSession.setShowAddNew(false);

  // Direct reports' sessions (review feedback + approve completed).
  modJsList.tabSubEmployeeTraining = new SubEmployeeTrainingSessionAdapter('EmployeeTrainingSession', 'SubEmployeeTraining', '', '');
  modJsList.tabSubEmployeeTraining.setObjectTypeName('Training Session');
  modJsList.tabSubEmployeeTraining.setDataPipe(new IceDataPipe(modJsList.tabSubEmployeeTraining));
  modJsList.tabSubEmployeeTraining.setAccess(permissions.EmployeeTrainingSession || ['get', 'element', 'save', 'delete']);
  modJsList.tabSubEmployeeTraining.setRemoteTable(true);
  modJsList.tabSubEmployeeTraining.setShowAddNew(false);

  // Sessions the current user coordinates (full edit, no delete).
  modJsList.tabCoordinatedTrainingSession = new CoordinatedTrainingSessionAdapter('CoordinatedTrainingSession', 'CoordinatedTrainingSession', '', '');
  modJsList.tabCoordinatedTrainingSession.setObjectTypeName('Training Session');
  modJsList.tabCoordinatedTrainingSession.setDataPipe(new IceDataPipe(modJsList.tabCoordinatedTrainingSession));
  modJsList.tabCoordinatedTrainingSession.setAccess(permissions.CoordinatedTrainingSession || ['get', 'element', 'save']);
  modJsList.tabCoordinatedTrainingSession.setShowDelete(false);

  window.modJs = modJsList.tabTrainingSession;
  window.modJsList = modJsList;
}

window.initUserTraining = init;
