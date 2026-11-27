import React, { useEffect, useMemo, useState } from 'react';
import {
  Button, Empty, Skeleton, Space, Tag, Typography,
} from 'antd';
import { t } from '../../../../../../web/api/i18n';
import { errorMessage, makeApi } from '../shared/api';
import { formatDate, hireStatusColor, hireStatusLabel } from '../shared/constants';
import { HireDetail } from './HireDrawer';
import HireDrawer from './HireDrawer';

const { Text } = Typography;

/**
 * "Onboarding" section of the admin employee profile, registered through meta.json
 * native.profileSections. Shows the employee's onboarding checklist, if they had one.
 */
export default function OnboardingProfileSection({ employeeId, apiClient }) {
  const api = useMemo(() => makeApi(apiClient), [apiClient]);
  const [data, setData] = useState(undefined);
  const [error, setError] = useState(null);
  const [isHr, setIsHr] = useState(false);
  const [open, setOpen] = useState(false);

  const load = () => {
    api.get(`onboarding/employees/${employeeId}/onboarding`)
      .then((d) => setData(d || null))
      .catch((e) => setError(errorMessage(e, t('Onboarding could not be loaded'))));
  };
  useEffect(() => {
    setData(undefined);
    load();
    api.get('onboarding/context').then((c) => setIsHr(!!(c && c.is_hr))).catch(() => {});
  }, [employeeId]);

  if (error) return <Text type="secondary">{error}</Text>;
  if (data === undefined) return <Skeleton active />;
  if (data === null) return <Empty description={t('This employee was not onboarded with a workflow.')} />;

  return (
    <div>
      <div style={{
        display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 8, flexWrap: 'wrap', marginBottom: 12,
      }}
      >
        <Space wrap>
          <Text strong>{data.workflow}</Text>
          <Tag color={hireStatusColor(data.status)}>{hireStatusLabel(data.status)}</Tag>
          {data.track === 'workforce'
            ? (data.joined_date ? <Text type="secondary">{t('Joined {date}', { date: formatDate(data.joined_date) })}</Text> : null)
            : <Text type="secondary">{t('Start date: {date}', { date: formatDate(data.start_date) })}</Text>}
        </Space>
        <Button onClick={() => setOpen(true)}>{t('Open onboarding')}</Button>
      </div>
      <HireDetail api={api} data={data} isHr={isHr} compact onChanged={load} />
      <HireDrawer api={api} id={data.id} open={open} isHr={isHr} onClose={() => setOpen(false)} onChanged={load} />
    </div>
  );
}
