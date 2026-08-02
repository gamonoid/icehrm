import {
  EmployeeSkillAdapter,
  EmployeeEducationAdapter,
  EmployeeCertificationAdapter,
  EmployeeLanguageAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.EmployeeSkillAdapter = EmployeeSkillAdapter;
window.EmployeeEducationAdapter = EmployeeEducationAdapter;
window.EmployeeCertificationAdapter = EmployeeCertificationAdapter;
window.EmployeeLanguageAdapter = EmployeeLanguageAdapter;
window.IceDataPipe = IceDataPipe;

// Native SPA init (mounted by the shell via NativeModuleRegistry).
function init(data) {
  const perms = (data && data.permissions) || {};
  const mk = (Adapter, key, label) => {
    const a = new Adapter(key, key, '', '');
    a.setObjectTypeName(label);
    a.setDataPipe(new IceDataPipe(a));
    a.setAccess(perms[key] || ['get', 'element', 'save', 'delete']);
    return a;
  };
  const modJsList = {};
  modJsList.tabEmployeeSkill = mk(EmployeeSkillAdapter, 'EmployeeSkill', 'Employee Skill');
  modJsList.tabEmployeeEducation = mk(EmployeeEducationAdapter, 'EmployeeEducation', 'Employee Education');
  modJsList.tabEmployeeCertification = mk(EmployeeCertificationAdapter, 'EmployeeCertification', 'Employee Certification');
  modJsList.tabEmployeeLanguage = mk(EmployeeLanguageAdapter, 'EmployeeLanguage', 'Employee Language');
  window.modJs = modJsList.tabEmployeeSkill;
  window.modJsList = modJsList;
}
window.initModulesQualifications = init;
