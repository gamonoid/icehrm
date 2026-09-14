import {
  ProjectAdapter,
  EmployeeProjectAdapter,
  ClientAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.ProjectAdapter = ProjectAdapter;
window.EmployeeProjectAdapter = EmployeeProjectAdapter;
window.ClientAdapter = ClientAdapter;

// Native (SPA shell) init — mirrors the inline <script> in core/admin/projects/index.php
// so the module can be mounted without the legacy page. See NativeModuleRegistry.
function init(data) {
  const modJsList = [];

  modJsList.tabProject = new ProjectAdapter('Project', 'Project');
  modJsList.tabProject.setObjectTypeName('Project');
  modJsList.tabProject.setAccess(data.permissions.Project);
  modJsList.tabProject.setDataPipe(new IceDataPipe(modJsList.tabProject));
  modJsList.tabProject.setRemoteTable(true);
  modJsList.tabProject.setCustomFields(data.customFields.Project);

  modJsList.tabEmployeeProject = new EmployeeProjectAdapter('EmployeeProject', 'EmployeeProject');
  modJsList.tabEmployeeProject.setObjectTypeName('Employee Project');
  modJsList.tabEmployeeProject.setAccess(data.permissions.EmployeeProject);
  modJsList.tabEmployeeProject.setDataPipe(new IceDataPipe(modJsList.tabEmployeeProject));
  modJsList.tabEmployeeProject.setRemoteTable(true);

  modJsList.tabClient = new ClientAdapter('Client');
  modJsList.tabClient.setObjectTypeName('Client');
  modJsList.tabClient.setAccess(data.permissions.Client);
  modJsList.tabClient.setDataPipe(new IceDataPipe(modJsList.tabClient));
  modJsList.tabClient.setRemoteTable(true);
  modJsList.tabClient.setCustomFields(data.customFields.Client);

  window.modJs = modJsList.tabProject;
  window.modJsList = modJsList;
}

window.initAdminProjects = init;
