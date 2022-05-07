import { FieldNameAdapter } from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

function init(data) {
  const modJsList = {};

  modJsList.tabEmployeeFieldName = new FieldNameAdapter('FieldNameMapping','EmployeeFieldName',{"type":"Employee"});
  modJsList.tabEmployeeFieldName.setObjectTypeName('Field Name Mapping');
  modJsList.tabEmployeeFieldName.setShowDelete(false);
  modJsList.tabEmployeeFieldName.setShowAddNew(false);
  modJsList.tabEmployeeFieldName.setDataPipe(new IceDataPipe(modJsList.tabEmployeeFieldName));
  modJsList.tabEmployeeFieldName.setAccess(data.permissions.FieldNameMapping);

  window.modJs = modJsList.tabEmployeeFieldName;
  window.modJsList = modJsList;
}

window.initAdminEmployeeFieldName = init;
