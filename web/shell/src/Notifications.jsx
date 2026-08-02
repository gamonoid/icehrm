import React, {
  useEffect, useState, useCallback,
} from 'react';
import {
  Badge, Popover, List, Avatar, Button, Empty, Typography, theme,
} from 'antd';
import { BellOutlined, UserOutlined } from '@ant-design/icons';

const { Text } = Typography;

// Legacy tab anchors (used in notification action URLs) → native SPA tab keys,
// where they differ. Anchors that already match a SPA tab key pass through.
const LEGACY_TAB_ALIASES = {
  // Leave module: legacy notification anchors (tab + mapping name) → native SPA
  // tab keys, which the SPA rebuild shortened.
  tabSubEmployeeLeaveAll: 'tabSubLeaveAll', // "applied for a leave" → Leave Requests (Direct Reports)
  tabEmployeeLeaveApproval: 'tabLeaveApproval', // "assigned … for approval" → Approval Requests
  tabEmployeeLeaveApproved: 'tabMyLeaveApproved', // "leave approved" → Approved Leave
  tabSubEmployeeLeaveCancel: 'tabSubLeaveCancel', // cancellation request → Leave Cancellation Requests
};

// Notifications come from the legacy service.php (cookie session), the same
// source the legacy top bar polled. The shell reuses it with credentials.
function parseAction(action) {
  try {
    const a = typeof action === 'string' ? JSON.parse(action) : action;
    if (a && a.type === 'url' && a.url) {
      // The legacy anchor (e.g. #tabSubEmployeeLeaveAll) names the tab the
      // notification should land on — strip it before query parsing.
      const hashIdx = a.url.indexOf('#');
      const frag = hashIdx >= 0 ? a.url.slice(hashIdx + 1) : null;
      const urlNoFrag = hashIdx >= 0 ? a.url.slice(0, hashIdx) : a.url;
      // The url may be a full legacy URL or just a query string; parse the part
      // after '?' so the first key isn't swallowed by the scheme/path.
      const qs = urlNoFrag.indexOf('?') >= 0 ? urlNoFrag.split('?')[1] : urlNoFrag;
      const sp = new URLSearchParams(qs);
      const g = sp.get('g');
      const n = sp.get('n');
      // The editor extension is a document viewer, not a standalone module — it
      // can't be mounted by route (it needs a hash/object). Open it in the native
      // document modal instead (e.g. a "you were assigned a task list" link).
      if (g === 'extension' && n === 'editor|user' && (sp.get('hash') || sp.get('object'))) {
        return { doc: a.url };
      }
      if (g && n) {
        const tab = frag ? (LEGACY_TAB_ALIASES[frag] || frag) : null;
        return { g, n, tab };
      }
    }
  } catch (e) { /* ignore */ }
  return null;
}

export default function Notifications({ clientBaseUrl, onNavigate, onOpenDocument }) {
  const { token } = theme.useToken();
  const [count, setCount] = useState(0);
  const [list, setList] = useState([]);
  const [open, setOpen] = useState(false);

  const load = useCallback(() => {
    fetch(`${clientBaseUrl}service.php?a=getNotifications`, { credentials: 'same-origin' })
      .then((r) => r.json())
      .then((j) => {
        if (j && j.data) {
          setCount(j.data[0] || 0);
          setList(Array.isArray(j.data[1]) ? j.data[1] : []);
        }
      })
      .catch(() => {});
  }, [clientBaseUrl]);

  useEffect(() => {
    load();
    const id = setInterval(load, 60000);
    return () => clearInterval(id);
  }, [load]);

  const clearAll = () => {
    fetch(`${clientBaseUrl}service.php?a=clearNotifications`, { credentials: 'same-origin' })
      .then(() => { setCount(0); load(); })
      .catch(() => {});
  };

  const handleClick = (item) => {
    const dest = parseAction(item.action);
    setOpen(false);
    if (!dest) return;
    if (dest.doc && onOpenDocument) { onOpenDocument(dest.doc); return; }
    if (dest.g && onNavigate) {
      if (dest.tab) {
        // Land on the tab the notification points at (e.g. a leave application →
        // "Leave Requests (Direct Reports)"). NativeModuleHost consumes the hint
        // at module boot — and only if the module actually has that tab.
        try {
          window.__iceShellStartTab = dest.tab;
          // Already viewing that module? Switch its tab directly.
          const cur = decodeURIComponent(window.location.hash.replace(/^#/, ''));
          if (cur === `${dest.g}::${dest.n}` && window.iceShellSwitchModuleTab) {
            window.iceShellSwitchModuleTab(dest.tab);
          }
        } catch (e) { /* ignore */ }
      }
      onNavigate(dest.g, dest.n);
    }
  };

  const panel = (
    <div style={{ width: 340, maxHeight: 440, overflow: 'auto' }}>
      <div style={{
        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
        padding: '4px 4px 10px', borderBottom: `1px solid ${token.colorBorderSecondary}`, marginBottom: 4,
      }}>
        <Text strong>Notifications</Text>
        {list.length > 0 && <Button type="link" size="small" onClick={clearAll}>Mark all read</Button>}
      </div>
      {list.length === 0 ? (
        <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="No notifications" style={{ padding: 16 }} />
      ) : (
        <List
          dataSource={list}
          renderItem={(item) => (
            <List.Item style={{ padding: '10px 6px', cursor: parseAction(item.action) ? 'pointer' : 'default' }} onClick={() => handleClick(item)}>
              <List.Item.Meta
                avatar={<Avatar src={item.image} icon={<UserOutlined />} size="small" />}
                title={<span style={{ fontSize: 13, fontWeight: 500 }}>{item.type || 'Notification'}</span>}
                description={(
                  <>
                    <div style={{ fontSize: 13, color: token.colorText }}>{item.message}</div>
                    <div style={{ fontSize: 11, color: token.colorTextTertiary, marginTop: 2 }}>{item.time}</div>
                  </>
                )}
              />
            </List.Item>
          )}
        />
      )}
    </div>
  );

  return (
    <Popover
      content={panel}
      trigger="click"
      open={open}
      onOpenChange={setOpen}
      placement="bottomRight"
    >
      <Button type="text" style={{ display: 'flex', alignItems: 'center' }} aria-label="Notifications">
        <Badge count={count} size="small" overflowCount={99}>
          <BellOutlined style={{ fontSize: 18, color: '#fff' }} />
        </Badge>
      </Button>
    </Popover>
  );
}
