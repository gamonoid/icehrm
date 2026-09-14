import { EmployeeDependentAdapter } from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.EmployeeDependentAdapter = EmployeeDependentAdapter;
window.IceDataPipe = IceDataPipe;

// Native SPA init (mounted by the shell via NativeModuleRegistry). The backend
// scopes EmployeeDependent to the current user's own employee, same as legacy.
function init(data) {
  const modJsList = {};
  const a = new EmployeeDependentAdapter('EmployeeDependent', 'EmployeeDependent', '', '');
  a.setObjectTypeName('Employee Dependent');
  a.setDataPipe(new IceDataPipe(a));
  a.setAccess((data && data.permissions && data.permissions.EmployeeDependent) || ['get', 'element', 'save', 'delete']);
  modJsList.tabEmployeeDependent = a;
  window.modJs = a;
  window.modJsList = modJsList;
}
window.initModulesDependents = init;
