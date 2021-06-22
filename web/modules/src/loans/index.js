import {
  EmployeeCompanyLoanAdapter,
} from './lib';

import IceDataPipe from "../../../api/IceDataPipe";


function init(data) {
  const modJsList = {};
  //modJsList.tabEmployeeCompanyLoan = new EmployeeCompanyLoanAdapter('EmployeeCompanyLoan','');
  modJsList.tabEmployeeCompanyLoan = new EmployeeCompanyLoanAdapter('EmployeeCompanyLoan', 'EmployeeCompanyLoan');
  //modJsList.tabEmployeeCompanyLoan.setObjectTypeName('Employee Company Loan');
  modJsList.tabEmployeeCompanyLoan.setDataPipe(new IceDataPipe(modJsList.tabEmployeeCompanyLoan));
  modJsList.tabEmployeeCompanyLoan.setAccess(data.permissions.EmployeeCompanyLoan);

  window.modJs = modJsList.tabEmployeeCompanyLoan;
  window.modJsList = modJsList;
}

window.initAdminLoan = init;
