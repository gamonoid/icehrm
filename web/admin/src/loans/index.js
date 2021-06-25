import {
  CompanyLoanAdapter,
  EmployeeCompanyLoanAdapter,
} from './lib';


import IceDataPipe from "../../../api/IceDataPipe";


function init(data) {
  const modJsList = {};
  modJsList.tabCompanyLoan = new CompanyLoanAdapter('CompanyLoan', 'CompanyLoan');
  modJsList.tabCompanyLoan.setObjectTypeName('Company Loan');
  modJsList.tabCompanyLoan.setDataPipe(new IceDataPipe(modJsList.tabCompanyLoan));
  modJsList.tabCompanyLoan.setAccess(data.permissions.CompanyLoan);

  modJsList.tabEmployeeCompanyLoan = new EmployeeCompanyLoanAdapter('EmployeeCompanyLoan', 'EmployeeCompanyLoan');
  modJsList.tabEmployeeCompanyLoan.setObjectTypeName('Employee Company Loan');
  modJsList.tabEmployeeCompanyLoan.setDataPipe(new IceDataPipe(modJsList.tabEmployeeCompanyLoan));
  modJsList.tabEmployeeCompanyLoan.setAccess(data.permissions.EmployeeCompanyLoan);

  window.modJs = modJsList.tabCompanyLoan;
  window.modJsList = modJsList;
}

window.initAdminLoan = init;
