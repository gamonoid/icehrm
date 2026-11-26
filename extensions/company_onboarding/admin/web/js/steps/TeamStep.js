import React, { useEffect, useMemo, useState } from 'react';
import {
  Alert, Button, Input, Modal, Progress, Result, Select, Skeleton, Space, Table, Tag, Tooltip, Typography, message,
} from 'antd';
import {
  DeleteOutlined, ExclamationCircleOutlined, PlusOutlined, SendOutlined, SnippetsOutlined,
} from '@ant-design/icons';
import { t, tp } from '../../../../../../web/api/i18n';
import { errorMessage } from '../api';
import StepFooter from '../components/StepFooter';

const { Text, Paragraph } = Typography;

const CHUNK = 25;
const DRAFT_KEY = 'company-onboarding-team-draft';
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

let rowSeq = 0;
function blankRow() {
  rowSeq += 1;
  return {
    key: `r${new Date().getTime().toString(36)}${rowSeq}`,
    first_name: '',
    last_name: '',
    email: '',
    joined_date: '',
    job_title: null,
    department: null,
    employment_status: null,
  };
}

function loadDraft() {
  try {
    const rows = JSON.parse(window.localStorage.getItem(DRAFT_KEY) || 'null');
    return Array.isArray(rows) && rows.length ? rows : null;
  } catch (e) {
    return null;
  }
}

function saveDraft(rows) {
  try {
    window.localStorage.setItem(DRAFT_KEY, JSON.stringify(rows));
  } catch (e) {
    // Storage blocked: the grid just isn't kept between visits.
  }
}

/** "2024-03-01", "01/03/2024" (day first) or "2024/3/1" -> "2024-03-01", else ''. */
function normaliseDate(value) {
  const v = String(value || '').trim();
  let m = v.match(/^(\d{4})[-/.](\d{1,2})[-/.](\d{1,2})$/);
  if (m) return `${m[1]}-${m[2].padStart(2, '0')}-${m[3].padStart(2, '0')}`;
  m = v.match(/^(\d{1,2})[-/.](\d{1,2})[-/.](\d{4})$/);
  if (m) return `${m[3]}-${m[2].padStart(2, '0')}-${m[1].padStart(2, '0')}`;
  return '';
}

const filterOption = (input, option) => String(option.label || '').toLowerCase().includes(input.toLowerCase());

/**
 * Step 7 (required): add the company's existing employees and invite them. Each gets a
 * workforce onboarding (Onboarding extension) with an invitation to set up their
 * account; their manager is the admin sending it until HR sets the real one.
 */
export default function TeamStep({
  api, step, summary, busy, onContinue, onSkip,
}) {
  const [lookups, setLookups] = useState(null);
  const [choices, setChoices] = useState([]);
  const [rows, setRows] = useState(() => loadDraft() || [blankRow(), blankRow(), blankRow()]);
  const [workflow, setWorkflow] = useState(null);
  const [welcome, setWelcome] = useState('');
  const [pasteOpen, setPasteOpen] = useState(false);
  const [pasteText, setPasteText] = useState('');
  const [missing, setMissing] = useState(null); // {job_titles:[], departments:[], employment:[]}
  const [sending, setSending] = useState(null); // {done, total}
  const [results, setResults] = useState({}); // row key -> result
  const [invitedCount, setInvitedCount] = useState(0);

  const available = summary.onboarding_available;

  const loadLookups = () => api.get('onboarding/lookups').then(setLookups).catch(() => setLookups({}));
  useEffect(() => {
    if (!available) return;
    loadLookups();
    api.get('onboarding/workflow-choices', { track: 'workforce' }).then((c) => {
      setChoices(c || []);
      if (c && c.length && !workflow) setWorkflow(c[0].value);
    }).catch(() => setChoices([]));
    api.get('onboarding/context', { track: 'workforce' }).then((ctx) => {
      const counts = (ctx && ctx.counts) || {};
      setInvitedCount((counts.open || 0) + (counts.Completed || 0));
    }).catch(() => {});
  }, []);

  // Keep the grid between visits, without the people already invited.
  useEffect(() => {
    saveDraft(rows.filter((r) => !(results[r.key] && results[r.key].ok)));
  }, [rows, results]);

  const named = useMemo(() => {
    const index = (list) => {
      const m = {};
      (list || []).forEach((o) => { m[String(o.label).trim().toLowerCase()] = o.value; });
      return m;
    };
    const l = lookups || {};
    return {
      job_titles: index(l.job_titles),
      departments: index(l.departments),
      employment: index(l.employment_statuses),
    };
  }, [lookups]);

  if (!available) {
    return (
      <>
        <Alert type="info" showIcon message={t('Inviting your team needs the Onboarding extension.')} description={t('Enable New Hire Onboarding under System > Extensions, then come back to this step.')} />
        <StepFooter step={step} busy={busy} onSkip={onSkip} onContinue={onContinue} />
      </>
    );
  }
  if (!lookups) return <Skeleton active />;

  const setRow = (key, patch) => setRows(rows.map((r) => (r.key === key ? { ...r, ...patch } : r)));
  const removeRow = (key) => setRows(rows.filter((r) => r.key !== key));
  const filled = rows.filter((r) => r.first_name || r.last_name || r.email);

  const emailCounts = {};
  filled.forEach((r) => {
    const e = r.email.trim().toLowerCase();
    if (e) emailCounts[e] = (emailCounts[e] || 0) + 1;
  });
  const problemOf = (r) => {
    if (!r.first_name.trim()) return t('Enter the first name.');
    if (!r.last_name.trim()) return t('Enter the last name.');
    if (!EMAIL.test(r.email.trim())) return t('Enter a valid email address.');
    if (emailCounts[r.email.trim().toLowerCase()] > 1) return t('This email is in the list twice.');
    return null;
  };
  const ready = filled.filter((r) => !problemOf(r) && !(results[r.key] && results[r.key].ok));

  // Paste rows copied from a spreadsheet: first name, last name, email, joined date,
  // job title, department, employment type (tab separated, any header row skipped).
  const applyPaste = () => {
    const unknown = { job_titles: new Set(), departments: new Set(), employment: new Set() };
    const parsed = pasteText.split(/\r?\n/).map((line) => line.split('\t').map((c) => c.trim()))
      .filter((cells) => cells.some(Boolean))
      .filter((cells) => !(cells[2] && !EMAIL.test(cells[2]) && /mail/i.test(cells[2])))
      .map((cells) => {
        const pick = (list, value, set) => {
          if (!value) return null;
          const id = named[list][value.toLowerCase()];
          if (!id) set.add(value);
          return id || null;
        };
        return {
          ...blankRow(),
          first_name: cells[0] || '',
          last_name: cells[1] || '',
          email: cells[2] || '',
          joined_date: normaliseDate(cells[3]),
          job_title: pick('job_titles', cells[4], unknown.job_titles),
          department: pick('departments', cells[5], unknown.departments),
          employment_status: pick('employment', cells[6], unknown.employment),
          pasted: { job_title: cells[4] || '', department: cells[5] || '', employment_status: cells[6] || '' },
        };
      });
    const kept = rows.filter((r) => r.first_name || r.last_name || r.email);
    setRows([...kept, ...parsed]);
    setPasteText('');
    setPasteOpen(false);
    const m = {
      job_titles: [...unknown.job_titles],
      departments: [...unknown.departments],
      employment: [...unknown.employment],
    };
    setMissing(m.job_titles.length || m.departments.length || m.employment.length ? m : null);
  };

  // Add the job titles, departments and employment types the pasted rows named but
  // IceHrm does not have yet, then match the rows again.
  const addMissing = () => {
    const calls = [];
    if (missing.job_titles.length) calls.push(api.post('company-onboarding/lists/job_titles', { entries: missing.job_titles.map((name) => ({ name })) }));
    if (missing.employment.length) calls.push(api.post('company-onboarding/lists/employment', { entries: missing.employment.map((name) => ({ name })) }));
    missing.departments.forEach((title) => calls.push(api.post('company-onboarding/structure', { title, type: 'Department' })));
    Promise.all(calls)
      .then(() => api.get('onboarding/lookups'))
      .then((l) => {
        setLookups(l);
        const index = (list) => {
          const m = {};
          (list || []).forEach((o) => { m[String(o.label).trim().toLowerCase()] = o.value; });
          return m;
        };
        const jt = index(l.job_titles);
        const dp = index(l.departments);
        const es = index(l.employment_statuses);
        setRows((current) => current.map((r) => (r.pasted ? {
          ...r,
          job_title: r.job_title || jt[r.pasted.job_title.toLowerCase()] || null,
          department: r.department || dp[r.pasted.department.toLowerCase()] || null,
          employment_status: r.employment_status || es[r.pasted.employment_status.toLowerCase()] || null,
        } : r)));
        setMissing(null);
        message.success(t('Added'));
      })
      .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))));
  };

  // Invite the ready rows, 25 per request, so a large team does not time out.
  const send = () => {
    const queue = ready.slice();
    if (!queue.length || !workflow) return;
    const total = queue.length;
    const collected = { ...results };
    let processed = 0;
    let invited = 0;
    let unsent = 0;
    setSending({ done: 0, total });
    const next = () => {
      const chunk = queue.splice(0, CHUNK);
      if (!chunk.length) {
        setSending(null);
        setInvitedCount((n) => n + invited);
        if (invited && unsent) {
          // Added, but some invitation emails did not go out: say so, and where the links are.
          message.warning(`${tp(invited, '{count} person added.', '{count} people added.')} ${tp(unsent, '{count} invitation email could not be sent. Copy the link from the list.', '{count} invitation emails could not be sent. Copy their links from the list.')}`, 8);
        } else if (invited) {
          message.success(tp(invited, '{count} person invited', '{count} people invited'));
        }
        return Promise.resolve();
      }
      return api.post('onboarding/workforce/invite', {
        workflow,
        welcome_message: welcome,
        rows: chunk.map((r, i) => ({
          index: i,
          first_name: r.first_name.trim(),
          last_name: r.last_name.trim(),
          email: r.email.trim(),
          joined_date: r.joined_date || null,
          job_title: r.job_title,
          department: r.department,
          employment_status: r.employment_status,
        })),
      }).then((res) => {
        (res.results || []).forEach((result) => {
          const row = chunk[result.index];
          if (row) collected[row.key] = result;
        });
        processed += chunk.length;
        invited += chunk.filter((r) => collected[r.key] && collected[r.key].ok).length;
        unsent += chunk.filter((r) => collected[r.key] && collected[r.key].ok && !collected[r.key].sent).length;
        setResults({ ...collected });
        setSending({ done: processed, total });
        return next();
      });
    };
    next().catch((e) => {
      setSending(null);
      setInvitedCount((n) => n + invited);
      message.error(errorMessage(e, t('The invitations could not be sent')));
    });
  };

  const sentRows = rows.filter((r) => results[r.key] && results[r.key].ok);
  const clearSent = () => {
    const left = rows.filter((r) => !(results[r.key] && results[r.key].ok));
    setRows(left.length ? left : [blankRow()]);
  };

  const columns = [
    {
      title: t('First name'),
      dataIndex: 'first_name',
      width: 150,
      render: (v, r) => <Input size="small" value={v} onChange={(e) => setRow(r.key, { first_name: e.target.value })} maxLength={100} />,
    },
    {
      title: t('Last name'),
      dataIndex: 'last_name',
      width: 150,
      render: (v, r) => <Input size="small" value={v} onChange={(e) => setRow(r.key, { last_name: e.target.value })} maxLength={100} />,
    },
    {
      title: t('Email'),
      dataIndex: 'email',
      width: 240,
      render: (v, r) => <Input size="small" value={v} onChange={(e) => setRow(r.key, { email: e.target.value })} maxLength={100} />,
    },
    {
      title: t('Joined date'),
      dataIndex: 'joined_date',
      width: 150,
      render: (v, r) => <Input size="small" type="date" value={v} onChange={(e) => setRow(r.key, { joined_date: e.target.value })} />,
    },
    {
      title: t('Job title'),
      dataIndex: 'job_title',
      width: 170,
      render: (v, r) => <Select size="small" style={{ width: '100%' }} allowClear showSearch filterOption={filterOption} value={v || undefined} options={lookups.job_titles || []} onChange={(x) => setRow(r.key, { job_title: x || null })} />,
    },
    {
      title: t('Department'),
      dataIndex: 'department',
      width: 170,
      render: (v, r) => <Select size="small" style={{ width: '100%' }} allowClear showSearch filterOption={filterOption} value={v || undefined} options={lookups.departments || []} onChange={(x) => setRow(r.key, { department: x || null })} />,
    },
    {
      title: t('Employment type'),
      dataIndex: 'employment_status',
      width: 150,
      render: (v, r) => <Select size="small" style={{ width: '100%' }} allowClear showSearch filterOption={filterOption} value={v || undefined} options={lookups.employment_statuses || []} onChange={(x) => setRow(r.key, { employment_status: x || null })} />,
    },
    {
      title: '',
      key: 'state',
      width: 190,
      // Kept in view while the wide grid scrolls, so each row's result is visible next to the names.
      fixed: 'right',
      render: (_, r) => {
        const result = results[r.key];
        if (result && result.ok) {
          return result.sent ? <Tag color="green">{t('Invited')}</Tag> : (
            <Tooltip title={t('Added, but the invitation email could not be sent. Copy the link and share it yourself.')}>
              <span style={{ whiteSpace: 'nowrap' }}>
                <Tag color="error" icon={<ExclamationCircleOutlined />}>{t('Email not sent')}</Tag>
                {result.url ? <Text copyable={{ text: result.url }} style={{ fontSize: 12, whiteSpace: 'nowrap' }}>{t('Link')}</Text> : null}
              </span>
            </Tooltip>
          );
        }
        if (result && !result.ok) {
          const text = Object.values(result.errors || {}).join(' ');
          return <Tooltip title={text}><Tag color="red">{text.length > 22 ? `${text.slice(0, 22)}…` : text}</Tag></Tooltip>;
        }
        const problem = (r.first_name || r.last_name || r.email) ? problemOf(r) : null;
        if (problem) return <Tooltip title={problem}><Tag color="orange">{t('Check')}</Tag></Tooltip>;
        return (
          <Button size="small" type="text" danger icon={<DeleteOutlined />} onClick={() => removeRow(r.key)} />
        );
      },
    },
  ];

  return (
    <>
      {invitedCount > 0 ? (
        <Alert type="success" showIcon style={{ marginBottom: 12 }} message={tp(invitedCount, '{count} person invited so far. Follow their progress on the People tab.', '{count} people invited so far. Follow their progress on the People tab.')} />
      ) : null}
      <Paragraph type="secondary" style={{ marginBottom: 8 }}>
        {t('Add each employee with the email they will use to sign in. Only first name, last name and email are needed; the rest they or you can fill in later.')}
      </Paragraph>
      <Space wrap style={{ marginBottom: 10 }}>
        <Button icon={<PlusOutlined />} onClick={() => setRows([...rows, blankRow()])}>{t('Add a row')}</Button>
        <Button icon={<SnippetsOutlined />} onClick={() => setPasteOpen(true)}>{t('Paste from a spreadsheet')}</Button>
        {sentRows.length ? <Button type="link" onClick={clearSent}>{t('Clear invited rows')}</Button> : null}
      </Space>

      {missing ? (
        <Alert
          type="warning"
          showIcon
          style={{ marginBottom: 10 }}
          message={t('Some names are not in IceHrm yet')}
          description={(
            <div>
              {missing.job_titles.length ? <div>{t('Job titles: {names}', { names: missing.job_titles.join(', ') })}</div> : null}
              {missing.departments.length ? <div>{t('Departments: {names}', { names: missing.departments.join(', ') })}</div> : null}
              {missing.employment.length ? <div>{t('Employment types: {names}', { names: missing.employment.join(', ') })}</div> : null}
            </div>
          )}
          action={(
            <Space direction="vertical">
              <Button size="small" type="primary" onClick={addMissing}>{t('Add them')}</Button>
              <Button size="small" onClick={() => setMissing(null)}>{t('Ignore')}</Button>
            </Space>
          )}
        />
      ) : null}

      <Table
        size="small"
        rowKey="key"
        columns={columns}
        dataSource={rows}
        pagination={rows.length > 50 ? { pageSize: 50 } : false}
        scroll={{ x: 1370 }}
      />

      <div style={{ marginTop: 20, maxWidth: 720 }}>
        <Text strong>{t('Workflow')}</Text>
        <Select
          style={{ width: '100%', margin: '4px 0 12px' }}
          value={workflow || undefined}
          onChange={setWorkflow}
          options={[
            { label: t('Standard workflows'), options: choices.filter((c) => c.group === 'standard').map((c) => ({ value: c.value, label: c.label })) },
            { label: t('Custom workflows'), options: choices.filter((c) => c.group === 'custom').map((c) => ({ value: c.value, label: c.label })) },
          ].filter((g) => g.options.length)}
          placeholder={t('Choose a workflow')}
        />
        <Text strong>{t('Message to your team (optional)')}</Text>
        <Input.TextArea
          style={{ marginTop: 4 }}
          value={welcome}
          onChange={(e) => setWelcome(e.target.value)}
          autoSize={{ minRows: 2, maxRows: 6 }}
          maxLength={5000}
          placeholder={t('For example: We are moving our HR to IceHrm. Please set up your account and check your details by Friday.')}
        />
        <Text type="secondary" style={{ display: 'block', marginTop: 6, fontSize: 12 }}>
          {t('Everyone gets an email with a link to set up their account. Until you set their manager, you are their manager.')}
        </Text>
      </div>

      {sending ? (
        <Progress style={{ marginTop: 16 }} percent={sending.total ? Math.round((sending.done / sending.total) * 100) : 0} status="active" />
      ) : null}

      <StepFooter
        step={step}
        busy={busy || !!sending}
        onSkip={onSkip}
        onContinue={onContinue}
        continueLabel={t('Continue')}
        continueDisabled={invitedCount === 0 && sentRows.length === 0}
        extra={(
          <Button type="primary" icon={<SendOutlined />} loading={!!sending} disabled={!ready.length || !workflow} onClick={send}>
            {ready.length ? tp(ready.length, 'Invite {count} person', 'Invite {count} people') : t('Invite')}
          </Button>
        )}
      />

      <Modal
        open={pasteOpen}
        title={t('Paste from a spreadsheet')}
        okText={t('Add to the list')}
        okButtonProps={{ disabled: !pasteText.trim() }}
        onOk={applyPaste}
        onCancel={() => setPasteOpen(false)}
        width={720}
      >
        <Paragraph type="secondary">
          {t('Copy the rows from Excel or Google Sheets and paste them here, with the columns in this order:')}
        </Paragraph>
        <Paragraph code>
          {[t('First name'), t('Last name'), t('Email'), t('Joined date'), t('Job title'), t('Department'), t('Employment type')].join(' | ')}
        </Paragraph>
        <Input.TextArea value={pasteText} onChange={(e) => setPasteText(e.target.value)} autoSize={{ minRows: 6, maxRows: 14 }} />
      </Modal>
    </>
  );
}
