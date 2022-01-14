import {
  EmployeeDocumentAdapter,
  EmployeeCompanyDocumentAdapter,
} from './lib';

import IceDataPipe from '../../../api/IceDataPipe';

function init(data) {
  const modJsList = {};

  modJsList.tabEmployeeDocument = new EmployeeDocumentAdapter('EmployeeDocument', 'EmployeeDocument', '', '');
  modJsList.tabEmployeeDocument.setRemoteTable(true);
  modJsList.tabEmployeeDocument.setObjectTypeName('Employee Document');
  modJsList.tabEmployeeDocument.setDataPipe(new IceDataPipe(modJsList.tabEmployeeDocument));
  modJsList.tabEmployeeDocument.setAccess(data.permissions.EmployeeDocument);

  modJsList.tabCompanyDocument = new EmployeeCompanyDocumentAdapter('CompanyDocument', 'CompanyDocument', '', '');
  modJsList.tabCompanyDocument.setRemoteTable(true);
  modJsList.tabCompanyDocument.setObjectTypeName('Company Document');
  modJsList.tabCompanyDocument.setDataPipe(new IceDataPipe(modJsList.tabCompanyDocument));
  modJsList.tabCompanyDocument.setAccess(data.permissions.CompanyDocument);

  window.modJs = modJsList.tabEmployeeDocument;
  window.modJsList = modJsList;
}

window.initDocumentsModule = init;
