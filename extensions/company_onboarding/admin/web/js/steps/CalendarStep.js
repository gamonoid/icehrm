import React, { useEffect, useState } from 'react';
import {
  Alert, Button, Divider, Input, InputNumber, List, Popconfirm, Segmented, Skeleton, Space, Typography, message,
} from 'antd';
import { CloudDownloadOutlined, DeleteOutlined, PlusOutlined } from '@ant-design/icons';
import { t, tp } from '../../../../../../web/api/i18n';
import { errorMessage } from '../api';
import StepFooter from '../components/StepFooter';

const { Title, Text } = Typography;

const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

function dayLabel(day) {
  switch (day) {
    case 'Monday': return t('Monday');
    case 'Tuesday': return t('Tuesday');
    case 'Wednesday': return t('Wednesday');
    case 'Thursday': return t('Thursday');
    case 'Friday': return t('Friday');
    case 'Saturday': return t('Saturday');
    default: return t('Sunday');
  }
}

function formatDate(value) {
  const d = new Date(`${value}T00:00:00`);
  if (Number.isNaN(d.getTime())) return value;
  return d.toLocaleDateString(undefined, {
    weekday: 'short', day: 'numeric', month: 'short', year: 'numeric',
  });
}

/** Step 5 (optional): which days are worked, and the public holidays. */
export default function CalendarStep({
  api, step, busy, onContinue, onSkip,
}) {
  const [year, setYear] = useState(new Date().getFullYear());
  const [data, setData] = useState(null);
  const [days, setDays] = useState(null);
  const [draft, setDraft] = useState({ name: '', date: '' });
  const [working, setWorking] = useState(null);
  const [notice, setNotice] = useState(null);

  const load = (y) => api.get('company-onboarding/calendar', { year: y })
    .then((d) => { setData(d); setDays((current) => current || d.workdays); })
    .catch((e) => message.error(errorMessage(e, t('The calendar could not be loaded'))));
  useEffect(() => { load(year); }, [year]);

  if (!data || !days) return <Skeleton active />;

  const run = (key, promise, after) => {
    setWorking(key);
    return promise
      .then((res) => { if (after) after(res); return load(year); })
      .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))))
      .finally(() => setWorking(null));
  };

  const importHolidays = () => {
    setNotice(null);
    run('import', api.post('company-onboarding/holidays-import', { year }), (res) => {
      setNotice({ type: 'success', text: tp(res.added, '{count} public holiday added', '{count} public holidays added') });
    }).catch(() => {});
  };
  const addHoliday = () => run('add', api.post('company-onboarding/holidays', draft), () => setDraft({ name: '', date: '' }));
  const removeHoliday = (h) => run(`del-${h.id}`, api.del(`company-onboarding/holidays/${h.id}`));

  // The work week is saved when the step is completed.
  const finish = () => {
    setWorking('days');
    api.post('company-onboarding/workdays', days)
      .then(onContinue)
      .catch((e) => message.error(errorMessage(e, t('The work week could not be saved'))))
      .finally(() => setWorking(null));
  };

  const statusOptions = [
    { value: 'Full Day', label: t('Full day') },
    { value: 'Half Day', label: t('Half day') },
    { value: 'Non-working Day', label: t('Day off') },
  ];

  return (
    <>
      <Title level={5} style={{ marginTop: 0 }}>{t('Work week')}</Title>
      <div style={{ display: 'grid', gridTemplateColumns: 'minmax(90px, 140px) 1fr', gap: '8px 12px', alignItems: 'center', maxWidth: 520 }}>
        {DAYS.map((day) => (
          <React.Fragment key={day}>
            <Text>{dayLabel(day)}</Text>
            <Segmented size="small" value={days[day]} options={statusOptions} onChange={(v) => setDays({ ...days, [day]: v })} />
          </React.Fragment>
        ))}
      </div>

      <Divider />
      <div style={{
        display: 'flex', justifyContent: 'space-between', alignItems: 'center', flexWrap: 'wrap', gap: 8,
      }}
      >
        <Title level={5} style={{ margin: 0 }}>{t('Public holidays')}</Title>
        <Space wrap>
          <InputNumber min={2000} max={2100} value={year} onChange={(v) => v && setYear(v)} style={{ width: 100 }} />
          <Button icon={<CloudDownloadOutlined />} loading={working === 'import'} onClick={importHolidays}>{t('Load public holidays')}</Button>
        </Space>
      </div>
      <Text type="secondary" style={{ display: 'block', margin: '6px 0 10px', fontSize: 12 }}>
        {t('Loads the national public holidays of your company\'s country. Check them, and add company holidays yourself.')}
      </Text>
      {notice ? <Alert type={notice.type} showIcon closable onClose={() => setNotice(null)} message={notice.text} style={{ marginBottom: 10 }} /> : null}
      <Space.Compact style={{ width: '100%', maxWidth: 560 }}>
        <Input value={draft.name} onChange={(e) => setDraft({ ...draft, name: e.target.value })} placeholder={t('Holiday name')} maxLength={100} />
        <Input type="date" value={draft.date} onChange={(e) => setDraft({ ...draft, date: e.target.value })} style={{ width: 190, flex: 'none' }} />
        <Button type="primary" icon={<PlusOutlined />} loading={working === 'add'} disabled={!draft.name.trim() || !draft.date} onClick={addHoliday}>{t('Add')}</Button>
      </Space.Compact>
      <List
        size="small"
        style={{ marginTop: 10 }}
        locale={{ emptyText: t('No holidays for {year} yet', { year }) }}
        dataSource={data.holidays}
        renderItem={(h) => (
          <List.Item actions={[
            <Popconfirm key="del" title={t('Remove {name}?', { name: h.name })} okText={t('Remove')} okButtonProps={{ danger: true }} cancelText={t('Cancel')} onConfirm={() => removeHoliday(h)}>
              <Button size="small" type="text" danger icon={<DeleteOutlined />} loading={working === `del-${h.id}`} />
            </Popconfirm>,
          ]}
          >
            <Space>
              <Text style={{ minWidth: 150, display: 'inline-block' }}>{formatDate(h.date)}</Text>
              <Text>{h.name}</Text>
              {h.status === 'Half Day' ? <Text type="secondary">{t('Half day')}</Text> : null}
            </Space>
          </List.Item>
        )}
      />
      <StepFooter step={step} busy={busy || working === 'days'} onSkip={onSkip} onContinue={finish} continueLabel={t('Save and continue')} />
    </>
  );
}
