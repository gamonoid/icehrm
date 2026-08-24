import React, {
  useEffect, useState, useCallback, useMemo,
} from 'react';
import {
  Card, Table, Button, Tag, Space, Segmented, Spin, Empty, message, Modal, Select,
  Typography, InputNumber, Tooltip, Alert, theme, Avatar, Popconfirm, Timeline, Input, Checkbox,
} from 'antd';
import {
  ReloadOutlined, ArrowLeftOutlined, SaveOutlined, CheckCircleOutlined,
  DownloadOutlined, EditOutlined, StepBackwardOutlined, StepForwardOutlined,
  AuditOutlined, ClockCircleOutlined, CloseCircleOutlined, UserOutlined,
  DeleteOutlined, CalendarOutlined, CoffeeOutlined, HistoryOutlined,
} from '@ant-design/icons';

const { Text, Title } = Typography;

const STATUS_COLORS = {
  Pending: 'orange', Submitted: 'blue', Approved: 'green', Rejected: 'red',
};
// EmployeeLeaves.status values (see the leave module's approval workflow).
const LEAVE_STATUS_COLORS = {
  Approved: 'green',
  Pending: 'orange',
  Processing: 'blue',
  Rejected: 'red',
  'Cancellation Requested': 'gold',
  Cancelled: 'default',
};
const MANAGER_LEVELS = ['Admin', 'Manager', 'Restricted Admin', 'Restricted Manager'];
const MODULE = 'modules=time_sheets';
const DAY_NAMES = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const ctx = () => (typeof window !== 'undefined' && window.__timesheetsCtx) || {};
const adapters = () => (typeof window !== 'undefined' && window.modJsList) || {};

function fmtDate(d) {
  if (!d) return '';
  const dt = new Date(`${String(d).slice(0, 10)}T00:00:00`);
  if (Number.isNaN(dt.getTime())) return d;
  return dt.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}
// Compact date for the leave list: "Mon, Aug 3".
function fmtShortDate(d) {
  if (!d) return '';
  const dt = new Date(`${String(d).slice(0, 10)}T00:00:00`);
  if (Number.isNaN(dt.getTime())) return String(d);
  return dt.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
}
// A leave day is stored as 'Full Day', 'Half Day - Morning', '2 Hours - Afternoon', …
// Anything that is not a full day is a partial day and gets the softer colour.
function isFullDayLeave(type) {
  return /^\s*full day\s*$/i.test(String(type || ''));
}
function parseDT(s) {
  if (!s) return null;
  const dt = new Date(String(s).replace(' ', 'T'));
  return Number.isNaN(dt.getTime()) ? null : dt;
}
function fmtTime(dt) {
  return dt ? dt.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' }) : '';
}
function duration(start, end) {
  const a = parseDT(start); const b = parseDT(end);
  if (!a || !b) return '';
  const min = Math.max(0, Math.round((b - a) / 60000));
  return `${Math.floor(min / 60)}h ${min % 60}m`;
}

// Run an adapter custom action and resolve with the server payload.
let actionSeq = 0;
function callAction(adapter, action, req, isPost = false) {
  return new Promise((resolve, reject) => {
    if (!adapter || typeof adapter.customAction !== 'function') { reject(new Error('no adapter')); return; }
    actionSeq += 1;
    const okName = `__ts_ok_${actionSeq}`;
    const failName = `__ts_fail_${actionSeq}`;
    adapter[okName] = (payload) => resolve(payload);
    adapter[failName] = (payload) => reject(payload || new Error('action failed'));
    const cb = { callBackData: [], callBackSuccess: okName, callBackFail: failName };
    try {
      adapter.customAction(action, MODULE, JSON.stringify(req || {}), cb, isPost);
    } catch (e) { reject(e); }
  });
}

export default function TimeSheets() {
  const { token } = theme.useToken();
  const isManager = MANAGER_LEVELS.indexOf(ctx().userLevel) !== -1;

  const [view, setView] = useState('list'); // 'list' | 'grid' | 'calendar'
  const [tab, setTab] = useState('All');
  const [reportEmp, setReportEmp] = useState(null); // Direct Reports employee filter
  const [reportStatuses, setReportStatuses] = useState(['Submitted']); // visible statuses
  const [selectedReportIds, setSelectedReportIds] = useState([]); // bulk-approve selection
  const [bulkApproving, setBulkApproving] = useState(false);
  const [myRows, setMyRows] = useState(null);
  const [reportRows, setReportRows] = useState(null);
  const [reportLeave, setReportLeave] = useState({}); // { timesheetId: leaveDays }
  const [reportLeaveOn, setReportLeaveOn] = useState(false); // leave module installed?
  const [busyId, setBusyId] = useState(null);
  const [statusModal, setStatusModal] = useState(null);

  const [current, setCurrent] = useState(null); // { id, date_start, date_end, status, readOnly }

  // Grid state
  const [grid, setGrid] = useState(null);
  const [edits, setEdits] = useState({});
  const [leaveDays, setLeaveDays] = useState([]); // [{ date:'Y-m-d', type, half }]
  const [gridLoading, setGridLoading] = useState(false);
  const [saving, setSaving] = useState(false);

  // Calendar / entries state
  const [cal, setCal] = useState(null); // { entries, employee, timesheet }
  const [calLoading, setCalLoading] = useState(false);
  const [tsLogs, setTsLogs] = useState([]); // approval log entries (newest first)
  // Leave requests overlapping the open timesheet, every status:
  // { available, requests: [{ id, leave_type, date_start, date_end, status, details, days, days_total }] }
  const [leaveReq, setLeaveReq] = useState({ available: false, requests: [] });
  const [rejectModal, setRejectModal] = useState(null); // { id, note } | null

  // ---- list loading -------------------------------------------------------
  const loadMine = useCallback(() => {
    const a = adapters().tabEmployeeTimeSheetAll;
    if (!a || !a.dataPipe) { setMyRows([]); return; }
    setMyRows(null);
    a.dataPipe.get({ page: 1, limit: 500, search: '' })
      .then((d) => setMyRows((d && d.items) || [])).catch(() => setMyRows([]));
  }, []);
  const loadReports = useCallback(() => {
    const a = adapters().tabSubEmployeeTimeSheetAll;
    if (!a || !a.dataPipe) { setReportRows([]); return; }
    setReportRows(null);
    a.dataPipe.get({ page: 1, limit: 500, search: '' })
      .then((d) => {
        const items = (d && d.items) || [];
        setReportRows(items);
        // Leave time per timesheet (only when the leave module is installed).
        const ids = items.map((r) => r.id);
        if (ids.length) {
          callAction(a, 'getLeaveDaysCountForTimeSheets', { ids })
            .then((res) => {
              setReportLeaveOn(!!(res && res.available));
              setReportLeave((res && res.counts) || {});
            })
            .catch(() => { setReportLeaveOn(false); setReportLeave({}); });
        } else { setReportLeaveOn(false); setReportLeave({}); }
      })
      .catch(() => setReportRows([]));
  }, []);
  useEffect(() => { loadMine(); if (isManager) loadReports(); }, [loadMine, loadReports, isManager]);

  const reloadList = () => { if (tab === 'Reports') loadReports(); else loadMine(); };
  const backToList = () => { setView('list'); setCurrent(null); setGrid(null); setCal(null); setEdits({}); reloadList(); };

  // ---- grid (Edit) --------------------------------------------------------
  const openGrid = (ts) => {
    const qt = adapters().tabQtsheet;
    if (!qt) return;
    qt.setCurrentTimeSheetId(ts.id);
    setCurrent({ ...ts, readOnly: false });
    setEdits({}); setGrid(null); setLeaveDays([]); setView('grid'); setGridLoading(true);
    callAction(qt, 'getAllData', {
      rowTable: 'Project', columnTable: 'QTDays', valueTable: 'EmployeeTimeEntry', currentId: ts.id, save: 0,
    })
      .then((d) => { setGrid({ projects: d[0] || [], dates: d[1] || [], entries: d[2] || [] }); setGridLoading(false); })
      .catch(() => { setGridLoading(false); message.error('Could not load the timesheet grid', 5); });
    callAction(qt, 'getLeaveDaysForTimeSheet', { id: ts.id })
      .then((d) => setLeaveDays(Array.isArray(d) ? d : [])).catch(() => setLeaveDays([]));
  };

  // ---- calendar / entries (View) -----------------------------------------
  const openCalendar = (ts, isReport) => {
    const a = isReport ? adapters().tabSubEmployeeTimeSheetAll : adapters().tabEmployeeTimeSheetAll;
    const entryAdapter = adapters().tabEmployeeTimeEntry;
    const sm = (entryAdapter && entryAdapter.getSourceMapping) ? JSON.stringify(entryAdapter.getSourceMapping()) : '';
    setCurrent({ ...ts, readOnly: true, isReport: !!isReport });
    setCal(null); setTsLogs([]); setLeaveReq({ available: false, requests: [] });
    setView('calendar'); setCalLoading(true);
    callAction(a, 'getTimeEntries', { id: ts.id, sm })
      .then((d) => { setCal({ entries: d[0] || [], employee: d[1] || {}, timesheet: d[2] || {} }); setCalLoading(false); })
      .catch(() => { setCalLoading(false); message.error('Could not load timesheet entries', 5); });
    callAction(a, 'getTimeSheetLogs', { id: ts.id })
      .then((d) => setTsLogs(Array.isArray(d) ? d : [])).catch(() => setTsLogs([]));
    // Leave the employee has in this period — hidden when the leave module is
    // not installed (available: false).
    callAction(a, 'getLeaveRequestsForTimeSheet', { id: ts.id })
      .then((d) => setLeaveReq({
        available: !!(d && d.available),
        requests: (d && Array.isArray(d.requests)) ? d.requests : [],
      }))
      .catch(() => setLeaveReq({ available: false, requests: [] }));
  };

  const reloadCalendar = () => { if (current) openCalendar(current, current.isReport); };

  // ---- row / status actions ----------------------------------------------
  const rowAction = (adapter, action, id, okMsg) => {
    setBusyId(id);
    callAction(adapter, action, { id }, true)
      .then(() => { message.success(okMsg); loadMine(); })
      .catch((e) => message.error((e && e.message) || `Could not ${action}`, 5))
      .finally(() => setBusyId(null));
  };

  const doBulkApprove = () => {
    const a = adapters().tabSubEmployeeTimeSheetAll || adapters().tabEmployeeTimeSheetAll;
    if (!a || !selectedReportIds.length) return;
    setBulkApproving(true);
    callAction(a, 'bulkApproveTimeSheets', { ids: selectedReportIds }, true)
      .then((res) => {
        const n = (res && res.approved) || 0;
        message.success(`${n} timesheet${n === 1 ? '' : 's'} approved`);
        setSelectedReportIds([]);
        loadReports();
      })
      .catch(() => message.error('Could not approve the selected timesheets', 5))
      .finally(() => setBulkApproving(false));
  };

  const changeStatus = (id, status, after, note) => {
    const a = adapters().tabSubEmployeeTimeSheetAll || adapters().tabEmployeeTimeSheetAll;
    setBusyId(id);
    return callAction(a, 'changeTimeSheetStatus', { id, status, note: note || '' }, true)
      .then(() => { message.success(`Timesheet ${status.toLowerCase()}`); if (after) after(); })
      .catch(() => message.error('Could not change status', 5))
      .finally(() => setBusyId(null));
  };

  const deleteEntry = (entryId) => {
    const e = adapters().tabEmployeeTimeEntry;
    if (!e || typeof e.cleanDelete !== 'function') return;
    e.cleanDelete(entryId, (httpStatus, status) => {
      if (httpStatus === 200 && status === 'SUCCESS') { message.success('Entry deleted'); reloadCalendar(); }
      else message.error('Could not delete entry', 5);
    });
  };

  // ---- grid helpers -------------------------------------------------------
  const realProjects = useMemo(
    () => (grid ? grid.projects.filter((p) => p.id !== -1 && p.id !== '-1') : []), [grid],
  );
  const cellValue = useCallback((projectId, dateId) => {
    const key = `${dateId}=${projectId}`;
    if (key in edits) return edits[key];
    if (!grid) return '';
    const e = grid.entries.find((x) => x.project === projectId && x.date === dateId);
    return (e && e.amount != null) ? e.amount : '';
  }, [edits, grid]);
  const colTotal = useCallback((dateId) => realProjects.reduce((s, p) => {
    const v = parseFloat(cellValue(p.id, dateId)); return s + (Number.isNaN(v) ? 0 : v);
  }, 0), [realProjects, cellValue]);
  const rowTotal = useCallback((projectId) => (grid ? grid.dates.reduce((s, d) => {
    const v = parseFloat(cellValue(projectId, d.id)); return s + (Number.isNaN(v) ? 0 : v);
  }, 0) : 0), [grid, cellValue]);
  const editable = current && !current.readOnly && current.status !== 'Approved';
  const setCell = (projectId, dateId, value) => setEdits((p) => ({ ...p, [`${dateId}=${projectId}`]: value == null ? '' : value }));

  // Map a leave day to a readable label + its per-day hour cap.
  const leaveDayInfo = (ld) => {
    const dt = new Date(`${String(ld.date).slice(0, 10)}T00:00:00`);
    const dateStr = Number.isNaN(dt.getTime())
      ? ld.date : dt.toLocaleDateString(undefined, { weekday: 'short', month: 'short', day: 'numeric' });
    let kind = 'Full Day';
    if (ld.half) {
      const part = /morning/i.test(ld.type) ? 'Morning' : (/after|evening/i.test(ld.type) ? 'Afternoon' : 'Half Day');
      kind = `Half Day · ${part}`;
    }
    return { dateStr, kind, cap: ld.half ? 4 : 0 };
  };

  const persist = (action) => {
    const qt = adapters().tabQtsheet;
    if (!qt || !current) return;
    const over = grid.dates.find((d) => colTotal(d.id) > 24);
    if (over) { message.error(`Total hours for ${over.name} exceed 24.`, 5); return; }
    // On save AND submit, block time logged on approved-leave days (full day:
    // none; half day: up to 4h). The backend enforces the same rule.
    if (leaveDays.length) {
      const violations = leaveDays
        .map((ld) => ({ ld, total: colTotal(ld.date) }))
        .filter(({ ld, total }) => total > (ld.half ? 4 : 0));
      if (violations.length) {
        Modal.error({
          title: 'Time logged on leave days',
          content: (
            <div>
              <p>{`You cannot ${action === 'updateAllData' ? 'submit' : 'save'} — these days have approved leave:`}</p>
              <ul style={{ paddingLeft: 18, margin: 0 }}>
                {violations.map(({ ld, total }) => {
                  const info = leaveDayInfo(ld);
                  return (
                    <li key={ld.date} style={{ marginBottom: 4 }}>
                      <b>{info.dateStr}</b> — {info.kind}: {ld.half
                        ? `max 4h allowed, you logged ${total}h`
                        : `no time allowed, you logged ${total}h`}
                    </li>
                  );
                })}
              </ul>
            </div>
          ),
        });
        return;
      }
    }
    const req = { rowTable: 'Project', columnTable: 'QTDays', valueTable: 'EmployeeTimeEntry', currentId: current.id };
    Object.keys(edits).forEach((key) => {
      const [dateId, projectId] = key.split('=');
      req[key] = [dateId, projectId, `${edits[key] === '' || edits[key] == null ? 0 : edits[key]}`];
    });
    setSaving(true);
    callAction(qt, action, req, true)
      .then(() => {
        message.success(action === 'updateAllData' ? 'Timesheet submitted' : 'Timesheet saved');
        setEdits({});
        const newStatus = action === 'updateAllData' ? 'Submitted' : current.status;
        openGrid({ ...current, status: newStatus });
      })
      .catch(() => message.error('Could not save the timesheet', 5))
      .finally(() => setSaving(false));
  };

  const download = () => {
    if (!grid) return;
    const header = ['Project', ...grid.dates.map((d) => d.name), 'Total'];
    const lines = [header.join(',')];
    realProjects.forEach((p) => lines.push([JSON.stringify(p.name), ...grid.dates.map((d) => cellValue(p.id, d.id) || 0), rowTotal(p.id)].join(',')));
    lines.push(['Total', ...grid.dates.map((d) => colTotal(d.id)), ''].join(','));
    const a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([lines.join('\r\n')], { type: 'text/csv;charset=utf-8' }));
    a.download = `timesheet_${current.id}.csv`; a.click(); URL.revokeObjectURL(a.href);
  };

  const statusTag = (s) => <Tag color={STATUS_COLORS[s] || 'default'}>{s}</Tag>;

  // ---- list view ----------------------------------------------------------
  const renderList = () => {
    const reports = tab === 'Reports';
    const all = reports ? reportRows : myRows;
    if (all === null) return <div style={{ textAlign: 'center', padding: 60 }}><Spin size="large" /></div>;
    let rows = reports ? all : (tab === 'All' ? all : all.filter((r) => r.status === tab));
    if (reports && reportEmp) rows = rows.filter((r) => r.employee === reportEmp);
    if (reports) rows = rows.filter((r) => reportStatuses.indexOf(r.status) !== -1);
    const columns = [
      ...(reports ? [{ title: 'Employee', dataIndex: 'employee', key: 'employee' }] : []),
      { title: 'Period', key: 'period', render: (_, r) => <Text strong>{`${fmtDate(r.date_start)} – ${fmtDate(r.date_end)}`}</Text> },
      { title: 'Total Time', dataIndex: 'total_time', key: 'total_time', render: (t) => <Space size={4}><ClockCircleOutlined />{t || '00:00'}</Space> },
      ...(reports && reportLeaveOn ? [{
        title: 'Leave Time',
        key: 'leave_time',
        render: (_, r) => {
          const d = reportLeave[r.id] || 0;
          return d > 0
            ? <Tag color="gold" style={{ margin: 0 }}>{`${d} day${d === 1 ? '' : 's'}`}</Tag>
            : <Text type="secondary">—</Text>;
        },
      }] : []),
      { title: 'Status', dataIndex: 'status', key: 'status', render: statusTag },
      {
        title: 'Actions',
        key: 'actions',
        // Clicks on action buttons must not trigger the row's view action.
        render: (_, r) => (
          <Space wrap onClick={(e) => e.stopPropagation()}>
            {!reports && (
              <Tooltip title="Edit hours grid">
                <Button size="small" icon={<EditOutlined />} onClick={() => openGrid(r)} />
              </Tooltip>
            )}
            {reports && (
              <Button size="small" icon={<AuditOutlined />} onClick={() => setStatusModal({ id: r.id, value: 'Approved' })}>Status</Button>
            )}
            {!reports && (
              <>
                <Tooltip title="Create the previous week's timesheet">
                  <Button size="small" icon={<StepBackwardOutlined />} loading={busyId === r.id} onClick={() => rowAction(adapters().tabEmployeeTimeSheetAll, 'createPreviousTimesheet', r.id, 'Previous timesheet created')}>Previous Week</Button>
                </Tooltip>
                <Tooltip title="Create the next week's timesheet">
                  <Button size="small" icon={<StepForwardOutlined />} loading={busyId === r.id} onClick={() => rowAction(adapters().tabEmployeeTimeSheetAll, 'createNextWeekTimesheet', r.id, 'Next timesheet created')}>Next Week</Button>
                </Tooltip>
              </>
            )}
          </Space>
        ),
      },
    ];
    return (
      <>
        {reports && (
          <div style={{
            display: 'flex', alignItems: 'center', flexWrap: 'wrap', gap: 8, marginBottom: 14,
          }}
          >
            <Text type="secondary" style={{ marginRight: 4 }}>Show status:</Text>
            <Checkbox.Group
              value={reportStatuses}
              onChange={setReportStatuses}
              options={['Pending', 'Submitted', 'Approved', 'Rejected'].map((s) => ({ label: s, value: s }))}
            />
            {selectedReportIds.length > 0 && (
              <Button
                type="primary"
                icon={<CheckCircleOutlined />}
                loading={bulkApproving}
                style={{ marginLeft: 'auto' }}
                onClick={doBulkApprove}
              >
                {`Approve ${selectedReportIds.length} selected`}
              </Button>
            )}
          </div>
        )}
        <Table
          rowKey="id" size="middle" columns={columns} dataSource={rows}
          pagination={{ pageSize: 10, hideOnSinglePage: true }} scroll={{ x: 'max-content' }}
          onRow={(r) => ({ onClick: () => openCalendar(r, reports), style: { cursor: 'pointer' } })}
          locale={{ emptyText: <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="No timesheets" /> }}
          {...(reports ? {
            rowSelection: {
              selectedRowKeys: selectedReportIds,
              onChange: setSelectedReportIds,
              // Only Submitted timesheets are approvable in bulk.
              getCheckboxProps: (r) => ({ disabled: r.status !== 'Submitted' }),
            },
          } : {})}
        />
      </>
    );
  };

  // ---- grid view ----------------------------------------------------------
  const renderGrid = () => {
    if (gridLoading || !grid) return <div style={{ textAlign: 'center', padding: 60 }}><Spin size="large" /></div>;
    const columns = [
      { title: 'Project', dataIndex: 'name', key: 'name', fixed: 'left', width: 200, render: (n) => <Text strong>{n}</Text> },
      ...grid.dates.map((d) => ({
        title: d.name, key: d.id, align: 'center', width: 96,
        render: (_, p) => {
          const ed = editable && d.editable !== 'No';
          const val = cellValue(p.id, d.id);
          if (!ed) return <span>{val === '' || val == null ? '–' : val}</span>;
          return (
            <InputNumber size="small" min={0} max={24} step={0.5} controls={false}
              value={val === '' ? null : Number(val)} onChange={(v) => setCell(p.id, d.id, v)} style={{ width: 70 }} />
          );
        },
      })),
      { title: 'Total', key: 'rowtotal', align: 'center', fixed: 'right', width: 80, render: (_, p) => <Text strong>{rowTotal(p.id).toFixed(2).replace(/\.00$/, '')}</Text> },
    ];
    return (
      <>
        <Table
          rowKey="id" size="small" bordered columns={columns} dataSource={realProjects}
          pagination={false} scroll={{ x: 'max-content' }}
          locale={{ emptyText: <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="No projects assigned" /> }}
          summary={() => (
            <Table.Summary fixed>
              <Table.Summary.Row>
                <Table.Summary.Cell index={0}><Text strong>Total</Text></Table.Summary.Cell>
                {grid.dates.map((d, i) => {
                  const t = colTotal(d.id);
                  return <Table.Summary.Cell key={d.id} index={i + 1} align="center"><Text strong style={{ color: t > 24 ? token.colorError : undefined }}>{t.toFixed(2).replace(/\.00$/, '')}</Text></Table.Summary.Cell>;
                })}
                <Table.Summary.Cell index={grid.dates.length + 1} align="center"><Text strong>{realProjects.reduce((s, p) => s + rowTotal(p.id), 0).toFixed(2).replace(/\.00$/, '')}</Text></Table.Summary.Cell>
              </Table.Summary.Row>
            </Table.Summary>
          )}
        />
        {leaveDays.length > 0 && (
          <Alert
            type="warning"
            showIcon
            icon={<CoffeeOutlined />}
            style={{ marginTop: 16, borderRadius: 10 }}
            message={<Text strong>You have approved leave this week</Text>}
            description={(
              <div>
                <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8, margin: '8px 0' }}>
                  {leaveDays.map((ld) => {
                    const info = leaveDayInfo(ld);
                    return (
                      <Tag
                        key={ld.date}
                        color={ld.half ? 'gold' : 'volcano'}
                        icon={<CalendarOutlined />}
                        style={{ padding: '3px 12px', borderRadius: 14, fontSize: 13, margin: 0 }}
                      >
                        <b>{info.dateStr}</b>
                        {' · '}
                        {info.kind}
                      </Tag>
                    );
                  })}
                </div>
                <Text type="secondary" style={{ fontSize: 12.5 }}>
                  No time can be logged on a full-day leave; up to 4 hours on a half-day leave.
                </Text>
              </div>
            )}
          />
        )}
      </>
    );
  };

  // ---- calendar / entries view -------------------------------------------
  const fmtHM = (min) => (min > 0
    ? `${Math.floor(min / 60)}h${min % 60 ? ` ${min % 60}m` : ''}` : '0h');
  const entryMin = (e) => {
    const a = parseDT(e.date_start); const b = parseDT(e.date_end);
    return (a && b) ? Math.max(0, Math.round((b - a) / 60000)) : 0;
  };

  const renderCalendar = () => {
    if (calLoading || !cal) return <div style={{ textAlign: 'center', padding: 60 }}><Spin size="large" /></div>;
    const start = parseDT(current.date_start);
    const todayKey = new Date().toISOString().slice(0, 10);
    const days = Array.from({ length: 7 }, (_, i) => {
      const d = new Date(start); d.setDate(start.getDate() + i);
      const key = d.toISOString().slice(0, 10);
      return {
        key,
        dayName: DAY_NAMES[d.getDay()],
        dayNum: d.getDate(),
        mon: d.toLocaleDateString(undefined, { month: 'short' }),
        isToday: key === todayKey,
        isWeekend: d.getDay() === 0 || d.getDay() === 6,
        entries: [],
        minutes: 0,
      };
    });
    let totalMin = 0;
    cal.entries.forEach((e) => {
      const k = String(e.date_start || '').slice(0, 10);
      const min = entryMin(e);
      const day = days.find((dd) => dd.key === k);
      if (day) { day.entries.push(e); day.minutes += min; }
      totalMin += min;
    });
    const daysWorked = days.filter((d) => d.minutes > 0).length;
    const entryCount = cal.entries.length;
    const canDelete = !current.isReport && current.status !== 'Approved';

    const Stat = ({ icon, label, value, accent }) => (
      <div style={{
        flex: '1 1 130px', minWidth: 130, background: token.colorFillQuaternary,
        borderRadius: 12, padding: '12px 16px',
      }}
      >
        <div style={{ fontSize: 12, color: token.colorTextSecondary, marginBottom: 3 }}>{label}</div>
        <div style={{
          fontSize: 22, fontWeight: 700, lineHeight: 1.1,
          color: accent || token.colorText, display: 'flex', alignItems: 'center', gap: 7,
        }}
        >
          {icon}
          {value}
        </div>
      </div>
    );

    return (
      <>
        {/* Summary */}
        <div style={{ display: 'flex', gap: 12, flexWrap: 'wrap', marginBottom: 18 }}>
          <Stat icon={<ClockCircleOutlined />} label="Total logged" value={fmtHM(totalMin)} accent={token.colorPrimary} />
          <Stat label="Entries" value={entryCount} />
          <Stat label="Days worked" value={`${daysWorked} / 7`} />
          <Stat label="Avg / working day" value={daysWorked ? fmtHM(Math.round(totalMin / daysWorked)) : '0h'} />
        </div>

        {/* Week calendar */}
        <div style={{ overflowX: 'auto' }}>
          <div style={{
            display: 'flex', minWidth: 760, borderRadius: 12, overflow: 'hidden',
            border: `1px solid ${token.colorBorderSecondary}`,
          }}
          >
            {days.map((d, i) => (
              <div
                key={d.key}
                style={{
                  flex: 1, minWidth: 106,
                  borderRight: i < 6 ? `1px solid ${token.colorBorderSecondary}` : 'none',
                  background: d.isWeekend ? token.colorFillQuaternary : token.colorBgContainer,
                }}
              >
                <div style={{
                  textAlign: 'center', padding: '8px 4px',
                  background: d.isToday ? token.colorPrimary : token.colorFillSecondary,
                  color: d.isToday ? '#fff' : token.colorText,
                  borderBottom: `1px solid ${token.colorBorderSecondary}`,
                }}
                >
                  <div style={{ fontSize: 10.5, textTransform: 'uppercase', letterSpacing: 0.6, opacity: 0.85 }}>{d.dayName}</div>
                  <div style={{ fontSize: 17, fontWeight: 700, lineHeight: 1.15 }}>{d.dayNum}</div>
                  <div style={{ fontSize: 10.5, opacity: 0.8 }}>{d.mon}</div>
                </div>
                <div style={{
                  textAlign: 'center', padding: '4px 0', fontSize: 12, fontWeight: 700,
                  color: d.minutes > 0 ? token.colorPrimary : token.colorTextQuaternary,
                  borderBottom: `1px dashed ${token.colorBorderSecondary}`,
                }}
                >
                  {d.minutes > 0 ? fmtHM(d.minutes) : '—'}
                </div>
                <div style={{ minHeight: 78, padding: '6px', display: 'flex', flexDirection: 'column', gap: 5 }}>
                  {d.entries.map((e) => (
                    <div
                      key={e.id}
                      style={{
                        background: token.colorPrimaryBg, border: `1px solid ${token.colorPrimaryBorder}`,
                        borderRadius: 7, padding: '4px 7px', fontSize: 11.5,
                      }}
                    >
                      <div style={{ fontWeight: 600, display: 'flex', justifyContent: 'space-between', gap: 4 }}>
                        <span>{fmtTime(parseDT(e.date_start))}</span>
                        <span style={{ color: token.colorPrimary }}>{fmtHM(entryMin(e))}</span>
                      </div>
                      <div style={{
                        color: token.colorTextSecondary, whiteSpace: 'nowrap',
                        overflow: 'hidden', textOverflow: 'ellipsis',
                      }}
                      >
                        {e.project && e.project !== 'null' ? e.project : 'No project'}
                      </div>
                    </div>
                  ))}
                  {d.entries.length === 0 && (
                    <div style={{ textAlign: 'center', color: token.colorTextQuaternary, fontSize: 11, paddingTop: 6 }}>—</div>
                  )}
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Leave taken during this timesheet period */}
        {leaveReq.available && leaveReq.requests.length > 0 && (
          <div style={{ marginTop: 22 }}>
            <Text strong style={{ display: 'block', marginBottom: 8 }}>
              <CoffeeOutlined style={{ marginRight: 6 }} />
              {`Leave in this period (${leaveReq.requests.length})`}
            </Text>
            <Table
              rowKey="id" size="small" pagination={false} scroll={{ x: 'max-content' }}
              dataSource={leaveReq.requests}
              columns={[
                {
                  title: 'Leave Type',
                  key: 'type',
                  render: (_, r) => (
                    <div>
                      <div style={{ fontWeight: 600 }}>{r.leave_type || 'Leave'}</div>
                      {r.details && String(r.details).trim() ? (
                        <Text type="secondary" style={{ fontSize: 12 }}>{String(r.details).trim()}</Text>
                      ) : null}
                    </div>
                  ),
                },
                {
                  title: 'Requested',
                  key: 'range',
                  render: (_, r) => (r.date_start === r.date_end
                    ? fmtShortDate(r.date_start)
                    : `${fmtShortDate(r.date_start)} – ${fmtShortDate(r.date_end)}`),
                },
                {
                  title: 'Days in this period',
                  key: 'days',
                  render: (_, r) => {
                    const days = Array.isArray(r.days) ? r.days : [];
                    if (!days.length) return <Text type="secondary">—</Text>;
                    return (
                      <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                        {days.map((d) => (
                          <Tag
                            key={`${r.id}-${d.date}`}
                            color={isFullDayLeave(d.type) ? 'volcano' : 'gold'}
                            icon={<CalendarOutlined />}
                            style={{ margin: 0, borderRadius: 12, padding: '1px 10px' }}
                          >
                            <b>{fmtShortDate(d.date)}</b>
                            {' · '}
                            {d.type}
                          </Tag>
                        ))}
                      </div>
                    );
                  },
                },
                {
                  title: 'Total',
                  key: 'total',
                  align: 'center',
                  render: (_, r) => {
                    const t = Number(r.days_total) || 0;
                    return t ? `${t} day${t === 1 ? '' : 's'}` : <Text type="secondary">—</Text>;
                  },
                },
                {
                  title: 'Status',
                  key: 'status',
                  render: (_, r) => (
                    <Tag color={LEAVE_STATUS_COLORS[r.status] || 'default'} style={{ margin: 0 }}>
                      {r.status}
                    </Tag>
                  ),
                },
              ]}
            />
          </div>
        )}

        {/* Entries table */}
        <Text strong style={{ display: 'block', margin: '20px 0 8px' }}>
          {`All entries (${entryCount})`}
        </Text>
        <Table
          rowKey="id" size="small"
          dataSource={cal.entries} pagination={false}
          locale={{ emptyText: <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="No time entries" /> }}
          columns={[
            { title: 'Start', key: 'start', render: (_, e) => { const d = parseDT(e.date_start); return d ? `${d.toLocaleDateString()} ${fmtTime(d)}` : '-'; } },
            { title: 'End', key: 'end', render: (_, e) => { const d = parseDT(e.date_end); return d ? `${d.toLocaleDateString()} ${fmtTime(d)}` : '-'; } },
            { title: 'Duration', key: 'dur', render: (_, e) => <Tag color="blue" style={{ margin: 0 }}>{fmtHM(entryMin(e))}</Tag> },
            { title: 'Project', key: 'project', render: (_, e) => (e.project && e.project !== 'null' ? e.project : <Text type="secondary">No project</Text>) },
            { title: 'Details', dataIndex: 'details', key: 'details', ellipsis: true, render: (t) => t || <Text type="secondary">—</Text> },
            ...(canDelete ? [{
              title: '', key: 'del', width: 50,
              render: (_, e) => (
                <Popconfirm title="Delete this entry?" onConfirm={() => deleteEntry(e.id)} okText="Delete" okButtonProps={{ danger: true }}>
                  <Button size="small" type="text" danger icon={<DeleteOutlined />} />
                </Popconfirm>
              ),
            }] : []),
          ]}
        />

        {/* Approval log */}
        {tsLogs.length > 0 && (
          <div style={{ marginTop: 24 }}>
            <Text strong style={{ display: 'block', marginBottom: 12 }}>
              <HistoryOutlined style={{ marginRight: 6 }} />
              Approval Log
            </Text>
            <Timeline
              items={tsLogs.map((l, i) => ({
                color: l.status_to === 'Approved' ? 'green'
                  : (l.status_to === 'Rejected' ? 'red' : 'blue'),
                key: i,
                children: (
                  <div>
                    <div style={{ fontWeight: 600 }}>{`${l.status_from || ''} → ${l.status_to || ''}`}</div>
                    <div style={{ color: token.colorTextSecondary, fontSize: 12 }}>{l.time}</div>
                    {l.note && l.note.trim() ? (
                      <div style={{ marginTop: 2 }}>{l.note.trim()}</div>
                    ) : null}
                  </div>
                ),
              }))}
            />
          </div>
        )}
      </>
    );
  };

  // ---- top-level ----------------------------------------------------------
  if (view === 'grid' && current) {
    const canSubmit = editable && current.status !== 'Submitted';
    return (
      <div style={{ padding: 8 }}>
        <Card
          style={{ borderRadius: 14 }}
          title={(
            <Space wrap>
              <Button icon={<ArrowLeftOutlined />} onClick={backToList}>Back</Button>
              <Title level={5} style={{ margin: 0 }}>{`${fmtDate(current.date_start)} – ${fmtDate(current.date_end)}`}</Title>
              {statusTag(current.status)}
            </Space>
          )}
          extra={(
            <Space wrap>
              <Button icon={<DownloadOutlined />} onClick={download}>Download</Button>
              {editable && <Button icon={<SaveOutlined />} loading={saving} onClick={() => persist('updateData')}>Save</Button>}
              {canSubmit && <Button type="primary" icon={<CheckCircleOutlined />} loading={saving} onClick={() => persist('updateAllData')}>Submit</Button>}
            </Space>
          )}
        >
          {renderGrid()}
        </Card>
      </div>
    );
  }

  if (view === 'calendar' && current) {
    const emp = (cal && cal.employee) || {};
    const ts = (cal && cal.timesheet) || {};
    const isReport = current.isReport;
    return (
      <div style={{ padding: 8 }}>
        <Card
          style={{ borderRadius: 14 }}
          styles={{ header: { paddingTop: 12, paddingBottom: 12 } }}
          title={(
            <Space wrap size="middle">
              <Button icon={<ArrowLeftOutlined />} onClick={backToList}>Back</Button>
              <Avatar size={40} src={emp.image} icon={<UserOutlined />} />
              <div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <Title level={5} style={{ margin: 0 }}>{emp.name || 'My Timesheet'}</Title>
                  {statusTag(ts.status || current.status)}
                </div>
                <Text type="secondary" style={{ fontSize: 12.5, fontWeight: 400 }}>
                  {`${fmtDate(current.date_start)} – ${fmtDate(current.date_end)}`}
                </Text>
              </div>
            </Space>
          )}
          extra={isReport ? (
            <Space wrap>
              <Button type="primary" icon={<CheckCircleOutlined />} loading={busyId === current.id}
                onClick={() => changeStatus(current.id, 'Approved', backToList)}>Approve</Button>
              <Button danger icon={<CloseCircleOutlined />} loading={busyId === current.id}
                onClick={() => setRejectModal({ id: current.id, note: '' })}>Reject</Button>
            </Space>
          ) : null}
        >
          {renderCalendar()}
        </Card>

        <Modal
          title="Reject Timesheet"
          open={!!rejectModal}
          okText="Reject"
          okButtonProps={{ danger: true }}
          confirmLoading={busyId === (rejectModal && rejectModal.id)}
          onOk={() => {
            const rm = rejectModal;
            setRejectModal(null);
            changeStatus(rm.id, 'Rejected', backToList, rm.note);
          }}
          onCancel={() => setRejectModal(null)}
        >
          <div style={{ marginBottom: 6, color: token.colorTextSecondary }}>Note (optional)</div>
          <Input.TextArea
            rows={3}
            value={rejectModal ? rejectModal.note : ''}
            onChange={(e) => setRejectModal((m) => ({ ...m, note: e.target.value }))}
            placeholder="Add a note explaining why this timesheet is rejected…"
          />
        </Modal>
      </div>
    );
  }

  const segOptions = [
    { label: 'All My Timesheets', value: 'All' },
    { label: 'Approved', value: 'Approved' },
    { label: 'Pending', value: 'Pending' },
    { label: 'Rejected', value: 'Rejected' },
    ...(isManager ? [{ label: 'Direct Reports - Time Sheets', value: 'Reports' }] : []),
  ];
  const reportEmployees = Array.from(new Set((reportRows || []).map((r) => r.employee).filter(Boolean))).sort();
  return (
    <div style={{ padding: 8 }}>
      <Card style={{ borderRadius: 14 }} styles={{ body: { paddingTop: 16 } }}>
        <Space style={{ width: '100%', justifyContent: 'space-between', marginBottom: 16 }} wrap>
          <div style={{ overflowX: 'auto' }}><Segmented options={segOptions} value={tab} onChange={(t) => { setSelectedReportIds([]); setTab(t); }} /></div>
          <Space wrap>
            {tab === 'Reports' && (
              <Select
                allowClear
                showSearch
                placeholder="All employees"
                style={{ minWidth: 200 }}
                value={reportEmp || undefined}
                onChange={(v) => setReportEmp(v || null)}
                options={reportEmployees.map((e) => ({ value: e, label: e }))}
              />
            )}
            <Button icon={<ReloadOutlined />} onClick={reloadList}>Refresh</Button>
          </Space>
        </Space>
        {renderList()}
      </Card>

      <Modal
        title="Change Timesheet Status" open={!!statusModal}
        onCancel={() => setStatusModal(null)}
        onOk={() => changeStatus(statusModal.id, statusModal.value, () => { setStatusModal(null); loadReports(); }, statusModal && statusModal.note)}
        confirmLoading={busyId === (statusModal && statusModal.id)} okText="Change Status"
      >
        <div style={{ marginBottom: 8 }}><Text type="secondary">Timesheet Status</Text></div>
        <Select style={{ width: '100%' }} value={statusModal && statusModal.value}
          onChange={(v) => setStatusModal((m) => ({ ...m, value: v }))}
          options={['Approved', 'Pending', 'Rejected', 'Submitted'].map((s) => ({ value: s, label: s }))} />
        {statusModal && statusModal.value === 'Rejected' && (
          <div style={{ marginTop: 14 }}>
            <div style={{ marginBottom: 6 }}><Text type="secondary">Note (optional)</Text></div>
            <Input.TextArea
              rows={3}
              value={statusModal.note || ''}
              onChange={(e) => setStatusModal((m) => ({ ...m, note: e.target.value }))}
              placeholder="Add a note explaining why this timesheet is rejected…"
            />
          </div>
        )}
      </Modal>
    </div>
  );
}
