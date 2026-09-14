import {
  EmployeeLeaveCalendarAdapter,
} from './lib';

window.EmployeeLeaveCalendarAdapter = EmployeeLeaveCalendarAdapter;

// Native (SPA shell) init — the calendar itself is a shell component
// (LeaveCalendar) that fetches via the shell's REST token, so init only needs
// to satisfy the host's required initFn.
window.initUserLeaveCal = function initUserLeaveCal() {
  window.modJsList = [];
};

// IceHrmPro marker: register this module (by its init fn) so the SPA shell can
// license-gate it. Baked into the (obfuscated) bundle, so it travels with the
// code and cannot be bypassed by moving the extension directory.
(window.iceProModules = window.iceProModules || {})['initUserLeaveCal'] = true;
