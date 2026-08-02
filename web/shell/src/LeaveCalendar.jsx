import React, { useEffect, useState, useCallback } from 'react';
import {
  Badge, Calendar, Avatar, Tooltip, Alert, theme,
} from 'antd';

const pad = (n) => (Number(n) < 10 ? `0${n}` : `${n}`);

/**
 * Leave Calendar (modules::leavecal) — a month/year calendar showing the
 * employee's and their direct reports' leaves, plus holidays. Data comes from
 * the leave-calendar REST endpoint, fetched with the shell's auth token.
 */
export default function LeaveCalendar({ shellConfig }) {
  const { token } = theme.useToken();
  const [byDate, setByDate] = useState({}); // 'YYYY-MM-DD' -> [{ employee, status }]
  const [holidays, setHolidays] = useState([]);
  const [byMonth, setByMonth] = useState({}); // year -> { 'YYYY-MM': [{ employee, count }] }

  const apiGet = useCallback((path) => {
    const base = (shellConfig && shellConfig.restApiBase) || '';
    return fetch(`${base}${path}`, {
      headers: { Authorization: `Bearer ${shellConfig && shellConfig.token}` },
      credentials: 'same-origin',
    }).then((r) => r.json());
  }, [shellConfig]);

  const loadMonth = useCallback((year, month) => {
    apiGet(`leave-calendar/month/${year}/${pad(month)}`)
      .then((d) => {
        setByDate((prev) => ({ ...prev, ...((d && d.leave) || {}) }));
        setHolidays((d && d.holidays) || []);
      })
      .catch(() => { /* ignore */ });
  }, [apiGet]);

  const loadYear = useCallback((year) => {
    apiGet(`leave-calendar/year/${year}`)
      .then((d) => setByMonth((prev) => ({ ...prev, ...(d || {}) })))
      .catch(() => { /* ignore */ });
  }, [apiGet]);

  useEffect(() => {
    const now = new Date();
    loadMonth(now.getFullYear(), now.getMonth() + 1);
  }, [loadMonth]);

  const onPanelChange = (value, mode) => {
    if (mode === 'month') loadMonth(value.year(), value.month() + 1);
    else loadYear(value.year());
  };

  const dateCell = (value) => {
    const key = `${value.year()}-${pad(value.month() + 1)}-${pad(value.date())}`;
    const list = byDate[key] || [];
    const holiday = holidays.find((h) => h && h.dateh === key);
    if (!holiday && !list.length) return null;
    return (
      <div style={{ marginTop: 4 }}>
        {holiday ? (
          <Alert message={holiday.name} type="warning" showIcon style={{ marginBottom: 6, padding: '1px 8px' }} />
        ) : null}
        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
          {list.map((item, i) => (
            <Tooltip
              // eslint-disable-next-line react/no-array-index-key
              key={`${item.employee.id}-${i}`}
              title={`${item.employee.name} · ${item.status}`}
              color="#108ee9"
            >
              <Badge color={item.status === 'Approved' ? 'green' : 'orange'} dot>
                <Avatar size="small" src={item.employee.image}>
                  {item.employee.name ? item.employee.name.charAt(0) : '?'}
                </Avatar>
              </Badge>
            </Tooltip>
          ))}
        </div>
      </div>
    );
  };

  const monthCell = (value) => {
    const yd = byMonth[value.year()];
    const mk = `${value.year()}-${pad(value.month() + 1)}`;
    const list = (yd && yd[mk]) || [];
    if (!list.length) return null;
    return (
      <div style={{ marginTop: 8, display: 'flex', flexWrap: 'wrap', gap: 8 }}>
        {list.map((item, i) => (
          <Tooltip
            // eslint-disable-next-line react/no-array-index-key
            key={`${item.employee.id}-${i}`}
            title={item.employee.name}
            color="#108ee9"
          >
            <Badge color="green" size="small" count={item.count}>
              <Avatar size="small" src={item.employee.image}>
                {item.employee.name ? item.employee.name.charAt(0) : '?'}
              </Avatar>
            </Badge>
          </Tooltip>
        ))}
      </div>
    );
  };

  return (
    <div style={{
      background: token.colorBgContainer, borderRadius: 10, padding: 12,
    }}
    >
      <Calendar
        cellRender={(current, info) => (info.type === 'date' ? dateCell(current) : monthCell(current))}
        onPanelChange={onPanelChange}
      />
    </div>
  );
}
