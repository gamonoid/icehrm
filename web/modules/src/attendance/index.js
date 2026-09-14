import {
  AttendanceAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.AttendanceAdapter = AttendanceAdapter;
window.IceDataPipe = IceDataPipe;

// Native SPA init (mounted by the shell via NativeModuleRegistry). The employee's
// own attendance + Punch In/Out widget. Punch state + time settings come from the
// module-context (data); the backend scopes records to the current employee.
function init(data) {
  const modJsList = {};
  const a = new AttendanceAdapter('Attendance', 'Attendance', '', 'in_time desc');
  a.setUseServerTime(data && data.useServerTime ? data.useServerTime : 0);
  a.setHasOpenPunch(data && data.hasOpenPunch ? data.hasOpenPunch : 0);
  a.setPunchedOutToday(data && data.punchedOutToday ? data.punchedOutToday : 0);
  a.setObjectTypeName('Attendance');
  a.setAccess(['get', 'element']);
  a.setDataPipe(new IceDataPipe(a));
  a.setOvertimeStartHour(data && data.overtimeStartHour ? data.overtimeStartHour : 0);
  modJsList.tabAttendance = a;
  window.modJs = a;
  window.modJsList = modJsList;
}
window.initModulesAttendance = init;
