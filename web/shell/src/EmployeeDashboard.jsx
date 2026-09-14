import React, { useEffect, useMemo, useState } from 'react';
import {
  Row, Col, Card, List, Avatar, Tag, Spin, Empty, Typography, Button, Timeline, theme,
} from 'antd';
import {
  ClockCircleOutlined, CheckSquareOutlined, CoffeeOutlined, TeamOutlined,
  UsergroupAddOutlined, GiftOutlined, RightOutlined, CalendarOutlined,
  AlertOutlined, FireOutlined, WarningOutlined, InfoCircleOutlined, CheckCircleOutlined,
} from '@ant-design/icons';
import { MUI_SHADOW } from './theme';

const { Text } = Typography;

const PALETTE = ['#346CB0', '#5AD8A6', '#5B8FF9', '#F6BD16', '#E8684A', '#9270CA', '#6DC8EC', '#FF99C3'];

function initials(name) {
  const p = (name || '').trim().split(/\s+/);
  return (((p[0] && p[0][0]) || '') + ((p[1] && p[1][0]) || '')).toUpperCase() || '?';
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
function daysLabel(n) {
  if (n === 0) return 'Today';
  if (n === 1) return 'Tomorrow';
  return `in ${n} days`;
}

// Map a Task priority to a timeline dot icon (mirrors the legacy My To-Do List).
function todoDot(priority) {
  if (priority >= 1000) return <AlertOutlined style={{ color: '#d32f2f', fontSize: 16 }} />;
  if (priority >= 100) return <FireOutlined style={{ color: '#e8684a', fontSize: 16 }} />;
  if (priority >= 50) return <WarningOutlined style={{ color: '#ed6c02', fontSize: 16 }} />;
  if (priority >= 20) return <InfoCircleOutlined style={{ color: '#1677ff', fontSize: 16 }} />;
  return <CheckCircleOutlined style={{ color: '#2e7d32', fontSize: 16 }} />;
}
// Pull g/n out of a legacy task link so we can navigate within the SPA.
function routeFromLink(link) {
  try {
    const qs = link.indexOf('?') >= 0 ? link.split('?')[1] : link;
    const sp = new URLSearchParams(qs);
    const g = sp.get('g');
    const n = sp.get('n');
    return (g && n) ? { g, n } : null;
  } catch (e) {
    return null;
  }
}

export default function EmployeeDashboard({ config, onNavigate, onOpenDocument }) {
  const { token } = theme.useToken();
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [showAllTodo, setShowAllTodo] = useState(false);

  useEffect(() => {
    let alive = true;
    setLoading(true);
    fetch(`${config.restApiBase}appshell/employee-dashboard`, {
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

  const att = data.attendance || {};
  const todo = data.todo || [];
  const cels = data.celebrations || [];
  const directReports = data.directReports || [];
  const teams = data.teams || [];

  // --- stat cards ----------------------------------------------------------
  const stats = [];
  if (att && (att.hoursToday !== undefined)) {
    stats.push({
      label: 'Hours today',
      value: `${(att.hoursToday || 0).toFixed(1)}h`,
      sub: att.punchedIn ? 'Clocked in' : (att.punchedOutToday ? 'Clocked out' : 'Not clocked in'),
      icon: <ClockCircleOutlined />,
      color: '#346CB0',
      nav: ['modules', 'attendance'],
    });
  }
  stats.push({
    label: 'Action items',
    value: todo.length,
    sub: 'On your to-do list',
    icon: <CheckSquareOutlined />,
    color: '#5AD8A6',
  });
  if (data.leave) {
    stats.push({
      label: 'My pending leave',
      value: data.leave.pending || 0,
      sub: 'Awaiting approval',
      icon: <CoffeeOutlined />,
      color: '#F6BD16',
      nav: ['modules', 'leaves'],
    });
  }
  if (data.isManager && data.teamStats) {
    stats.push({
      label: 'Direct reports',
      value: data.teamStats.reports || 0,
      sub: 'In your team',
      icon: <TeamOutlined />,
      color: '#9270CA',
      nav: ['admin', 'employees'],
    });
    stats.push({
      label: 'Team on leave',
      value: data.teamStats.onLeaveToday || 0,
      sub: 'Today',
      icon: <CalendarOutlined />,
      color: '#E8684A',
      nav: ['modules', 'leaves'],
    });
  }

  const cardHead = (icon, title, extra) => ({
    title: (
      <span style={{ display: 'inline-flex', alignItems: 'center', gap: 8 }}>
        {icon}
        {title}
      </span>
    ),
    extra,
  });

  return (
    <div style={{ padding: 24, maxWidth: 1500, margin: '0 auto' }}>
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
        <Text type="secondary">Here’s your day at a glance.</Text>
      </div>

      {/* Stat row */}
      <Row gutter={[16, 16]}>
        {stats.map((s) => (
          <Col xs={12} sm={8} md={data.isManager ? 6 : 8} xl={data.isManager ? 4 : 6} key={s.label}>
            <Card
              hoverable
              onClick={() => s.nav && onNavigate && onNavigate(s.nav[0], s.nav[1])}
              style={{ borderRadius: 12, boxShadow: MUI_SHADOW }}
              styles={{ body: { padding: 16 } }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <span style={{
                  width: 42, height: 42, borderRadius: 11, flex: '0 0 auto', fontSize: 19,
                  background: `${s.color}22`, color: s.color,
                  display: 'flex', alignItems: 'center', justifyContent: 'center',
                }}>
                  {s.icon}
                </span>
                <div style={{ minWidth: 0 }}>
                  <div style={{ fontSize: 22, fontWeight: 700, lineHeight: 1.1 }}>{s.value}</div>
                  <div style={{ fontSize: 12, color: token.colorTextSecondary }}>{s.label}</div>
                </div>
              </div>
              {s.sub && (
                <div style={{ fontSize: 11.5, color: token.colorTextTertiary, marginTop: 8 }}>{s.sub}</div>
              )}
            </Card>
          </Col>
        ))}
      </Row>

      <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
        {/* My To-Do List — actionable system tasks (status, check-in, setup…). */}
        <Col xs={24} lg={data.isManager ? 12 : 14}>
          <Card
            {...cardHead(<CheckSquareOutlined style={{ color: '#5B8FF9' }} />, 'My To-Do List')}
            style={{ borderRadius: 12, boxShadow: MUI_SHADOW }}
          >
            {todo.length === 0 ? (
              <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="You're all caught up 🎉" />
            ) : (
              <>
                <Timeline
                  items={(showAllTodo ? todo : todo.slice(0, 4)).map((t) => {
                    const route = t.link ? routeFromLink(t.link) : null;
                    return {
                      dot: todoDot(t.priority),
                      children: (
                        <div>
                          <div style={{ fontWeight: 500 }}>{t.text}</div>
                          {t.details && (
                            <div style={{ fontSize: 12, color: token.colorTextTertiary, marginTop: 2 }}>{t.details}</div>
                          )}
                          {t.link && t.action && (
                            <Button
                              type="link"
                              size="small"
                              style={{ paddingLeft: 0 }}
                              onClick={() => {
                                if (route && onNavigate) onNavigate(route.g, route.n);
                                else window.location.href = t.link;
                              }}
                            >
                              {t.action}
                            </Button>
                          )}
                        </div>
                      ),
                    };
                  })}
                />
                {todo.length > 4 && (
                  <Button type="primary" onClick={() => setShowAllTodo((v) => !v)}>
                    {showAllTodo ? 'Show less' : `View all ${todo.length} tasks`}
                  </Button>
                )}
              </>
            )}
          </Card>
        </Col>

        {/* Upcoming celebrations */}
        <Col xs={24} lg={data.isManager ? 12 : 10}>
          <Card
            {...cardHead(<GiftOutlined style={{ color: '#E8684A' }} />, 'Upcoming celebrations')}
            style={{ borderRadius: 12, boxShadow: MUI_SHADOW }}
          >
            {cels.length === 0 ? (
              <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="Nothing coming up" />
            ) : (
              <List
                dataSource={cels}
                renderItem={(c) => (
                  <List.Item>
                    <List.Item.Meta
                      avatar={<Avatar style={{ background: colorFor(c.name) }}>{initials(c.name)}</Avatar>}
                      title={<span style={{ fontWeight: 600 }}>{c.name}</span>}
                      description={c.type === 'birthday'
                        ? `🎂 Birthday · ${fmtDate(c.date)}`
                        : `🎉 ${c.years}yr anniversary · ${fmtDate(c.date)}`}
                    />
                    <Tag color={c.days === 0 ? 'red' : 'default'}>{daysLabel(c.days)}</Tag>
                  </List.Item>
                )}
              />
            )}
          </Card>
        </Col>
      </Row>

      {/* Direct Reports (line management) — managers only. */}
      {data.isManager && (
        <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
          <Col xs={24}>
            <Card
              {...cardHead(<TeamOutlined style={{ color: '#9270CA' }} />, 'Direct Reports',
                <Button type="link" size="small" onClick={() => onNavigate && onNavigate('admin', 'employees')}>
                  View employees
                  <RightOutlined />
                </Button>)}
              style={{ borderRadius: 12, boxShadow: MUI_SHADOW }}
            >
              {directReports.length === 0 ? (
                <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="No direct reports" />
              ) : (
                <Row gutter={[12, 12]}>
                  {directReports.map((m) => (
                    <Col xs={12} sm={8} md={6} xl={4} key={m.id}>
                      <div style={{
                        display: 'flex', alignItems: 'center', gap: 10, padding: '8px 10px',
                        border: `1px solid ${token.colorBorderSecondary}`, borderRadius: 10,
                      }}>
                        <Avatar style={{ background: colorFor(m.name), flex: '0 0 auto' }}>{initials(m.name)}</Avatar>
                        <div style={{ minWidth: 0 }}>
                          <div style={{
                            fontWeight: 600, fontSize: 13, overflow: 'hidden',
                            textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                          }}>
                            {m.name}
                          </div>
                          <div style={{
                            fontSize: 11, color: token.colorTextTertiary, overflow: 'hidden',
                            textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                          }}>
                            {m.title || '—'}
                          </div>
                        </div>
                      </div>
                    </Col>
                  ))}
                </Row>
              )}
            </Card>
          </Col>
        </Row>
      )}

      {/* My Teams (collaboration / matrix) — anyone who is on a team. */}
      {teams.length > 0 && (
        <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
          <Col xs={24}>
            <Card
              {...cardHead(<UsergroupAddOutlined style={{ color: '#0288d1' }} />, 'My Teams',
                <Button type="link" size="small" onClick={() => onNavigate && onNavigate('extension', 'team|user')}>
                  View teams
                  <RightOutlined />
                </Button>)}
              style={{ borderRadius: 12, boxShadow: MUI_SHADOW }}
            >
              <Row gutter={[12, 12]}>
                {teams.map((t) => (
                  <Col xs={24} sm={12} md={8} xl={6} key={t.id}>
                    <div style={{
                      display: 'flex', alignItems: 'center', gap: 10, padding: '10px 12px',
                      border: `1px solid ${token.colorBorderSecondary}`, borderRadius: 10,
                    }}>
                      <span style={{
                        width: 36, height: 36, borderRadius: 9, flex: '0 0 auto',
                        background: (t.color || '#0288d1') + '22', color: t.color || '#0288d1',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                      }}>
                        <TeamOutlined />
                      </span>
                      <div style={{ minWidth: 0, flex: 1 }}>
                        <div style={{
                          fontWeight: 600, fontSize: 13, overflow: 'hidden',
                          textOverflow: 'ellipsis', whiteSpace: 'nowrap',
                        }}>
                          {t.name}
                        </div>
                        <div style={{ fontSize: 11, color: token.colorTextTertiary }}>
                          {`${t.members} member${t.members === 1 ? '' : 's'}`}
                        </div>
                      </div>
                      {t.role && t.role !== 'Member' && <Tag color="blue">{t.role}</Tag>}
                    </div>
                  </Col>
                ))}
              </Row>
            </Card>
          </Col>
        </Row>
      )}
    </div>
  );
}
