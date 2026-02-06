import {
  CourseAdapter,
  TrainingSessionAdapter,
  EmployeeTrainingSessionAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

function init(data) {
  const modJsList = [];

  modJsList.tabCourse = new CourseAdapter('Course', 'Course');
  modJsList.tabCourse.setObjectTypeName('Courses');
  modJsList.tabCourse.setDataPipe(new IceDataPipe(modJsList.tabCourse));
  modJsList.tabCourse.setAccess(data.permissions.Course);

  modJsList.tabTrainingSession = new TrainingSessionAdapter('TrainingSession', 'TrainingSession');
  modJsList.tabTrainingSession.setObjectTypeName('Training Sessions');
  modJsList.tabTrainingSession.setDataPipe(new IceDataPipe(modJsList.tabTrainingSession));
  modJsList.tabTrainingSession.setAccess(data.permissions.TrainingSession);

  modJsList.tabEmployeeTrainingSession = new EmployeeTrainingSessionAdapter('EmployeeTrainingSession');
  modJsList.tabEmployeeTrainingSession.setObjectTypeName('Employee Training Sessions');
  modJsList.tabEmployeeTrainingSession.setDataPipe(new IceDataPipe(modJsList.tabEmployeeTrainingSession));
  modJsList.tabEmployeeTrainingSession.setAccess(data.permissions.EmployeeTrainingSession);

  window.modJs = modJsList.tabCourse;
  window.modJsList = modJsList;
}

window.initAdminTraining = init;
