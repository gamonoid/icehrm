import React from 'react';
import {
  Card, Button, Typography, Row, Col, theme,
} from 'antd';
import {
  CloudOutlined, CheckCircleFilled, CloseCircleFilled, RocketOutlined, SafetyOutlined,
  SyncOutlined, CustomerServiceOutlined, DatabaseOutlined, ThunderboltOutlined,
  CloudServerOutlined, LockOutlined, ClockCircleOutlined,
} from '@ant-design/icons';

const { Title, Paragraph, Text } = Typography;

const MIGRATE_URL = 'https://icehrm.zendesk.com/hc/en-us/requests/new?tf_subject=Migrate%20IceHrm%20Open%20Source';

const CLOUD = '#0ea5e9';
const CLOUD_DARK = '#0369a1';

const TRUST = [
  { icon: <CloudServerOutlined />, label: 'Managed hosting' },
  { icon: <LockOutlined />, label: 'Encrypted & backed up' },
  { icon: <ClockCircleOutlined />, label: 'Migration handled for you' },
];

const COMPARISON = [
  { label: 'Servers & maintenance', self: 'You provision, patch and monitor', cloud: 'Fully managed by our team' },
  { label: 'Version updates', self: 'Manual upgrade on every release', cloud: 'Applied automatically' },
  { label: 'Backups', self: 'You configure and verify them', cloud: 'Automated daily, stored offsite' },
  { label: 'Premium extensions', self: 'Licensed separately', cloud: 'All included in your plan' },
  { label: 'Support', self: 'Community forums', cloud: 'Priority support with onboarding' },
];

const BENEFITS = [
  { icon: <CloudOutlined />, title: 'No Server Management', desc: 'We handle infrastructure, scaling and monitoring so your team never touches a server.' },
  { icon: <SyncOutlined />, title: 'Automatic Updates', desc: 'Every new IceHrm release is applied for you, with no downtime window to plan.' },
  { icon: <ThunderboltOutlined />, title: 'All Premium Extensions', desc: 'Advanced Leave, Performance, Recruitment, Expenses and Learning — all included.' },
  { icon: <DatabaseOutlined />, title: 'Full Data Migration', desc: 'We move your employees, documents and history across from your current instance.' },
  { icon: <SafetyOutlined />, title: 'Security & Backups', desc: 'Encryption in transit and at rest, with automated daily offsite backups.' },
  { icon: <CustomerServiceOutlined />, title: 'Priority Support', desc: 'Faster response times and a guided onboarding session for your HR team.' },
];

const STEPS = [
  { title: 'Get in touch', desc: 'Open a migration request and we\'ll provision your cloud instance.' },
  { title: 'Send us your data', desc: 'Share a database export plus any custom files or extensions you use.' },
  { title: 'We migrate and verify', desc: 'We import everything, check it against your current system, and hand it over.' },
];

export default function IceHrmCloudPromo() {
  const { token } = theme.useToken();

  const sectionTitle = (text) => (
    <Title level={4} style={{ marginBottom: 16, marginTop: 0 }}>{text}</Title>
  );

  const cellBorder = `1px solid ${token.colorBorderSecondary}`;

  return (
    <div style={{ padding: 24, maxWidth: 1000, margin: '0 auto' }}>
      <style>
        {`
          .cloud-benefit { transition: transform .18s ease, box-shadow .18s ease; }
          .cloud-benefit:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(14,165,233,.18) !important; }
          .cloud-row:last-child { border-bottom: none !important; }
        `}
      </style>

      {/* Hero */}
      <Card
        bordered={false}
        style={{
          borderRadius: 16,
          marginBottom: 32,
          background: `linear-gradient(135deg, ${CLOUD} 0%, ${CLOUD_DARK} 100%)`,
          boxShadow: '0 8px 24px rgba(3,105,161,.25)',
        }}
        bodyStyle={{ padding: 36 }}
      >
        <Row gutter={[28, 24]} align="middle">
          <Col xs={24} md={15}>
            <div style={{
              display: 'inline-flex', alignItems: 'center', gap: 8, marginBottom: 14,
              background: 'rgba(255,255,255,0.18)', padding: '4px 12px', borderRadius: 20,
            }}
            >
              <CloudOutlined style={{ color: '#fff', fontSize: 14 }} />
              <Text style={{ color: '#fff', fontSize: 12, fontWeight: 600, letterSpacing: .3 }}>ICEHRM CLOUD</Text>
            </div>
            <Title level={2} style={{ color: '#fff', margin: 0, lineHeight: 1.2 }}>
              Let us run IceHrm for you
            </Title>
            <Paragraph style={{
              color: 'rgba(255,255,255,0.92)', marginBottom: 0, marginTop: 12, fontSize: 16, maxWidth: 520,
            }}
            >
              Keep the IceHrm you already know — without the servers, upgrades and backups.
              Our team migrates your existing data across for you.
            </Paragraph>
          </Col>
          <Col xs={24} md={9}>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12, alignItems: 'flex-start' }}>
              <Button
                size="large"
                icon={<RocketOutlined />}
                href={MIGRATE_URL}
                target="_blank"
                rel="noopener noreferrer"
                style={{
                  background: '#fff', color: CLOUD_DARK, borderColor: '#fff',
                  fontWeight: 700, height: 46, padding: '0 28px', fontSize: 15,
                }}
              >
                Talk to us about migrating
              </Button>
              {TRUST.map((t) => (
                <div key={t.label} style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                  <span style={{ color: 'rgba(255,255,255,0.95)', fontSize: 14 }}>{t.icon}</span>
                  <Text style={{ color: 'rgba(255,255,255,0.9)', fontSize: 13 }}>{t.label}</Text>
                </div>
              ))}
            </div>
          </Col>
        </Row>
      </Card>

      {/* Self-hosted vs Cloud */}
      {sectionTitle('Self-hosted vs IceHrm Cloud')}
      <Card
        bordered={false}
        style={{ borderRadius: 14, marginBottom: 32, boxShadow: token.boxShadowTertiary, overflow: 'hidden' }}
        bodyStyle={{ padding: 0 }}
      >
        <Row style={{ borderBottom: cellBorder, background: token.colorFillQuaternary }}>
          <Col xs={24} sm={9} style={{ padding: '14px 20px' }}>
            <Text strong style={{ fontSize: 13 }}>&nbsp;</Text>
          </Col>
          <Col xs={12} sm={7} style={{ padding: '14px 20px' }}>
            <Text type="secondary" strong style={{ fontSize: 13 }}>Self-hosted</Text>
          </Col>
          <Col xs={12} sm={8} style={{ padding: '14px 20px' }}>
            <Text strong style={{ fontSize: 13, color: CLOUD }}>IceHrm Cloud</Text>
          </Col>
        </Row>
        {COMPARISON.map((row) => (
          <Row key={row.label} className="cloud-row" style={{ borderBottom: cellBorder }}>
            <Col xs={24} sm={9} style={{ padding: '14px 20px' }}>
              <Text strong style={{ fontSize: 14 }}>{row.label}</Text>
            </Col>
            <Col xs={12} sm={7} style={{ padding: '14px 20px', display: 'flex', gap: 8 }}>
              <CloseCircleFilled style={{ color: token.colorTextQuaternary, fontSize: 15, marginTop: 3, flexShrink: 0 }} />
              <Text type="secondary" style={{ fontSize: 13 }}>{row.self}</Text>
            </Col>
            <Col xs={12} sm={8} style={{ padding: '14px 20px', display: 'flex', gap: 8 }}>
              <CheckCircleFilled style={{ color: CLOUD, fontSize: 15, marginTop: 3, flexShrink: 0 }} />
              <Text style={{ fontSize: 13 }}>{row.cloud}</Text>
            </Col>
          </Row>
        ))}
      </Card>

      {/* Benefits */}
      {sectionTitle('What you get')}
      <Row gutter={[16, 16]} style={{ marginBottom: 32 }}>
        {BENEFITS.map((b) => (
          <Col xs={24} sm={12} lg={8} key={b.title}>
            <Card
              bordered={false}
              className="cloud-benefit"
              style={{ borderRadius: 14, height: '100%', boxShadow: token.boxShadowTertiary }}
              bodyStyle={{ padding: 22 }}
            >
              <div style={{
                width: 42, height: 42, borderRadius: 11, marginBottom: 14,
                background: `${CLOUD}1f`, color: CLOUD,
                display: 'flex', alignItems: 'center', justifyContent: 'center', fontSize: 20,
              }}
              >
                {b.icon}
              </div>
              <div style={{ fontWeight: 600, marginBottom: 6, fontSize: 15 }}>{b.title}</div>
              <Text type="secondary" style={{ fontSize: 13, lineHeight: 1.6 }}>{b.desc}</Text>
            </Card>
          </Col>
        ))}
      </Row>

      {/* Migration steps */}
      {sectionTitle('How migration works')}
      <Row gutter={[16, 16]} style={{ marginBottom: 32 }}>
        {STEPS.map((s, i) => (
          <Col xs={24} md={8} key={s.title}>
            <Card
              bordered={false}
              style={{ borderRadius: 14, height: '100%', boxShadow: token.boxShadowTertiary }}
              bodyStyle={{ padding: 22 }}
            >
              <div style={{
                width: 32, height: 32, borderRadius: '50%', marginBottom: 14,
                background: CLOUD, color: '#fff', fontWeight: 700, fontSize: 14,
                display: 'flex', alignItems: 'center', justifyContent: 'center',
              }}
              >
                {i + 1}
              </div>
              <div style={{ fontWeight: 600, marginBottom: 6, fontSize: 15 }}>{s.title}</div>
              <Text type="secondary" style={{ fontSize: 13, lineHeight: 1.6 }}>{s.desc}</Text>
            </Card>
          </Col>
        ))}
      </Row>

      {/* Closing CTA */}
      <Card
        bordered={false}
        style={{
          borderRadius: 14,
          textAlign: 'center',
          background: token.colorFillQuaternary,
          border: `1px solid ${token.colorBorderSecondary}`,
        }}
        bodyStyle={{ padding: 32 }}
      >
        <Title level={4} style={{ margin: 0 }}>Ready to move to the cloud?</Title>
        <Paragraph type="secondary" style={{ marginTop: 8, marginBottom: 20 }}>
          Tell us about your current setup and we&#39;ll put together a migration plan.
        </Paragraph>
        <Button
          size="large"
          type="primary"
          icon={<CloudOutlined />}
          href={MIGRATE_URL}
          target="_blank"
          rel="noopener noreferrer"
          style={{
            background: CLOUD, borderColor: CLOUD, fontWeight: 600, height: 46, padding: '0 32px',
          }}
        >
          Contact Us to Migrate
        </Button>
      </Card>
    </div>
  );
}
