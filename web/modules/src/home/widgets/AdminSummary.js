import React from 'react';
import { Card, Row, Col, List, Avatar, Typography, Button, theme } from 'antd';
import { BankOutlined } from '@ant-design/icons';
import { registerWidget } from '../registry';
import { openIntent } from '../intent';
import { ACCENT, chip, soft, personColor, initials } from '../palette';
import { t } from '../../../../api/i18n';

const { Text } = Typography;

// The dashboard KPIs arrive keyed by camelCase name; give them human labels and a
// colour rather than printing the raw key ("totalEmployees") at the user.
const KPI_META = {
  totalEmployees: { label: 'Employees', color: '#1677ff' },
  departments: { label: 'Departments', color: '#13c2c2' },
  newHires: { label: 'New hires (1y)', color: '#52c41a' },
  activeProjects: { label: 'Active projects', color: '#722ed1' },
  jobTitles: { label: 'Job titles', color: '#fa8c16' },
};

function humanise(key) {
  return String(key)
    .replace(/([a-z0-9])([A-Z])/g, '$1 $2')
    .replace(/[_-]+/g, ' ')
    .replace(/^./, (c) => c.toUpperCase());
}

function kpiList(kpis) {
  if (!kpis) return [];
  if (Array.isArray(kpis)) {
    return kpis.map((k) => ({
      label: t(k.title || k.name || k.label),
      value: k.value,
      color: ACCENT.org,
    }));
  }
  return Object.keys(kpis).map((key) => ({
    label: (KPI_META[key] && t(KPI_META[key].label)) || humanise(key),
    value: kpis[key],
    color: (KPI_META[key] && KPI_META[key].color) || ACCENT.org,
  }));
}

/**
 * Admin / Manager glance at the org — a slim slice of Organization Overview
 * (headline KPIs + recent hires) as colour-coded stat tiles. The full charts stay
 * on that app; "View all" deep-links to it.
 */
function AdminSummary({ data }) {
  const { token } = theme.useToken();
  const admin = (data && data.admin) || {};
  const kpis = kpiList(admin.kpis).slice(0, 4);
  const hires = (admin.recentHires || []).slice(0, 4);

  return (
    <Card
      size="small"
      style={{ height: '100%' }}
      title={(
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 8 }}>
          <span style={chip(ACCENT.org)}><BankOutlined /></span>
          {t('Organization at a glance')}
        </span>
      )}
      extra={(
        <Button type="link" size="small" onClick={() => openIntent({ group: 'admin', module: 'dashboard' })}>
          {t('View all')}
        </Button>
      )}
    >
      {kpis.length > 0 && (
        <Row gutter={[10, 10]} style={{ marginBottom: 12 }}>
          {kpis.map((k) => (
            <Col span={12} key={k.label}>
              <div style={{
                padding: '10px 12px',
                borderRadius: 10,
                background: soft(k.color, 0.12),
                border: `1px solid ${soft(k.color, 0.25)}`,
              }}
              >
                <div style={{ fontSize: 22, fontWeight: 700, color: k.color, lineHeight: 1.2 }}>
                  {k.value != null ? k.value : '—'}
                </div>
                <Text type="secondary" style={{ fontSize: 12 }}>{k.label}</Text>
              </div>
            </Col>
          ))}
        </Row>
      )}
      {hires.length > 0 && (
        <div>
          <Text strong style={{ fontSize: 12, color: token.colorTextSecondary, textTransform: 'uppercase' }}>{t('Recent hires')}</Text>
          <List
            size="small"
            dataSource={hires}
            renderItem={(h) => (
              <List.Item style={{ paddingLeft: 0, paddingRight: 0 }}>
                <List.Item.Meta
                  avatar={(
                    <Avatar
                      src={h.image || undefined}
                      style={h.image ? undefined : { background: personColor(h.name) }}
                    >
                      {h.image ? null : initials(h.name)}
                    </Avatar>
                  )}
                  title={h.name}
                  description={[h.title, h.date].filter(Boolean).join(' · ')}
                />
              </List.Item>
            )}
          />
        </div>
      )}
      {kpis.length === 0 && hires.length === 0 && <Text type="secondary">{t('No summary available.')}</Text>}
    </Card>
  );
}

registerWidget({
  id: 'admin-summary', order: 60, column: 'side', size: 'full', requires: { manager: true, dataKey: 'admin' }, component: AdminSummary,
});

export default AdminSummary;
