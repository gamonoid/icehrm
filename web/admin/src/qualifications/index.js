import {
  SkillAdapter,
  EducationAdapter,
  CertificationAdapter,
  LanguageAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

function init(data) {
  const modJsList = [];

  modJsList.tabSkill = new SkillAdapter('Skill');
  modJsList.tabSkill.setObjectTypeName('Skills');
  modJsList.tabSkill.setDataPipe(new IceDataPipe(modJsList.tabSkill));
  modJsList.tabSkill.setAccess(data.permissions.Skill);

  modJsList.tabEducation = new EducationAdapter('Education');
  modJsList.tabEducation.setObjectTypeName('Education');
  modJsList.tabEducation.setDataPipe(new IceDataPipe(modJsList.tabEducation));
  modJsList.tabEducation.setAccess(data.permissions.Education);

  modJsList.tabCertification = new CertificationAdapter('Certification');
  modJsList.tabCertification.setObjectTypeName('Education');
  modJsList.tabCertification.setDataPipe(new IceDataPipe(modJsList.tabCertification));
  modJsList.tabCertification.setAccess(data.permissions.Certification);

  modJsList.tabLanguage = new LanguageAdapter('Language');
  modJsList.tabLanguage.setObjectTypeName('Language');
  modJsList.tabLanguage.setDataPipe(new IceDataPipe(modJsList.tabLanguage));
  modJsList.tabLanguage.setAccess(data.permissions.Language);

  window.modJs = modJsList.tabSkill;
  window.modJsList = modJsList;
}

window.initAdminQualifications = init;
