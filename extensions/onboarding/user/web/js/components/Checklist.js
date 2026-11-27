import React, { useRef, useState } from 'react';
import {
  Alert, Button, Card, Checkbox, Input, Progress, Space, Typography, message, theme,
} from 'antd';
import {
  CheckCircleFilled, ClockCircleOutlined, LinkOutlined, PlayCircleOutlined, UploadOutlined,
} from '@ant-design/icons';
import { t } from '../../../../../../web/api/i18n';
import { errorMessage } from '../../../../admin/web/js/shared/api';
import { formatDate, openModule, phaseLabel } from '../../../../admin/web/js/shared/constants';
import StepList from '../../../../admin/web/js/shared/StepList';

const { Title, Text, Paragraph } = Typography;

function Acknowledge({ step, onSubmit, busy }) {
  const { token } = theme.useToken();
  const [agree, setAgree] = useState(false);
  const [name, setName] = useState('');
  return (
    <div style={{ marginTop: 8 }}>
      {step.config.text ? (
        <div style={{
          maxHeight: 260, overflowY: 'auto', padding: 12, border: `1px solid ${token.colorBorderSecondary}`, borderRadius: 8, whiteSpace: 'pre-line',
        }}
        >
          {step.config.text}
        </div>
      ) : null}
      {step.config.link ? (
        <div style={{ marginTop: 8 }}>
          <a href={step.config.link} target="_blank" rel="noopener noreferrer">
            <LinkOutlined />
            {' '}
            {step.config.link_label || t('Read the document')}
          </a>
        </div>
      ) : null}
      {step.can_complete ? (
        <div style={{ marginTop: 12 }}>
          <Checkbox checked={agree} onChange={(e) => setAgree(e.target.checked)}>{t('I have read this and agree to follow it')}</Checkbox>
          <Space.Compact style={{ width: '100%', marginTop: 8, maxWidth: 480 }}>
            <Input placeholder={t('Type your full name')} value={name} onChange={(e) => setName(e.target.value)} maxLength={150} />
            <Button type="primary" disabled={!agree || !name.trim()} loading={busy} onClick={() => onSubmit({ agree: true, name })}>{t('Confirm')}</Button>
          </Space.Compact>
        </div>
      ) : null}
    </div>
  );
}

function DocumentUpload({ step, api, onSubmit, busy }) {
  const input = useRef(null);
  const [uploading, setUploading] = useState(false);
  const pick = (file) => {
    if (!file) return;
    const fd = new FormData();
    fd.append('file', file);
    setUploading(true);
    api.post('onboarding/me/upload', fd)
      .then((res) => onSubmit({ file: res.name, original: res.original }))
      .catch((e) => message.error(errorMessage(e, t('Upload failed'))))
      .finally(() => {
        setUploading(false);
        if (input.current) input.current.value = '';
      });
  };
  return (
    <div style={{ marginTop: 8 }}>
      {step.instructions ? <Paragraph type="secondary" style={{ whiteSpace: 'pre-line' }}>{step.instructions}</Paragraph> : null}
      {step.file ? (
        <Text style={{ display: 'block', marginBottom: 8 }}>
          {t('Uploaded: {name}', { name: step.file.name })}
        </Text>
      ) : null}
      {step.can_complete ? (
        <>
          <input ref={input} type="file" style={{ display: 'none' }} onChange={(e) => pick(e.target.files && e.target.files[0])} />
          <Button icon={<UploadOutlined />} loading={uploading || busy} onClick={() => input.current && input.current.click()}>
            {step.file ? t('Upload again') : t('Upload')}
          </Button>
          <Text type="secondary" style={{ marginLeft: 8, fontSize: 12 }}>{t('PDF, image or Office file, up to 10 MB')}</Text>
        </>
      ) : null}
    </div>
  );
}

/**
 * Open the step's e-signature or course. A signing request opens directly in My
 * Signatures, which offers a way back here (sessionStorage hand-off).
 */
function openLinked(step) {
  try {
    if (step.type === 'esign' && step.my_esign) {
      window.sessionStorage.setItem('esign-open', JSON.stringify({ id: step.my_esign, from: 'onboarding' }));
    }
  } catch (e) {
    // Storage blocked: the module opens on its list instead.
  }
  openModule(step.link_url);
}

function Linked({ step }) {
  const label = {
    esign: t('Review and sign'),
    course: t('Start the course'),
  }[step.type];
  return (
    <div style={{ marginTop: 8 }}>
      {step.instructions ? <Paragraph type="secondary" style={{ whiteSpace: 'pre-line' }}>{step.instructions}</Paragraph> : null}
      {step.status === 'Submitted' ? <Text type="secondary">{t('Submitted. HR will review it.')}</Text> : null}
      {['Pending', 'ChangesRequested'].includes(step.status) ? (
        step.link_url ? (
          <Button type="primary" icon={<PlayCircleOutlined />} onClick={() => openLinked(step)}>{label}</Button>
        ) : (
          <Text type="secondary">{t('This step is being prepared. Check back soon.')}</Text>
        )
      ) : null}
    </div>
  );
}

/** The new hire's onboarding: welcome, progress and their steps by phase. */
export default function Checklist({ api, data, onChanged }) {
  const [busy, setBusy] = useState(null);

  const submit = (step, body) => {
    setBusy(step.id);
    api.post(`onboarding/me/steps/${step.id}`, body || {})
      .then(() => {
        message.success(t('Done'));
        if (onChanged) onChanged();
      })
      .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))))
      .finally(() => setBusy(null));
  };

  const renderBody = (step) => {
    if (step.status === 'Done' || step.status === 'Skipped') {
      return step.acknowledgement ? (
        <Text type="secondary" style={{ display: 'block', marginTop: 4, fontSize: 12 }}>{t('Confirmed by {name}', { name: step.acknowledgement.name })}</Text>
      ) : null;
    }
    switch (step.type) {
      case 'info':
        return (
          <div style={{ marginTop: 8 }}>
            {step.config.text ? <Paragraph style={{ whiteSpace: 'pre-line' }}>{step.config.text}</Paragraph> : null}
            {step.config.link ? (
              <Paragraph>
                <a href={step.config.link} target="_blank" rel="noopener noreferrer">
                  <LinkOutlined />
                  {' '}
                  {step.config.link_label || step.config.link}
                </a>
              </Paragraph>
            ) : null}
            {step.can_complete ? <Button type="primary" ghost loading={busy === step.id} onClick={() => submit(step, {})}>{t('Got it')}</Button> : null}
          </div>
        );
      case 'acknowledge':
        return <Acknowledge step={step} busy={busy === step.id} onSubmit={(body) => submit(step, body)} />;
      case 'document':
        return <DocumentUpload step={step} api={api} busy={busy === step.id} onSubmit={(body) => submit(step, body)} />;
      default:
        return <Linked step={step} />;
    }
  };

  const pct = data.progress.total ? Math.round((data.progress.done / data.progress.total) * 100) : 0;
  const myDone = data.steps.filter((s) => s.status === 'Done' || s.status === 'Skipped').length;
  const myTotal = data.steps.length;
  const days = data.days_to_start;

  return (
    <div style={{ maxWidth: 860 }}>
      <Card
        style={{
          marginBottom: 20,
          background: 'linear-gradient(135deg, rgba(22,119,255,0.10), rgba(46,125,50,0.08))',
        }}
        styles={{ body: { padding: 20 } }}
      >
        <Title level={3} style={{ marginTop: 0 }}>{t('Welcome, {name}!', { name: data.first_name })}</Title>
        {data.welcome_message ? <Paragraph style={{ whiteSpace: 'pre-line', fontSize: 15 }}>{data.welcome_message}</Paragraph> : null}
        <Space size={16} wrap style={{ marginBottom: 8 }}>
          <Text strong>
            {data.track === 'workforce' ? t('Please check and complete your details.') : null}
            {data.track !== 'workforce' && days > 1 ? t('You start in {n} days, on {date}', { n: days, date: formatDate(data.start_date) }) : null}
            {data.track !== 'workforce' && days === 1 ? t('You start tomorrow') : null}
            {data.track !== 'workforce' && days === 0 ? t('Today is your first day!') : null}
            {data.track !== 'workforce' && days < 0 ? t('You started on {date}', { date: formatDate(data.start_date) }) : null}
          </Text>
          {data.welcome_video_url ? (
            <a href={data.welcome_video_url} target="_blank" rel="noopener noreferrer">
              <PlayCircleOutlined />
              {' '}
              {t('Watch the welcome video')}
            </a>
          ) : null}
        </Space>
        <Progress percent={myTotal ? Math.round((myDone / myTotal) * 100) : pct} />
        <Text type="secondary">{t('{done} of {total} of your steps done', { done: myDone, total: myTotal })}</Text>
        {data.first_day_info ? (
          <Alert type="info" showIcon style={{ marginTop: 12 }} message={t('Your first day')} description={<span style={{ whiteSpace: 'pre-line' }}>{data.first_day_info}</span>} />
        ) : null}
      </Card>

      {myTotal > 0 && myDone === myTotal ? (
        <Alert type="success" showIcon icon={<CheckCircleFilled />} style={{ marginBottom: 16 }} message={t('You have done everything on your list. Thank you!')} />
      ) : null}

      <StepList steps={data.steps} showOwner={false} renderBody={renderBody} track={data.track} />

      {data.team_steps && data.team_steps.length ? (
        <Card size="small" title={t('What the team is getting ready for you')} style={{ marginTop: 8 }}>
          {data.team_steps.map((s) => (
            <div key={s.id} style={{ display: 'flex', gap: 8, padding: '4px 0' }}>
              {s.status === 'Done' ? <CheckCircleFilled style={{ color: '#2e7d32', marginTop: 4 }} /> : <ClockCircleOutlined style={{ color: '#9e9e9e', marginTop: 4 }} />}
              <Text type={s.status === 'Done' ? 'secondary' : undefined}>{s.title}</Text>
              <Text type="secondary" style={{ fontSize: 12, marginLeft: 'auto' }}>{phaseLabel(s.phase, data.track)}</Text>
            </div>
          ))}
        </Card>
      ) : null}
    </div>
  );
}
