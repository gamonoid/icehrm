import React from 'react';
import {
  Button, Card, List, Result, Space, Tag, Typography,
} from 'antd';
import { RightOutlined } from '@ant-design/icons';
import { t } from '../../../../../../web/api/i18n';
import { SKIPPED, NOT_STARTED, stepInfo } from '../constants';

const { Text } = Typography;

/** Open another module in the shell (hash route "group::name"). */
function go(route) {
  window.location.hash = `#${encodeURIComponent(route)}`;
}

/**
 * Shown once every step is done or skipped (and from the step list): the skipped
 * steps to come back to, and where to go next.
 */
export default function WhatsNext({ summary, onOpenStep }) {
  const open = summary.steps.filter((s) => s.status === NOT_STARTED);
  const skipped = summary.steps.filter((s) => s.status === SKIPPED);
  const links = [
    { route: 'admin::leaves', title: t('Leave rules and approvals'), text: t('Carry-over, accrual, who approves leave.') },
    { route: 'admin::attendance', title: t('Attendance'), text: t('Clock in and out, and attendance rules.') },
    { route: 'admin::users', title: t('Logins and permissions'), text: t('Give managers and HR staff the access they need.') },
    { route: 'admin::settings', title: t('Settings'), text: t('Email, notifications and other system settings.') },
  ];
  return (
    <Card>
      {open.length === 0 ? (
        <Result
          status="success"
          title={t('Your company is set up')}
          subTitle={summary.real_employees < 2
            ? t('People appear in Employees as they accept their invitations.')
            : t('Your team is on IceHrm.')}
        />
      ) : null}
      {open.length || skipped.length ? (
        <>
          <Text strong>{t('Steps to come back to')}</Text>
          <List
            size="small"
            style={{ margin: '8px 0 20px' }}
            dataSource={[...open, ...skipped]}
            renderItem={(s) => (
              <List.Item actions={[<Button key="open" size="small" onClick={() => onOpenStep(s.key)}>{t('Open')}</Button>]}>
                <Space>
                  <Text>{stepInfo(s.key).title}</Text>
                  {s.status === SKIPPED ? <Tag>{t('Skipped')}</Tag> : <Tag color="blue">{t('Not done yet')}</Tag>}
                </Space>
              </List.Item>
            )}
          />
        </>
      ) : null}
      <Text strong>{t('What\'s next')}</Text>
      <List
        size="small"
        style={{ marginTop: 8 }}
        dataSource={links}
        renderItem={(l) => (
          <List.Item onClick={() => go(l.route)} style={{ cursor: 'pointer' }} actions={[<RightOutlined key="go" />]}>
            <List.Item.Meta title={l.title} description={l.text} />
          </List.Item>
        )}
      />
    </Card>
  );
}
