import { t } from '../../../../../../web/api/i18n';

export const PHASES = ['before', 'day1', 'week1', 'month1'];

export function phaseLabel(phase, track) {
  // Existing staff (workforce track) have no first day.
  if (track === 'workforce') {
    switch (phase) {
      case 'before':
      case 'day1': return t('First steps');
      case 'week1': return t('Within a week');
      case 'month1': return t('Later');
      default: return phase;
    }
  }
  switch (phase) {
    case 'before': return t('Before the first day');
    case 'day1': return t('First day');
    case 'week1': return t('First week');
    case 'month1': return t('First 90 days');
    default: return phase;
  }
}

// No 'form' step: IceHrm open source has no Forms extension.
export const STEP_TYPES = ['info', 'acknowledge', 'document', 'task', 'esign', 'course', 'asset', 'set_manager'];

export function typeLabel(type) {
  switch (type) {
    case 'info': return t('Information');
    case 'acknowledge': return t('Read and acknowledge');
    case 'document': return t('Document upload');
    case 'task': return t('Task');
    case 'esign': return t('E-signature');
    case 'course': return t('Course');
    case 'asset': return t('Hand over an asset');
    case 'set_manager': return t('Set manager');
    default: return type;
  }
}

export function typeHelp(type) {
  switch (type) {
    case 'info': return t('Something for the new hire to read, such as a welcome note or first-day details.');
    case 'acknowledge': return t('A policy the new hire reads and confirms with their name.');
    case 'document': return t('The new hire uploads a document; it is saved to their Documents.');
    case 'task': return t('Work for HR, IT, the manager, the buddy or a named person.');
    case 'esign': return t('A document from E-Sign that the new hire signs.');
    case 'course': return t('A Learn course the new hire completes.');
    case 'asset': return t('HR hands over equipment from Company Assets.');
    case 'set_manager': return t('HR chooses who the person reports to, once they have accepted the invitation.');
    default: return '';
  }
}

/** Step types the new hire completes; the others are done by someone else. */
export const NEW_HIRE_TYPES = ['info', 'acknowledge', 'document', 'esign', 'course'];

export function ownerLabel(owner, track) {
  switch (owner) {
    case 'new_hire': return track === 'workforce' ? t('Employee') : t('New hire');
    case 'hr': return t('HR');
    case 'manager': return t('Manager');
    case 'buddy': return t('Buddy');
    case 'employee': return t('A named person');
    default: return owner;
  }
}

export function stepStatusLabel(status) {
  switch (status) {
    case 'Pending': return t('To do');
    case 'Submitted': return t('Waiting for review');
    case 'ChangesRequested': return t('Changes requested');
    case 'Done': return t('Done');
    case 'Skipped': return t('Skipped');
    default: return status;
  }
}

export function stepStatusColor(status) {
  switch (status) {
    case 'Submitted': return 'blue';
    case 'ChangesRequested': return 'orange';
    case 'Done': return 'green';
    case 'Skipped': return 'default';
    default: return 'default';
  }
}

export function hireStatusLabel(status) {
  switch (status) {
    case 'Invited': return t('Invited');
    case 'InProgress': return t('In progress');
    case 'Completed': return t('Completed');
    case 'Cancelled': return t('Cancelled');
    default: return status;
  }
}

export function hireStatusColor(status) {
  switch (status) {
    case 'Invited': return 'gold';
    case 'InProgress': return 'blue';
    case 'Completed': return 'green';
    case 'Cancelled': return 'default';
    default: return 'default';
  }
}

export function invitationLabel(state) {
  switch (state) {
    case 'valid': return t('Invitation sent');
    case 'email_failed': return t('Invitation email not sent');
    case 'expired': return t('Invitation expired');
    case 'revoked': return t('Invitation revoked');
    default: return null;
  }
}

export function dueLabel(offset, track) {
  const n = Number(offset) || 0;
  // Existing staff: due dates count from the day the invitation is sent.
  if (track === 'workforce') {
    if (n <= 0) return t('When the invitation is sent');
    return n === 1 ? t('1 day after the invitation') : t('{n} days after the invitation', { n });
  }
  if (n === 0) return t('On the first day');
  if (n < 0) return n === -1 ? t('1 day before the start') : t('{n} days before the start', { n: -n });
  return n === 1 ? t('1 day after the start') : t('{n} days after the start', { n });
}

export function formatDate(value) {
  if (!value) return '';
  const d = new Date(`${String(value).slice(0, 10)}T00:00:00`);
  if (Number.isNaN(d.getTime())) return value;
  return d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
}

export function formatDateTime(value) {
  if (!value) return '';
  const d = new Date(String(value).replace(' ', 'T'));
  if (Number.isNaN(d.getTime())) return value;
  return d.toLocaleString(undefined, {
    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
  });
}

/** Open another module of the app, e.g. "g=extension&n=onboarding|user". */
export function openModule(url) {
  if (!url) return;
  const params = {};
  String(url).split('&').forEach((part) => {
    const [k, v] = part.split('=');
    params[k] = v;
  });
  if (!params.g || !params.n) return;
  window.location.hash = `#${encodeURIComponent(`${params.g}::${params.n}`)}`;
}
