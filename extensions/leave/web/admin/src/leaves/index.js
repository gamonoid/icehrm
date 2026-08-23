import {
  LeaveTypeAdapter,
  LeaveRuleAdapter,
  LeavePeriodAdapter,
  WorkDayAdapter,
  HoliDayAdapter,
  EmployeeLeaveAdapter,
  LeaveGroupAdapter,
  LeaveGroupEmployeeAdapter,
  LeaveStartingBalanceAdapter,
} from './lib';
import IceDataPipe from '../../../../../../web/api/IceDataPipe';

window.LeaveTypeAdapter = LeaveTypeAdapter;
window.LeaveRuleAdapter = LeaveRuleAdapter;
window.LeavePeriodAdapter = LeavePeriodAdapter;
window.WorkDayAdapter = WorkDayAdapter;
window.HoliDayAdapter = HoliDayAdapter;
window.EmployeeLeaveAdapter = EmployeeLeaveAdapter;
window.LeaveGroupAdapter = LeaveGroupAdapter;
window.LeaveGroupEmployeeAdapter = LeaveGroupEmployeeAdapter;
window.LeaveStartingBalanceAdapter = LeaveStartingBalanceAdapter;
window.IceDataPipe = IceDataPipe;

// Native (SPA shell) init — replicates the ModuleBuilderV2 tabs in
// core/admin/leaves/index.php so the module mounts without the legacy page.
// See NativeModuleRegistry (admin/leaves).
function init(data) {
  const modJsList = [];
  const perm = data.permissions || {};

  const wire = (adapter, name, entity) => {
    adapter.setObjectTypeName(name);
    adapter.setDataPipe(new IceDataPipe(adapter));
    adapter.setAccess(perm[entity] || []);
    return adapter;
  };

  modJsList.tabLeaveType = wire(new LeaveTypeAdapter('LeaveType', 'LeaveType', '', ''), 'Leave Type', 'LeaveType');
  modJsList.tabLeaveType.setModalType('Steps');

  modJsList.tabLeavePeriod = wire(new LeavePeriodAdapter('LeavePeriod', 'LeavePeriod', '', 'date_start desc'), 'Leave Period', 'LeavePeriod');
  modJsList.tabLeavePeriod.setCountryBasedLeavePeriods(false);

  modJsList.tabWorkDay = wire(new WorkDayAdapter('WorkDay', 'WorkDay', '', ''), 'Work Day', 'WorkDay');

  modJsList.tabHoliDay = wire(new HoliDayAdapter('HoliDay', 'HoliDay', '', 'dateh desc'), 'Holiday', 'HoliDay');
  modJsList.tabHoliDay.setRemoteTable(true);

  modJsList.tabLeaveRule = wire(new LeaveRuleAdapter('LeaveRule', 'LeaveRule', '', ''), 'Leave Rule', 'LeaveRule');

  modJsList.tabLeaveStartingBalance = wire(new LeaveStartingBalanceAdapter('LeaveStartingBalance', 'LeaveStartingBalance', '', 'created desc'), 'Leave Adjustment', 'LeaveStartingBalance');

  modJsList.tabLeaveGroup = wire(new LeaveGroupAdapter('LeaveGroup', 'LeaveGroup', '', ''), 'Leave Group', 'LeaveGroup');

  modJsList.tabLeaveGroupEmployee = wire(new LeaveGroupEmployeeAdapter('LeaveGroupEmployee', 'LeaveGroupEmployee', '', 'leave_group'), 'Leave Group Employee', 'LeaveGroupEmployee');

  // Employee Leave List — approve/reject via the native status modal
  // (changeLeaveStatus on admin=leaves). Defaults to showing Pending leaves.
  const emp = wire(new EmployeeLeaveAdapter('EmployeeLeave', 'EmployeeLeave', '', 'date_start desc'), 'Employee Leave', 'EmployeeLeave');
  emp.setRemoteTable(true);
  emp.setShowAddNew(false);
  emp.modulePathName = 'leaves';
  emp.filter = { status: 'Pending' };
  modJsList.tabEmployeeLeave = emp;

  window.modJs = modJsList.tabLeaveType;
  window.modJsList = modJsList;
}

window.initAdminLeaves = init;

// IceHrmPro marker: register this module (by its init fn) so the SPA shell can
// license-gate it. Baked into the (obfuscated) bundle, so it travels with the
// code and cannot be bypassed by moving the extension directory.
(window.iceProModules = window.iceProModules || {})['initAdminLeaves'] = true;
