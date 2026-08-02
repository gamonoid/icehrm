import React, { useMemo, useRef, useEffect } from 'react';
import {
  Typography, Table, Collapse, Descriptions, Tag, Empty, theme,
} from 'antd';
import { Column, GroupedColumn } from '@antv/g2plot';
import LeaveCalcParser from './LeaveCalcParser';
import { MUI_DARK } from './theme';

const { Text, Title } = Typography;

const fmt = (v) => {
  const n = Number(v);
  if (!Number.isFinite(n)) return '0';
  return Number.isInteger(n) ? String(n) : String(Math.round(n * 1000) / 1000);
};

// Thin g2plot 1.x wrapper (mirrors the shell Dashboard chart wrapper). The
// bundled g2plot is 1.x, so grouping uses the dedicated GroupedColumn plot and
// the { visible: true } config style. Dark mode is handled by feeding theme
// colours into the axis/label config rather than g2plot's opaque 'dark' theme.
function Plot({ ctor: Ctor, config, height = 260 }) {
  const ref = useRef(null);
  useEffect(() => {
    if (!ref.current) return undefined;
    let plot;
    try {
      plot = new Ctor(ref.current, config);
      plot.render();
    } catch (e) { /* ignore render errors */ }
    return () => { try { if (plot) plot.destroy(); } catch (e) { /* */ } };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [Ctor, JSON.stringify(config), height]);
  return <div ref={ref} style={{ height }} />;
}

/**
 * LeaveCalcChart — renders a graphical view of how a leave type's entitlement
 * was calculated, parsed from the raw "How is this calculated?" log lines.
 *
 * For leave types that carry forward, it charts the year-over-year propagation
 * (carried in vs carried to next) plus a per-period breakdown table. For simple
 * types it charts the current-period derivation (allocated → joined-date →
 * accrual). The raw log is preserved in a collapsible panel.
 */
export default function LeaveCalcChart({ lines }) {
  const { token } = theme.useToken();
  const model = useMemo(() => new LeaveCalcParser(lines || []).parse(), [lines]);

  const isDark = token.colorBgContainer === MUI_DARK.paper;
  const darkAxis = isDark ? {
    label: { style: { fill: 'rgba(255,255,255,0.45)' } },
    line: { style: { stroke: 'rgba(255,255,255,0.25)' } },
    grid: { line: { style: { stroke: 'rgba(255,255,255,0.12)' } } },
  } : {};
  const darkLegend = isDark ? { text: { style: { fill: 'rgba(255,255,255,0.85)' } } } : {};
  const darkLabel = isDark ? { style: { fill: 'rgba(255,255,255,0.85)' } } : {};

  const hasCarry = model.carryForward.length > 0;

  // --- Chart data ---------------------------------------------------------
  // Two series tell the propagation story: how much was allocated each period vs
  // how much actually carried forward to the next one (the rest expires / is used).
  const carryData = [];
  model.carryForward.forEach((p) => {
    carryData.push({ period: p.label, type: 'Allocated', value: p.allocated + p.pto });
    carryData.push({ period: p.label, type: 'Carried to next', value: p.carriedToNext });
  });

  const derivationData = [];
  if (model.totalForPeriod != null) derivationData.push({ step: 'Allocated', value: model.totalForPeriod });
  if (model.joinedDateAdjusted) derivationData.push({ step: 'After join date', value: model.afterJoinedDate });
  if (model.accrualApplied) derivationData.push({ step: 'After accrual', value: model.afterAccrue });

  const carryConfig = {
    data: carryData,
    xField: 'period',
    yField: 'value',
    groupField: 'type',
    color: ['#5B8FF9', '#5AD8A6'],
    legend: { visible: true, position: 'top-center', ...darkLegend },
    label: { visible: false },
    xAxis: { visible: true, title: { visible: true, text: 'Period' }, ...darkAxis },
    yAxis: {
      visible: true, min: 0, title: { visible: true, text: 'Leaves' }, ...darkAxis,
    },
  };

  const derivationConfig = {
    data: derivationData,
    xField: 'step',
    yField: 'value',
    color: '#5B8FF9',
    columnSize: 48,
    label: { visible: true, position: 'top', ...darkLabel },
    xAxis: { visible: true, ...darkAxis },
    yAxis: {
      visible: true, min: 0, title: { visible: true, text: 'Leaves' }, ...darkAxis,
    },
  };

  // --- Per-period breakdown table ----------------------------------------
  const tableData = model.carryForward.map((p, i) => ({ key: i, ...p }));
  const columns = [
    { title: 'Period', dataIndex: 'label', key: 'label' },
    { title: 'Carried in', dataIndex: 'carriedIn', key: 'carriedIn', render: fmt },
    { title: 'Allocated', key: 'allocated', render: (_, r) => fmt(r.allocated + r.pto) },
    { title: 'Taken', dataIndex: 'taken', key: 'taken', render: fmt },
    { title: 'Valid till', dataIndex: 'validTill', key: 'validTill' },
    { title: 'Carried to next', dataIndex: 'carriedToNext', key: 'carriedToNext', render: (v) => <Text strong>{fmt(v)}</Text> },
  ];

  const rawPanel = (
    <Collapse
      ghost
      items={[{
        key: 'raw',
        label: 'Show calculation log',
        children: (
          <ul style={{ fontSize: 12.5, paddingLeft: 18, margin: 0, color: token.colorTextSecondary }}>
            {(lines || []).map((line, i) => (
              // eslint-disable-next-line react/no-array-index-key
              <li key={i} style={{ marginBottom: 3 }}>{String(line).replace(/^\(client=[^)]*\)\s*/, '')}</li>
            ))}
          </ul>
        ),
      }]}
    />
  );

  if (!hasCarry && derivationData.length === 0) {
    return (
      <div>
        <Empty description="No calculation details available" />
        {rawPanel}
      </div>
    );
  }

  return (
    <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>
      <Descriptions size="small" column={2} bordered>
        <Descriptions.Item label="Leave type">{model.leaveType || '—'}</Descriptions.Item>
        <Descriptions.Item label="Current period">
          {model.period ? `${model.period.from} → ${model.period.to}` : '—'}
        </Descriptions.Item>
        <Descriptions.Item label="Allocated for period">{fmt(model.totalForPeriod || 0)}</Descriptions.Item>
        <Descriptions.Item label="Available this period">
          <Text strong>{fmt(model.afterAccrue != null ? model.afterAccrue : model.totalForPeriod || 0)}</Text>
          {model.accrualApplied ? <Tag color="blue" style={{ marginLeft: 8 }}>accrued</Tag> : null}
        </Descriptions.Item>
      </Descriptions>

      {hasCarry ? (
        <div>
          <Title level={5} style={{ marginBottom: 4 }}>Carry-forward propagation</Title>
          <Text type="secondary" style={{ fontSize: 12.5 }}>
            {`How leaves carried across ${model.carryForward.length} past period(s) into the current one.`}
          </Text>
          <Plot ctor={GroupedColumn} config={carryConfig} />
          <Table
            size="small"
            columns={columns}
            dataSource={tableData}
            pagination={false}
            scroll={{ y: 220 }}
            style={{ marginTop: 8 }}
          />
        </div>
      ) : (
        <div>
          <Title level={5} style={{ marginBottom: 4 }}>How this period was calculated</Title>
          <Text type="secondary" style={{ fontSize: 12.5 }}>
            Entitlement for the current period, step by step.
          </Text>
          <Plot ctor={Column} config={derivationConfig} height={220} />
        </div>
      )}

      {rawPanel}
    </div>
  );
}
