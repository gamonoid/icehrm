import React from 'react';
import {
  Card, Button, List, Tag, Typography, Empty,
} from 'antd';
import { CalendarOutlined, PlusOutlined } from '@ant-design/icons';
import { registerWidget } from '../registry';
import { openIntent } from '../intent';
import { ACCENT, chip, soft } from '../palette';
import { t, formatDate } from '../../../../api/i18n';

const { Text } = Typography;

// 'YYYY-MM-DD' as a local date (new Date('2026-11-04') would be UTC midnight,
// which is the previous day west of Greenwich).
function parseDay(s) {
  const p = String(s || '').split('-').map(Number);
  return p.length === 3 ? new Date(p[0], p[1] - 1, p[2]) : null;
}

const fmt = (d, opts) => formatDate(d, opts);

/** "Wed, Nov 4" / "Nov 4 – 6" / "Dec 30 – Jan 2". */
function range(start, end) {
  const a = parseDay(start);
  const b = parseDay(end);
  if (!a) return '';
  if (!b || a.getTime() === b.getTime()) return fmt(a, { weekday: 'short', month: 'short', day: 'numeric' });
  const sameMonth = a.getMonth() === b.getMonth() && a.getFullYear() === b.getFullYear();
  return `${fmt(a, { month: 'short', day: 'numeric' })} – ${sameMonth ? b.getDate() : fmt(b, { month: 'short', day: 'numeric' })}`;
}

/**
 * Time off ("Glance"): my upcoming leave (approved and pending), and a shortcut
 * into the Leaves module's Apply form. Hidden when the leave module isn't
 * installed (appshell/home sends no `leave`).
 */
function TimeOff({ data, ctx }) {
  const leave = (data && data.leave) || {};
  const upcoming = Array.isArray(leave.upcoming) ? leave.upcoming : [];
  // By route key: an admin also has the admin-side "leaves" module.
  const leavesApp = (ctx.apps || []).find((a) => a.key === 'modules::leaves');

  return (
    <Card
      size="small"
      style={{ height: '100%' }}
      title={(
        <span style={{ display: 'inline-flex', alignItems: 'center', gap: 8 }}>
          <span style={chip(ACCENT.timeOff)}><CalendarOutlined /></span>
          {t('Upcoming time off')}
        </span>
      )}
      extra={leavesApp ? (
        <a onClick={() => openIntent({ module: leavesApp.key, tab: 'tabMyLeaveAll' })}>{t('View all')}</a>
      ) : null}
    >
      {upcoming.length === 0 ? (
        <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description={t('No time off booked')} style={{ margin: '8px 0' }} />
      ) : (
        <List
          size="small"
          dataSource={upcoming}
          split={false}
          renderItem={(l) => {
            const pending = l.status === 'Pending';
            return (
              <List.Item style={{ paddingLeft: 0, paddingRight: 0 }}>
                <div style={{
                  display: 'flex', alignItems: 'center', gap: 10, width: '100%', minWidth: 0,
                }}
                >
                  <span style={{
                    width: 4, alignSelf: 'stretch', borderRadius: 2, background: l.color || ACCENT.timeOff, flex: '0 0 auto',
                  }}
                  />
                  <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{ fontWeight: 500 }}>{range(l.start, l.end)}</div>
                    <Text type="secondary" style={{ fontSize: 12 }}>{l.type || t('Leave')}</Text>
                  </div>
                  {pending && (
                    <Tag bordered={false} style={{ margin: 0, color: ACCENT.needsYou, background: soft(ACCENT.needsYou) }}>
                      {t('Pending')}
                    </Tag>
                  )}
                </div>
              </List.Item>
            );
          }}
        />
      )}
      {leavesApp && (
        <Button
          block
          icon={<PlusOutlined />}
          style={{ marginTop: 8 }}
          onClick={() => openIntent({ module: leavesApp.key, action: 'apply' })}
        >
          {t('Apply for time off')}
        </Button>
      )}
    </Card>
  );
}

registerWidget({
  id: 'timeOff', order: 31, column: 'side', size: 'full', requires: { dataKey: 'leave' }, component: TimeOff,
});

export default TimeOff;
