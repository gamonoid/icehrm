import React, { useEffect, useState } from 'react';
import {
  Spin, Empty, Button, Modal, theme, Typography,
} from 'antd';
import {
  CalendarOutlined, InfoCircleOutlined, PlusOutlined, WarningOutlined, ClockCircleOutlined,
} from '@ant-design/icons';
import { MUI_SHADOW } from './theme';
import LeaveCalcChart from './LeaveCalcChart';

const { Text } = Typography;

// Semantic colours that read on both light and dark grounds. Kept out of the
// theme tokens on purpose: these encode leave state (remaining / pending /
// taken), which is separate from the app's blue accent.
const C = {
  available: '#2e9e50',
  availableSoft: 'rgba(46,158,80,0.14)',
  used: '#7c879a',
  pending: '#e08321',
  danger: '#d64545',
};

const num = (v) => {
  const n = Number(v);
  if (Number.isNaN(n)) return '0';
  return Number.isInteger(n) ? String(n) : String(Math.round(n * 1000) / 1000);
};

const pct = (part, denom) => (denom > 0 ? Math.max(0, Math.min(100, (part / denom) * 100)) : 0);

/**
 * Leave Entitlement — one summary card per leave type. The card answers, at a
 * glance, "how much do I have left and can I book it": a hero Available figure,
 * a proportion meter (taken / pending / available of the entitlement), quiet
 * supporting detail, and an Apply action prefilled to that leave type.
 *
 * Data comes from the bundled adapter's getEntitlement action; Apply opens the
 * main leave adapter's "Apply Leave" modal preselected to this type.
 */
export default function LeaveEntitlement({ tabKey }) {
  const { token } = theme.useToken();
  const [rows, setRows] = useState(null);
  const [calc, setCalc] = useState(null); // { name, lines: [] }

  useEffect(() => {
    let cancelled = false;
    let tries = 0;
    const tick = () => {
      if (cancelled) return;
      const m = (window.modJsList || {})[tabKey];
      if (m && typeof m.fetchEntitlement === 'function') {
        m.fetchEntitlement()
          .then((data) => { if (!cancelled) setRows(Array.isArray(data) ? data : []); })
          .catch(() => { if (!cancelled) setRows([]); });
        return;
      }
      tries += 1;
      if (tries > 100) { setRows([]); return; }
      setTimeout(tick, 100);
    };
    tick();
    return () => { cancelled = true; };
  }, [tabKey]);

  // Open the "Apply Leave" modal (owned by the All My Leaves adapter) with the
  // leave type preselected. renderForm() sets up its own portal-rendered modal,
  // so this works even though that tab isn't the active one.
  const applyFor = (leaveTypeId) => {
    const adapter = (window.modJsList || {}).tabMyLeaveAll || window.modJs;
    if (!adapter || typeof adapter.renderForm !== 'function') return;
    const open = () => adapter.renderForm({ leave_type: leaveTypeId });
    // The Apply form's Leave Type select reads its options from the adapter's
    // master data, which is only fetched when that tab's list loads. Opening
    // from here (that tab was never visited) needs it loaded first, otherwise
    // the select shows the raw id instead of the type name. Cached after first load.
    const reader = adapter.masterDataReader;
    if (reader && typeof reader.updateAllMasterData === 'function') {
      reader.updateAllMasterData().then(open, open);
    } else {
      open();
    }
  };

  if (rows === null) {
    return <div style={{ display: 'flex', justifyContent: 'center', padding: 60 }}><Spin size="large" /></div>;
  }
  if (!rows.length) {
    return <Empty description="No leave entitlement for the current period" />;
  }

  const chipStyle = (variant) => ({
    display: 'inline-flex', alignItems: 'center', gap: 6, fontSize: 12, fontWeight: 600,
    padding: '4px 10px', borderRadius: 999,
    background: variant === 'warn' ? 'rgba(224,131,33,0.14)' : token.colorFillQuaternary,
    color: variant === 'warn' ? C.pending : token.colorTextSecondary,
    border: variant === 'warn' ? '1px solid transparent' : `1px solid ${token.colorBorderSecondary}`,
  });

  return (
    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(340px, 1fr))', gap: 18 }}>
      {rows.map((r) => {
        const total = Number(r.totalLeaves) || 0;
        const approved = Number(r.approvedLeaves) || 0;
        const pending = Number(r.pendingLeaves) || 0;
        const available = Number(r.availableLeaves) || 0;
        const carried = Number(r.carriedForward) || 0;
        const adj = Number(r.paidTimeOff) || 0;
        const accrue = Number(r.tobeAccrued) || 0;

        const denom = Math.max(total, approved + pending + available, 0.0001);
        const usedW = pct(approved, denom);
        const pendW = pct(pending, denom);
        const availW = pct(available, denom);

        const isZero = available <= 0;
        const isLow = !isZero && total > 0 && (available <= 1 || available / total <= 0.15);
        const heroColor = isZero ? C.danger : (isLow ? C.pending : C.available);

        return (
          <div
            key={r.id}
            style={{
              background: token.colorBgContainer,
              border: `1px solid ${token.colorBorderSecondary}`,
              borderRadius: 14,
              boxShadow: MUI_SHADOW,
              padding: '20px 22px 18px',
              display: 'flex',
              flexDirection: 'column',
              gap: 16,
            }}
          >
            {/* Header: type + hero available */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 13 }}>
              <div style={{
                width: 42, height: 42, borderRadius: 11, flex: '0 0 auto', display: 'grid',
                placeItems: 'center', background: 'rgba(25,118,210,0.10)', color: token.colorPrimary, fontSize: 20,
              }}
              >
                <CalendarOutlined />
              </div>
              <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontSize: 16.5, fontWeight: 650, letterSpacing: '-0.01em' }}>{r.name}</div>
                <Text type="secondary" style={{ fontSize: 12.5 }}>Current period</Text>
              </div>
              <div style={{ textAlign: 'right', flex: '0 0 auto' }}>
                <div style={{
                  fontSize: 30, fontWeight: 750, lineHeight: 1, color: heroColor,
                  fontVariantNumeric: 'tabular-nums', letterSpacing: '-0.02em',
                }}
                >
                  {num(available)}
                  <span style={{ fontSize: 14, fontWeight: 600, color: token.colorTextSecondary, marginLeft: 3 }}>
                    {Math.abs(available) === 1 ? 'day' : 'days'}
                  </span>
                </div>
                <div style={{
                  fontSize: 11.5, letterSpacing: '0.04em', textTransform: 'uppercase',
                  color: token.colorTextTertiary, marginTop: 3, fontWeight: 600,
                }}
                >
                  Available
                </div>
              </div>
            </div>

            {/* Proportion meter — the glanceable ratio */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 9 }}>
              <div
                role="img"
                aria-label={`Of ${num(total)} entitled: ${num(approved)} taken, ${num(pending)} pending, ${num(available)} available`}
                style={{
                  height: 12, borderRadius: 999, background: token.colorFillTertiary, display: 'flex', overflow: 'hidden',
                }}
              >
                <div style={{ width: `${usedW}%`, background: C.used }} />
                <div style={{
                  width: `${pendW}%`,
                  background: `repeating-linear-gradient(45deg, ${C.pending}, ${C.pending} 5px, rgba(255,255,255,0.45) 5px, rgba(255,255,255,0.45) 10px)`,
                }}
                />
                <div style={{ width: `${availW}%`, background: C.available }} />
              </div>
              <div style={{
                display: 'flex', flexWrap: 'wrap', gap: '4px 16px', fontSize: 12.5,
                color: token.colorTextSecondary, fontVariantNumeric: 'tabular-nums',
              }}
              >
                {approved > 0 && (
                  <LegendItem color={C.used} label="Taken" value={approved} token={token} />
                )}
                {pending > 0 && (
                  <LegendItem color={C.pending} label="Pending" value={pending} token={token} />
                )}
                <LegendItem color={C.available} label="Available" value={available} token={token} />
                <span style={{ marginLeft: 'auto', color: token.colorTextTertiary }}>
                  of <b style={{ color: token.colorText, fontWeight: 650 }}>{num(total)}</b> entitled
                </span>
              </div>
            </div>

            {/* Secondary detail as chips — every card keeps the same shape */}
            {(isLow || isZero || (carried > 0 && r.carriedForwardLeaveExpireDate) || accrue > 0 || adj !== 0) && (
              <div style={{ display: 'flex', flexWrap: 'wrap', gap: 7 }}>
                {isZero && (
                  <span style={chipStyle('warn')}><WarningOutlined />None left this period</span>
                )}
                {isLow && (
                  <span style={chipStyle('warn')}><WarningOutlined />Running low</span>
                )}
                {carried > 0 && r.carriedForwardLeaveExpireDate && (
                  <span style={chipStyle('warn')}>
                    <ClockCircleOutlined />
                    {`${num(r.carriedForwardAvailable)} carried-forward expire ${r.carriedForwardLeaveExpireDate}`}
                  </span>
                )}
                {accrue > 0 && (
                  <span style={chipStyle()}>{`+${num(accrue)} still to accrue this period`}</span>
                )}
                {adj !== 0 && (
                  <span style={chipStyle()}>{`Adjustment ${adj > 0 ? '+' : ''}${num(adj)}`}</span>
                )}
              </div>
            )}

            {/* Foot: quiet detail link + primary action */}
            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12, marginTop: 2 }}>
              {Array.isArray(r.calculation) && r.calculation.length ? (
                <Button
                  type="link"
                  size="small"
                  icon={<InfoCircleOutlined />}
                  style={{ padding: 0 }}
                  onClick={() => setCalc({ name: r.name, lines: r.calculation })}
                >
                  How is this calculated?
                </Button>
              ) : <span />}
              <Button type="primary" icon={<PlusOutlined />} onClick={() => applyFor(r.id)}>
                Apply
              </Button>
            </div>
          </div>
        );
      })}

      <Modal
        open={!!calc}
        title={calc ? `${calc.name} — how this is calculated` : ''}
        footer={[<Button key="c" onClick={() => setCalc(null)}>Close</Button>]}
        onCancel={() => setCalc(null)}
        width={760}
      >
        {calc ? <LeaveCalcChart lines={calc.lines} /> : null}
      </Modal>
    </div>
  );
}

function LegendItem({ color, label, value, token }) {
  return (
    <span style={{ display: 'inline-flex', alignItems: 'center', gap: 6 }}>
      <i style={{
        width: 9, height: 9, borderRadius: 3, flex: '0 0 auto', background: color, display: 'inline-block',
      }}
      />
      {label} <b style={{ color: token.colorText, fontWeight: 650 }}>{num(value)}</b>
    </span>
  );
}
