import {
  EmployeeCompanyLoanAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.EmployeeCompanyLoanAdapter = EmployeeCompanyLoanAdapter;

// Native (SPA shell) init — replicates core/modules/loans/index.php (read-only
// list of the signed-in employee's loans). See NativeModuleRegistry (modules/loans).
function init() {
  const modJsList = {};

  modJsList.tabEmployeeCompanyLoan = new EmployeeCompanyLoanAdapter('EmployeeCompanyLoan', 'EmployeeCompanyLoan', '', '');
  modJsList.tabEmployeeCompanyLoan.setObjectTypeName('Loan');
  modJsList.tabEmployeeCompanyLoan.setDataPipe(new IceDataPipe(modJsList.tabEmployeeCompanyLoan));
  // The general permission check reports only 'get' for user-scoped tables, but
  // employees may view their own loans (getUserOnlyMeAccess grants element and
  // the server enforces ownership) — so grant the view action in the UI.
  modJsList.tabEmployeeCompanyLoan.setAccess(['get', 'element']);
  modJsList.tabEmployeeCompanyLoan.setShowAddNew(false);
  modJsList.tabEmployeeCompanyLoan.setShowSave(false);
  modJsList.tabEmployeeCompanyLoan.setShowDelete(false);
  modJsList.tabEmployeeCompanyLoan.setShowEdit(false);

  window.modJs = modJsList.tabEmployeeCompanyLoan;
  window.modJsList = modJsList;
}

window.initUserLoans = init;
