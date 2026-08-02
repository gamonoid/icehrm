import {
  AuditAdapter,
  EmailLogAdapter,
} from './lib';
import IceDataPipe from '../../../api/IceDataPipe';

window.AuditAdapter = AuditAdapter;
window.EmailLogAdapter = EmailLogAdapter;

// Native (SPA shell) init — mirrors the inline <script> in core/admin/audit/index.php.
function init(data) {
  const modJsList = [];

  modJsList.tabAudit = new AuditAdapter('Audit', 'Audit', '', 'id desc');
  modJsList.tabAudit.setObjectTypeName('Audit Log Entry');
  modJsList.tabAudit.setDataPipe(new IceDataPipe(modJsList.tabAudit));
  modJsList.tabAudit.setAccess(data.permissions.Audit);
  modJsList.tabAudit.setShowDelete(false);
  modJsList.tabAudit.setShowAddNew(false);
  modJsList.tabAudit.setShowSave(false);

  modJsList.tabEmailLog = new EmailLogAdapter('EmailLogEntry', 'EmailLog', '', 'id desc');
  modJsList.tabEmailLog.setObjectTypeName('Email Log Entry');
  modJsList.tabEmailLog.setDataPipe(new IceDataPipe(modJsList.tabEmailLog));
  modJsList.tabEmailLog.setAccess(data.permissions.EmailLog);
  modJsList.tabEmailLog.setShowAddNew(false);
  modJsList.tabEmailLog.setShowSave(false);
  modJsList.tabEmailLog.setShowEdit(false);

  window.modJs = modJsList.tabAudit;
  window.modJsList = modJsList;
}

window.initAdminAudit = init;
