import {
  EmployeeDocumentAdapter,
  EmployeeCompanyDocumentAdapter,
  EmployeePayslipDocumentAdapter,
} from './lib';

import IceDataPipe from '../../../api/IceDataPipe';

function init(data) {
  const modJsList = {};

  modJsList.tabEmployeeDocument = new EmployeeDocumentAdapter('EmployeeDocument', 'EmployeeDocument', '', 'date_added desc');
  modJsList.tabEmployeeDocument.setRemoteTable(true);
  modJsList.tabEmployeeDocument.setObjectTypeName('Employee Document');
  modJsList.tabEmployeeDocument.setDataPipe(new IceDataPipe(modJsList.tabEmployeeDocument));
  modJsList.tabEmployeeDocument.setAccess(data.permissions.EmployeeDocument);

  modJsList.tabEmployeePayslipDocument = new EmployeePayslipDocumentAdapter('PayslipDocument', 'EmployeePayslipDocument', '', 'date_added desc');
  modJsList.tabEmployeePayslipDocument.setRemoteTable(true);
  modJsList.tabEmployeePayslipDocument.setObjectTypeName('Payslip');
  modJsList.tabEmployeePayslipDocument.setDataPipe(new IceDataPipe(modJsList.tabEmployeePayslipDocument));
  modJsList.tabEmployeePayslipDocument.setAccess(data.permissions.EmployeePayslipDocument);

  modJsList.tabCompanyDocument = new EmployeeCompanyDocumentAdapter('CompanyDocument', 'CompanyDocument', '', 'id desc');
  modJsList.tabCompanyDocument.setRemoteTable(true);
  modJsList.tabCompanyDocument.setObjectTypeName('Company Document');
  modJsList.tabCompanyDocument.setDataPipe(new IceDataPipe(modJsList.tabCompanyDocument));
  modJsList.tabCompanyDocument.setAccess(data.permissions.CompanyDocument);

  window.modJs = modJsList.tabEmployeeDocument;
  window.modJsList = modJsList;
}

window.initDocumentsModule = init;
