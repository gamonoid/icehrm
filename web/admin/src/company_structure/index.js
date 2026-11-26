import {
  CompanyStructureAdapter, CompanyGraphAdapter, WorkLocationAdapter, CostCenterAdapter,
} from './lib';
import IceDataPipe from "../../../api/IceDataPipe";


function init(data) {
  const modJsList = {};

  modJsList.tabCompanyStructure = new CompanyStructureAdapter('CompanyStructure');
  modJsList.tabCompanyStructure.setObjectTypeName('Company Structure');
  modJsList.tabCompanyStructure.setDataPipe(new IceDataPipe(modJsList.tabCompanyStructure));
  modJsList.tabCompanyStructure.setAccess(data.permissions.CompanyStructure);
  modJsList.tabCompanyStructure.setCustomFields(data.customFields);

  modJsList.tabCompanyGraph = new CompanyGraphAdapter('CompanyStructure');

  const lookups = [
    ['tabWorkLocation', WorkLocationAdapter, 'WorkLocation', 'Work Locations'],
    ['tabCostCenter', CostCenterAdapter, 'CostCenter', 'Cost Centers'],
  ];
  lookups.forEach(([key, Adapter, entity, name]) => {
    const a = new Adapter(entity, entity, '', '');
    a.setObjectTypeName(name);
    a.setDataPipe(new IceDataPipe(a));
    a.setAccess((data.permissions && data.permissions[entity]) || []);
    modJsList[key] = a;
  });

  window.modJs = modJsList.tabCompanyStructure;
  window.modJsList = modJsList;
}

window.initAdminCompanyStructure = init;
