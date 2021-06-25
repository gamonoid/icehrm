import {
  EmployeeSalaryAdapter,
} from './lib';

import IceDataPipe from "../../../api/IceDataPipe";


function init(data) {
  const modJsList = {};
  modJsList.tabEmployeeSalary = new EmployeeSalaryAdapter('EmployeeSalary', 'EmployeeSalary');
  modJsList.tabEmployeeSalary.setObjectTypeName('Employee Salary');
  modJsList.tabEmployeeSalary.setDataPipe(new IceDataPipe(modJsList.tabEmployeeSalary));
  modJsList.tabEmployeeSalary.setAccess(data.permissions.EmployeeSalary);

  window.modJs = modJsList.tabEmployeeSalary;
  window.modJsList = modJsList;
}

window.initAdminSalary = init;
