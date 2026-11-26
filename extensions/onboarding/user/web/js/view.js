import React, { useEffect, useMemo, useState } from 'react';
import {
  Result, Spin, Tabs, Typography,
} from 'antd';
import { t } from '../../../../../web/api/i18n';
import { errorMessage, makeApi } from '../../../admin/web/js/shared/api';
import Checklist from './components/Checklist';
import TasksList from '../../../admin/web/js/components/TasksList';

const { Title, Text } = Typography;

/**
 * My Onboarding: a new hire's checklist, and the onboarding tasks other people (HR,
 * IT, managers, buddies) have been given for new hires.
 */
export default function OnboardingUserView({ apiClient }) {
  const api = useMemo(() => makeApi(apiClient), [apiClient]);
  const [me, setMe] = useState(undefined);
  const [tasks, setTasks] = useState(null);
  const [error, setError] = useState(null);
  const [refreshKey, setRefreshKey] = useState(0);

  const load = () => {
    api.get('onboarding/me').then((d) => setMe(d || null)).catch((e) => setError(errorMessage(e, t('Onboarding could not be loaded'))));
    api.get('onboarding/me/tasks').then(setTasks).catch(() => setTasks([]));
  };
  useEffect(load, [refreshKey]);

  if (error) return <Result status="warning" title={error} />;
  if (me === undefined || tasks === null) return <div style={{ padding: 40, textAlign: 'center' }}><Spin /></div>;

  const changed = () => setRefreshKey((k) => k + 1);
  const items = [];
  if (me) {
    items.push({ key: 'mine', label: t('My onboarding'), children: <Checklist api={api} data={me} onChanged={changed} /> });
  }
  if (!me || tasks.length > 0) {
    items.push({ key: 'tasks', label: t('Onboarding tasks'), children: <TasksList api={api} refreshKey={refreshKey} onChanged={changed} /> });
  }

  return (
    <div style={{ padding: '4px 4px 24px' }}>
      {!me ? (
        <div style={{ marginBottom: 8 }}>
          <Title level={4} style={{ margin: 0 }}>{t('Onboarding tasks')}</Title>
          <Text type="secondary">{t('Tasks you have been given to get new team members ready.')}</Text>
        </div>
      ) : null}
      {items.length === 1 ? items[0].children : <Tabs items={items} />}
    </div>
  );
}
