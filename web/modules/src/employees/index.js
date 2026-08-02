import {
  EmployeeAdapter,
  CompanyGraphAdapter,
  MobileAppAdapter,
} from './lib';

window.EmployeeAdapter = EmployeeAdapter;
window.CompanyGraphAdapter = CompanyGraphAdapter;
window.MobileAppAdapter = MobileAppAdapter;

// Native (SPA shell) init — mirrors the inline <script> in
// core/modules/employees/index.php so the personal-details module can be
// mounted without the legacy page. See NativeModuleRegistry (modules/employees).
function init(data) {
  const modJsList = [];

  // My Details — the logged-in user's own profile. get() renders EmployeeProfile
  // into #Employee and loads the current employee via a custom action.
  const emp = new EmployeeAdapter('Employee');
  emp.setFieldNameMap(data.fieldNameMap || []);
  emp.setCustomFields(data.customFields || {});
  emp.setObjectTypeName('Employee');
  emp.setModalType(EmployeeAdapter.MODAL_TYPE_STEPS);
  modJsList.tabEmployee = emp;

  // Mobile App — drives the one-time login code request (the Company org chart is
  // a native OrgChart component tab, so no CompanyGraphAdapter is needed here).
  const mobile = new MobileAppAdapter('MobileApp');
  if (data.jwtToken) mobile.setToken(data.jwtToken);
  mobile.apiEnabled = data.apiEnabled;
  mobile.apiBaseUrl = data.apiBaseUrl;
  modJsList.tabMobileApp = mobile;

  // The password-change modal reads this CSRF token.
  window.passwordCSRF = data.csrf;

  window.modJs = emp;
  window.modJsList = modJsList;
}

window.initModulesEmployees = init;
