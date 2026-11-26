import React, { useState } from 'react';
import { Progress } from 'antd';
import { RocketOutlined, SettingOutlined, TeamOutlined } from '@ant-design/icons';
import { t } from '../api/i18n';

/**
 * Banners that extensions add to the landing pages (admin dashboard, employee
 * dashboard and Home), from DashboardService::addBannerProvider. Each banner:
 *   {key, tone, icon, title, message, vars, progress: {done, total},
 *    actions: [{label, g, n, primary, sessionHint: {key, value}}], dismissible}
 * Texts arrive in English and are translated here (vars fill {placeholders}).
 * A dismiss lasts until the next login (sessionStorage).
 */
const TONES = {
  green: { gradient: 'linear-gradient(135deg, #52c41a 0%, #389e0d 100%)', shadow: 'rgba(82, 196, 26, 0.3)', color: '#389e0d' },
  blue: { gradient: 'linear-gradient(135deg, #346CB0 0%, #1d4f8f 100%)', shadow: 'rgba(52, 108, 176, 0.3)', color: '#1d4f8f' },
  purple: { gradient: 'linear-gradient(135deg, #7b5cd6 0%, #5a3fb0 100%)', shadow: 'rgba(123, 92, 214, 0.3)', color: '#5a3fb0' },
  orange: { gradient: 'linear-gradient(135deg, #fa8c16 0%, #d46b08 100%)', shadow: 'rgba(250, 140, 22, 0.3)', color: '#d46b08' },
};
const ICONS = { rocket: <RocketOutlined />, team: <TeamOutlined />, setting: <SettingOutlined /> };
const STORE = 'dashboard-dismissed-banners';

function dismissedBanners() {
  try {
    return JSON.parse(window.sessionStorage.getItem(STORE) || '[]');
  } catch (e) {
    return [];
  }
}

function goTo(action, onNavigate) {
  if (action.sessionHint && action.sessionHint.key) {
    try {
      window.sessionStorage.setItem(action.sessionHint.key, action.sessionHint.value);
    } catch (e) {
      // Storage blocked: the module opens on its default page.
    }
  }
  if (onNavigate) {
    onNavigate(action.g, action.n);
  } else {
    window.location.hash = `#${encodeURIComponent(`${action.g}::${action.n}`)}`;
  }
}

function BannerButton({
  label, color, ghost, onClick,
}) {
  return (
    <a
      href="#"
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
        whiteSpace: 'nowrap',
        boxShadow: '0 2px 8px rgba(0,0,0,0.1)',
      }}
    >
      {label}
    </a>
  );
}

export default function ExtensionBanners({ banners, onNavigate, style }) {
  const [dismissed, setDismissed] = useState(dismissedBanners);
  if (!Array.isArray(banners) || banners.length === 0) return null;
  const dismiss = (key) => {
    const next = dismissed.concat([key]);
    setDismissed(next);
    try {
      window.sessionStorage.setItem(STORE, JSON.stringify(next));
    } catch (e) {
      // Storage blocked: the dismiss lasts for this page only.
    }
  };
  const visible = banners.filter((b) => dismissed.indexOf(b.key) === -1);
  if (visible.length === 0) return null;
  return (
    <div style={style}>
      {visible.map((b) => {
        const tone = TONES[b.tone] || TONES.blue;
        const progress = b.progress && b.progress.total > 0 ? b.progress : null;
        return (
          <div
            key={b.key}
            style={{
              background: tone.gradient,
              borderRadius: 12,
              padding: '20px 24px',
              marginBottom: 18,
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              gap: 16,
              flexWrap: 'wrap',
              boxShadow: `0 4px 15px ${tone.shadow}`,
              border: '2px solid rgba(255,255,255,0.3)',
            }}
          >
            <div style={{ display: 'flex', alignItems: 'center', gap: 16, minWidth: 0 }}>
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
                {ICONS[b.icon] || <RocketOutlined />}
              </div>
              <div style={{ minWidth: 0 }}>
                <div style={{
                  color: '#fff', fontWeight: 600, fontSize: 16, marginBottom: 4,
                }}
                >
                  {t(b.title, b.vars)}
                </div>
                <div style={{ color: 'rgba(255,255,255,0.9)', fontSize: 14 }}>{t(b.message, b.vars)}</div>
                {progress ? (
                  <div style={{
                    display: 'flex', alignItems: 'center', gap: 10, marginTop: 8, maxWidth: 360,
                  }}
                  >
                    <Progress
                      percent={Math.round((progress.done / progress.total) * 100)}
                      showInfo={false}
                      size="small"
                      strokeColor="#fff"
                      trailColor="rgba(255,255,255,0.3)"
                      style={{ flex: 1, margin: 0 }}
                    />
                    <span style={{ fontSize: 12, whiteSpace: 'nowrap', color: 'rgba(255,255,255,0.9)' }}>
                      {t('{done} of {total} steps', { done: progress.done, total: progress.total })}
                    </span>
                  </div>
                ) : null}
              </div>
            </div>
            <div style={{
              display: 'flex', gap: 10, alignItems: 'center', flexWrap: 'wrap',
            }}
            >
              {(b.actions || []).map((a, i) => (
                <BannerButton
                  key={a.label}
                  label={t(a.label)}
                  color={tone.color}
                  ghost={!a.primary && i > 0}
                  onClick={() => goTo(a, onNavigate)}
                />
              ))}
              {b.dismissible ? <BannerButton label={t('Dismiss')} ghost onClick={() => dismiss(b.key)} /> : null}
            </div>
          </div>
        );
      })}
    </div>
  );
}
