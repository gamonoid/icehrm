import {
  EmployeeLeaveAdapter,
  EmployeeApprovedLeaveAdapter,
  SubEmployeeLeaveAdapter,
  EmployeeLeaveApprovalAdapter,
  EmployeeLeaveEntitlementAdapter,
} from './lib';
import IceDataPipe from '../../../../../../web/api/IceDataPipe';

window.EmployeeLeaveAdapter = EmployeeLeaveAdapter;
window.EmployeeApprovedLeaveAdapter = EmployeeApprovedLeaveAdapter;
window.SubEmployeeLeaveAdapter = SubEmployeeLeaveAdapter;
window.EmployeeLeaveApprovalAdapter = EmployeeLeaveApprovalAdapter;
window.EmployeeLeaveEntitlementAdapter = EmployeeLeaveEntitlementAdapter;
window.IceDataPipe = IceDataPipe;

// Native (SPA shell) init — replicates the employee-facing leave tabs from
// core/modules/leaves/index.php so the module mounts without the legacy page.
// See NativeModuleRegistry (modules/leaves). Phase 1 covers the leave-list
// tabs; Entitlement + multi-level Approval are handled separately.
function initUserLeaves(data) {
  const modJsList = [];
  const perm = (data && data.permissions) || {};

  const wire = (adapter, name, entity) => {
    adapter.setObjectTypeName(name);
    adapter.setDataPipe(new IceDataPipe(adapter));
    adapter.setAccess(perm[entity] || ['get', 'element', 'save', 'delete']);
    return adapter;
  };

  // All My Leaves — the only tab that can apply for leave.
  modJsList.tabMyLeaveAll = wire(
    new EmployeeLeaveAdapter('EmployeeLeave', 'EmployeeLeaveAll', '', 'date_start desc'),
    'Leave Request', 'MyLeave',
  );
  modJsList.tabMyLeaveAll.setShowEdit(false);
  modJsList.tabMyLeaveAll.setShowDelete(false);

  // Approved Leave — Leave Details + request cancellation.
  modJsList.tabMyLeaveApproved = wire(
    new EmployeeApprovedLeaveAdapter('EmployeeLeave', 'EmployeeLeaveApproved', { status: 'Approved' }, 'date_start desc'),
    'Approved Leave', 'MyLeaveApproved',
  );
  modJsList.tabMyLeaveApproved.setShowAddNew(false);
  modJsList.tabMyLeaveApproved.setShowEdit(false);
  modJsList.tabMyLeaveApproved.setShowDelete(false);

  // Pending Leave — Leave Details + cancel (while still pending). Includes
  // "Processing" (mid multi-level-approval) leaves alongside "Pending".
  modJsList.tabMyLeavePending = wire(
    new EmployeeLeaveAdapter('EmployeeLeave', 'EmployeeLeavePending', { status: ['Pending', 'Processing'] }, 'date_start desc'),
    'Pending Leave', 'MyLeave',
  );
  modJsList.tabMyLeavePending.setShowAddNew(false);
  modJsList.tabMyLeavePending.setShowEdit(false);
  modJsList.tabMyLeavePending.setShowDelete(false);

  // Leave Requests (Direct Reports) — approve/reject pending requests.
  modJsList.tabSubLeaveAll = wire(
    new SubEmployeeLeaveAdapter('EmployeeLeave', 'SubEmployeeLeaveAll', { status: 'Pending' }, 'date_start desc'),
    'Subordinate Leave Request', 'SubLeave',
  );
  modJsList.tabSubLeaveAll.setShowAddNew(false);
  modJsList.tabSubLeaveAll.setShowEdit(false);
  modJsList.tabSubLeaveAll.setShowDelete(false);

  // Leave Cancellation Requests — approve/reject cancellation requests.
  modJsList.tabSubLeaveCancel = wire(
    new SubEmployeeLeaveAdapter('EmployeeLeave', 'SubEmployeeLeaveCancel', { status: 'Cancellation Requested' }, 'date_start desc'),
    'Leave Cancellation Request', 'SubLeave',
  );
  modJsList.tabSubLeaveCancel.setShowAddNew(false);
  modJsList.tabSubLeaveCancel.setShowEdit(false);
  modJsList.tabSubLeaveCancel.setShowDelete(false);

  // Leave Entitlement — summary cards (custom component fetches via
  // getEntitlement; no standard list).
  const entitlement = new EmployeeLeaveEntitlementAdapter('EmployeeLeaveEntitlement', 'EmployeeLeaveEntitlement');
  entitlement.setShowAddNew(false);
  entitlement.setShowEdit(false);
  entitlement.setShowDelete(false);
  modJsList.tabMyLeaveEntitlement = entitlement;

  // Approval Requests — multi-level approval queue (only shown when enabled).
  modJsList.tabLeaveApproval = wire(
    new EmployeeLeaveApprovalAdapter('EmployeeLeaveApprove', 'EmployeeLeaveApproval', '', ''),
    'Leave Approval Request', 'LeaveApproval',
  );
  modJsList.tabLeaveApproval.setShowAddNew(false);
  modJsList.tabLeaveApproval.setShowEdit(false);
  modJsList.tabLeaveApproval.setShowDelete(false);

  window.modJs = modJsList.tabMyLeaveAll;
  window.modJsList = modJsList;
}

window.initUserLeaves = initUserLeaves;

// IceHrmPro marker: register this module (by its init fn) so the SPA shell can
// license-gate it. Baked into the (obfuscated) bundle, so it travels with the
// code and cannot be bypassed by moving the extension directory.
(window.iceProModules = window.iceProModules || {})['initUserLeaves'] = true;
