import React from 'react';
import ReactDOM from 'react-dom';
import { Alert, Space } from 'antd';
import {
  DocumentAdapter, CompanyDocumentAdapter, EmployeeDocumentAdapter, EmployeePayslipDocumentAdapter,
} from './lib';

window.DocumentAdapter = DocumentAdapter;
window.CompanyDocumentAdapter = CompanyDocumentAdapter;
window.EmployeeDocumentAdapter = EmployeeDocumentAdapter;
window.EmployeePayslipDocumentAdapter = EmployeePayslipDocumentAdapter;

import IceDataPipe from '../../../api/IceDataPipe';

// Shared init — used by both the legacy page and the SPA shell (see
// NativeModuleRegistry admin/documents). Replicates the page's original wiring.
function init(data) {
  const modJsList = {};
  const permissions = (data && data.permissions) || {};

  modJsList.tabCompanyDocument = new CompanyDocumentAdapter('CompanyDocument', 'CompanyDocument', '', '');
  modJsList.tabCompanyDocument.setObjectTypeName('CompanyDocument');
  modJsList.tabCompanyDocument.setAccess(permissions.CompanyDocument || []);
  modJsList.tabCompanyDocument.setDataPipe(new IceDataPipe(modJsList.tabCompanyDocument));
  modJsList.tabCompanyDocument.setRemoteTable(1);
  modJsList.tabCompanyDocument.setTitle('Company Documents');

  modJsList.tabDocument = new DocumentAdapter('Document', 'Document', '', '');
  modJsList.tabDocument.setObjectTypeName('Document');
  modJsList.tabDocument.setAccess(permissions.Document || []);
  modJsList.tabDocument.setDataPipe(new IceDataPipe(modJsList.tabDocument));
  modJsList.tabDocument.setRemoteTable(1);
  modJsList.tabDocument.setTitle('Document Types');

  modJsList.tabEmployeeDocument = new EmployeeDocumentAdapter('EmployeeDocument', 'EmployeeDocument', '', 'date_added desc');
  modJsList.tabEmployeeDocument.setRemoteTable(1);
  modJsList.tabEmployeeDocument.setObjectTypeName('EmployeeDocument');
  modJsList.tabEmployeeDocument.setAccess(permissions.EmployeeDocument || []);
  modJsList.tabEmployeeDocument.setDataPipe(new IceDataPipe(modJsList.tabEmployeeDocument));
  modJsList.tabEmployeeDocument.setTitle('Employee Documents');

  modJsList.tabPayslipDocument = new EmployeePayslipDocumentAdapter('PayslipDocument', 'PayslipDocument', '', 'date_added desc');
  modJsList.tabPayslipDocument.setRemoteTable(1);
  modJsList.tabPayslipDocument.setObjectTypeName('PayslipDocument');
  modJsList.tabPayslipDocument.setAccess(permissions.PayslipDocument || []);
  modJsList.tabPayslipDocument.setDataPipe(new IceDataPipe(modJsList.tabPayslipDocument));
  modJsList.tabPayslipDocument.setTitle('Employee Payslip');

  window.modJs = modJsList.tabCompanyDocument;
  window.modJsList = modJsList;
}

window.initAdminDocuments = init;
