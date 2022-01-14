import {
  OvertimeCategoryAdapter,
  EmployeeOvertimeAdminAdapter,
} from './lib';

import IceDataPipe from '../../../api/IceDataPipe';

function init(data) {
  const modJsList = {};

  modJsList.tabOvertimeCategory = new OvertimeCategoryAdapter('OvertimeCategory', 'OvertimeCategory', '', '');
  modJsList.tabOvertimeCategory.setObjectTypeName('Overtime Category');
  modJsList.tabOvertimeCategory.setDataPipe(new IceDataPipe(modJsList.tabOvertimeCategory));
  modJsList.tabOvertimeCategory.setAccess(data.permissions.OvertimeCategory);

  modJsList.tabEmployeeOvertime = new EmployeeOvertimeAdminAdapter('EmployeeOvertime', 'EmployeeOvertime', '', '');
  modJsList.tabEmployeeOvertime.setObjectTypeName('Overtime Request');
  modJsList.tabEmployeeOvertime.setDataPipe(new IceDataPipe(modJsList.tabEmployeeOvertime));
  modJsList.tabEmployeeOvertime.setAccess(data.permissions.EmployeeOvertime);


  window.modJs = modJsList.tabOvertimeCategory;
  window.modJsList = modJsList;
}

window.initAdminOvertime = init;
