import React, { useEffect, useMemo, useState } from 'react';
import {
  Result, Spin, Tabs, Typography,
} from 'antd';
import { t } from '../../../../../web/api/i18n';
import { errorMessage, makeApi } from './shared/api';
import HiresTab from './components/HiresTab';
import WorkflowsTab from './components/WorkflowsTab';
import TasksList from './components/TasksList';

const { Title, Text } = Typography;

/**
 * Onboarding (admin). HR invites new hires, builds workflows and follows progress;
 * managers see the onboarding of the people they manage.
 */
export default function OnboardingAdminView({ apiClient }) {
  // This module is New Hire Onboarding; Company Onboarding reuses the tabs for the
  // workforce track.
  const track = 'new_hire';
  const api = useMemo(() => makeApi(apiClient), [apiClient]);
  const [ctx, setCtx] = useState(null);
  const [error, setError] = useState(null);
  const [refreshKey, setRefreshKey] = useState(0);

  const loadCtx = () => api.get('onboarding/context', { track }).then(setCtx).catch((e) => setError(errorMessage(e, t('Onboarding could not be loaded'))));
  useEffect(() => { loadCtx(); }, []);

  if (error) return <Result status="warning" title={error} />;
  if (!ctx) return <div style={{ padding: 40, textAlign: 'center' }}><Spin /></div>;

  const changed = () => { setRefreshKey((k) => k + 1); loadCtx(); };
  const items = [
    {
      key: 'hires',
      label: t('New hires'),
      children: <HiresTab api={api} ctx={ctx} refreshKey={refreshKey} onChanged={changed} />,
    },
    {
      key: 'tasks',
      label: t('My onboarding tasks'),
      children: <TasksList api={api} refreshKey={refreshKey} onChanged={changed} />,
    },
  ];
  if (ctx.is_hr) {
    items.push({
      key: 'workflows',
      label: t('Workflows'),
      children: <WorkflowsTab api={api} ctx={ctx} onChanged={changed} />,
    });
  }

  return (
    <div style={{ padding: '4px 4px 24px' }}>
      <div style={{ marginBottom: 8 }}>
        <Title level={4} style={{ margin: 0 }}>{t('New Hire Onboarding')}</Title>
        <Text type="secondary">
          {ctx.is_hr
            ? t('Invite new hires, give them a checklist, and get everyone ready for day one.')
            : t('See how the onboarding of your new team members is going.')}
        </Text>
      </div>
      <Tabs items={items} destroyInactiveTabPane={false} />
    </div>
  );
}
