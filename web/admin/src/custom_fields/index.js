import { CommonCustomFieldAdapter } from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

function init(data) {
  const modJsList = {};

  modJsList.tabCustomField = new CommonCustomFieldAdapter('CustomField', 'CustomField', {}, '');
  modJsList.tabCustomField.setRemoteTable(true);
  modJsList.tabCustomField.setObjectTypeName('Custom Field');
  modJsList.tabCustomField.setDataPipe(new IceDataPipe(modJsList.tabCustomField));
  modJsList.tabCustomField.setAccess(data.permissions.CustomField);
  modJsList.tabCustomField.setTypes(data.types);

  window.modJs = modJsList.tabCustomField;
  window.modJsList = modJsList;
}

window.initAdminCustomFields = init;
