import {
  SalaryComponentTypeAdapter,
  SalaryComponentAdapter,
  EmployeeSalaryAdapter,
} from './lib';

/*window.SalaryComponentTypeAdapter = SalaryComponentTypeAdapter;
window.SalaryComponentAdapter = SalaryComponentAdapter;
window.EmployeeSalaryAdapter = EmployeeSalaryAdapter;*/

import IceDataPipe from "../../../api/IceDataPipe";


function init(data) {
  const modJsList = [];
  modJsList.tabSalaryComponentType = new SalaryComponentTypeAdapter('SalaryComponentType', 'SalaryComponentType');
  modJsList.tabSalaryComponentType.setObjectTypeName('Salary Component Types');
  modJsList.tabSalaryComponentType.setDataPipe(new IceDataPipe(modJsList.tabSalaryComponentType));
  modJsList.tabSalaryComponentType.setAccess(data.permissions.tabSalaryComponentType);

  modJsList.tabSalaryComponent = new SalaryComponentAdapter('SalaryComponent', 'SalaryComponent');
  modJsList.tabSalaryComponent.setObjectTypeName('Salary components');
  modJsList.tabSalaryComponent.setDataPipe(new IceDataPipe(modJsList.tabSalaryComponent));
  modJsList.tabSalaryComponent.setAccess(data.permissions.SalaryComponent);

  modJsList.tabEmployeeSalary = new EmployeeSalaryAdapter('EmployeeSalary', 'EmployeeSalary');
  modJsList.tabEmployeeSalary.setObjectTypeName('Employee Salary');
  modJsList.tabEmployeeSalary.setDataPipe(new IceDataPipe(modJsList.tabEmployeeSalary));
  modJsList.tabEmployeeSalary.setAccess(data.permissions.EmployeeSalary);

  //window.modJs = modJsList.tabEmployeeSalary;
  window.modJs = modJsList.tabSalaryComponentType;
  window.modJsList = modJsList;
}

window.initAdminSalary = init;