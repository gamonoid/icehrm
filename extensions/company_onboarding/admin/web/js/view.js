import React, { useEffect, useMemo, useState } from 'react';
import {
  ConfigProvider, Tabs, Typography, theme,
} from 'antd';
import { t } from '../../../../../web/api/i18n';
import { makeApi } from './api';
import SetupWizard from './components/SetupWizard';
import HiresTab from '../../../../onboarding/admin/web/js/components/HiresTab';
import WorkflowsTab from '../../../../onboarding/admin/web/js/components/WorkflowsTab';

const { Title, Text } = Typography;

// The shell's success colour (#2e7d32) is dark, so antd derives a muddy grey-green
// success background from it in light mode. Use a clear light green for alerts here.
const LIGHT_SUCCESS_ALERT = { colorSuccessBg: '#edf7ed', colorSuccessBorder: '#b7dfb9' };

function isDarkColor(color) {
  const m = /^#([0-9a-f]{6})$/i.exec(String(color || ''));
  if (!m) return false;
  const n = parseInt(m[1], 16);
  // eslint-disable-next-line no-bitwise
  const lum = (0.299 * ((n >> 16) & 255)) + (0.587 * ((n >> 8) & 255)) + (0.114 * (n & 255));
  return lum < 128;
}

/**
 * Company Onboarding (admin): a guided setup that brings the company onto IceHrm and
 * then invites its existing staff. The People and Workflows tabs reuse the Onboarding
 * extension's screens on the "workforce" track.
 */
export default function CompanyOnboardingAdminView({ apiClient }) {
  const api = useMemo(() => makeApi(apiClient), [apiClient]);
  const [ctx, setCtx] = useState(null);
  const [refreshKey, setRefreshKey] = useState(0);
  const { token } = theme.useToken();
  const dark = isDarkColor(token.colorBgContainer);

  // Without the Onboarding extension only the setup is shown.
  const loadCtx = () => api.get('onboarding/context', { track: 'workforce' }).then(setCtx).catch(() => setCtx(null));
  useEffect(() => { loadCtx(); }, []);
  const changed = () => { setRefreshKey((k) => k + 1); loadCtx(); };

  const items = [
    { key: 'setup', label: t('Setup'), children: <SetupWizard api={api} /> },
  ];
  if (ctx && ctx.is_hr) {
    items.push(
      {
        key: 'people',
        label: t('People'),
        children: <HiresTab api={api} ctx={ctx} refreshKey={refreshKey} onChanged={changed} />,
      },
      {
        key: 'workflows',
        label: t('Workflows'),
        children: <WorkflowsTab api={api} ctx={ctx} onChanged={changed} />,
      },
    );
  }

  const content = (
    <div style={{ padding: '4px 4px 24px' }}>
      <div style={{ marginBottom: 8 }}>
        <Title level={4} style={{ margin: 0 }}>{t('Company Onboarding')}</Title>
        <Text type="secondary">{t('Set up your company in IceHrm and bring your team on board.')}</Text>
      </div>
      <Tabs items={items} destroyInactiveTabPane />
    </div>
  );
  // Always wrapped (also in dark mode), so switching the colour mode does not remount.
  return <ConfigProvider theme={{ components: { Alert: dark ? {} : LIGHT_SUCCESS_ALERT } }}>{content}</ConfigProvider>;
}
