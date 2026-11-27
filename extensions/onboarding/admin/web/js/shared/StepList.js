import React from 'react';
import {
  Alert, Space, Tag, Typography, theme,
} from 'antd';
import {
  CheckCircleFilled, ClockCircleOutlined, FileOutlined, MinusCircleOutlined, UserOutlined,
} from '@ant-design/icons';
import { t } from '../../../../../../web/api/i18n';
import {
  PHASES, formatDate, formatDateTime, ownerLabel, phaseLabel, stepStatusColor, stepStatusLabel, typeLabel,
} from './constants';

const { Text, Paragraph } = Typography;

function StatusIcon({ status }) {
  if (status === 'Done') return <CheckCircleFilled style={{ color: '#2e7d32', fontSize: 18 }} />;
  if (status === 'Skipped') return <MinusCircleOutlined style={{ color: '#9e9e9e', fontSize: 18 }} />;
  return <ClockCircleOutlined style={{ color: status === 'Pending' ? '#9e9e9e' : '#1677ff', fontSize: 18 }} />;
}

/**
 * Steps grouped by phase. `renderActions(step)` adds the buttons the viewer may use;
 * `renderBody(step)` (optional) replaces the default details under the title;
 * `showProgress` false hides the "x of y done" counters (a workflow preview).
 */
export default function StepList({
  steps, renderActions, renderBody, showOwner = true, showProgress = true, track,
}) {
  const { token } = theme.useToken();
  const groups = PHASES.map((phase) => ({ phase, items: steps.filter((s) => s.phase === phase) }))
    .filter((g) => g.items.length > 0);

  return (
    <div>
      {groups.map((group) => {
        const done = group.items.filter((s) => s.status === 'Done' || s.status === 'Skipped').length;
        return (
          <div key={group.phase} style={{ marginBottom: 20 }}>
            <div style={{
              display: 'flex', justifyContent: 'space-between', alignItems: 'baseline', marginBottom: 8,
            }}
            >
              <Text strong style={{ fontSize: 15 }}>{phaseLabel(group.phase, track)}</Text>
              {showProgress ? (
                <Text type="secondary" style={{ fontSize: 12 }}>{t('{done} of {total} done', { done, total: group.items.length })}</Text>
              ) : null}
            </div>
            {group.items.map((step) => (
              <div
                key={step.id}
                style={{
                  display: 'flex',
                  gap: 12,
                  padding: '12px 14px',
                  marginBottom: 8,
                  border: `1px solid ${token.colorBorderSecondary}`,
                  borderRadius: 10,
                  background: step.status === 'Done' || step.status === 'Skipped' ? 'transparent' : token.colorBgContainer,
                  opacity: step.status === 'Skipped' ? 0.6 : 1,
                }}
              >
                <div style={{ paddingTop: 2 }}><StatusIcon status={step.status} /></div>
                <div style={{ flex: 1, minWidth: 0 }}>
                  <div style={{
                    display: 'flex', gap: 8, flexWrap: 'wrap', alignItems: 'center', justifyContent: 'space-between',
                  }}
                  >
                    <Text strong style={{ textDecoration: step.status === 'Skipped' ? 'line-through' : 'none' }}>{step.title}</Text>
                    <Space size={4} wrap>
                      {step.status !== 'Pending' ? <Tag color={stepStatusColor(step.status)}>{stepStatusLabel(step.status)}</Tag> : null}
                      {step.overdue ? <Tag color="red">{t('Overdue')}</Tag> : null}
                      {!step.required ? <Tag>{t('Optional')}</Tag> : null}
                    </Space>
                  </div>
                  <Space size={12} wrap style={{ marginTop: 2 }}>
                    <Text type="secondary" style={{ fontSize: 12 }}>
                      {typeLabel(step.type)}
                      {step.subject ? `: ${step.subject}` : ''}
                    </Text>
                    {showOwner && step.owner_type !== 'new_hire' ? (
                      <Text type="secondary" style={{ fontSize: 12 }}>
                        <UserOutlined />
                        {' '}
                        {step.owner_name || ownerLabel(step.owner_type, track)}
                      </Text>
                    ) : null}
                    {step.due_date ? (
                      <Text type={step.overdue ? 'danger' : 'secondary'} style={{ fontSize: 12 }}>
                        {t('Due {date}', { date: formatDate(step.due_date) })}
                      </Text>
                    ) : null}
                  </Space>

                  {renderBody ? renderBody(step) : (
                    <>
                      {step.instructions ? <Paragraph type="secondary" style={{ margin: '6px 0 0', whiteSpace: 'pre-line' }}>{step.instructions}</Paragraph> : null}
                      {step.file ? (
                        <div style={{ marginTop: 6 }}>
                          <FileOutlined />
                          {' '}
                          {step.file.url ? <a href={step.file.url} target="_blank" rel="noopener noreferrer">{step.file.name}</a> : step.file.name}
                        </div>
                      ) : null}
                      {step.acknowledgement ? (
                        <Text type="secondary" style={{ display: 'block', marginTop: 6, fontSize: 12 }}>
                          {t('Acknowledged by {name} on {date}', { name: step.acknowledgement.name, date: formatDateTime(step.acknowledgement.at) })}
                        </Text>
                      ) : null}
                      {step.asset ? <Text style={{ display: 'block', marginTop: 6 }}>{t('Handed over: {asset}', { asset: step.asset })}</Text> : null}
                      {step.manager_name ? <Text style={{ display: 'block', marginTop: 6 }}>{t('Manager: {name}', { name: step.manager_name })}</Text> : null}
                      {step.waiting_for_accept && step.status !== 'Done' && step.status !== 'Skipped' ? (
                        <Text type="secondary" style={{ display: 'block', marginTop: 6, fontSize: 12 }}>{t('Can be done once the invitation is accepted.')}</Text>
                      ) : null}
                      {step.note ? <Paragraph italic style={{ margin: '6px 0 0' }}>{step.note}</Paragraph> : null}
                    </>
                  )}
                  {step.status === 'ChangesRequested' && step.review_note ? (
                    <Alert type="warning" showIcon style={{ marginTop: 8 }} message={t('Changes requested')} description={step.review_note} />
                  ) : null}
                  {step.error ? <Alert type="error" showIcon style={{ marginTop: 8 }} message={step.error} /> : null}
                  {renderActions ? <div style={{ marginTop: 8 }}>{renderActions(step)}</div> : null}
                </div>
              </div>
            ))}
          </div>
        );
      })}
    </div>
  );
}
