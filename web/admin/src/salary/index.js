import {
  SalaryComponentTypeAdapter,
  SalaryComponentAdapter,
  EmployeeSalaryAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.SalaryComponentTypeAdapter = SalaryComponentTypeAdapter;
window.SalaryComponentAdapter = SalaryComponentAdapter;
window.EmployeeSalaryAdapter = EmployeeSalaryAdapter;

// Native (SPA shell) init — replicates core/admin/salary/index.php so the module
// mounts without the legacy page. See NativeModuleRegistry (admin/salary).
function init(data) {
  const modJsList = {};
  const permissions = (data && data.permissions) || {};

  modJsList.tabSalaryComponentType = new SalaryComponentTypeAdapter('SalaryComponentType');
  modJsList.tabSalaryComponentType.setRemoteTable(true);
  modJsList.tabSalaryComponentType.setObjectTypeName('Salary Component Type');
  modJsList.tabSalaryComponentType.setDataPipe(new IceDataPipe(modJsList.tabSalaryComponentType));
  modJsList.tabSalaryComponentType.setAccess(permissions.SalaryComponentType || []);

  modJsList.tabSalaryComponent = new SalaryComponentAdapter('SalaryComponent');
  modJsList.tabSalaryComponent.setObjectTypeName('Salary Component');
  modJsList.tabSalaryComponent.setDataPipe(new IceDataPipe(modJsList.tabSalaryComponent));
  modJsList.tabSalaryComponent.setAccess(permissions.SalaryComponent || []);

  modJsList.tabEmployeeSalary = new EmployeeSalaryAdapter('EmployeeSalary');
  modJsList.tabEmployeeSalary.setObjectTypeName('Employee Salary');
  modJsList.tabEmployeeSalary.setRemoteTable(true);
  modJsList.tabEmployeeSalary.setDataPipe(new IceDataPipe(modJsList.tabEmployeeSalary));
  modJsList.tabEmployeeSalary.setAccess(permissions.EmployeeSalary || []);

  window.modJs = modJsList.tabSalaryComponentType;
  window.modJsList = modJsList;
}

window.initAdminSalary = init;
