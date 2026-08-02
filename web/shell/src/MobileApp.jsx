import React, { useState } from 'react';
import {
  Card, Button, Typography, Space, message, Alert,
} from 'antd';
import { LockOutlined, MobileOutlined } from '@ant-design/icons';
import { MUI_SHADOW } from './theme';

const { Text, Paragraph } = Typography;

// Native "Mobile App" tab for modules::employees — mirrors the legacy
// index.php panels (app-store badges, one-time login code, API access token).
// The login-code request reuses the legacy MobileAppAdapter (window.modJsList
// .tabMobileApp) so it hits the same backend custom action; we just swap its
// DOM-writing callbacks for React state.
export default function MobileApp() {
  const adapter = (window.modJsList || {}).tabMobileApp || null;
  const [code, setCode] = useState(null);
  const [loading, setLoading] = useState(false);

  const requestCode = () => {
    if (!adapter) return;
    setLoading(true);
    adapter.loginCodeSuccessCallback = (cb) => {
      setLoading(false);
      const v = Array.isArray(cb) ? cb[0] : cb;
      const out = v && typeof v === 'object'
        ? (v.code || v.data || v.loginCode || JSON.stringify(v))
        : v;
      setCode(out != null ? String(out) : null);
    };
    adapter.loginCodeFailCallBack = () => {
      setLoading(false);
      message.error('Could not get a login code. Please try again later.', 5);
    };
    try { adapter.getOneTimeLoginCode(); } catch (e) { setLoading(false); }
  };

  const cardStyle = { borderRadius: 12, boxShadow: MUI_SHADOW, marginBottom: 16 };

  return (
    <div style={{ width: '100%' }}>
      <Card
        style={cardStyle}
        title={<Space><MobileOutlined />Download Mobile App</Space>}
      >
        <Paragraph type="secondary">
          Access IceHrm on the go. Download our mobile app for iOS or Android.
        </Paragraph>
        <Space wrap>
          <a href="https://apps.apple.com/gb/app/icehrm/id1624346692" target="_blank" rel="noopener noreferrer">
            <img
              src="https://developer.apple.com/assets/elements/badges/download-on-the-app-store.svg"
              alt="Download on the App Store"
              style={{ height: 50 }}
            />
          </a>
          <a href="https://play.google.com/store/apps/details?id=com.icehrm.m3&hl=en" target="_blank" rel="noopener noreferrer">
            <img
              src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
              alt="Get it on Google Play"
              style={{ height: 50 }}
            />
          </a>
        </Space>
      </Card>

      <Card
        style={cardStyle}
        title={<Space><LockOutlined />Mobile Authentication Code</Space>}
      >
        <Paragraph type="secondary">
          Use this one-time code to securely log in to the mobile app.
        </Paragraph>
        {code ? (
          <Alert
            type="success"
            showIcon
            style={{ marginBottom: 12 }}
            message={<Text strong copyable style={{ fontSize: 18, letterSpacing: 1 }}>{code}</Text>}
          />
        ) : null}
        <Button type="primary" icon={<LockOutlined />} loading={loading} onClick={requestCode}>
          Request One-time Login Code
        </Button>
      </Card>
    </div>
  );
}
