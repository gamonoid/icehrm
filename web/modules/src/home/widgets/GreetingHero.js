import React, { useEffect, useState } from 'react';
import {
  Button, Space, Tooltip, Typography,
} from 'antd';
import { CalendarOutlined, ClockCircleOutlined, TrophyOutlined } from '@ant-design/icons';
import { registerWidget } from '../registry';
import { openIntent } from '../intent';
import { getKudosMe } from '../feedApi';
import { ACCENT } from '../palette';
import { t, formatDate } from '../../../../api/i18n';

const { Title, Text } = Typography;

/**
 * Greeting + the top intents one click from the door ("Act"). Each quick action
 * appears only when the user can open the module it targets.
 */
function GreetingHero({ data, ctx, apiClient }) {
  const name = (data && data.greetingName) || '';
  const apps = ctx.apps || [];
  // Route by the app's shell key (core "modules::x" or extension "extension::x|user").
  // These are personal actions, so prefer the user's self-service module: an admin
  // also has "admin::attendance", which must not win just because it sorts first.
  const personal = (a) => a.key.indexOf('modules::') === 0 || /\|user$/.test(a.key);
  const app = (n) => apps.find((a) => a.name === n && personal(a)) || apps.find((a) => a.name === n);
  const has = (n) => !!app(n);
  const go = (n, extra) => openIntent({ module: app(n).key, ...(extra || {}) });
  // Kudos left to give this month, shown on the Give Kudos action. Optional: if the
  // lookup fails the button just renders without a count.
  const [kudosQuota, setKudosQuota] = useState(null);
  const hasKudos = has('kudos');
  useEffect(() => {
    if (!hasKudos || !apiClient) return undefined;
    let cancelled = false;
    getKudosMe(apiClient)
      .then((me) => { if (!cancelled) setKudosQuota((me && me.quota) || null); })
      .catch(() => {});
    return () => { cancelled = true; };
  }, [hasKudos, apiClient]);
  const kudosLeft = kudosQuota ? kudosQuota.remaining : null;
  const outOfKudos = kudosLeft !== null && kudosLeft <= 0;

  const today = formatDate(new Date(), { weekday: 'long', day: 'numeric', month: 'short' });

  return (
    <div style={{
      display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12,
      padding: '22px 26px', borderRadius: 14, position: 'relative', overflow: 'hidden',
      // A warm gradient band, the one deliberately bold surface on the page — it
      // anchors the greeting the way HiBob's hero does, without a stock image.
      background: `linear-gradient(120deg, ${ACCENT.feed} 0%, #6f4bf0 45%, ${ACCENT.birthday} 100%)`,
      color: '#fff',
    }}
    >
      {/* soft light blooms, so the band is not a flat rectangle */}
      <div style={{
        position: 'absolute', right: -40, top: -70, width: 240, height: 240, borderRadius: '50%',
        background: 'rgba(255,255,255,0.13)', pointerEvents: 'none',
      }}
      />
      <div style={{
        position: 'absolute', right: 130, bottom: -90, width: 170, height: 170, borderRadius: '50%',
        background: 'rgba(255,255,255,0.09)', pointerEvents: 'none',
      }}
      />
      <div style={{ position: 'relative' }}>
        <Title level={3} style={{ margin: 0, color: '#fff' }}>
          {name ? t("Hi {name}, glad you're here", { name }) : t('Welcome')}
          <span role="img" aria-label="wave" style={{ marginLeft: 8 }}>👋</span>
        </Title>
        <Text style={{ color: 'rgba(255,255,255,0.85)' }}>{t("{date} · here's your day", { date: today })}</Text>
      </div>
      <Space wrap style={{ position: 'relative' }}>
        {has('leaves') && (
          <Button
            icon={<CalendarOutlined />}
            onClick={() => go('leaves')}
            style={{ background: '#fff', borderColor: '#fff', color: ACCENT.feed, fontWeight: 600 }}
          >
            {t('Request time off')}
          </Button>
        )}
        {has('attendance') && (
          <Button
            icon={<ClockCircleOutlined />}
            onClick={() => openIntent({ module: 'modules::attendance' })}
            style={{ background: 'rgba(255,255,255,0.18)', borderColor: 'rgba(255,255,255,0.55)', color: '#fff' }}
          >
            {t('Clock in')}
          </Button>
        )}
        {hasKudos && (
          <Tooltip
            title={outOfKudos
              ? t("You've given all {limit} kudos for this month. More become available on the 1st.", { limit: kudosQuota.limit })
              : null}
          >
            <Button
              icon={<TrophyOutlined />}
              disabled={outOfKudos}
              onClick={() => go('kudos', { action: 'compose' })}
              style={{
                background: 'rgba(255,255,255,0.18)',
                borderColor: 'rgba(255,255,255,0.55)',
                color: '#fff',
                opacity: outOfKudos ? 0.6 : 1,
              }}
            >
              {t('Give Kudos')}
              {kudosLeft !== null && (
                <span style={{
                  marginLeft: 8, padding: '0 8px', borderRadius: 999, fontSize: 12, fontWeight: 600,
                  background: 'rgba(255,255,255,0.25)',
                }}
                >
                  {t('{count} left', { count: kudosLeft })}
                </span>
              )}
            </Button>
          </Tooltip>
        )}
      </Space>
    </div>
  );
}

registerWidget({
  id: 'greeting', order: 0, column: 'hero', size: 'full', component: GreetingHero,
});

export default GreetingHero;
