import React, { useEffect, useState } from 'react';
import {
  Spin, Empty, Tag, theme,
} from 'antd';
import {
  BankOutlined, HomeOutlined, ApartmentOutlined, ClusterOutlined,
  TeamOutlined, UserOutlined,
} from '@ant-design/icons';
import { MUI, MUI_SHADOW } from './theme';

// A clean, dependency-free top-down org chart (pure-CSS connectors) for the
// company structure. Replaces the legacy d3 graph.

const orgCss = (token) => `
.org-wrap { overflow-x: auto; padding: 12px 8px 24px; }
.org-tree, .org-tree ul { position: relative; padding-top: 22px; display: flex; justify-content: center; }
.org-tree ul { padding-left: 0; }
.org-tree li {
  list-style: none; position: relative; padding: 22px 10px 0; text-align: center;
}
.org-tree li::before, .org-tree li::after {
  content: ''; position: absolute; top: 0; right: 50%;
  border-top: 2px solid ${token.colorBorderSecondary}; width: 50%; height: 22px;
}
.org-tree li::after { right: auto; left: 50%; border-left: 2px solid ${token.colorBorderSecondary}; }
.org-tree li:only-child::after, .org-tree li:only-child::before { display: none; }
.org-tree li:only-child { padding-top: 0; }
.org-tree li:first-child::before, .org-tree li:last-child::after { border: 0 none; }
.org-tree li:last-child::before { border-right: 2px solid ${token.colorBorderSecondary}; border-radius: 0 6px 0 0; }
.org-tree li:first-child::after { border-radius: 6px 0 0 0; }
.org-tree ul ul::before {
  content: ''; position: absolute; top: 0; left: 50%;
  border-left: 2px solid ${token.colorBorderSecondary}; width: 0; height: 22px;
}
.org-node {
  display: inline-flex; align-items: center; gap: 12px;
  padding: 12px 16px; min-width: 180px; max-width: 240px;
  background: ${token.colorBgContainer}; border: 1px solid ${token.colorBorderSecondary}; border-radius: 12px;
  box-shadow: ${MUI_SHADOW}; text-align: left; transition: box-shadow .15s, transform .15s;
}
.org-node:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.12); }
.org-node .ic {
  width: 38px; height: 38px; border-radius: 10px; flex: 0 0 auto;
  display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.org-node .ttl { font-weight: 600; font-size: 14px; line-height: 1.2; color: ${token.colorText}; }
.org-node .sub { font-size: 12px; color: ${token.colorTextSecondary}; margin-top: 2px; }
`;

const TYPE_STYLE = {
  Company: { icon: <BankOutlined />, color: MUI.primary },
  'Head Office': { icon: <HomeOutlined />, color: '#0288d1' },
  'Regional Office': { icon: <ClusterOutlined />, color: '#7b1fa2' },
  Department: { icon: <ApartmentOutlined />, color: '#2e7d32' },
  Unit: { icon: <TeamOutlined />, color: '#ed6c02' },
  'Sub Unit': { icon: <TeamOutlined />, color: '#ed6c02' },
  Other: { icon: <ApartmentOutlined />, color: '#607d8b' },
};

function nodeStyle(type) {
  return TYPE_STYLE[type] || { icon: <ApartmentOutlined />, color: '#607d8b' };
}

function buildForest(nodes) {
  const byId = {};
  nodes.forEach((n) => { byId[n.id] = { ...n, children: [] }; });
  const roots = [];
  nodes.forEach((n) => {
    if (n.parent && byId[n.parent]) byId[n.parent].children.push(byId[n.id]);
    else roots.push(byId[n.id]);
  });
  return roots;
}

function NodeCard({ node }) {
  const st = nodeStyle(node.type);
  return (
    <div className="org-node">
      <div className="ic" style={{ background: `${st.color}18`, color: st.color }}>{st.icon}</div>
      <div style={{ minWidth: 0 }}>
        <div className="ttl">{node.title}</div>
        <div className="sub">
          {node.type || 'Unit'}
          {node.headcount > 0 && (
            <>
              {' · '}
              <UserOutlined style={{ fontSize: 11 }} />
              {' '}
              {node.headcount}
            </>
          )}
        </div>
      </div>
    </div>
  );
}

function TreeNode({ node }) {
  return (
    <li>
      <NodeCard node={node} />
      {node.children && node.children.length > 0 && (
        <ul>
          {node.children.map((c) => <TreeNode key={c.id} node={c} />)}
        </ul>
      )}
    </li>
  );
}

export default function OrgChart({ shellConfig }) {
  const [nodes, setNodes] = useState(null);
  const [err, setErr] = useState(false);
  const { token } = theme.useToken();

  useEffect(() => {
    let alive = true;
    fetch(`${shellConfig.restApiBase}appshell/org-structure`, {
      headers: { Authorization: `Bearer ${shellConfig.token}` },
      credentials: 'same-origin',
    })
      .then((r) => r.json())
      .then((d) => { if (alive) setNodes((d && d.nodes) || []); })
      .catch(() => { if (alive) setErr(true); });
    return () => { alive = false; };
  }, [shellConfig]);

  if (err) return <Empty description="Could not load the org chart" />;
  if (!nodes) return <div style={{ display: 'flex', justifyContent: 'center', padding: 60 }}><Spin /></div>;
  if (nodes.length === 0) return <Empty description="No company structure defined yet" />;

  const roots = buildForest(nodes);

  return (
    <>
      <style>{orgCss(token)}</style>
      <div className="org-wrap">
        <div className="org-tree">
          <ul>
            {roots.map((r) => <TreeNode key={r.id} node={r} />)}
          </ul>
        </div>
      </div>
    </>
  );
}
