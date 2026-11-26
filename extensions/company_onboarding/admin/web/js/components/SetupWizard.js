import React, { useEffect, useState } from 'react';
import {
  Alert, Button, Card, Col, Popconfirm, Result, Row, Space, Spin, Steps, Tag, Typography, message, theme,
} from 'antd';
import { CheckCircleFilled, MinusCircleOutlined } from '@ant-design/icons';
import { t } from '../../../../../../web/api/i18n';
import { errorMessage } from '../api';
import {
  DONE, NOT_STARTED, SKIPPED, stepInfo,
} from '../constants';
import StartCleanStep from '../steps/StartCleanStep';
import CompanyStep from '../steps/CompanyStep';
import StructureStep from '../steps/StructureStep';
import JobTitlesStep from '../steps/JobTitlesStep';
import EmploymentStep from '../steps/EmploymentStep';
import CalendarStep from '../steps/CalendarStep';
import LeaveStep from '../steps/LeaveStep';
import TeamStep from '../steps/TeamStep';
import WhatsNext from './WhatsNext';

const { Text, Paragraph } = Typography;

const STEP_COMPONENTS = {
  clean: StartCleanStep,
  company: CompanyStep,
  structure: StructureStep,
  job_titles: JobTitlesStep,
  employment: EmploymentStep,
  calendar: CalendarStep,
  leave: LeaveStep,
  team: TeamStep,
};

const FINISHED = '__finished';

/** The first step that is neither done nor skipped, else the "what's next" page. */
function firstOpen(summary) {
  const open = summary.steps.find((s) => s.status === NOT_STARTED);
  return open ? open.key : FINISHED;
}

/**
 * Where the wizard opens. "Invite an Employee" (Employees module) asks for the invite
 * step, which opens "Invite your team" whether or not the earlier steps are done: the
 * step creates any job titles, employment types and departments it needs itself.
 * Otherwise the setup carries on where it was left.
 */
function initialStep(summary) {
  let wanted = null;
  try {
    wanted = window.sessionStorage.getItem('company-onboarding-open');
    window.sessionStorage.removeItem('company-onboarding-open');
  } catch (e) {
    wanted = null;
  }
  if (wanted === 'invite' && summary.steps.some((s) => s.key === 'team')) {
    return 'team';
  }
  return firstOpen(summary);
}

export default function SetupWizard({ api }) {
  const { token } = theme.useToken();
  const [summary, setSummary] = useState(null);
  const [current, setCurrent] = useState(null);
  const [error, setError] = useState(null);
  const [busy, setBusy] = useState(false);

  const load = (keepCurrent) => api.get('company-onboarding/setup')
    .then((s) => {
      setSummary(s);
      if (!keepCurrent) setCurrent(initialStep(s));
    })
    .catch((e) => setError(errorMessage(e, t('The setup could not be loaded'))));
  useEffect(() => { load(false); }, []);

  if (error) return <Result status="warning" title={error} />;
  if (!summary || !current) return <div style={{ padding: 40, textAlign: 'center' }}><Spin /></div>;

  const steps = summary.steps;
  const index = steps.findIndex((s) => s.key === current);
  const step = index >= 0 ? steps[index] : null;

  // Save a step's status, then move on to the next step that is still open.
  const mark = (status) => {
    setBusy(true);
    return api.post(`company-onboarding/setup/steps/${step.key}`, { status })
      .then((s) => {
        setSummary(s);
        const after = s.steps.slice(index + 1).find((x) => x.status === NOT_STARTED);
        setCurrent(after ? after.key : firstOpen(s));
      })
      .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))))
      .finally(() => setBusy(false));
  };

  const handled = steps.filter((s) => s.status !== NOT_STARTED).length;
  const items = steps.map((s) => {
    const info = stepInfo(s.key);
    let icon;
    if (s.status === DONE) icon = <CheckCircleFilled style={{ color: token.colorSuccess }} />;
    if (s.status === SKIPPED) icon = <MinusCircleOutlined style={{ color: token.colorTextTertiary }} />;
    return {
      title: info.title,
      description: (
        <Space size={4} wrap>
          {s.status === SKIPPED ? <Tag>{t('Skipped')}</Tag> : null}
          {s.required ? <Tag color="blue">{t('Required')}</Tag> : <Tag>{t('Optional')}</Tag>}
        </Space>
      ),
      status: s.key === current ? 'process' : (s.status === NOT_STARTED ? 'wait' : 'finish'),
      icon,
    };
  });

  const StepComponent = step ? STEP_COMPONENTS[step.key] : null;
  const info = step ? stepInfo(step.key) : null;

  return (
    <Row gutter={[16, 16]}>
      <Col xs={24} lg={8} xl={7}>
        <Card size="small">
          <Text type="secondary" style={{ display: 'block', marginBottom: 12 }}>
            {t('{done} of {total} steps', { done: handled, total: steps.length })}
          </Text>
          <Steps
            direction="vertical"
            size="small"
            current={index >= 0 ? index : steps.length}
            items={items}
            onChange={(i) => setCurrent(steps[i].key)}
          />
          <Button type="link" style={{ padding: 0 }} onClick={() => setCurrent(FINISHED)}>{t('What\'s next')}</Button>
        </Card>
      </Col>
      <Col xs={24} lg={16} xl={17}>
        {current === FINISHED ? (
          <WhatsNext summary={summary} onOpenStep={setCurrent} />
        ) : (
          <Card
            title={info.title}
            extra={step.status !== NOT_STARTED ? (
              <Space size={8}>
                <Tag color={step.status === DONE ? 'green' : 'default'}>{step.status === DONE ? t('Done') : t('Skipped')}</Tag>
                <Popconfirm title={t('Mark this step as not done?')} okText={t('Yes')} cancelText={t('Cancel')} onConfirm={() => mark(NOT_STARTED)}>
                  <Button size="small" type="text">{t('Reopen')}</Button>
                </Popconfirm>
              </Space>
            ) : null}
          >
            <Paragraph type="secondary">{info.description}</Paragraph>
            {StepComponent ? (
              <StepComponent
                key={step.key}
                api={api}
                step={step}
                summary={summary}
                busy={busy}
                onContinue={() => mark(DONE)}
                onSkip={() => mark(SKIPPED)}
                onRefresh={() => load(true)}
              />
            ) : <Alert type="info" message={t('This step is not available.')} />}
          </Card>
        )}
      </Col>
    </Row>
  );
}
