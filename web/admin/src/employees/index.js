import {
  EmployeeAdapter,
  TerminatedEmployeeAdapter,
  ArchivedEmployeeAdapter,
  EmployeeSkillAdapter,
  EmployeeEducationAdapter,
  EmployeeCertificationAdapter,
  EmployeeLanguageAdapter,
  EmployeeDependentAdapter,
  EmergencyContactAdapter,
  EmployeeImmigrationAdapter,
  EmployeeSubSkillsAdapter,
  EmployeeSubEducationAdapter,
  EmployeeSubCertificationAdapter,
  EmployeeSubLanguageAdapter,
  EmployeeSubDependentAdapter,
  EmployeeSubEmergencyContactAdapter,
  EmployeeSubDocumentAdapter,
  EmployeeCareerAdapter,
} from './lib';
import { UserAdapter, UserInvitationAdapter } from '../users/lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.EmployeeAdapter = EmployeeAdapter;
window.TerminatedEmployeeAdapter = TerminatedEmployeeAdapter;
window.ArchivedEmployeeAdapter = ArchivedEmployeeAdapter;
window.EmployeeSkillAdapter = EmployeeSkillAdapter;
window.EmployeeEducationAdapter = EmployeeEducationAdapter;
window.EmployeeCertificationAdapter = EmployeeCertificationAdapter;
window.EmployeeLanguageAdapter = EmployeeLanguageAdapter;
window.EmployeeDependentAdapter = EmployeeDependentAdapter;
window.EmergencyContactAdapter = EmergencyContactAdapter;
window.EmployeeImmigrationAdapter = EmployeeImmigrationAdapter;
window.EmployeeSubSkillsAdapter = EmployeeSubSkillsAdapter;
window.EmployeeSubEducationAdapter = EmployeeSubEducationAdapter;
window.EmployeeSubCertificationAdapter = EmployeeSubCertificationAdapter;
window.EmployeeSubLanguageAdapter = EmployeeSubLanguageAdapter;
window.EmployeeSubDependentAdapter = EmployeeSubDependentAdapter;
window.EmployeeSubEmergencyContactAdapter = EmployeeSubEmergencyContactAdapter;
window.EmployeeSubDocumentAdapter = EmployeeSubDocumentAdapter;
window.EmployeeCareerAdapter = EmployeeCareerAdapter;

window.IceDataPipe = IceDataPipe;

// Native (SPA shell) init — mirrors the inline <script> in core/admin/employees/index.php
// so the module can be mounted without the legacy page. See NativeModuleRegistry.
function init(data) {
  const modJsList = [];
  const perm = data.permissions || {};
  const isAdmin = data.user_level === 'Admin';

  modJsList.tabEmployee = new EmployeeAdapter('Employee', 'Employee', { status: 'Active' });
  if (!isAdmin) {
    modJsList.tabEmployee.setShowAddNew(false);
    modJsList.tabEmployee.setShowDelete(false);
    modJsList.tabEmployee.setAllowSwitchToEmployeeProfile(
      data.user_level === 'Manager' && !!data.managersCanSwitch,
    );
  } else {
    modJsList.tabEmployee.setAllowSwitchToEmployeeProfile(true);
  }
  modJsList.tabEmployee.setObjectTypeName('Employee');
  modJsList.tabEmployee.setModalType(EmployeeAdapter.MODAL_TYPE_STEPS);
  modJsList.tabEmployee.setDataPipe(new IceDataPipe(modJsList.tabEmployee));
  modJsList.tabEmployee.setAccess(perm.Employee || []);
  modJsList.tabEmployee.enableLocalStorage();
  modJsList.tabEmployee.setRemoteTable(true);
  modJsList.tabEmployee.setFieldNameMap(data.fieldNameMap || {});
  modJsList.tabEmployee.setCustomFields(data.customFields || {});
  modJsList.tabEmployee.setEmployeeNumberGeneration(
    Number(data.generateEmployeeNumbers) === 1,
    data.employeeNumberPrefix || '',
  );
  modJsList.tabEmployee.setMultiLevelApprovals(
    Number(data.mlaLeave) === 1 || Number(data.mlaExpense) === 1
    || Number(data.mlaOvertime) === 1 || Number(data.mlaTravel) === 1,
  );

  const simpleLists = [
    ['tabEmployeeCareer', EmployeeCareerAdapter, ['EmployeeCareer', 'EmployeeCareer', '', 'date_start desc'], 'Work History', 'EmployeeCareer'],
    ['tabEmployeeSkill', EmployeeSkillAdapter, ['EmployeeSkill'], 'Employee Skill', 'EmployeeSkill'],
    ['tabEmployeeEducation', EmployeeEducationAdapter, ['EmployeeEducation'], 'Employee Education', 'EmployeeEducation'],
    ['tabEmployeeCertification', EmployeeCertificationAdapter, ['EmployeeCertification'], 'Employee Certification', 'EmployeeCertification'],
    ['tabEmployeeLanguage', EmployeeLanguageAdapter, ['EmployeeLanguage'], 'Employee Language', 'EmployeeLanguage'],
    ['tabEmployeeDependent', EmployeeDependentAdapter, ['EmployeeDependent'], 'Employee Dependent', 'EmployeeDependent'],
    ['tabEmergencyContact', EmergencyContactAdapter, ['EmergencyContact'], 'Emergency Contact', 'EmergencyContact'],
  ];
  simpleLists.forEach(([key, Adapter, args, name, permKey]) => {
    const a = new Adapter(...args);
    a.setRemoteTable(true);
    a.setObjectTypeName(name);
    a.setDataPipe(new IceDataPipe(a));
    a.setAccess(perm[permKey] || []);
    modJsList[key] = a;
  });

  modJsList.tabTerminatedEmployee = new TerminatedEmployeeAdapter('Employee', 'TerminatedEmployee', { status: 'Terminated' });
  modJsList.tabTerminatedEmployee.setRemoteTable(true);
  modJsList.tabTerminatedEmployee.setShowAddNew(false);
  modJsList.tabTerminatedEmployee.setShowEdit(false);
  modJsList.tabTerminatedEmployee.setObjectTypeName('Deactivated Employees');
  modJsList.tabTerminatedEmployee.setDataPipe(new IceDataPipe(modJsList.tabTerminatedEmployee));
  modJsList.tabTerminatedEmployee.setAccess(perm.Employee || []);

  modJsList.tabArchivedEmployee = new ArchivedEmployeeAdapter('ArchivedEmployee');
  modJsList.tabArchivedEmployee.setRemoteTable(true);
  modJsList.tabArchivedEmployee.setShowAddNew(false);
  modJsList.tabArchivedEmployee.setShowEdit(false);
  modJsList.tabArchivedEmployee.setObjectTypeName('Archived Employee');
  modJsList.tabArchivedEmployee.setDataPipe(new IceDataPipe(modJsList.tabArchivedEmployee));
  modJsList.tabArchivedEmployee.setAccess(perm.Employee || []);

  // Hidden helper adapters used by the Employee add/edit + user-account flow.
  modJsList.tabUser = new UserAdapter('User');
  modJsList.tabUser.setCSRFRequired(true);
  modJsList.tabUser.setRemoteTable(true);
  modJsList.tabUser.setObjectTypeName('User');
  modJsList.tabUser.setDataPipe(new IceDataPipe(modJsList.tabUser));
  modJsList.tabUser.setAccess(perm.UserRole || []);

  modJsList.tabUserInvitation = new UserInvitationAdapter('UserInvitation');
  modJsList.tabUserInvitation.setObjectTypeName('User Invitation');
  modJsList.tabUserInvitation.setRemoteTable(true);
  modJsList.tabUserInvitation.setDataPipe(new IceDataPipe(modJsList.tabUserInvitation));
  modJsList.tabUserInvitation.setAccess(perm.UserInvitation || []);
  modJsList.tabUserInvitation.setModalType(UserInvitationAdapter.MODAL_TYPE_STEPS);

  window.modJs = modJsList.tabEmployee;
  window.modJsList = modJsList;
}

window.initAdminEmployees = init;
