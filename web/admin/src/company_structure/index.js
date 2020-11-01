import { CompanyStructureAdapter, CompanyGraphAdapter } from './lib';
import IceDataPipe from "../../../api/IceDataPipe";
import CustomFieldAdapter from "../../../api/ReactCustomFieldAdapter";


function init(data) {
  const modJsList = {};
  modJsList.tabCompanyStructure = new CompanyStructureAdapter('CompanyStructure');
  modJsList.tabCompanyStructure.setObjectTypeName('Company Structure');
  modJsList.tabCompanyStructure.setDataPipe(new IceDataPipe(modJsList.tabCompanyStructure));
  modJsList.tabCompanyStructure.setAccess(data.permissions.CompanyStructure);

  modJsList.tabCompanyGraph = new CompanyGraphAdapter('CompanyStructure');

  window.modJs = modJsList.tabCompanyStructure;
  window.modJsList = modJsList;
}

window.initAdminCompanyStructure = init;
