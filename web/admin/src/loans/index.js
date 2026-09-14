import {
  CompanyLoanAdapter,
  EmployeeCompanyLoanAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.CompanyLoanAdapter = CompanyLoanAdapter;
window.EmployeeCompanyLoanAdapter = EmployeeCompanyLoanAdapter;

// Native (SPA shell) init — replicates core/admin/loans/index.php so the module
// mounts without the legacy page. See NativeModuleRegistry (admin/loans).
function init(data) {
  const modJsList = {};
  const permissions = (data && data.permissions) || {};

  modJsList.tabCompanyLoan = new CompanyLoanAdapter('CompanyLoan', 'CompanyLoan', '', '');
  modJsList.tabCompanyLoan.setObjectTypeName('Loan Type');
  modJsList.tabCompanyLoan.setDataPipe(new IceDataPipe(modJsList.tabCompanyLoan));
  modJsList.tabCompanyLoan.setAccess(permissions.CompanyLoan || []);

  modJsList.tabEmployeeCompanyLoan = new EmployeeCompanyLoanAdapter('EmployeeCompanyLoan', 'EmployeeCompanyLoan', '', '');
  modJsList.tabEmployeeCompanyLoan.setObjectTypeName('Employee Loan');
  modJsList.tabEmployeeCompanyLoan.setDataPipe(new IceDataPipe(modJsList.tabEmployeeCompanyLoan));
  modJsList.tabEmployeeCompanyLoan.setAccess(permissions.EmployeeCompanyLoan || []);
  modJsList.tabEmployeeCompanyLoan.setRemoteTable(true);

  window.modJs = modJsList.tabCompanyLoan;
  window.modJsList = modJsList;
}

window.initAdminLoans = init;
