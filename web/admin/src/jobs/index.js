import { JobTitleAdapter, PayGradeAdapter, EmploymentStatusAdapter } from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

function init(data) {
  const modJsList = [];

  modJsList.tabJobTitle = new JobTitleAdapter('JobTitle', 'JobTitle', '', '');
  modJsList.tabJobTitle.setObjectTypeName('Job Titles');
  modJsList.tabJobTitle.setDataPipe(new IceDataPipe(modJsList.tabJobTitle));
  modJsList.tabJobTitle.setAccess(data.permissions.JobTitle);

  modJsList.tabPayGrade = new PayGradeAdapter('PayGrade', 'PayGrade', '', '');
  modJsList.tabPayGrade.setObjectTypeName('Pay Grades');
  modJsList.tabPayGrade.setDataPipe(new IceDataPipe(modJsList.tabPayGrade));
  modJsList.tabPayGrade.setAccess(data.permissions.PayGrade);

  modJsList.tabEmploymentStatus = new EmploymentStatusAdapter('EmploymentStatus', 'EmploymentStatus', '', '');
  modJsList.tabEmploymentStatus.setObjectTypeName('Employment Status');
  modJsList.tabEmploymentStatus.setDataPipe(new IceDataPipe(modJsList.tabEmploymentStatus));
  modJsList.tabEmploymentStatus.setAccess(data.permissions.EmploymentStatus);

  window.modJs = modJsList.tabJobTitle;
  window.modJsList = modJsList;
}

window.initAdminJobs = init;
