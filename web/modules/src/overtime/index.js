import {
  EmployeeOvertimeAdapter,
  EmployeeOvertimeApproverAdapter,
  SubordinateEmployeeOvertimeAdapter,
} from './lib';

import IceDataPipe from '../../../api/IceDataPipe';

function init(data) {
  const modJsList = {};

  modJsList[`tab${data.moduleMainName}`] = new EmployeeOvertimeAdapter(data.moduleMainName, data.moduleMainName, '', '');
  modJsList[`tab${data.moduleMainName}`].setObjectTypeName('Overtime Request');
  modJsList[`tab${data.moduleMainName}`].setDataPipe(new IceDataPipe(modJsList[`tab${data.moduleMainName}`]));
  modJsList[`tab${data.moduleMainName}`].setAccess(data.permissions);
  modJsList[`tab${data.moduleMainName}`].setCustomFields(data.customFields);

  modJsList[`tab${data.approveModName}`] = new EmployeeOvertimeApproverAdapter(data.approveModName, data.approveModName, '', '');
  modJsList[`tab${data.approveModName}`].setObjectTypeName('Overtime Request');
  modJsList[`tab${data.approveModName}`].setDataPipe(new IceDataPipe(modJsList[`tab${data.approveModName}`]));
  modJsList[`tab${data.approveModName}`].setAccess(data.permissions);
  modJsList[`tab${data.approveModName}`].setShowAddNew(false);

  modJsList[`tab${data.subModuleMainName}`] = new SubordinateEmployeeOvertimeAdapter(data.moduleMainName, data.subModuleMainName, '', '');
  modJsList[`tab${data.subModuleMainName}`].setObjectTypeName('Overtime Request');
  modJsList[`tab${data.subModuleMainName}`].setDataPipe(new IceDataPipe(modJsList[`tab${data.subModuleMainName}`]));
  modJsList[`tab${data.subModuleMainName}`].setAccess(data.permissions);
  modJsList[`tab${data.subModuleMainName}`].setShowAddNew(false);

  window.modJs = modJsList[`tab${data.moduleMainName}`];
  window.modJsList = modJsList;
}

window.initUserOvertime = init;
