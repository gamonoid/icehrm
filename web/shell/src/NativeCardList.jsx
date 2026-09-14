import React, {
  useCallback, useEffect, useMemo, useRef, useState,
} from 'react';
import {
  Card, Tag, Button, Input, Pagination, Empty, Spin, Tooltip, theme, Modal, message, Avatar,
  Select, Timeline, Checkbox, Progress,
} from 'antd';
import {
  PlusOutlined, EditOutlined, DeleteOutlined, CopyOutlined, EyeOutlined,
  FilterOutlined, FileOutlined, AppstoreOutlined, IdcardOutlined, DollarOutlined,
  ReadOutlined, ProjectOutlined, TeamOutlined, UserOutlined, AuditOutlined, UsergroupAddOutlined,
  MailOutlined, ProfileOutlined, BankOutlined, ApartmentOutlined, ClockCircleOutlined,
  SolutionOutlined, FileTextOutlined, CalendarOutlined,
  LoginOutlined, UserDeleteOutlined, CheckCircleOutlined, DownloadOutlined, UploadOutlined, ContactsOutlined,
  FormOutlined, ExportOutlined, MonitorOutlined, CloseCircleOutlined, HistoryOutlined,
  FieldTimeOutlined, KeyOutlined, TagsOutlined, ScheduleOutlined, CarryOutOutlined, GiftOutlined,
  InfoCircleOutlined, SettingOutlined, SlidersOutlined, SendOutlined, LinkOutlined, RedoOutlined,
  GlobalOutlined, FilePdfOutlined,
} from '@ant-design/icons';
import { MUI_SHADOW } from './theme';
import ExpenseDialog from './ExpenseDialog';

// Icon name (from declarative extension card config) -> component.
const ICON_BY_NAME = {
  file: <FileOutlined />,
  form: <FormOutlined />,
  export: <ExportOutlined />,
  monitor: <MonitorOutlined />,
};
const iconByName = (name) => ICON_BY_NAME[name] || <FileOutlined />;

// Lighten a hex colour toward white by `amt` (0..1) so the saturated action-icon
// hues (tuned for light mode) stay legible on the dark sidebar/cards.
function lighten(hex, amt) {
  const m = /^#?([0-9a-fA-F]{6})$/.exec(hex || '');
  if (!m) return hex;
  const n = parseInt(m[1], 16);
  let r = (n >> 16) & 255; let g = (n >> 8) & 255; let b = n & 255;
  r = Math.round(r + (255 - r) * amt);
  g = Math.round(g + (255 - g) * amt);
  b = Math.round(b + (255 - b) * amt);
  return `rgb(${r}, ${g}, ${b})`;
}

const PAGE_SIZE = 8;

// Lazy, idempotent loader for an extension bundle that exposes a native
// document-mount function (e.g. the editor's window.mountEditorDocument). A
// per-session cache-bust keeps it fresh across hard reloads without re-fetching
// the (large) bundle on every modal open.
const NATIVE_CB = String(Date.now());
const nativeBundles = {};
function loadNativeBundle(url) {
  if (nativeBundles[url]) return nativeBundles[url];
  const p = new Promise((resolve, reject) => {
    const s = document.createElement('script');
    s.src = `${url}${url.indexOf('?') >= 0 ? '&' : '?'}cb=${NATIVE_CB}`;
    s.async = false;
    s.onload = () => resolve();
    s.onerror = () => { s.remove(); reject(new Error(`Failed to load ${url}`)); };
    document.head.appendChild(s);
  });
  // Evict on failure so the next open retries instead of replaying the rejection.
  nativeBundles[url] = p.catch((e) => { delete nativeBundles[url]; throw e; });
  return nativeBundles[url];
}

// Injected into a same-origin document-editor iframe to hide the legacy page
// chrome (top bar + sidebar) so only the editor content shows.
const EMBED_CSS = `
  header.header { display: none !important; }
  aside.left-side, .sidebar-offcanvas, .skeletonSideMenu { display: none !important; }
  .right-side { margin-left: 0 !important; left: 0 !important; }
  .wrapper, body, html { padding-top: 0 !important; margin-top: 0 !important; background: #fff !important; }
  body { min-width: 0 !important; }
  #DemoModeNotice, #IceHrmConnectionNotice { display: none !important; }
`;

// A representative icon + accent colour per entity (falls back to a generic one).
const ENTITY_STYLE = {
  JobTitle: { icon: <IdcardOutlined />, color: '#1976d2' },
  PayGrade: { icon: <DollarOutlined />, color: '#2e7d32' },
  EmploymentStatus: { icon: <ProfileOutlined />, color: '#7b1fa2' },
  Skill: { icon: <ReadOutlined />, color: '#1976d2' },
  Education: { icon: <ReadOutlined />, color: '#0288d1' },
  Certification: { icon: <ReadOutlined />, color: '#7b1fa2' },
  Language: { icon: <ReadOutlined />, color: '#ed6c02' },
  Project: { icon: <ProjectOutlined />, color: '#1976d2' },
  EmployeeProject: { icon: <TeamOutlined />, color: '#2e7d32' },
  Client: { icon: <BankOutlined />, color: '#7b1fa2' },
  CustomField: { icon: <AppstoreOutlined />, color: '#1976d2' },
  Audit: { icon: <AuditOutlined />, color: '#546e7a' },
  EmailLogEntry: { icon: <MailOutlined />, color: '#0288d1' },
  Employee: { icon: <UserOutlined />, color: '#1976d2' },
  EmployeeCareer: { icon: <SolutionOutlined />, color: '#0288d1' },
  EmployeeSkill: { icon: <ReadOutlined />, color: '#1976d2' },
  EmployeeEducation: { icon: <ReadOutlined />, color: '#0288d1' },
  EmployeeCertification: { icon: <ReadOutlined />, color: '#7b1fa2' },
  EmployeeLanguage: { icon: <ReadOutlined />, color: '#ed6c02' },
  EmployeeDependent: { icon: <TeamOutlined />, color: '#2e7d32' },
  EmergencyContact: { icon: <ContactsOutlined />, color: '#d32f2f' },
  TerminatedEmployee: { icon: <UserDeleteOutlined />, color: '#ed6c02' },
  ArchivedEmployee: { icon: <UserOutlined />, color: '#546e7a' },
  EmployeeDataHistory: { icon: <ProfileOutlined />, color: '#0288d1' },
  MyAttendance: { icon: <ClockCircleOutlined />, color: '#0288d1' },
  OvertimeCategory: { icon: <FieldTimeOutlined />, color: '#ed6c02' },
  MyOvertime: { icon: <ClockCircleOutlined />, color: '#ed6c02' },
  EmployeeOvertime: { icon: <ClockCircleOutlined />, color: '#ed6c02' },
  EmployeeOvertimeApproval: { icon: <ClockCircleOutlined />, color: '#7b1fa2' },
  User: { icon: <UserOutlined />, color: '#1976d2' },
  UserRole: { icon: <TeamOutlined />, color: '#7b1fa2' },
  UserInvitation: { icon: <MailOutlined />, color: '#0288d1' },
  LeaveType: { icon: <TagsOutlined />, color: '#ed6c02' },
  LeavePeriod: { icon: <ScheduleOutlined />, color: '#2e7d32' },
  WorkDay: { icon: <CarryOutOutlined />, color: '#0288d1' },
  HoliDay: { icon: <GiftOutlined />, color: '#7b1fa2' },
  LeaveStartingBalance: { icon: <SlidersOutlined />, color: '#0288d1' },
  LeaveGroup: { icon: <TeamOutlined />, color: '#1976d2' },
  EmployeeLeave: { icon: <SolutionOutlined />, color: '#ed6c02' },
  // User leaves (modules::leaves)
  MyLeave: { icon: <CalendarOutlined />, color: '#1976d2' },
  MyLeaveApproved: { icon: <CalendarOutlined />, color: '#2e7d32' },
  SubLeave: { icon: <SolutionOutlined />, color: '#ed6c02' },
  LeaveApproval: { icon: <SolutionOutlined />, color: '#7b1fa2' },
  // Expenses extension
  ExpensesCategory: { icon: <TagsOutlined />, color: '#7b1fa2' },
  ExpensesPaymentMethod: { icon: <DollarOutlined />, color: '#2e7d32' },
  EmployeeExpense: { icon: <DollarOutlined />, color: '#1976d2' },
  MyExpense: { icon: <DollarOutlined />, color: '#1976d2' },
  SubExpense: { icon: <DollarOutlined />, color: '#ed6c02' },
  ExpenseApproval: { icon: <DollarOutlined />, color: '#7b1fa2' },
  // Job positions (extension::jobpositions|admin)
  Job: { icon: <AuditOutlined />, color: '#1976d2' },
  // Candidates (extension::candidates|admin)
  Candidate: { icon: <UsergroupAddOutlined />, color: '#7b1fa2' },
  // Recruitment setup (extension::jobsetup|admin)
  EmployementType: { icon: <IdcardOutlined />, color: '#1976d2' },
  ExperienceLevel: { icon: <SlidersOutlined />, color: '#ed6c02' },
  JobFunction: { icon: <SolutionOutlined />, color: '#7b1fa2' },
  EducationLevel: { icon: <ReadOutlined />, color: '#2e7d32' },
  Benifit: { icon: <GiftOutlined />, color: '#d81b60' },
  // System (admin::settings / admin::modules / admin::permissions)
  Setting: { icon: <SettingOutlined />, color: '#546e7a' },
  Module: { icon: <AppstoreOutlined />, color: '#1976d2' },
  Permission: { icon: <KeyOutlined />, color: '#7b1fa2' },
  // Metadata (admin::metadata) — master data lookups.
  Country: { icon: <GlobalOutlined />, color: '#1976d2' },
  Province: { icon: <ApartmentOutlined />, color: '#0288d1' },
  CurrencyType: { icon: <DollarOutlined />, color: '#2e7d32' },
  Nationality: { icon: <IdcardOutlined />, color: '#7b1fa2' },
  Ethnicity: { icon: <TeamOutlined />, color: '#ed6c02' },
  ImmigrationStatus: { icon: <SolutionOutlined />, color: '#d81b60' },
  // Documents (admin::documents / modules::documents)
  CompanyDocument: { icon: <FileTextOutlined />, color: '#1976d2' },
  Document: { icon: <TagsOutlined />, color: '#7b1fa2' },
  EmployeeDocument: { icon: <FileOutlined />, color: '#0288d1' },
  PayslipDocument: { icon: <DollarOutlined />, color: '#2e7d32' },
  MyDocument: { icon: <FileOutlined />, color: '#0288d1' },
  MyCompanyDocument: { icon: <FileTextOutlined />, color: '#1976d2' },
  MyPayslip: { icon: <DollarOutlined />, color: '#2e7d32' },
  // Training (modules::training)
  TrainingSessionWithCourse: { icon: <ReadOutlined />, color: '#1976d2' },
  EmployeeTrainingSession: { icon: <ReadOutlined />, color: '#2e7d32' },
  SubEmployeeTraining: { icon: <ReadOutlined />, color: '#ed6c02' },
  CoordinatedTrainingSession: { icon: <ScheduleOutlined />, color: '#7b1fa2' },
  // Performance (admin::performance / modules::performance)
  PerformanceReview: { icon: <SolutionOutlined />, color: '#7b1fa2' },
  ReviewFeedback: { icon: <FormOutlined />, color: '#0288d1' },
  ReviewTemplate: { icon: <ProfileOutlined />, color: '#ed6c02' },
  EmployeeGoal: { icon: <CheckCircleOutlined />, color: '#2e7d32' },
  MyPerformanceReview: { icon: <SolutionOutlined />, color: '#1976d2' },
  MyReviewFeedback: { icon: <FormOutlined />, color: '#ed6c02' },
  // Salary (admin::salary)
  SalaryComponentType: { icon: <TagsOutlined />, color: '#7b1fa2' },
  SalaryComponent: { icon: <AppstoreOutlined />, color: '#0288d1' },
  EmployeeSalary: { icon: <DollarOutlined />, color: '#2e7d32' },
  // Loans (admin::loans / modules::loans)
  CompanyLoan: { icon: <BankOutlined />, color: '#2e7d32' },
  EmployeeCompanyLoan: { icon: <DollarOutlined />, color: '#1976d2' },
  MyLoan: { icon: <DollarOutlined />, color: '#1976d2' },
  // Travel (admin::travel)
  TravelProject: { icon: <ProjectOutlined />, color: '#0288d1' },
  EmployeeTravelRecord: { icon: <SendOutlined />, color: '#1976d2' },
  MyTravel: { icon: <SendOutlined />, color: '#1976d2' },
  SubTravel: { icon: <SendOutlined />, color: '#ed6c02' },
  TravelApproval: { icon: <SendOutlined />, color: '#7b1fa2' },
};
const entityStyle = (e) => ENTITY_STYLE[e] || { icon: <AppstoreOutlined />, color: '#607d8b' };

// Icon + colour for a custom-field Object Type value (the type column).
const OBJECT_TYPE_STYLE = {
  Employee: { icon: <UserOutlined />, color: '#1976d2' },
  CompanyStructure: { icon: <ApartmentOutlined />, color: '#0288d1' },
  Project: { icon: <ProjectOutlined />, color: '#7b1fa2' },
  Client: { icon: <BankOutlined />, color: '#2e7d32' },
  JobTitle: { icon: <IdcardOutlined />, color: '#ed6c02' },
  Qualification: { icon: <ReadOutlined />, color: '#1976d2' },
  Recruitment: { icon: <SolutionOutlined />, color: '#7b1fa2' },
  Document: { icon: <FileTextOutlined />, color: '#546e7a' },
  Leave: { icon: <CalendarOutlined />, color: '#ed6c02' },
};
const objectTypeStyle = (t) => OBJECT_TYPE_STYLE[t] || { icon: <AppstoreOutlined />, color: '#607d8b' };

// Per-entity card overrides: which column is the title, what to hide from the
// meta line, which fields to show as a Tag, dynamic icons, and whether to hide
// the dedicated View button (row click still views).
// Shared status -> tag colour map for performance review cards (admin + employee
// lists), so a review's status reads the same everywhere.
const REVIEW_STATUS_COLORS = {
  Pending: 'gold', Submitted: 'green', Completed: 'blue', Rejected: 'red',
};

const ENTITY_CONFIG = {
  // AttendanceModal renders its own antd Modal (controlled by `element`), so the
  // card list must NOT also wrap it in a Modal (that showed two modals).
  // Show the employee's profile photo as the card avatar, name as the title.
  Attendance: {
    childSelfModal: true,
    avatarField: 'image',
    titleField: 'employee',
    hideMeta: ['image'],
  },
  AttendanceStatus: {
    avatarField: 'image',
    titleField: 'employee',
    hideMeta: ['image'],
    hideActions: true, // read-only status view — no row actions / Add New
  },
  // The employee's own attendance (modules::attendance) — one card per punch day.
  // Title by clock-in time; the detail view is AttendanceModal (self-modal).
  MyAttendance: {
    childSelfModal: true,
    titleField: 'in_time',
    hideMeta: ['image'],
  },
  // Overtime — the employee's own requests (modules::overtime own tab) have no
  // employee column, so title by category. The admin / subordinate / approval
  // adapters carry the employee photo + name (like AttendanceStatus).
  MyOvertime: { titleField: 'category' },
  EmployeeOvertime: { titleField: 'employee', avatarField: 'image', hideMeta: ['image'] },
  EmployeeOvertimeApproval: { titleField: 'employee', avatarField: 'image', hideMeta: ['image'] },
  JobTitle: { titleField: 'name' },
  Language: { titleField: 'description' },
  CustomField: {
    title: (r) => `${r.type || ''} → ${r.name || ''}`,
    hideMeta: ['name', 'type', 'display'],
    iconField: 'type',
  },
  Audit: {
    title: (r) => r.details,
    tagFields: ['time'],
    tagIcon: <ClockCircleOutlined />,
    hideMeta: ['details', 'time'],
    hideViewButton: true,
  },
  EmailLogEntry: { titleField: 'subject', hideMeta: ['subject'] },
  Project: { hideViewButton: true },
  EmployeeProject: { hideViewButton: true },
  Client: { hideViewButton: true },
  Employee: {
    title: (r) => `${r.first_name || ''} ${r.last_name || ''}`.trim() || r.employee_id,
    avatarField: 'image',
    hideMeta: ['first_name', 'last_name', 'image', 'id'],
    extraActions: [
      {
        key: 'switch', tip: 'Switch to profile', icon: <LoginOutlined />, color: '#ed6c02',
        method: 'setAdminProfile', show: (m) => !!m.allowSwitchToEmployeeProfile,
      },
      {
        key: 'resign', tip: 'Initiate resignation', icon: <UserDeleteOutlined />, color: '#d32f2f',
        method: 'terminateEmployee', show: (m) => m.hasAccess('delete') && m.showDelete !== false,
      },
    ],
  },
  TerminatedEmployee: {
    title: (r) => `${r.first_name || ''} ${r.last_name || ''}`.trim() || r.employee_id,
    avatarField: 'image',
    hideMeta: ['first_name', 'last_name', 'image', 'id'],
    extraActions: [
      {
        key: 'activate', tip: 'Activate', icon: <CheckCircleOutlined />, color: '#2e7d32',
        method: 'activateEmployee',
      },
    ],
  },
  ArchivedEmployee: {
    title: (r) => `${r.first_name || ''} ${r.last_name || ''}`.trim() || r.employee_id,
    hideMeta: ['first_name', 'last_name', 'image', 'id'],
    extraActions: [
      {
        key: 'download', tip: 'Download', icon: <DownloadOutlined />, color: '#0288d1',
        method: 'download',
      },
    ],
  },
  EmployeeDataHistory: {
    titleField: 'employee',
    tagFields: ['created'],
    tagIcon: <ClockCircleOutlined />,
    hideMeta: ['created'],
  },
  // Leave groups (admin::leaves) — no view; the "Add Employees" action opens a
  // dialog to view the group and manage its members.
  LeaveGroup: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true,
    // Clicking the card opens the manage-members dialog; the icon action does too.
    cardClickAction: 'manageGroupEmployees',
    extraActions: [
      {
        key: 'members', tip: 'Manage members', icon: <UsergroupAddOutlined />, color: '#1677ff',
        method: 'manageGroupEmployees', first: true,
      },
    ],
  },
  // Leave rules (admin::leaves → Leave Rule). Title by leave type name, plus the
  // employee name when the rule targets a specific employee (most rules apply to
  // everyone and have no employee). No dedicated View button — clicking the card
  // opens the rule (row-click still views).
  LeaveRule: {
    title: (r, get) => {
      const type = get('leave_type') || 'Leave Rule';
      return r.employee ? `${type} — ${get('employee')}` : type;
    },
    hideViewButton: true,
    hideMeta: ['id', 'leave_type', 'employee'],
  },
  // Employee Leave List (admin::leaves → Employee Leave List). Mirrors the
  // legacy three actions: Leave Days (details + logs), Leave Status (approve
  // workflow) and Cancel Leave (delete, shown last).
  EmployeeLeave: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideEditButton: true,
    disableView: true,
    deleteLast: true,
    deleteTip: 'Cancel Leave',
    deleteIcon: <CloseCircleOutlined />,
    deleteConfirm: 'Are you sure you want to cancel this leave? This cannot be undone.',
    statusIcon: <SettingOutlined />,
    extraActions: [
      {
        key: 'leavedays', tip: 'Leave Details', icon: <InfoCircleOutlined />, color: '#2e7d32',
        method: 'getLeaveDaysReadonly', first: true,
      },
    ],
  },
  // --- User leaves (modules::leaves) ------------------------------------
  // My own leave requests (All My Leaves + Pending): Leave Details, and Cancel
  // (delete) shown only while the request is still Pending.
  MyLeave: {
    titleField: 'leave_type',
    hideMeta: ['id'],
    hideEditButton: true,
    hideCopyButton: true,
    disableView: true,
    // Clicking the card opens the leave details dialog.
    cardClickAction: 'getLeaveDaysReadonly',
    extraActions: [
      {
        key: 'leavedays', tip: 'Leave Details', icon: <InfoCircleOutlined />, color: '#2e7d32',
        method: 'getLeaveDaysReadonly', first: true,
      },
      {
        key: 'cancel', tip: 'Cancel Leave', icon: <CloseCircleOutlined />, color: '#d32f2f',
        method: 'cancelMyLeave', show: (m, rec) => rec && rec.status === 'Pending',
      },
    ],
  },
  // My approved leaves: Leave Details + request cancellation (cancelLeave).
  MyLeaveApproved: {
    titleField: 'leave_type',
    hideMeta: ['id'],
    hideEditButton: true,
    disableView: true,
    extraActions: [
      {
        key: 'leavedays', tip: 'Leave Details', icon: <InfoCircleOutlined />, color: '#2e7d32',
        method: 'getLeaveDaysReadonly', first: true,
      },
      {
        key: 'cancel', tip: 'Request Cancellation', icon: <CloseCircleOutlined />, color: '#d32f2f',
        method: 'cancelLeave',
      },
    ],
  },
  // Direct reports' leaves (approve/reject): Leave Details + Change Status (cog,
  // driven by the adapter's getStatusOptionsData approve workflow).
  SubLeave: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideEditButton: true,
    disableView: true,
    statusIcon: <SettingOutlined />,
    statusAction: 'changeLeaveStatus',
    extraActions: [
      {
        key: 'leavedays', tip: 'Leave Details', icon: <InfoCircleOutlined />, color: '#2e7d32',
        method: 'getLeaveDaysReadonly', first: true,
      },
    ],
  },
  // Multi-level approval queue (same shape/actions as direct reports).
  LeaveApproval: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideEditButton: true,
    disableView: true,
    statusIcon: <SettingOutlined />,
    statusAction: 'changeLeaveStatus',
    extraActions: [
      {
        key: 'leavedays', tip: 'Leave Details', icon: <InfoCircleOutlined />, color: '#2e7d32',
        method: 'getLeaveDaysReadonly', first: true,
      },
    ],
  },
  // Employee Expenses (extension::expenses|admin) — approve workflow on the
  // expense record; show the employee photo + name.
  EmployeeExpense: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    // Bespoke expense dialog (ExpenseDialog) with a status-history sidebar,
    // instead of the generic legacy view/edit modals. Admins/managers view here;
    // admins may also edit (owner/admin only, enforced by the backend).
    expenseDialog: true,
    // Admins/managers can change the status from within the view dialog.
    dialogStatusChange: true,
    // A reason is mandatory when changing an expense's status.
    requireStatusReason: true,
    // Managers cannot change the status of a Paid expense (admins still can);
    // backend enforces this in Expenses\Admin\Controller::changeStatus.
    managerLockedStatuses: ['Paid'],
    // Managers cannot set an expense TO Paid (admins still can); backend enforces
    // this in Expenses\Admin\Controller::changeStatus.
    managerBlockedTargetStatuses: ['Paid'],
    deleteLast: true,
    statusIcon: <SettingOutlined />,
  },
  // --- User expenses (extension::expenses|user) -------------------------
  // My own expenses: edit/delete, and request cancellation (for approved).
  MyExpense: {
    titleField: 'category',
    hideMeta: ['id'],
    // Bespoke expense dialog (ExpenseDialog) with a status-history sidebar for
    // view / edit / re-submit — no legacy view/edit modal.
    expenseDialog: true,
    // Quick "filter by status" dropdown in the toolbar.
    statusFilter: ['Pending', 'Approved', 'Rejected', 'Paid'],
    // Employees may delete their own expense while it is Pending or Rejected
    // (the backend enforces this via EmployeeExpense::getUserOnlyMeAccess).
    deleteWhen: (rec) => rec.status === 'Pending' || rec.status === 'Rejected',
    // Employees may only edit their own expenses while still Pending.
    editWhen: (rec) => rec.status === 'Pending',
    // A rejected expense can be re-submitted (status -> Pending) by its owner.
    resubmitAction: 'resubmit',
    resubmitWhen: (rec) => rec.status === 'Rejected',
    // Expenses do not support employee-initiated cancellation of approved rows.
    hideCancelButton: true,
  },
  // Direct reports' expenses (and the multi-level approval queue): approve
  // workflow with the employee photo + name.
  SubExpense: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideEditButton: true,
    hideCopyButton: true,
    disableView: true,
    deleteLast: true,
    statusIcon: <SettingOutlined />,
  },
  ExpenseApproval: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideEditButton: true,
    hideCopyButton: true,
    disableView: true,
    deleteLast: true,
    statusIcon: <SettingOutlined />,
  },
  // Candidates (extension::candidates|admin) — the eye opens the rich
  // CandidateProfile via getTableChildComponents (same mechanism as the
  // employees module). All hiring-stage tabs share this config.
  Candidate: {
    title: (r) => `${r.first_name || ''} ${r.last_name || ''}`.trim() || r.email,
    hideMeta: ['first_name', 'last_name', 'id'],
    tagFields: ['hiringStage'],
  },
  // Job positions (extension::jobpositions|admin) — the rest of the card config
  // comes inline from the extension's meta.json native block; extraActions hold
  // React elements so they live here. Copy Job Link mirrors the legacy Job Link
  // action (public apply?ref=<code> URL).
  Job: {
    extraActions: [
      {
        key: 'joblink', tip: 'Copy Job Link', icon: <LinkOutlined />, color: '#ed6c02',
        method: 'copyJobLink', first: true,
      },
      {
        key: 'openjob', tip: 'Open Job Page', icon: <MonitorOutlined />, color: '#1565c0',
        method: 'openJobPage', first: true,
      },
    ],
  },
  // System (admin::settings) — settings rows are edit-only.
  Setting: {
    titleField: 'name',
    hideMeta: ['id', 'meta', 'category', 'setting_order'],
    hideCopyButton: true,
    disableView: true,
  },
  Module: {
    titleField: 'label',
    tagFields: ['status'],
    hideMeta: ['id', 'update_path', 'mod_order'],
    hideCopyButton: true,
    disableView: true,
  },
  Permission: {
    title: (r) => `${r.user_level || ''} — ${r.permission || ''}`,
    hideMeta: ['id', 'user_level', 'permission'],
    hideCopyButton: true,
    disableView: true,
  },
  // Metadata (admin::metadata) — simple lookups.
  Country: { titleField: 'name', hideMeta: ['id'], disableView: true },
  Province: { titleField: 'name', hideMeta: ['id'], disableView: true },
  CurrencyType: { titleField: 'name', hideMeta: ['id'], disableView: true },
  Nationality: { titleField: 'name', hideMeta: ['id'], disableView: true },
  Ethnicity: { titleField: 'name', hideMeta: ['id'], disableView: true },
  ImmigrationStatus: { titleField: 'name', hideMeta: ['id'], disableView: true },
  // Documents (admin::documents)
  CompanyDocument: {
    titleField: 'name',
    tagFields: ['status'],
    hideMeta: ['id'],
    hideCopyButton: true,
  },
  Document: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true,
  },
  EmployeeDocument: {
    titleField: 'employee',
    avatarField: 'image',
    tagFields: ['status'],
    hideMeta: ['id', 'image', 'attachment'],
    hideCopyButton: true,
    disableView: true,
    extraActions: [
      {
        key: 'download', tip: 'Download Document', icon: <DownloadOutlined />, color: '#2e7d32',
        method: 'downloadAttachment', first: true, show: (m, rec) => !!(rec && rec.attachment),
      },
    ],
  },
  PayslipDocument: {
    titleField: 'employee',
    avatarField: 'image',
    tagFields: ['status'],
    hideMeta: ['id', 'image', 'attachment'],
    hideCopyButton: true,
    disableView: true,
    extraActions: [
      {
        key: 'download', tip: 'Download Document', icon: <DownloadOutlined />, color: '#2e7d32',
        method: 'downloadAttachment', first: true, show: (m, rec) => !!(rec && rec.attachment),
      },
    ],
  },
  // Documents (modules::documents) — read-only lists with downloads.
  MyDocument: {
    titleField: 'document',
    tagFields: ['status'],
    hideMeta: ['id', 'attachment'],
    hideCopyButton: true,
    disableView: true,
    extraActions: [
      {
        key: 'download', tip: 'Download Document', icon: <DownloadOutlined />, color: '#2e7d32',
        method: 'downloadAttachment', first: true, show: (m, rec) => !!(rec && rec.attachment),
      },
    ],
  },
  MyCompanyDocument: {
    titleField: 'name',
    hideMeta: ['id', 'attachment'],
    hideCopyButton: true,
    hideEditButton: true,
    disableView: true,
    extraActions: [
      {
        key: 'download', tip: 'Download Document', icon: <DownloadOutlined />, color: '#2e7d32',
        method: 'downloadAttachment', first: true, show: (m, rec) => !!(rec && rec.attachment),
      },
    ],
  },
  MyPayslip: {
    titleField: 'document',
    tagFields: ['status'],
    hideMeta: ['id', 'attachment'],
    hideCopyButton: true,
    hideEditButton: true,
    disableView: true,
    extraActions: [
      {
        key: 'download', tip: 'Download Payslip', icon: <DownloadOutlined />, color: '#2e7d32',
        method: 'downloadAttachment', first: true, show: (m, rec) => !!(rec && rec.attachment),
      },
    ],
  },
  // Training (modules::training)
  TrainingSessionWithCourse: {
    titleField: 'name',
    hideMeta: ['id'],
    hideCopyButton: true,
    hideEditButton: true,
    extraActions: [
      {
        key: 'signup', tip: 'Sign Up', icon: <LoginOutlined />, color: '#2e7d32',
        method: 'signUp', first: true,
      },
    ],
  },
  EmployeeTrainingSession: {
    titleField: 'trainingSession',
    tagFields: ['status'],
    hideMeta: ['id', 'courseId'],
    hideCopyButton: true,
    disableView: true,
    extraActions: [
      {
        key: 'completed', tip: 'Mark Completed', icon: <CheckCircleOutlined />, color: '#2e7d32',
        method: 'completed', first: true, show: (m, rec) => rec && rec.status === 'Scheduled',
      },
    ],
  },
  SubEmployeeTraining: {
    titleField: 'employee',
    tagFields: ['status'],
    hideMeta: ['id', 'courseId'],
    hideCopyButton: true,
    disableView: true,
    extraActions: [
      {
        key: 'approve', tip: 'Approve Completed Status', icon: <CheckCircleOutlined />, color: '#2e7d32',
        method: 'completed', first: true, show: (m, rec) => rec && rec.status === 'Attended',
      },
    ],
  },
  CoordinatedTrainingSession: {
    titleField: 'name',
    tagFields: ['status'],
    hideMeta: ['id'],
    hideCopyButton: true,
    disableView: true,
  },
  // Performance (admin::performance / modules::performance). The eye opens the
  // rich review/feedback views via the adapters' viewElement flows.
  PerformanceReview: {
    titleField: 'employee',
    avatarField: 'image',
    tagFields: ['status'],
    tagColors: REVIEW_STATUS_COLORS,
    hideMeta: ['id', 'image', 'review_pdf'],
    hideCopyButton: true,
    extraActions: [
      {
        key: 'createPdf', tip: 'Create PDF', icon: <FilePdfOutlined />, color: '#c62828',
        method: 'createReviewPdf',
        // Anyone with access can generate the first PDF; once one exists only an
        // admin may regenerate it (the backend enforces this too).
        show: (m, rec) => rec && rec.status === 'Completed'
          && (isEmpty(rec.review_pdf) || (m.isAdminUser && m.isAdminUser())),
      },
      {
        key: 'viewPdf', tip: 'View PDF', icon: <FilePdfOutlined />, color: '#2e7d32',
        method: 'viewReviewPdf', show: (m, rec) => rec && !isEmpty(rec.review_pdf),
      },
    ],
  },
  ReviewFeedback: {
    titleField: 'employee',
    avatarField: 'image',
    tagFields: ['status'],
    hideMeta: ['id', 'image'],
    hideCopyButton: true,
  },
  ReviewTemplate: {
    titleField: 'name',
    hideMeta: ['id'],
    hideCopyButton: true,
  },
  EmployeeGoal: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideCopyButton: true,
    disableView: true,
    progressFields: ['manager_rating', 'employee_rating'],
  },
  // User performance (modules::performance)
  MyPerformanceReview: {
    // 'form' arrives as the review template name (resolved server-side via the
    // adapter's source mapping); the period dates render formatted through the
    // adapter's column render functions in the meta line.
    title: (r) => r.form || 'Performance Review',
    tagFields: ['status'],
    tagColors: REVIEW_STATUS_COLORS,
    hideMeta: ['id', 'form', 'review_pdf'],
    hideCopyButton: true,
    hideEditButton: true,
    // Employees can view (not create) the review PDF once a manager generates
    // it — the PDF only exists on Completed reviews.
    extraActions: [
      {
        key: 'viewPdf', tip: 'View PDF', icon: <FilePdfOutlined />, color: '#2e7d32',
        method: 'viewReviewPdf', show: (m, rec) => rec && !isEmpty(rec.review_pdf),
      },
    ],
  },
  MyReviewFeedback: {
    titleField: 'review',
    avatarField: 'image',
    tagFields: ['status'],
    hideMeta: ['id', 'image'],
    hideCopyButton: true,
    hideEditButton: true,
    extraActions: [
      {
        key: 'give', tip: 'Give Feedback', icon: <FormOutlined />, color: '#2e7d32',
        method: 'showConfigView', first: true, show: (m, rec) => rec && rec.status !== 'Submitted',
      },
      {
        key: 'submit', tip: 'Submit Feedback', icon: <ExportOutlined />, color: '#ed6c02',
        method: 'submitFeedback', first: true, show: (m, rec) => rec && rec.status === 'Pending',
      },
    ],
  },
  // Salary (admin::salary)
  SalaryComponentType: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true,
    exportEndpoint: 'payroll_config/export/salary',
    exportLabel: 'Export Salary Data',
    exportFilePrefix: 'salary-backup',
    importEndpoint: 'payroll_config/import/salary',
  },
  SalaryComponent: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true,
    exportEndpoint: 'payroll_config/export/salary',
    exportLabel: 'Export Salary Data',
    exportFilePrefix: 'salary-backup',
    importEndpoint: 'payroll_config/import/salary',
  },
  EmployeeSalary: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideCopyButton: true,
    disableView: true,
    exportEndpoint: 'payroll_config/export/salary',
    exportLabel: 'Export Salary Data',
    exportFilePrefix: 'salary-backup',
    importEndpoint: 'payroll_config/import/salary',
  },
  // Loans (admin::loans)
  CompanyLoan: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true,
  },
  EmployeeCompanyLoan: {
    titleField: 'employee',
    tagFields: ['status'],
    hideMeta: ['id'],
    hideCopyButton: true,
    disableView: true,
  },
  // My loans (modules::loans) — read-only; the eye opens the record details.
  MyLoan: {
    titleField: 'loan',
    tagFields: ['status'],
    hideMeta: ['id'],
    hideEditButton: true,
    hideCopyButton: true,
  },
  // Travel (admin::travel)
  TravelProject: {
    titleField: 'name',
    hideMeta: ['id'],
    disableView: true,
  },
  // Travel Requests — the View button opens a rich custom modal
  // (viewElement -> TravelRequestView) with status change; keep it enabled.
  EmployeeTravelRecord: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideCopyButton: true,
  },
  // --- User travel (modules::travel) ------------------------------------
  // My own travel requests: apply/edit/delete + request cancellation.
  MyTravel: {
    titleField: 'travel_to',
    hideMeta: ['id'],
    hideCopyButton: true,
    disableView: true,
  },
  // Direct reports' (and the multi-level approval queue) travel requests:
  // the View modal handles the status change.
  SubTravel: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideCopyButton: true,
  },
  TravelApproval: {
    titleField: 'employee',
    avatarField: 'image',
    hideMeta: ['id', 'image'],
    hideCopyButton: true,
    hideEditButton: true,
    statusIcon: <SettingOutlined />,
  },
  // Users module (admin::users).
  User: {
    titleField: 'username',
    avatarField: 'image',
    hideMeta: ['image', 'id'],
    extraActions: [
      {
        key: 'password', tip: 'Change Password', icon: <KeyOutlined />, color: '#1565c0',
        method: 'showPasswordChangeForm', show: (m) => !!(m.hasAccess && m.hasAccess('save')),
      },
    ],
  },
  UserRole: { hideMeta: ['id'] },
  UserInvitation: {
    titleField: 'username',
    tagFields: ['invitation_status_text'],
    hideMeta: ['id'],
  },
};

// Render a column cell for a record, tolerating legacy render() functions.
function cellValue(col, record) {
  const raw = record[col.dataIndex];
  if (typeof col.render === 'function') {
    try {
      const out = col.render(raw, record);
      if (out === null || out === undefined || out === '') return raw;
      return out;
    } catch (e) {
      return raw;
    }
  }
  return raw;
}

function isEmpty(v) {
  return v === null || v === undefined || v === '' || v === '-';
}

/**
 * Generic horizontal card list for natively-mounted legacy admin modules.
 * Drives off the module's legacy adapter (window.modJsList[tabKey]): it fetches
 * pages through the adapter's IceDataPipe, renders each row as a compact card
 * built from getTableColumns(), and exposes the adapter's actions (edit / view /
 * delete / copy / document) as buttons — gated by the same flags the legacy
 * table uses. Add/Edit/View/Filter modals render through the adapter (themed via
 * ReactModalAdapterBase.shellThemeWrap). Delete uses an antd confirm + the
 * adapter's cleanDelete (the legacy Bootstrap confirm modal is absent here).
 * Matches the Company Structure card design and is dark/light-mode aware.
 */
export default function NativeCardList({
  shellConfig, tabKey, entity, cardConfig,
}) {
  const { token } = theme.useToken();
  // Action-icon colours: the hues are tuned for light mode; in dark mode lighten
  // them so they stay legible on the dark cards.
  const isDark = typeof window !== 'undefined' && window.__shellColorMode === 'dark';
  const ac = (hex) => (isDark ? lighten(hex, 0.4) : hex);
  const [items, setItems] = useState(null);
  const [total, setTotal] = useState(0);
  const [page, setPage] = useState(1);
  // Page size is fixed at PAGE_SIZE unless the module opts into the size
  // selector via adapter.setShowPageSizeChanger(true) (e.g. admin Attendance).
  const [pageSize, setPageSize] = useState(PAGE_SIZE);
  const [search, setSearch] = useState('');
  const [statusFilterVal, setStatusFilterVal] = useState('');
  const [err, setErr] = useState(false);
  // Some adapters (Employee) "view" by setting a current element that the legacy
  // table renders as a child component (e.g. the employee profile) instead of a
  // modal — capture it and render that child in our own modal.
  const [currentElement, setCurrentElement] = useState(null);
  // A document URL (e.g. a task-list editor page) opened in an embedded modal.
  const [editorUrl, setEditorUrl] = useState(null);
  // A document opened as a NATIVE in-shell component (no iframe) — the URL drives
  // an extension's mount fn (e.g. the editor) into nativeElRef below.
  const [nativeDoc, setNativeDoc] = useState(null);
  const nativeElRef = useRef(null);
  // Approve-workflow state: the record whose status is being changed, and the
  // approval-log list being viewed (both null when closed).
  const [statusRec, setStatusRec] = useState(null);
  const [statusValue, setStatusValue] = useState(null);
  const [statusReason, setStatusReason] = useState('');
  const [statusSaving, setStatusSaving] = useState(false);
  const [logsState, setLogsState] = useState(null); // { loading, rows } | null
  const [expenseDialog, setExpenseDialog] = useState(null); // { rec, mode } | null
  const [, force] = useState(0);
  const bump = () => force((n) => n + 1);
  // Bulk-delete selection (enabled per entity via cfg.bulkDelete).
  const [selectedIds, setSelectedIds] = useState([]);
  const [bulkDeleting, setBulkDeleting] = useState(false);
  const clearSelection = () => setSelectedIds([]);

  // The module's adapter instances are stable for the life of the mounted module,
  // but window.modJsList is a SHARED global that another overlay can temporarily
  // borrow — e.g. the native document editor's initEditorUser replaces it while
  // its modal is open. So capture this tab's adapter instance once it's wired and
  // prefer the captured instance for render/load; only fall back to the live
  // global during initial discovery. This keeps the list rendering correctly even
  // while an editor modal has borrowed the globals.
  const boundRef = useRef(null);
  const adapter = () => boundRef.current || (window.modJsList || {})[tabKey];

  const load = useCallback((toPage = null, toSearch = null, toLimit = null) => {
    const m = adapter();
    if (!m || !m.dataPipe) { setErr(true); return; }
    const p = toPage != null ? toPage : page;
    const s = toSearch != null ? toSearch : search;
    const lim = toLimit != null ? toLimit : pageSize;
    setErr(false);
    m.dataPipe.get({ page: p, limit: lim, search: s })
      .then((d) => { setItems((d && d.items) || []); setTotal((d && d.total) || 0); bump(); })
      .catch(() => setErr(true));
  }, [tabKey, page, search, pageSize]); // eslint-disable-line react-hooks/exhaustive-deps

  // Wire the adapter to the shell: mount containers for its modals, a fake table
  // container so reload()/loading hooks resolve to us, master data for selects.
  // The module's init() (which creates the adapters) runs asynchronously after
  // the bundles load, so the adapter may not exist when this first mounts — poll
  // briefly until it is ready instead of erroring out.
  useEffect(() => {
    let cancelled = false;
    let tries = 0;
    // Detached mount points for the adapter's modals (the antd Modal inside
    // portals to <body> regardless; these just need to be real elements that
    // exist independently of this component's conditional render).
    const formEl = document.createElement('div');
    const filterEl = document.createElement('div');
    document.body.appendChild(formEl);
    document.body.appendChild(filterEl);
    const wireUp = (m) => {
      boundRef.current = m;
      window.modJs = m;
      if (typeof m.setContainers === 'function') {
        m.setContainers({ Form: formEl, FilterForm: filterEl });
      }
      // Force the adapter to (re)render its form/filter modals into the current
      // containers on the next open (the instance persists across remounts).
      m.formInitialized = false;
      m.tableContainer = {
        current: {
          reload: () => load(),
          setCurrentElement: (el) => setCurrentElement(el || null),
          setLoading: () => {},
          setFilterData: () => { bump(); },
        },
      };
      try {
        if (m.masterDataReader && m.masterDataReader.updateAllMasterData) {
          m.masterDataReader.updateAllMasterData();
        }
        if (typeof m.initFieldMasterData === 'function') m.initFieldMasterData();
      } catch (e) { /* ignore */ }
      setPage(1);
      load(1, '');
    };
    const tick = () => {
      if (cancelled) return;
      const m = adapter();
      if (m && m.dataPipe) { wireUp(m); return; }
      tries += 1;
      if (tries > 100) { setErr(true); return; } // ~10s
      setTimeout(tick, 100);
    };
    tick();
    return () => {
      cancelled = true;
      try {
        const m = adapter();
        if (m && typeof m.setContainers === 'function') m.setContainers(null);
        if (formEl.parentNode) formEl.parentNode.removeChild(formEl);
        if (filterEl.parentNode) filterEl.parentNode.removeChild(filterEl);
      } catch (e) { /* ignore */ }
      boundRef.current = null;
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [tabKey]);

  const m = adapter();

  // The "group=name" reference for customAction routing (the legacy `mod`
  // param). Prefer the adapter's own ref (approve/log adapters know whether
  // they hit admin= or modules=); otherwise derive from the SPA module scope:
  // core admin -> admin=<name>, core user -> modules=<name>, extensions
  // (registry name "<ext>|<subType>") -> admin=<ext> / modules=<ext>.
  const moduleRef = () => {
    if (!m) return 'admin=';
    if (typeof m.getActionModuleRef === 'function') return m.getActionModuleRef();
    if (m.spaModuleGroup === 'extension' && typeof m.spaModuleName === 'string' && m.spaModuleName.indexOf('|') >= 0) {
      const [ext, sub] = m.spaModuleName.split('|');
      return `${sub === 'user' ? 'modules' : 'admin'}=${m.modulePathName || ext}`;
    }
    if (m.spaModuleGroup && m.spaModuleName) return `${m.spaModuleGroup}=${m.spaModuleName}`;
    return `admin=${m.modulePathName}`;
  };

  const columns = useMemo(() => {
    if (!m || typeof m.getTableColumns !== 'function') return [];
    return (m.getTableColumns() || []).map((c) => ({
      ...c, label: m.gt ? m.gt(c.title) : c.title,
    }));
  }, [m, items]); // eslint-disable-line react-hooks/exhaustive-deps

  // Hardcoded per-entity config for core modules, optionally overridden by a
  // declarative config an extension supplies via its meta.json `native` block.
  const cfg = { ...(ENTITY_CONFIG[entity] || {}), ...(cardConfig || {}) };
  const docAction = cfg.documentAction || null;

  // --- capabilities -------------------------------------------------------
  const can = (a) => !!(m && m.hasAccess && m.hasAccess(a));
  // cfg.disableView turns off viewing entirely (no eye button AND no row-click
  // view) — e.g. task lists, which are opened via their document action instead.
  // A card-config method invoked when the card body is clicked (e.g. leave
  // groups open a manage-members dialog). Falls back to the default view.
  const cardClick = (cfg.cardClickAction && m && typeof m[cfg.cardClickAction] === 'function')
    ? (rec) => m[cfg.cardClickAction](rec.id, rec) : null;
  const hasView = !cfg.disableView && (!!cfg.expenseDialog || !!(m && (typeof m.showProjectDetails === 'function'
    || (can('element') && m.showViewButton && m.showViewButton()))));
  const showViewBtn = hasView && !cfg.hideViewButton;
  const canEdit = !!(m && can('save') && m.showEdit !== false) && !cfg.hideEditButton;
  const canCopy = !!(m && can('save') && m.showAddNew !== false) && !cfg.hideCopyButton;
  const canDelete = !!(m && can('delete') && m.showDelete !== false);
  // Per-record delete gate: some entities only allow deleting rows in a certain
  // state (e.g. employees may delete only their own Pending expenses). A card
  // config `deleteWhen(rec) => bool` refines the list-level canDelete row by row;
  // the backend still enforces the same rule per record.
  const rowDeletable = (rec) => canDelete
    && (typeof cfg.deleteWhen === 'function' ? !!cfg.deleteWhen(rec) : true);
  // Same per-record refinement for editing (e.g. employees may edit only their
  // own Pending expenses).
  const rowEditable = (rec) => (typeof cfg.editWhen === 'function' ? !!cfg.editWhen(rec) : true);
  const bulkEnabled = !!cfg.bulkDelete && canDelete && typeof (m && m.cleanDelete) === 'function';
  const canAdd = !!(m && can('save') && (m.getShowAddNew ? m.getShowAddNew() : m.showAddNew !== false));
  const hasFilters = !!(m && m.getFilters && m.getFilters());

  const doView = (id) => {
    if (!m) return;
    if (typeof m.showProjectDetails === 'function') m.showProjectDetails(id);
    else if (typeof m.viewElement === 'function') m.viewElement(id);
  };
  // Entities with a bespoke dialog (cfg.expenseDialog) open that instead of the
  // legacy generic view/edit modals, in the appropriate mode.
  const openView = (rec) => {
    if (cfg.expenseDialog) { setExpenseDialog({ rec, mode: 'view' }); return; }
    doView(rec.id);
  };
  const openEdit = (rec) => {
    if (cfg.expenseDialog) { setExpenseDialog({ rec, mode: 'edit' }); return; }
    if (m) m.edit(rec.id);
  };
  const doEdit = (id) => { if (m) m.edit(id); };
  const doCopy = (id) => { if (m && m.copyRow) m.copyRow(id); };
  const doAdd = () => { if (m) m.renderForm(); };

  // Fetch a backup from the REST API (using the shell's bearer token) and download it as
  // JSON. Driven by cfg.exportEndpoint (see the salary entities in ENTITY_CONFIG).
  const [exporting, setExporting] = useState(false);
  const doExport = async () => {
    if (!cfg.exportEndpoint) return;
    setExporting(true);
    try {
      const resp = await fetch(`${shellConfig.restApiBase}${cfg.exportEndpoint}`, {
        headers: { Authorization: `Bearer ${shellConfig.token}` },
        credentials: 'same-origin',
      });
      const backup = await resp.json();
      const blob = new Blob([JSON.stringify(backup, null, 2)], { type: 'application/json' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      const stamp = new Date().toISOString().slice(0, 19).replace(/[:T]/g, '-');
      a.href = url;
      a.download = `${cfg.exportFilePrefix || 'backup'}-${stamp}.json`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
      message.success('Backup downloaded');
    } catch (e) {
      message.error('Export failed');
    } finally {
      setExporting(false);
    }
  };

  // Restore a backup by POSTing an uploaded JSON file to cfg.importEndpoint.
  const [importing, setImporting] = useState(false);
  const importInputRef = useRef(null);
  const doImportFile = async (event) => {
    const file = event.target.files && event.target.files[0];
    event.target.value = '';
    if (!file || !cfg.importEndpoint) return;
    setImporting(true);
    try {
      const text = await file.text();
      const backup = JSON.parse(text);
      const resp = await fetch(`${shellConfig.restApiBase}${cfg.importEndpoint}`, {
        method: 'POST',
        headers: {
          Authorization: `Bearer ${shellConfig.token}`,
          'Content-Type': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify(backup),
      });
      const data = await resp.json();
      if (data && data.error) {
        message.error(data.error?.[0]?.[0]?.message || 'Import failed', 5);
      } else {
        message.success('Data restored — refreshing…');
        // A salary import restores multiple tables (types/components/salaries); reload so
        // every tab reflects it, not just the currently visible card list.
        setTimeout(() => window.location.reload(), 700);
      }
    } catch (e) {
      message.error('Import failed: invalid file or server error', 5);
    } finally {
      setImporting(false);
    }
  };

  // "Generate from industry" toolbar action (cfg.generateFromIndustry). Reusable:
  // fetches a list of industries from one endpoint and posts the chosen one to a
  // generate endpoint (e.g. Job Titles). Any module can opt in via its card config.
  const genCfg = cfg.generateFromIndustry || null;
  const [genOpen, setGenOpen] = useState(false);
  const [genIndustries, setGenIndustries] = useState([]);
  const [genIndustry, setGenIndustry] = useState(null);
  const [genLoading, setGenLoading] = useState(false);
  const [generating, setGenerating] = useState(false);
  const [genDelete, setGenDelete] = useState(false);
  const openGenerate = () => {
    if (!genCfg) return;
    setGenIndustry(null);
    setGenDelete(false);
    setGenOpen(true);
    setGenLoading(true);
    fetch(`${shellConfig.restApiBase}${genCfg.industriesEndpoint}`, {
      headers: { Authorization: `Bearer ${shellConfig.token}` },
      credentials: 'same-origin',
    })
      .then((r) => r.json())
      .then((d) => { setGenIndustries(Array.isArray(d) ? d : []); })
      .catch(() => message.error('Could not load industries', 5))
      .finally(() => setGenLoading(false));
  };
  const doGenerate = () => {
    if (!genCfg || !genIndustry) { message.error('Please select an industry.', 4); return; }
    setGenerating(true);
    fetch(`${shellConfig.restApiBase}${genCfg.generateEndpoint}`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${shellConfig.token}`, 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify({ industry: genIndustry, deleteExisting: genDelete }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d && d.error) { message.error(d.error?.[0]?.[0]?.message || 'Generation failed', 5); return; }
        const label = genCfg.itemsLabel || 'items';
        const parts = [`Added ${d.created} ${label}`];
        if (d.deleted) parts.push(`deleted ${d.deleted}`);
        if (d.kept_assigned) parts.push(`kept ${d.kept_assigned} in use`);
        if (d.skipped) parts.push(`${d.skipped} already existed`);
        message.success(`${parts.join(', ')}.`);
        setGenOpen(false);
        load();
      })
      .catch(() => message.error('Generation failed', 5))
      .finally(() => setGenerating(false));
  };

  const docUrl = (rec) => {
    const link = rec.document_link;
    if (!link) return null;
    const base = (window.baseUrl || '').replace('service.php', '');
    return /^https?:\/\//.test(link) ? link : `${base}${link}`;
  };
  const doDocument = (rec) => {
    const url = docUrl(rec);
    if (!url) return;
    // Native in-shell mount (no iframe, theme-aware), an embedded iframe modal,
    // or a new tab — per the entity's documentAction config.
    if (docAction && docAction.openIn === 'native') {
      setNativeDoc(url);
      return;
    }
    if (docAction && docAction.openIn === 'iframe-modal') {
      setEditorUrl(url);
      return;
    }
    window.open(url, '_blank', 'noopener');
  };
  const doDelete = (rec) => {
    if (!m) return;
    // Use the same title the card shows (respects the entity's titleField /
    // title config) — falling back to a generic message if it isn't a plain
    // string (e.g. a rendered cell), so we never surface a raw URL/field value.
    const t = titleOf(rec);
    const label = typeof t === 'string' && t.trim() ? t : null;
    Modal.confirm({
      title: cfg.deleteTip || 'Delete',
      content: cfg.deleteConfirm || (label
        ? `Are you sure you want to delete “${label}”?`
        : 'Are you sure you want to delete this item?'),
      okText: cfg.deleteTip || 'Delete',
      okType: 'danger',
      onOk: () => new Promise((resolve) => {
        try {
          m.cleanDelete(rec.id, (httpStatus, status, data) => {
            if (httpStatus === 200 && status === 'SUCCESS') {
              message.success('Deleted');
              load();
            } else {
              // Prefer the server's reason (e.g. "…assigned to N employees").
              const serverMsg = data && typeof data.data === 'string' ? data.data : null;
              message.error(serverMsg || 'Could not delete. It may be in use.', 5);
            }
            resolve();
          });
        } catch (e) { message.error('Could not delete', 5); resolve(); }
      }),
    });
  };

  // Delete one id via the adapter, resolved to true/false (never rejects).
  const deleteOne = (id) => new Promise((resolve) => {
    try {
      m.cleanDelete(id, (httpStatus, status) => resolve(httpStatus === 200 && status === 'SUCCESS'));
    } catch (e) { resolve(false); }
  });

  const doBulkDelete = () => {
    if (!m || !selectedIds.length) return;
    const ids = [...selectedIds];
    Modal.confirm({
      title: 'Delete selected',
      content: `Are you sure you want to delete ${ids.length} item${ids.length === 1 ? '' : 's'}? This cannot be undone.`,
      okText: `Delete ${ids.length}`,
      okType: 'danger',
      onOk: async () => {
        setBulkDeleting(true);
        let ok = 0;
        // Sequential to avoid hammering the endpoint; tolerant of individual failures.
        // eslint-disable-next-line no-restricted-syntax
        for (const id of ids) {
          // eslint-disable-next-line no-await-in-loop
          if (await deleteOne(id)) ok += 1;
        }
        setBulkDeleting(false);
        const failed = ids.length - ok;
        if (ok) message.success(`Deleted ${ok} item${ok === 1 ? '' : 's'}`);
        if (failed) message.error(`${failed} could not be deleted (may be in use)`, 5);
        clearSelection();
        load();
      },
    });
  };

  // --- approve workflow (overtime/expenses/leave/…) -----------------------
  // Detected from the adapter, driven natively (the legacy bootstrap status modal
  // + log popup don't exist in the SPA). changeStatus/getLogs/cancelRequest run
  // through the adapter's customAction with injected native callbacks.
  const isApprove = !!(m && typeof m.getStatusOptionsData === 'function');
  const canCancel = !!(m && typeof m.cancelRequest === 'function');
  const hasLogs = !!(m && typeof m.getLogs === 'function');
  // Target-status options available FROM a given current status, applying the
  // non-admin restrictions (a manager cannot change a Paid expense, nor set one
  // to Paid). Used by both the card status button and the dialog dropdown.
  const statusOptionsForStatus = (status) => {
    if (!isApprove) return [];
    const lvl = (m && typeof m.getUser === 'function' && m.getUser()) ? m.getUser().user_level : null;
    const isNonAdmin = !!lvl && lvl !== 'Admin';
    if (isNonAdmin && cfg.managerLockedStatuses && cfg.managerLockedStatuses.indexOf(status) >= 0) {
      return [];
    }
    let data = {};
    try { data = m.getStatusOptionsData(status) || {}; } catch (e) { data = {}; }
    let opts = Object.keys(data).map((k) => ({ value: data[k], label: k }));
    if (isNonAdmin && cfg.managerBlockedTargetStatuses) {
      opts = opts.filter((o) => cfg.managerBlockedTargetStatuses.indexOf(o.value) < 0);
    }
    return opts;
  };
  const statusOptionsFor = (rec) => statusOptionsForStatus(rec.status);
  const openStatus = (rec) => {
    const opts = statusOptionsFor(rec);
    setStatusRec(rec);
    setStatusValue(opts.length ? opts[0].value : null);
    setStatusReason('');
  };
  const submitStatus = () => {
    if (!m || !statusRec || !statusValue) return;
    setStatusSaving(true);
    const cb = {
      callBackData: [],
      callBackSuccess: '__nativeStatusOk',
      callBackFail: '__nativeStatusFail',
    };
    m.__nativeStatusOk = () => {
      setStatusSaving(false); setStatusRec(null);
      message.success('Status updated'); load();
    };
    m.__nativeStatusFail = (d) => {
      setStatusSaving(false);
      message.error(typeof d === 'string' ? d : 'Could not update status', 5);
    };
    const payload = JSON.stringify({ id: statusRec.id, status: statusValue, reason: statusReason });
    try {
      // Some modules use a differently-named status action (e.g. leaves ->
      // changeLeaveStatus); the card config can override the default.
      m.customAction(cfg.statusAction || 'changeStatus', moduleRef(), payload, cb, true);
    } catch (e) { m.__nativeStatusFail('Could not update status'); }
  };
  // Re-submit a rejected record (e.g. an employee resending a rejected expense
  // back into the approval queue -> status becomes Pending). Driven by a custom
  // action named in cfg.resubmitAction; the backend re-checks ownership/status.
  const doResubmit = (rec) => {
    if (!m || !cfg.resubmitAction) return;
    // With the expense dialog, re-submit opens the editable dialog (so the owner
    // can fix the expense before resending); the dialog itself does the resubmit.
    if (cfg.expenseDialog) { setExpenseDialog({ rec, mode: 'resubmit' }); return; }
    Modal.confirm({
      title: 'Re-submit expense',
      content: 'Re-submit this rejected expense for approval? Its status will be set back to Pending.',
      okText: 'Re-submit',
      onOk: () => {
        const cb = {
          callBackData: [],
          callBackSuccess: '__nativeResubmitOk',
          callBackFail: '__nativeResubmitFail',
        };
        m.__nativeResubmitOk = () => { message.success('Expense re-submitted'); load(); };
        m.__nativeResubmitFail = (d) => {
          message.error(typeof d === 'string' ? d : 'Could not re-submit', 5);
        };
        try {
          m.customAction(cfg.resubmitAction, moduleRef(), JSON.stringify({ id: rec.id }), cb, true);
        } catch (e) { m.__nativeResubmitFail('Could not re-submit'); }
      },
    });
  };
  const doCancel = (rec) => {
    if (!m || !m.cancelRequest) return;
    Modal.confirm({
      title: 'Cancel request',
      content: 'Request cancellation of this approved entry?',
      okText: 'Yes, cancel it',
      onOk: () => {
        m.cancelSuccessCallBack = () => { message.success('Cancellation requested'); load(); };
        m.cancelFailCallBack = (d) => message.error(typeof d === 'string' ? d : 'Could not cancel', 5);
        try { m.cancelRequest(rec.id); } catch (e) { message.error('Could not cancel', 5); }
      },
    });
  };
  const openLogs = (rec) => {
    if (!m || !m.getLogs) return;
    setLogsState({ loading: true, rows: [] });
    const cb = {
      callBackData: [],
      callBackSuccess: '__nativeLogsOk',
      callBackFail: '__nativeLogsFail',
    };
    m.__nativeLogsOk = (d) => {
      // Tolerate either an array or { data: [...] }.
      const rows = Array.isArray(d) ? d : (d && Array.isArray(d.data) ? d.data : []);
      setLogsState({ loading: false, rows });
    };
    m.__nativeLogsFail = () => setLogsState({ loading: false, rows: [] });
    try {
      m.customAction('getLogs', moduleRef(), JSON.stringify({ id: rec.id }), cb);
    } catch (e) { setLogsState({ loading: false, rows: [] }); }
  };

  // --- filters ------------------------------------------------------------
  // getFilterString resolves ids via the adapter's field master data, which may
  // not be loaded yet (e.g. a filter handed over before this tab first opened) —
  // never let a display-string lookup crash the whole card list render.
  let filterString = '';
  try {
    filterString = (m && hasFilters && m.filter && m.getFilterString)
      ? m.getFilterString(m.filter) : '';
  } catch (e) { filterString = ''; }
  const openFilters = () => { if (m && m.showFilters) m.showFilters(); };
  const clearFilters = () => { if (m && m.resetFilters) m.resetFilters(); };

  const onSearch = (val) => { clearSelection(); setSearch(val); setPage(1); load(1, val); };
  // Quick status filter (e.g. expenses). Applies the value as the adapter's
  // server-side filter ({status}) and reloads; clearing restores the tab's
  // original filter so the list shows every status again.
  const onStatusFilter = (val) => {
    if (!m) return;
    m.setFilter(val ? { status: val } : (m.origFilter != null ? m.origFilter : ''));
    setStatusFilterVal(val || '');
    clearSelection();
    setPage(1);
    load(1);
  };
  const onPage = (p, ps) => {
    clearSelection();
    // antd fires onChange for both page and page-size changes. When the size
    // changes, jump back to page 1 and refetch with the new limit.
    if (ps && ps !== pageSize) {
      setPageSize(ps);
      setPage(1);
      load(1, null, ps);
    } else {
      setPage(p);
      load(p, null);
    }
  };

  // Selection helpers (bulk delete).
  const isSelected = (id) => selectedIds.includes(id);
  const toggleSelect = (id) => setSelectedIds((prev) => (prev.includes(id)
    ? prev.filter((x) => x !== id) : [...prev, id]));
  const pageIds = (items || []).map((r) => r.id);
  const allOnPageSelected = pageIds.length > 0 && pageIds.every((id) => selectedIds.includes(id));
  const toggleSelectAll = () => setSelectedIds((prev) => (allOnPageSelected
    ? prev.filter((id) => !pageIds.includes(id))
    : Array.from(new Set([...prev, ...pageIds]))));

  const st = entityStyle(entity);

  // Title / meta / tag / icon resolution from the per-entity config.
  // When no titleField is configured, prefer the conventional IceHRM display
  // columns (`name` / `title`) before falling back to the first column — the
  // first column may be an avatar/image (e.g. Task Lists), which must never be
  // used as the title or in the delete confirmation.
  const hasCol = (k) => columns.some((c) => c.dataIndex === k);
  const conventionalTitle = ['name', 'title'].find(hasCol);
  const titleFieldName = cfg.titleField || conventionalTitle
    || (columns[0] && columns[0].dataIndex);
  const hideSet = new Set(cfg.hideMeta || []);
  // Don't repeat the title field down in the meta line (unless a custom title()
  // function is used, in which case the underlying columns are still meta).
  if (!cfg.title && titleFieldName) hideSet.add(titleFieldName);
  (cfg.tagFields || []).forEach((f) => hideSet.add(f));
  const metaColumns = columns.filter((c) => !hideSet.has(c.dataIndex));
  const tagColumns = (cfg.tagFields || [])
    .map((f) => columns.find((c) => c.dataIndex === f)).filter(Boolean);
  // Resolve a column to its displayed (name-resolved) value for a record, so a
  // custom title() can show a foreign key's name (e.g. leave_type -> "Annual
  // leave") instead of its raw id, exactly like the meta line does.
  const resolveField = (rec, field) => {
    const col = columns.find((c) => c.dataIndex === field);
    return col ? cellValue(col, rec) : rec[field];
  };
  const titleOf = (rec) => {
    let v;
    if (cfg.title) {
      v = cfg.title(rec, (field) => resolveField(rec, field));
    } else if (titleFieldName) {
      const col = columns.find((c) => c.dataIndex === titleFieldName) || { dataIndex: titleFieldName };
      v = cellValue(col, rec);
    } else {
      return `#${rec.id}`;
    }
    // Custom view elements may carry a nested record here (e.g. the performance
    // review view passes an employee OBJECT) — rendering that as a React child
    // crashes (#31). Reduce it to a display name.
    if (v && typeof v === 'object' && !React.isValidElement(v)) {
      const name = `${v.first_name || ''} ${v.last_name || ''}`.trim();
      return name || (v.name || `#${rec.id}`);
    }
    return v;
  };
  const iconOf = (rec) => (cfg.iconField ? objectTypeStyle(rec[cfg.iconField]) : st);

  // Native document mount: when a row's documentAction is openIn:'native', load
  // the extension's bundle (declared in the card config) and call its mount fn
  // into the modal container, passing the shell session + colour mode.
  //
  // The mounted bundle (e.g. the editor's initEditorUser) overwrites the shared
  // window.modJs/modJsList globals that THIS card list also drives off. The host
  // owns the snapshot + restore here — taken synchronously before the bundle can
  // clobber it, restored synchronously after unmount — so the list always comes
  // back. We restore here rather than in the editor because the editor re-mounts
  // on in-document navigation and can't know the original host value.
  useEffect(() => {
    if (!nativeDoc || !docAction || docAction.openIn !== 'native') return undefined;
    const el = nativeElRef.current;
    if (!el) return undefined;
    // Snapshot the host's module globals BEFORE the bundle's init clobbers them.
    const savedModJsList = window.modJsList;
    const savedModJs = window.modJs;
    let cancelled = false;
    const mountName = docAction.mountFn;
    const unmountName = mountName ? mountName.replace('mount', 'unmount') : null;
    const extBase = (window.BASE_URL || '').replace('/web/', '/extensions/');
    const bundleUrl = docAction.bundle ? `${extBase}${docAction.bundle}` : null;
    const run = () => {
      const fn = mountName && window[mountName];
      if (typeof fn === 'function') {
        fn(el, {
          documentUrl: nativeDoc,
          restApiBase: shellConfig.restApiBase,
          token: shellConfig.token,
          colorMode: window.__shellColorMode || 'light',
          onClose: () => setNativeDoc(null),
        });
      }
    };
    (bundleUrl ? loadNativeBundle(bundleUrl) : Promise.resolve())
      .then(() => { if (!cancelled) run(); })
      .catch(() => { /* ignore */ });
    return () => {
      cancelled = true;
      const ufn = unmountName && window[unmountName];
      // Unmount the editor's React root synchronously (runs its teardown).
      if (typeof ufn === 'function') { try { ufn(el); } catch (e) { /* ignore */ } }
      // Restore the host module globals, THEN refresh the list — order matters:
      // load() reads window.modJsList[tabKey], so it must run after the restore.
      window.modJsList = savedModJsList;
      window.modJs = savedModJs;
      load();
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [nativeDoc]);

  if (err) return <Empty description="Could not load this list" />;
  if (items === null) {
    return <div style={{ display: 'flex', justifyContent: 'center', padding: 60 }}><Spin size="large" /></div>;
  }

  const metaItem = (label, value) => (isEmpty(value) ? null : (
    <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, maxWidth: 320, overflow: 'hidden' }}>
      <span style={{ color: token.colorTextTertiary }}>{`${label}:`}</span>
      <span style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{value}</span>
    </span>
  ));

  // Fields listed in cfg.progressFields render as a real progress bar (a legacy
  // render()'s full-width <Progress> collapses inside the inline meta span).
  const progressSet = new Set(cfg.progressFields || []);
  const metaValue = (c, rec) => (progressSet.has(c.dataIndex)
    ? (
      <Progress
        percent={parseInt(rec[c.dataIndex], 10) || 0}
        size="small"
        style={{ width: 130, margin: 0 }}
      />
    )
    : cellValue(c, rec));

  return (
    <div>
      <div style={{
        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
        gap: 12, marginBottom: 16, flexWrap: 'wrap',
      }}>
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap' }}>
          {canAdd && !cfg.hideActions && (
            <Button type="primary" icon={<PlusOutlined />} onClick={doAdd}>
              {cfg.addLabel || (m && typeof m.getAddNewLabel === 'function' && m.getAddNewLabel()) || 'Add New'}
            </Button>
          )}
          {/* Module-specific header action(s), e.g. the attendance Punch In/Out
              button (adapter.getCustomTopButtons → showPunchDialog → renderForm). */}
          {m && typeof m.hasCustomTopButtons === 'function' && m.hasCustomTopButtons()
            && typeof m.getCustomTopButtons === 'function' && (
            <span>{m.getCustomTopButtons()}</span>
          )}
          {cfg.exportEndpoint && (
            <Button icon={<DownloadOutlined />} loading={exporting} onClick={doExport}>
              {cfg.exportLabel || 'Export'}
            </Button>
          )}
          {cfg.importEndpoint && (
            <>
              <input
                ref={importInputRef}
                type="file"
                accept="application/json,.json"
                style={{ display: 'none' }}
                onChange={doImportFile}
              />
              <Button
                icon={<UploadOutlined />}
                loading={importing}
                onClick={() => importInputRef.current && importInputRef.current.click()}
              >
                {cfg.importLabel || 'Import'}
              </Button>
            </>
          )}
          {genCfg && !cfg.hideActions && canAdd && (
            <Button icon={<AppstoreOutlined />} onClick={openGenerate}>
              {genCfg.label || 'Generate'}
            </Button>
          )}
          {hasFilters && (
            <Button icon={<FilterOutlined />} onClick={openFilters}>Filters</Button>
          )}
          {filterString && (
            <Tag color="blue" closable onClose={clearFilters} style={{ lineHeight: '28px', borderRadius: 6 }}>
              {filterString}
            </Tag>
          )}
          {bulkEnabled && selectedIds.length > 0 && (
            <>
              <Button
                danger
                icon={<DeleteOutlined />}
                loading={bulkDeleting}
                onClick={doBulkDelete}
              >
                {`Delete ${selectedIds.length} selected`}
              </Button>
              <Button type="text" onClick={clearSelection}>Clear</Button>
            </>
          )}
        </div>
        <div style={{ display: 'flex', gap: 8 }}>
          {cfg.statusFilter && (
            <Select
              value={statusFilterVal || undefined}
              allowClear
              placeholder="All statuses"
              style={{ minWidth: 150 }}
              onChange={(v) => onStatusFilter(v)}
              options={cfg.statusFilter.map((s) => ({ value: s, label: s }))}
            />
          )}
          <Input.Search
            allowClear
            placeholder="Search…"
            style={{ maxWidth: 280 }}
            onSearch={onSearch}
          />
        </div>
      </div>

      {items.length === 0 ? (
        <Empty description="No records" />
      ) : (
        <>
          {bulkEnabled && (
            <div style={{ display: 'flex', alignItems: 'center', gap: 10, margin: '0 4px 10px' }}>
              <Checkbox
                checked={allOnPageSelected}
                indeterminate={!allOnPageSelected && pageIds.some((id) => selectedIds.includes(id))}
                onChange={toggleSelectAll}
              >
                {allOnPageSelected ? 'Deselect all' : 'Select all'}
              </Checkbox>
              {selectedIds.length > 0 && (
                <span style={{ color: token.colorTextTertiary, fontSize: 12.5 }}>
                  {`${selectedIds.length} selected`}
                </span>
              )}
            </div>
          )}
          <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            {items.map((rec) => {
              const title = titleOf(rec);
              const rst = iconOf(rec);
              return (
                <Card
                  key={rec.id}
                  hoverable={!cfg.disableCardClick && (!!cardClick || hasView || !!docAction)}
                  onClick={cfg.disableCardClick ? undefined
                    : (cfg.cardClickView && hasView
                      ? () => openView(rec)
                      : (cardClick
                        ? () => cardClick(rec)
                        : (docAction
                          ? () => doDocument(rec)
                          : (hasView ? () => openView(rec) : undefined))))}
                  style={{ borderRadius: 10, boxShadow: MUI_SHADOW, cursor: (!cfg.disableCardClick && (cardClick || hasView || docAction)) ? 'pointer' : 'default' }}
                  styles={{ body: { padding: '12px 16px' } }}
                >
                  <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                    {bulkEnabled && (
                      <Checkbox
                        checked={isSelected(rec.id)}
                        onClick={(e) => e.stopPropagation()}
                        onChange={() => toggleSelect(rec.id)}
                        style={{ flex: '0 0 auto' }}
                      />
                    )}
                    {cfg.avatarField && !isEmpty(rec[cfg.avatarField]) ? (
                      <Avatar size={40} src={rec[cfg.avatarField]} style={{ flex: '0 0 auto' }} />
                    ) : (
                      <div style={{
                        width: 40, height: 40, borderRadius: 10, flex: '0 0 auto',
                        background: `${rst.color}18`, color: rst.color, display: 'flex',
                        alignItems: 'center', justifyContent: 'center', fontSize: 18,
                      }}>
                        {rst.icon}
                      </div>
                    )}

                    <div style={{ minWidth: 0, flex: 1 }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 8, minWidth: 0 }}>
                        <span style={{
                          fontWeight: 600, fontSize: 14, overflow: 'hidden',
                          textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                        }}>
                          {isEmpty(title) ? `#${rec.id}` : title}
                        </span>
                        {tagColumns.map((c) => {
                          const v = cellValue(c, rec);
                          // Per-value colours (e.g. status): cfg.tagColors maps a
                          // value -> antd tag colour; falls back to blue.
                          const tagColor = (cfg.tagColors && cfg.tagColors[v]) || 'blue';
                          return isEmpty(v) ? null : (
                            <Tag key={c.dataIndex} color={tagColor} style={{ borderRadius: 6, margin: 0, flex: '0 0 auto' }}>
                              {cfg.tagIcon}
                              {cfg.tagIcon ? ' ' : ''}
                              {v}
                            </Tag>
                          );
                        })}
                      </div>
                      <div style={{
                        display: 'flex', alignItems: 'center', gap: 16, marginTop: 4,
                        color: token.colorTextSecondary, fontSize: 12.5, flexWrap: 'wrap',
                      }}>
                        {metaColumns.map((c) => (
                          <React.Fragment key={c.dataIndex}>
                            {metaItem(c.label, metaValue(c, rec))}
                          </React.Fragment>
                        ))}
                      </div>
                    </div>

                    {!cfg.hideActions && (
                    <div
                      style={{ display: 'flex', gap: 10, flex: '0 0 auto' }}
                      onClick={(e) => e.stopPropagation()}
                    >
                      {(cfg.extraActions || []).filter((a) => a.first).map((a) => (
                        (typeof a.show === 'function' ? a.show(m, rec) : true) && typeof m[a.method] === 'function' ? (
                          <Tooltip key={a.key} title={a.tip}>
                            <Button
                              icon={React.cloneElement(a.icon, { style: { color: ac(a.color) } })}
                              onClick={() => m[a.method](rec.id, rec)}
                            />
                          </Tooltip>
                        ) : null
                      ))}
                      {showViewBtn && (
                        <Tooltip title="View">
                          <Button icon={<EyeOutlined style={{ color: ac('#1565c0') }} />} onClick={() => openView(rec)} />
                        </Tooltip>
                      )}
                      {canEdit && rowEditable(rec) && (
                        <Tooltip title="Edit">
                          <Button icon={<EditOutlined style={{ color: ac('#2e7d32') }} />} onClick={() => openEdit(rec)} />
                        </Tooltip>
                      )}
                      {!isEmpty(rec.document_link) && (
                        <Tooltip title={(docAction && docAction.label) || 'Document'}>
                          <Button
                            icon={React.cloneElement(
                              docAction ? iconByName(docAction.icon) : <FileOutlined />,
                              { style: { color: ac('#0288d1') } },
                            )}
                            onClick={() => doDocument(rec)}
                          />
                        </Tooltip>
                      )}
                      {canCopy && (
                        <Tooltip title="Copy">
                          <Button icon={<CopyOutlined style={{ color: ac('#546e7a') }} />} onClick={() => doCopy(rec.id)} />
                        </Tooltip>
                      )}
                      {rowDeletable(rec) && !cfg.deleteLast && (
                        <Tooltip title={cfg.deleteTip || 'Delete'}>
                          <Button
                            icon={React.cloneElement(
                              cfg.deleteIcon || <DeleteOutlined />, { style: { color: ac('#d32f2f') } },
                            )}
                            onClick={() => doDelete(rec)}
                          />
                        </Tooltip>
                      )}
                      {isApprove && statusOptionsFor(rec).length > 0 && (
                        <Tooltip title="Change Status">
                          <Button
                            icon={React.cloneElement(
                              cfg.statusIcon || <MonitorOutlined />, { style: { color: ac('#1565c0') } },
                            )}
                            onClick={() => openStatus(rec)}
                          />
                        </Tooltip>
                      )}
                      {canCancel && !cfg.hideCancelButton && rec.status === 'Approved' && (
                        <Tooltip title="Cancel">
                          <Button icon={<CloseCircleOutlined style={{ color: ac('#d32f2f') }} />} onClick={() => doCancel(rec)} />
                        </Tooltip>
                      )}
                      {cfg.resubmitAction && typeof cfg.resubmitWhen === 'function' && cfg.resubmitWhen(rec) && (
                        <Tooltip title="Re-submit">
                          <Button icon={<RedoOutlined style={{ color: ac('#1565c0') }} />} onClick={() => doResubmit(rec)} />
                        </Tooltip>
                      )}
                      {hasLogs && (
                        <Tooltip title="View Logs">
                          <Button icon={<HistoryOutlined style={{ color: ac('#546e7a') }} />} onClick={() => openLogs(rec)} />
                        </Tooltip>
                      )}
                      {(cfg.extraActions || []).filter((a) => !a.first).map((a) => (
                        (typeof a.show === 'function' ? a.show(m, rec) : true) && typeof m[a.method] === 'function' ? (
                          <Tooltip key={a.key} title={a.tip}>
                            <Button
                              icon={React.cloneElement(a.icon, { style: { color: ac(a.color) } })}
                              onClick={() => m[a.method](rec.id, rec)}
                            />
                          </Tooltip>
                        ) : null
                      ))}
                      {rowDeletable(rec) && cfg.deleteLast && (
                        <Tooltip title={cfg.deleteTip || 'Delete'}>
                          <Button
                            icon={React.cloneElement(
                              cfg.deleteIcon || <DeleteOutlined />, { style: { color: ac('#d32f2f') } },
                            )}
                            onClick={() => doDelete(rec)}
                          />
                        </Tooltip>
                      )}
                    </div>
                    )}
                  </div>
                </Card>
              );
            })}
          </div>

          <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: 16 }}>
            <Pagination
              current={page}
              pageSize={pageSize}
              total={total}
              onChange={onPage}
              showSizeChanger={!!(m && m.showPageSizeChanger)}
              pageSizeOptions={['8', '16', '24', '50', '100']}
              showTotal={(t) => `${t} records`}
            />
          </div>
        </>
      )}

      {currentElement && m && typeof m.getTableChildComponents === 'function'
        && m.getTableChildComponents() && (
        cfg.childSelfModal
          // The child component renders its OWN modal (e.g. AttendanceModal,
          // controlled by the `element` prop, closed via setCurrentElement(null)).
          // Render it directly — wrapping it in our Modal would show two modals.
          ? React.cloneElement(m.getTableChildComponents(), {
            element: currentElement, adapter: m, loading: false,
          })
          : (
            <Modal
              open
              width={1040}
              style={{ top: 24 }}
              footer={null}
              title={titleOf(currentElement)}
              onCancel={() => {
                setCurrentElement(null);
                try { if (m.hideElement) m.hideElement(); } catch (e) { /* ignore */ }
              }}
              styles={{ body: { maxHeight: '82vh', overflowY: 'auto' } }}
            >
              {React.cloneElement(m.getTableChildComponents(), {
                element: currentElement, adapter: m, loading: false,
              })}
            </Modal>
          )
      )}

      {nativeDoc && (
        <Modal
          open
          width="92%"
          style={{ top: 16, maxWidth: 1280 }}
          footer={null}
          title={(docAction && docAction.label) || 'Document'}
          onCancel={() => setNativeDoc(null)}
          styles={{ body: { padding: 16, minHeight: '60vh', maxHeight: '86vh', overflowY: 'auto' } }}
          destroyOnClose
        >
          <div ref={nativeElRef} />
        </Modal>
      )}

      {editorUrl && (
        <Modal
          open
          width="92%"
          style={{ top: 16, maxWidth: 1280 }}
          footer={null}
          title={(docAction && docAction.label) || 'Document'}
          onCancel={() => { setEditorUrl(null); load(); }}
          styles={{ body: { padding: 0, height: '82vh' } }}
          destroyOnClose
        >
          <iframe
            title={(docAction && docAction.label) || 'Document'}
            src={editorUrl}
            style={{ width: '100%', height: '100%', border: 0, display: 'block' }}
            onLoad={(e) => {
              try {
                const doc = e.target.contentDocument;
                if (doc && doc.head) {
                  const style = doc.createElement('style');
                  style.textContent = EMBED_CSS;
                  doc.head.appendChild(style);
                }
              } catch (err) { /* cross-origin — ignore */ }
            }}
          />
        </Modal>
      )}

      {statusRec && (
        <Modal
          open
          title="Change Status"
          okText="Update"
          confirmLoading={statusSaving}
          onOk={submitStatus}
          okButtonProps={{ disabled: !statusValue || (cfg.requireStatusReason && !statusReason.trim()) }}
          onCancel={() => setStatusRec(null)}
        >
          <div style={{ display: 'flex', flexDirection: 'column', gap: 12, paddingTop: 8 }}>
            <div>
              <div style={{ marginBottom: 6, color: token.colorTextSecondary }}>New status</div>
              <Select
                style={{ width: '100%' }}
                value={statusValue}
                onChange={setStatusValue}
                options={statusOptionsFor(statusRec)}
              />
            </div>
            <div>
              <div style={{ marginBottom: 6, color: token.colorTextSecondary }}>
                {cfg.requireStatusReason && <span style={{ color: token.colorError, marginRight: 4 }}>*</span>}
                {cfg.requireStatusReason ? 'Reason' : 'Reason (optional)'}
              </div>
              <Input.TextArea
                rows={3}
                value={statusReason}
                onChange={(e) => setStatusReason(e.target.value)}
                placeholder="Add a note for this status change…"
                status={cfg.requireStatusReason && !statusReason.trim() ? 'error' : undefined}
              />
            </div>
          </div>
        </Modal>
      )}

      {logsState && (
        <Modal
          open
          title="Approval Log"
          footer={null}
          onCancel={() => setLogsState(null)}
        >
          {logsState.loading ? (
            <div style={{ display: 'flex', justifyContent: 'center', padding: 32 }}><Spin /></div>
          ) : (logsState.rows.length ? (
            <Timeline
              items={logsState.rows.map((l) => ({
                children: (
                  <div>
                    <div style={{ fontWeight: 600 }}>
                      {`${l.status_from || ''} → ${l.status_to || ''}`}
                    </div>
                    <div style={{ color: token.colorTextSecondary, fontSize: 12 }}>{l.time}</div>
                    {l.note ? <div style={{ marginTop: 2 }}>{l.note}</div> : null}
                  </div>
                ),
              }))}
            />
          ) : <Empty description="No approval history" />)}
        </Modal>
      )}

      {cfg.expenseDialog && (
        <ExpenseDialog
          open={!!expenseDialog}
          rec={expenseDialog ? expenseDialog.rec : null}
          mode={expenseDialog ? expenseDialog.mode : 'view'}
          onClose={() => setExpenseDialog(null)}
          onSaved={() => load()}
          shellConfig={shellConfig}
          statusOptionsFor={cfg.dialogStatusChange ? statusOptionsForStatus : null}
        />
      )}

      {genCfg && (
        <Modal
          title={genCfg.label || 'Generate'}
          open={genOpen}
          onCancel={() => setGenOpen(false)}
          onOk={doGenerate}
          okText={genCfg.label || 'Generate'}
          confirmLoading={generating}
          okButtonProps={{ disabled: !genIndustry }}
        >
          <p style={{ marginTop: 0 }}>
            {`Select an industry to add a set of common ${genCfg.itemsLabel || 'items'}. Existing entries are skipped.`}
          </p>
          <Select
            style={{ width: '100%' }}
            placeholder="Select an industry"
            loading={genLoading}
            value={genIndustry}
            onChange={setGenIndustry}
            showSearch
            optionFilterProp="label"
            options={genIndustries.map((i) => ({ value: i.key, label: i.label }))}
          />
          {genCfg.allowDeleteExisting && (
            <div style={{ marginTop: 16 }}>
              <Checkbox checked={genDelete} onChange={(e) => setGenDelete(e.target.checked)}>
                {genCfg.deleteExistingLabel || 'Delete existing entries first'}
              </Checkbox>
              <div style={{ color: token.colorTextSecondary, fontSize: 12, marginTop: 4, marginLeft: 24 }}>
                Only entries not currently in use are removed; those in use are kept.
              </div>
            </div>
          )}
        </Modal>
      )}
    </div>
  );
}
