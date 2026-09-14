import React, { useState } from 'react';
import {
  Card, Typography, Space, theme, Empty, Button, Popconfirm, message,
} from 'antd';
import { ApiOutlined, ReloadOutlined } from '@ant-design/icons';
import { MUI_SHADOW } from './theme';

const { Paragraph } = Typography;

// Native "API Access" tab for modules::employees — shows the employee's REST API
// access token and lets them reset it. Reads from / writes to the same legacy
// MobileAppAdapter (window.modJsList.tabMobileApp) the Mobile App tab uses; the
// token/enabled flag are injected by initModulesEmployees.
export default function ApiAccess() {
  const { token } = theme.useToken();
  const adapter = (window.modJsList || {}).tabMobileApp || null;
  const apiEnabled = adapter ? (adapter.apiEnabled === '1' || adapter.apiEnabled === 1) : false;
  const apiBaseUrl = adapter ? adapter.apiBaseUrl : null;
  const [apiToken, setApiToken] = useState(adapter ? adapter.token : null);
  const [resetting, setResetting] = useState(false);

  const cardStyle = { borderRadius: 12, boxShadow: MUI_SHADOW, marginBottom: 16 };

  const resetToken = () => {
    if (!adapter) return;
    setResetting(true);
    adapter.resetApiTokenSuccessCallback = (cb) => {
      setResetting(false);
      const v = Array.isArray(cb) ? cb[0] : cb;
      const newToken = v && typeof v === 'object' ? v.jwtToken : null;
      if (newToken) {
        adapter.token = newToken; // keep it in sync for the Mobile App tab / re-mounts
        setApiToken(newToken);
        message.success('API token reset. The previous token no longer works.', 5);
      } else {
        message.error('Could not reset the API token. Please try again.', 5);
      }
    };
    adapter.resetApiTokenFailCallback = () => {
      setResetting(false);
      message.error('Could not reset the API token. Please try again.', 5);
    };
    try { adapter.resetApiToken(); } catch (e) { setResetting(false); }
  };

  if (!apiEnabled || !apiToken) {
    return (
      <div style={{ width: '100%' }}>
        <Card style={cardStyle} title={<Space><ApiOutlined />API Access</Space>}>
          <Empty
            image={Empty.PRESENTED_IMAGE_SIMPLE}
            description="REST API access is not enabled for your account."
          />
        </Card>
      </div>
    );
  }

  return (
    <div style={{ width: '100%' }}>
      {apiBaseUrl ? (
        <Card style={cardStyle} title={<Space><ApiOutlined />API Base URL</Space>}>
          <Paragraph type="secondary">
            Base URL for all REST API requests.
          </Paragraph>
          <Paragraph
            copyable={{ text: apiBaseUrl }}
            style={{
              wordBreak: 'break-all',
              fontFamily: 'monospace',
              fontSize: 12.5,
              background: token.colorFillTertiary,
              padding: 12,
              borderRadius: 8,
              marginBottom: 0,
            }}
          >
            {apiBaseUrl}
          </Paragraph>
        </Card>
      ) : null}

      <Card
        style={cardStyle}
        title={<Space><ApiOutlined />API Access Token</Space>}
        extra={(
          <Popconfirm
            title="Reset API token"
            description="This immediately invalidates your current token. Any integration using it will stop working until updated."
            okText="Reset"
            cancelText="Cancel"
            okButtonProps={{ danger: true }}
            onConfirm={resetToken}
          >
            <Button danger icon={<ReloadOutlined />} loading={resetting}>
              Reset Token
            </Button>
          </Popconfirm>
        )}
      >
        <Paragraph type="secondary">
          Use this token to authenticate REST API requests (as a Bearer token).
          Keep it secret — anyone with this token can act as you.
        </Paragraph>
        <Paragraph
          copyable={{ text: apiToken }}
          style={{
            wordBreak: 'break-all',
            fontFamily: 'monospace',
            fontSize: 12.5,
            background: token.colorFillTertiary,
            padding: 12,
            borderRadius: 8,
            marginBottom: 0,
          }}
        >
          {apiToken}
        </Paragraph>
      </Card>
    </div>
  );
}
