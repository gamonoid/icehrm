import { AttendanceAdapter, AttendanceStatusAdapter } from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

function init(data) {
  const modJsList = {};

  modJsList.tabAttendance = new AttendanceAdapter('Attendance','Attendance','','in_time desc');
  modJsList.tabAttendance.setObjectTypeName('Attendance');
  modJsList.tabAttendance.setDataPipe(new IceDataPipe(modJsList.tabAttendance));
  modJsList.tabAttendance.setAccess(data.permissions.Attendance);

  modJsList.tabAttendanceStatus = new AttendanceStatusAdapter('AttendanceStatus','AttendanceStatus','','');
  modJsList.tabAttendanceStatus.setRemoteTable(true);
  modJsList.tabAttendanceStatus.setObjectTypeName('Attendance');
  modJsList.tabAttendanceStatus.setDataPipe(new IceDataPipe(modJsList.tabAttendanceStatus));
  modJsList.tabAttendanceStatus.setAccess(data.permissions.AttendanceStatus);

  window.modJs = modJsList.tabAttendance;
  window.modJsList = modJsList;

}

window.initAdminAttendance = init;