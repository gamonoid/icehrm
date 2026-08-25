import React from 'react';
import {
  Card, Table, Button, Typography, Collapse,
} from 'antd';
import { CheckCircleFilled, CloseCircleOutlined, RocketOutlined } from '@ant-design/icons';

const { Title, Text, Paragraph } = Typography;

const PURCHASE_URL = 'https://icehrm.com/purchase-icehrmpro';

// Feature comparison between IceHrm Community Edition and IceHrmPro.
// Source: https://icehrm.com/compare
const SECTIONS = [
  {
    title: 'Core Features',
    note: 'Included in both editions.',
    rows: [
      'Employee Management',
      'Organization Overview',
      'Company Structure',
      'Employee History',
      'Document Management',
      'Attendance Management',
      'Training Management',
      'Overtime Management',
      'Clients & Project Management',
      'Timesheets',
      'Travel Management',
      'Single Sign-On (SSO) — Microsoft Entra ID',
      'Single Sign-On (SSO) — SAML 2.0',
      'Single Sign-On (SSO) — LDAP / Active Directory',
    ].map((name) => ({ name, oss: true, pro: true })),
  },
  {
    title: 'Free Extensions',
    note: 'Included in both editions.',
    rows: [
      'Payroll',
      'Advance Reports',
      'Staff Directory',
      'Company Overview',
    ].map((name) => ({ name, oss: true, pro: true })),
  },
  {
    title: 'Premium Extensions',
    note: 'Available only with IceHrmPro (or IceHrm Cloud).',
    rows: [
      { name: 'Team View', desc: 'Teams with roles and real-time attendance.' },
      { name: 'Expense Management', desc: 'Employee expenses and claim approvals.' },
      { name: 'Asset Management', desc: 'Track company assets assigned to employees.' },
      { name: 'Workforce Insights', desc: 'Advanced analytics for HR decisions.' },
      { name: 'Recruitment & ATS', desc: 'Jobs, CVs, interviews, and candidate tracking.' },
      { name: 'Task Management', desc: 'Assign tasks with tracking and notifications.' },
      { name: 'Learning Management', desc: 'Online courses with progress tracking.' },
      {
        name: 'Advanced Leave Management',
        desc: 'Leave carry forwarding, leave accrual, leave grouping and sandwich leaves, '
          + 'plus the ability to configure custom leave rules and multi-level leave approvals.',
      },
      { name: 'Performance Management', desc: 'Goal setting with 360° performance reviews and appraisals.' },
      { name: 'Document Signing', desc: 'Send contracts for e-signature with data placeholders.' },
    ].map((r) => ({ ...r, oss: false, pro: true })),
  },
];

const yes = <CheckCircleFilled style={{ color: '#5AD8A6', fontSize: 18 }} />;
const no = <CloseCircleOutlined style={{ color: '#bbb', fontSize: 18 }} />;

function CompareTable({ rows }) {
  const columns = [
    {
      title: 'Feature',
      dataIndex: 'name',
      key: 'name',
      render: (t, r) => (
        <div>
          <div style={{ fontWeight: 500 }}>{t}</div>
          {r.desc ? (
            <Text type="secondary" style={{ fontSize: 12 }}>{r.desc}</Text>
          ) : null}
        </div>
      ),
    },
    {
      title: 'Community Edition',
      dataIndex: 'oss',
      key: 'oss',
      align: 'center',
      width: 150,
      render: (v) => (v ? yes : no),
    },
    {
      title: (
        <span>
          IceHrm
          <b>Pro</b>
        </span>
      ),
      dataIndex: 'pro',
      key: 'pro',
      align: 'center',
      width: 140,
      render: (v) => (v ? yes : no),
    },
  ];
  return (
    <Table
      rowKey="name"
      size="small"
      pagination={false}
      columns={columns}
      dataSource={rows}
    />
  );
}

export default function IceHrmProCompareView() {
  const buyBtn = (
    <Button
      type="primary"
      size="large"
      icon={<RocketOutlined />}
      href={PURCHASE_URL}
      target="_blank"
      rel="noopener noreferrer"
    >
      Buy Now
    </Button>
  );

  return (
    <div style={{ padding: 24, maxWidth: 980, margin: '0 auto' }}>
      <Card
        bordered={false}
        style={{
          borderRadius: 14,
          marginBottom: 20,
          background: 'linear-gradient(135deg, #7B61FF 0%, #9270CA 100%)',
          color: '#fff',
        }}
        bodyStyle={{ padding: 24 }}
      >
        <div style={{
          display: 'flex', flexWrap: 'wrap', gap: 16, alignItems: 'center', justifyContent: 'space-between',
        }}
        >
          <div style={{ minWidth: 240, flex: 1 }}>
            <Title level={3} style={{ color: '#fff', margin: 0 }}>Upgrade to IceHrmPro</Title>
            <Paragraph style={{ color: 'rgba(255,255,255,0.9)', marginBottom: 0, marginTop: 6 }}>
              Every premium extension — Advanced Leave Management, Performance Management,
              Recruitment &amp; ATS, Expense Management, Learning, Document Signing and more —
              bundled in one edition.
            </Paragraph>
          </div>
          {buyBtn}
        </div>
      </Card>

      <Collapse
        // Premium Extensions expanded by default; Core Features and Free
        // Extensions collapsed (they are identical across editions).
        defaultActiveKey={['Premium Extensions']}
        style={{ marginBottom: 16, borderRadius: 14, overflow: 'hidden' }}
        items={SECTIONS.map((s) => ({
          key: s.title,
          label: (
            <span>
              <span style={{ fontWeight: 600 }}>{s.title}</span>
              <Text type="secondary" style={{ fontSize: 12, marginLeft: 8 }}>{s.note}</Text>
            </span>
          ),
          children: <CompareTable rows={s.rows} />,
        }))}
      />

      <div style={{ textAlign: 'center', margin: '24px 0 8px' }}>
        {buyBtn}
      </div>
    </div>
  );
}
