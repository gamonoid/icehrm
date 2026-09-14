import {
  EmployeeTimeSheetAdapter,
  SubEmployeeTimeSheetAdapter,
  EmployeeTimeEntryAdapter,
  QtsheetAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.EmployeeTimeSheetAdapter = EmployeeTimeSheetAdapter;
window.SubEmployeeTimeSheetAdapter = SubEmployeeTimeSheetAdapter;
window.EmployeeTimeEntryAdapter = EmployeeTimeEntryAdapter;
window.QtsheetAdapter = QtsheetAdapter;
window.IceDataPipe = IceDataPipe;

// Native (SPA shell) init — mirrors the inline <script> in
// core/modules/time_sheets/index.php. The adapters are used purely as a backend
// client (their proven custom-action contracts + IceDataPipe); the native
// TimeSheets React component renders the UI. See NativeModuleRegistry.
function init(data) {
  const modJsList = [];
  const need = data.advancedTimesheets ? 1 : 0;

  // My timesheets list (the 'EmployeeTimeSheetAll' tab name enables the
  // create-previous / create-next actions in the adapter).
  const mine = new EmployeeTimeSheetAdapter('EmployeeTimeSheet', 'EmployeeTimeSheetAll', '', 'date_start desc');
  mine.setShowAddNew(false);
  mine.setRemoteTable(true);
  mine.setNeedStartEndTime(need);
  // Legacy adapters have no setDataPipe; assign the pipe directly (IceDataPipe
  // only needs getDataMapping/getSourceMapping/getFilter/etc., which they have).
  mine.dataPipe = new IceDataPipe(mine);
  modJsList.tabEmployeeTimeSheetAll = mine;

  // Direct reports' timesheets (managers).
  const sub = new SubEmployeeTimeSheetAdapter('EmployeeTimeSheet', 'SubEmployeeTimeSheetAll', '', 'date_start desc');
  sub.setShowAddNew(false);
  sub.setRemoteTable(true);
  sub.setNeedStartEndTime(need);
  sub.dataPipe = new IceDataPipe(sub);
  modJsList.tabSubEmployeeTimeSheetAll = sub;

  // Time-entry adapter — used for its source mapping (resolves project names in
  // the calendar/entries view's getTimeEntries response).
  const entry = new EmployeeTimeEntryAdapter('EmployeeTimeEntry', 'EmployeeTimeEntry', '', '');
  entry.setShowAddNew(false);
  modJsList.tabEmployeeTimeEntry = entry;

  // The editable project x date hours grid.
  const qt = new QtsheetAdapter('Qtsheet', 'Qtsheet', '', 'name');
  qt.setRemoteTable(false);
  qt.setShowAddNew(false);
  qt.setModulePath('modules=time_sheets');
  qt.setRowFieldName('project');
  qt.setColumnFieldName('date');
  qt.setTables('Project', 'QTDays', 'EmployeeTimeEntry');
  modJsList.tabQtsheet = qt;

  // The React component reads these to decide what to show.
  window.__timesheetsCtx = {
    userLevel: data.user_level,
    advanced: need === 1,
  };

  window.modJs = mine;
  window.modJsList = modJsList;
}

window.initTimeSheets = init;
