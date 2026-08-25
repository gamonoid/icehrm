import React, { useEffect, useMemo, useRef, useState } from 'react';
import {
  Row, Col, Card, List, Avatar, Tag, Spin, Empty, Typography, Progress, Button, theme,
} from 'antd';
import {
  TeamOutlined, ApartmentOutlined,
  ClockCircleOutlined, DollarOutlined, GiftOutlined,
  RiseOutlined, UserAddOutlined, CalendarOutlined,
  WarningOutlined, ExclamationCircleOutlined, CreditCardOutlined,
  RocketOutlined, StarOutlined, PlusCircleOutlined, ArrowUpOutlined, TagOutlined,
} from '@ant-design/icons';
import {
  Pie, Donut, Column, Area, Bar,
} from '@antv/g2plot';
import { MUI_SHADOW, MUI_DARK } from './theme';

const { Text } = Typography;

const PALETTE = ['#346CB0', '#5AD8A6', '#5B8FF9', '#F6BD16', '#E8684A', '#9270CA', '#6DC8EC', '#FF99C3'];
const PLOTS = {
  pie: Pie, donut: Donut, column: Column, area: Area, bar: Bar,
};

// Thin React wrapper around g2plot 1.x (the version already bundled as vendorAntv).
// Dark mode is handled by injecting light text/axis colours into each chart's
// config (see darkChart* below) rather than g2plot's built-in 'dark' theme,
// which paints an opaque grey background that does not match the card. The
// donut's centre label is HTML (see the .ring-guide-* CSS in spa-shell.php).
function Chart({ type, config, height = 230 }) {
  const ref = useRef(null);
  useEffect(() => {
    if (!ref.current) return undefined;
    const Ctor = PLOTS[type];
    if (!Ctor) return undefined;
    let plot;
    try {
      plot = new Ctor(ref.current, config);
      plot.render();
    } catch (e) { /* ignore render errors */ }
    return () => { try { if (plot) plot.destroy(); } catch (e) { /* */ } };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [type, JSON.stringify(config), height]);
  return <div ref={ref} style={{ height }} />;
}

function initials(name) {
  const p = (name || '').trim().split(/\s+/);
  return ((p[0] && p[0][0]) || '' + ((p[1] && p[1][0]) || '')).toUpperCase()
    + ((p[1] && p[1][0]) || '').toUpperCase();
}
function colorFor(name) {
  let h = 0;
  for (let i = 0; i < (name || '').length; i += 1) h = (h * 31 + name.charCodeAt(i)) >>> 0;
  return PALETTE[h % PALETTE.length];
}
function fmtDate(d) {
  if (!d) return '';
  const dt = new Date(`${d}T00:00:00`);
  if (Number.isNaN(dt.getTime())) return d;
  return dt.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
}

function Kpi({
  icon, label, value, color, sub, labelColor, subColor, onClick,
}) {
  return (
    <Card
      bordered={false}
      hoverable={!!onClick}
      onClick={onClick}
      style={{ borderRadius: 14, boxShadow: MUI_SHADOW, cursor: onClick ? 'pointer' : 'default' }}
      bodyStyle={{ padding: 18 }}
    >
      <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
        <div style={{
          width: 46, height: 46, borderRadius: 12, flex: '0 0 auto',
          background: `${color}1f`, color, display: 'flex', alignItems: 'center',
          justifyContent: 'center', fontSize: 20,
        }}
        >
          {icon}
        </div>
        <div style={{ minWidth: 0 }}>
          <div style={{ fontSize: 26, fontWeight: 700, lineHeight: 1.1 }}>{value}</div>
          <div style={{ color: labelColor, fontSize: 13, whiteSpace: 'nowrap' }}>{label}</div>
        </div>
      </div>
      {sub ? <div style={{ marginTop: 10, fontSize: 12, color: subColor }}>{sub}</div> : null}
    </Card>
  );
}

// Small "upgrade to IceHrmPro" call-to-action shown in the KPI row. Clicking the
// card opens the marketplace's IceHrmPro comparison tab; the Buy Now button links
// straight to the purchase page.
const ICEHRM_PRO_PURCHASE_URL = 'https://icehrm.com/purchase-icehrmpro';
function UpgradeBox({ onCompare }) {
  return (
    <Card
      bordered={false}
      hoverable={!!onCompare}
      onClick={onCompare}
      style={{
        borderRadius: 14,
        boxShadow: MUI_SHADOW,
        cursor: onCompare ? 'pointer' : 'default',
        background: 'linear-gradient(135deg, #7B61FF 0%, #9270CA 100%)',
        color: '#fff',
        height: '100%',
      }}
      bodyStyle={{ padding: 18 }}
    >
      <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
        <div style={{
          width: 46, height: 46, borderRadius: 12, flex: '0 0 auto',
          background: 'rgba(255,255,255,0.2)', color: '#fff', display: 'flex',
          alignItems: 'center', justifyContent: 'center', fontSize: 20,
        }}
        >
          <RocketOutlined />
        </div>
        <div style={{ minWidth: 0 }}>
          <div style={{ fontSize: 15, fontWeight: 700, lineHeight: 1.2 }}>Upgrade to IceHrmPro</div>
          <div style={{ fontSize: 12, opacity: 0.9, whiteSpace: 'nowrap' }}>Unlock all premium extensions</div>
        </div>
      </div>
      <div style={{ marginTop: 12, display: 'flex', gap: 8, alignItems: 'center' }}>
        <Button
          size="small"
          href={ICEHRM_PRO_PURCHASE_URL}
          target="_blank"
          rel="noopener noreferrer"
          onClick={(e) => e.stopPropagation()}
          style={{ background: '#fff', borderColor: '#fff', color: '#7B61FF', fontWeight: 600 }}
        >
          Buy Now
        </Button>
        {onCompare ? (
          <span style={{ fontSize: 12, opacity: 0.9, textDecoration: 'underline' }}>Compare editions</span>
        ) : null}
      </div>
    </Card>
  );
}

function SectionCard({ title, extra, children, height }) {
  return (
    <Card
      title={<span style={{ fontWeight: 600 }}>{title}</span>}
      extra={extra}
      bordered={false}
      style={{ borderRadius: 14, boxShadow: MUI_SHADOW, height: height || '100%' }}
      bodyStyle={{ padding: 16 }}
    >
      {children}
    </Card>
  );
}

function PeopleList({ data, renderMeta, emptyText }) {
  if (!data || data.length === 0) {
    return <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description={emptyText || 'Nothing here'} />;
  }
  return (
    <List
      dataSource={data}
      split={false}
      renderItem={(item) => (
        <List.Item style={{ padding: '8px 0' }}>
          <List.Item.Meta
            avatar={(
              <Avatar style={{ backgroundColor: colorFor(item.name), verticalAlign: 'middle' }}>
                {initials(item.name)}
              </Avatar>
            )}
            title={<span style={{ fontSize: 14 }}>{item.name}</span>}
            description={renderMeta(item)}
          />
        </List.Item>
      )}
    />
  );
}

// Shared scaffold for the legacy-parity dashboard banners (payment reminder,
// demo-data prompt, trial upgrade ad): gradient card, round icon, title +
// message on the left, action buttons on the right.
function DashBanner({
  gradient, shadow, border, icon, title, message, actions,
}) {
  return (
    <div style={{
      background: gradient,
      borderRadius: 12,
      padding: '20px 24px',
      marginBottom: 18,
      display: 'flex',
      justifyContent: 'space-between',
      alignItems: 'center',
      gap: 16,
      flexWrap: 'wrap',
      boxShadow: shadow,
      border: border || 'none',
    }}
    >
      <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
        <div style={{
          background: 'rgba(255,255,255,0.2)',
          borderRadius: '50%',
          width: 48,
          height: 48,
          flex: '0 0 auto',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          color: '#fff',
          fontSize: 20,
        }}
        >
          {icon}
        </div>
        <div>
          <div style={{
            color: '#fff', fontWeight: 600, fontSize: 16, marginBottom: 4,
          }}
          >
            {title}
          </div>
          <div style={{ color: 'rgba(255,255,255,0.9)', fontSize: 14 }}>{message}</div>
        </div>
      </div>
      <div style={{ display: 'flex', gap: 10, alignItems: 'center' }}>{actions}</div>
    </div>
  );
}

function bannerButton({
  label, icon, color, onClick, ghost,
}) {
  return (
    <a
      key={label}
      onClick={(e) => { e.preventDefault(); onClick(); }}
      style={ghost ? {
        background: 'rgba(255,255,255,0.2)',
        color: '#fff',
        padding: '10px 16px',
        borderRadius: 8,
        textDecoration: 'none',
        fontSize: 14,
        fontWeight: 500,
        whiteSpace: 'nowrap',
        border: '1px solid rgba(255,255,255,0.3)',
      } : {
        background: '#fff',
        color,
        padding: '10px 24px',
        borderRadius: 8,
        textDecoration: 'none',
        fontSize: 14,
        fontWeight: 600,
        boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
        whiteSpace: 'nowrap',
      }}
    >
      {icon}
      {icon ? ' ' : null}
      {label}
    </a>
  );
}

// Unpaid-invoice banner — same conditions and copy as the legacy admin
// dashboard (core/admin/dashboard/index.php): orange reminder for a single
// unpaid invoice, red service-restricted notice for two or more.
function PaymentBanner({ billing, onNavigate }) {
  const count = (billing && billing.unpaidInvoices) || 0;
  if (count < 1) return null;
  const single = count === 1;
  const total = Number(billing.unpaidTotal || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2, maximumFractionDigits: 2,
  });
  return (
    <DashBanner
      gradient={single
        ? 'linear-gradient(135deg, #fa8c16 0%, #d46b08 100%)'
        : 'linear-gradient(135deg, #f5222d 0%, #cf1322 100%)'}
      shadow={single
        ? '0 4px 15px rgba(250, 140, 22, 0.3)'
        : '0 4px 15px rgba(245, 34, 45, 0.3)'}
      icon={single ? <WarningOutlined /> : <ExclamationCircleOutlined />}
      title={single ? 'Payment Reminder' : 'Service Restricted - Payment Required'}
      message={single
        ? `You have an unpaid invoice of ${total} USD. Please complete your payment to continue enjoying uninterrupted service.`
        : (
          <span>
            {`Your account has an overdue balance of ${total} USD. Manager access has been temporarily restricted.`}
            <br />
            Please complete payment and re-login to restore full access.
          </span>
        )}
      actions={bannerButton({
        label: 'Pay Now',
        icon: <CreditCardOutlined />,
        color: single ? '#d46b08' : '#cf1322',
        onClick: () => { if (onNavigate) onNavigate('admin', 'billing'); },
      })}
    />
  );
}

// Fresh-install sample data prompt — same conditions as the legacy banner in
// core/header.php (data.demoPrompt is computed server-side). Dismiss hides it
// for this render only, like the legacy display:none link.
function DemoBanner({ show, onNavigate }) {
  const [dismissed, setDismissed] = useState(false);
  if (!show || dismissed) return null;
  return (
    <DashBanner
      gradient="linear-gradient(135deg, #52c41a 0%, #389e0d 100%)"
      shadow="0 4px 15px rgba(82, 196, 26, 0.3)"
      border="2px solid rgba(255,255,255,0.3)"
      icon={<RocketOutlined />}
      title="Welcome to IceHrm! Want to see how it works?"
      message={(
        <span>
          Add sample employees, projects, attendance, and more to explore all features.
          <br />
          <strong>You can clear all sample data with one click</strong>
          {' '}
          when you&#39;re ready to go live.
        </span>
      )}
      actions={[
        bannerButton({
          label: 'Manage Sample Data',
          icon: <PlusCircleOutlined />,
          color: '#389e0d',
          onClick: () => { if (onNavigate) onNavigate('extension', 'demo-mode|admin'); },
        }),
        bannerButton({
          label: 'Dismiss',
          ghost: true,
          onClick: () => setDismissed(true),
        }),
      ]}
    />
  );
}

// Trial upgrade ad — same gate as the legacy admin dashboard banner
// (show_upgrade_ad session flag; data.upgradeAd carries remaining trial days).
function UpgradeBanner({ upgradeAd, onNavigate }) {
  if (!upgradeAd) return null;
  const { days } = upgradeAd;
  return (
    <DashBanner
      gradient="linear-gradient(135deg, #722ed1 0%, #531dab 100%)"
      shadow="0 4px 15px rgba(114, 46, 209, 0.3)"
      icon={<StarOutlined />}
      title="Unlock the Full Potential of IceHrm"
      message={(
        <span>
          {days !== null && days !== undefined ? (
            <span>
              You have
              {' '}
              <strong>
                {days}
                {' '}
                days
              </strong>
              {' '}
              left in your free trial.
              {' '}
            </span>
          ) : null}
          Upgrade to IceHrm Cloud for unlimited employees, priority support, and premium features.
        </span>
      )}
      actions={[
        bannerButton({
          label: 'Upgrade Now',
          icon: <ArrowUpOutlined />,
          color: '#531dab',
          onClick: () => { if (onNavigate) onNavigate('admin', 'billing'); },
        }),
        bannerButton({
          label: 'See Plans',
          icon: <TagOutlined />,
          ghost: true,
          onClick: () => { window.open('https://icehrm.com/cloud-hosting-charges', '_blank'); },
        }),
      ]}
    />
  );
}

export default function Dashboard({ config, onNavigate }) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const { token } = theme.useToken();
  const isDark = token.colorBgContainer === MUI_DARK.paper;

  // Light text/axis colours injected into g2plot configs in dark mode (the
  // canvas text otherwise defaults to dark grey and is invisible on the card).
  const D_TXT = 'rgba(255,255,255,0.85)';
  const D_AXIS = 'rgba(255,255,255,0.45)';
  const D_LINE = 'rgba(255,255,255,0.25)';
  const D_GRID = 'rgba(255,255,255,0.12)';
  const darkLegend = isDark ? { text: { style: { fill: D_TXT } } } : {};
  const darkAxis = isDark ? {
    label: { style: { fill: D_AXIS } },
    line: { style: { stroke: D_LINE } },
    grid: { line: { style: { stroke: D_GRID } } },
  } : {};
  const darkLabel = isDark ? { style: { fill: D_TXT } } : {};

  useEffect(() => {
    let alive = true;
    fetch(`${config.restApiBase}appshell/dashboard`, {
      headers: { Authorization: `Bearer ${config.token}` },
      credentials: 'same-origin',
    })
      .then((r) => r.json())
      .then((d) => { if (alive) { setData(d); setLoading(false); } })
      .catch((e) => { if (alive) { setError(e.message); setLoading(false); } });
    return () => { alive = false; };
  }, [config]);

  const greeting = useMemo(() => {
    const h = new Date().getHours();
    return h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening';
  }, []);

  const greetName = useMemo(() => {
    const n = (data && data.greetingName) || '';
    if (n.indexOf('@') !== -1) {
      return n.split('@')[0].replace(/[._]+/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
    }
    return n;
  }, [data]);

  if (loading) {
    return <div style={{ display: 'flex', justifyContent: 'center', padding: 80 }}><Spin size="large" /></div>;
  }
  if (error || !data) {
    return <div style={{ padding: 32 }}><Empty description={`Could not load dashboard${error ? `: ${error}` : ''}`} /></div>;
  }

  const k = data.kpis || {};
  const kpis = [
    {
      label: 'Employees', value: k.totalEmployees, icon: <TeamOutlined />, color: '#346CB0', nav: ['admin', 'employees'],
    },
    {
      label: 'Departments', value: k.departments, icon: <ApartmentOutlined />, color: '#5AD8A6', nav: ['admin', 'company_structure'],
    },
  ];
  if (data.leave) {
    kpis.push({
      label: 'Pending Approvals', value: data.leave.pendingRequests, icon: <ClockCircleOutlined />, color: '#E8684A', nav: ['admin', 'leaves', 'tabEmployeeLeave'],
    });
  }
  if (data.expenses) {
    kpis.push({
      label: 'Open Expenses', value: data.expenses.pendingCount, icon: <DollarOutlined />, color: '#5B8FF9', nav: ['extension', 'expenses|admin', 'tabEmployeeExpense'],
    });
  }

  const donutCfg = (rows) => ({
    data: rows || [],
    angleField: 'value',
    colorField: 'type',
    radius: 0.9,
    padding: 'auto',
    color: PALETTE,
    legend: { visible: true, position: 'bottom-center', ...darkLegend },
    label: { visible: false },
    statistic: { totalLabel: 'Total' },
  });

  return (
    <div style={{ padding: 24, maxWidth: 1500, margin: '0 auto' }}>
      {/* Legacy-parity banners (same conditions as the legacy dashboard) */}
      <DemoBanner show={data.demoPrompt} onNavigate={onNavigate} />
      <PaymentBanner billing={data.billing} onNavigate={onNavigate} />
      <UpgradeBanner upgradeAd={data.upgradeAd} onNavigate={onNavigate} />

      {/* Greeting */}
      <div style={{ marginBottom: 18 }}>
        <div style={{ fontSize: 22, fontWeight: 700 }}>
          {greeting}
          ,
          {' '}
          {greetName}
          {' '}
          👋
        </div>
        <Text type="secondary">Here’s what’s happening across your organisation today.</Text>
      </div>

      {/* KPI row */}
      <Row gutter={[16, 16]}>
        {kpis.map((kpi) => (
          <Col xs={12} sm={8} md={6} xl={kpis.length > 6 ? 4 : 6} key={kpi.label}>
            <Kpi
              {...kpi}
              labelColor={token.colorTextSecondary}
              subColor={token.colorTextTertiary}
              onClick={kpi.nav && onNavigate ? () => {
                // kpi.nav[2] optionally deep-links to a specific tab of the target module.
                if (kpi.nav[2]) { try { window.__iceShellStartTab = kpi.nav[2]; } catch (e) { /* */ } }
                onNavigate(kpi.nav[0], kpi.nav[1]);
              } : undefined}
            />
          </Col>
        ))}
        {/* Upgrade-to-Pro call to action, sitting next to the KPI tiles. */}
        <Col xs={12} sm={8} md={6} xl={kpis.length > 6 ? 4 : 6}>
          <UpgradeBox
            onCompare={onNavigate ? () => {
              // Deep-link into the marketplace's IceHrmPro comparison tab.
              try { window.__iceShellStartTab = 'tabIceHrmPro'; } catch (e) { /* */ }
              onNavigate('extension', 'marketplace|admin');
            } : undefined}
          />
        </Col>
      </Row>

      {/* Charts: trend + gender */}
      <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
        <Col xs={24} lg={16}>
          <SectionCard title="Headcount growth" extra={<Tag color="blue"><RiseOutlined /> cumulative</Tag>}>
            <Chart
              type="area"
              height={260}
              config={{
                data: data.headcountTrend || [],
                xField: 'year',
                yField: 'value',
                smooth: true,
                padding: 'auto',
                color: '#346CB0',
                areaStyle: { fill: `l(270) 0:${token.colorBgContainer} 1:#346CB0` },
                xAxis: { visible: true, ...darkAxis },
                yAxis: { visible: true, min: 0, ...darkAxis },
                point: { visible: false },
              }}
            />
          </SectionCard>
        </Col>
        <Col xs={24} lg={8}>
          <SectionCard title="Gender diversity">
            <Chart type="donut" height={260} config={donutCfg(data.genderDist)} />
          </SectionCard>
        </Col>
      </Row>

      {/* Charts: dept + employment type */}
      <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
        <Col xs={24} lg={14}>
          <SectionCard title="Headcount by department">
            <Chart
              type="column"
              height={250}
              config={{
                data: data.headcountByDept || [],
                xField: 'name',
                yField: 'value',
                padding: 'auto',
                color: '#5B8FF9',
                columnSize: 38,
                label: { visible: true, position: 'top', ...darkLabel },
                xAxis: { visible: true, ...darkAxis },
                yAxis: { visible: true, min: 0, ...darkAxis },
              }}
            />
          </SectionCard>
        </Col>
        <Col xs={24} lg={10}>
          <SectionCard title="Employment type">
            <Chart type="donut" height={250} config={donutCfg(data.employmentTypeDist)} />
          </SectionCard>
        </Col>
      </Row>

      {/* People + approvals */}
      <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
        <Col xs={24} md={12} xl={8}>
          <SectionCard title={<span><UserAddOutlined /> Recent hires</span>}>
            <PeopleList
              data={data.recentHires}
              emptyText="No recent hires"
              renderMeta={(it) => (
                <Text type="secondary" style={{ fontSize: 12 }}>
                  {it.title || 'Employee'}
                  {' · joined '}
                  {fmtDate(it.date)}
                </Text>
              )}
            />
          </SectionCard>
        </Col>

        {data.leave ? (
          <Col xs={24} md={12} xl={8}>
            <SectionCard
              title={<span><ClockCircleOutlined /> Pending leave approvals</span>}
              extra={onNavigate ? <a onClick={() => { try { window.__iceShellStartTab = 'tabEmployeeLeave'; } catch (e) { /* */ } onNavigate('admin', 'leaves'); }}>View all</a> : null}
            >
              <PeopleList
                data={data.leave.pendingList}
                emptyText="No pending requests"
                renderMeta={(it) => (
                  <Text type="secondary" style={{ fontSize: 12 }}>
                    {it.type || 'Leave'}
                    {' · '}
                    {fmtDate(it.start)}
                    {it.end && it.end !== it.start ? ` – ${fmtDate(it.end)}` : ''}
                  </Text>
                )}
              />
            </SectionCard>
          </Col>
        ) : null}

        {data.celebrations && data.celebrations.length > 0 ? (
          <Col xs={24} md={12} xl={8}>
            <SectionCard title={<span><GiftOutlined /> Celebrations</span>}>
              <PeopleList
                data={data.celebrations}
                renderMeta={(it) => (
                  <Text type="secondary" style={{ fontSize: 12 }}>
                    {it.type === 'birthday'
                      ? <span><CalendarOutlined /> Birthday</span>
                      : <span><GiftOutlined /> {it.years}-year anniversary</span>}
                    {' · '}
                    {fmtDate(it.date)}
                  </Text>
                )}
              />
            </SectionCard>
          </Col>
        ) : null}

      </Row>
    </div>
  );
}
