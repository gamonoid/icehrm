import React, {
  useCallback, useEffect, useMemo, useRef, useState,
} from 'react';
import {
  Card, Tag, Button, Input, Pagination, Empty, Spin, Tooltip, theme, Modal, message,
} from 'antd';
import {
  BankOutlined, HomeOutlined, ApartmentOutlined, ClusterOutlined, TeamOutlined,
  EnvironmentOutlined, GlobalOutlined, ClockCircleOutlined, UserOutlined,
  PlusOutlined, EditOutlined, DeleteOutlined, PartitionOutlined,
  CopyOutlined,
} from '@ant-design/icons';
import { MUI, MUI_SHADOW } from './theme';

const PAGE_SIZE = 6;
const TYPE_STYLE = {
  Company: { icon: <BankOutlined />, color: MUI.primary },
  'Head Office': { icon: <HomeOutlined />, color: '#0288d1' },
  'Regional Office': { icon: <ClusterOutlined />, color: '#7b1fa2' },
  Department: { icon: <ApartmentOutlined />, color: '#2e7d32' },
  Unit: { icon: <TeamOutlined />, color: '#ed6c02' },
  'Sub Unit': { icon: <TeamOutlined />, color: '#ed6c02' },
};
const typeStyle = (t) => TYPE_STYLE[t] || { icon: <ApartmentOutlined />, color: '#607d8b' };

export default function CompanyStructureCards({ shellConfig }) {
  const [nodes, setNodes] = useState(null);
  const [err, setErr] = useState(false);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const adapterRef = useRef(null);
  const { token } = theme.useToken();

  const load = useCallback(() => {
    fetch(`${shellConfig.restApiBase}appshell/org-structure`, {
      headers: { Authorization: `Bearer ${shellConfig.token}` },
      credentials: 'same-origin',
    })
      .then((r) => r.json())
      .then((d) => setNodes((d && d.nodes) || []))
      .catch(() => setErr(true));
  }, [shellConfig]);

  useEffect(() => { load(); }, [load]);

  // Use the legacy adapter (instantiated by the module's init) for Add/Edit/Delete
  // and hook its post-save reload into our refresh by injecting a fake table ref.
  useEffect(() => {
    const list = window.modJsList || {};
    const m = list.tabCompanyStructure;
    if (!m) return;
    adapterRef.current = m;
    m.tableContainer = {
      current: {
        reload: () => load(),
        setCurrentElement: () => {},
        setLoading: () => {},
        setFilterData: () => {},
      },
    };
    // Load the form's remote-source select options (Parent Structure, Country,
    // Time Zone, Leads). Normally done inside the adapter's get(); the cards tab
    // never calls get(), so do it here — otherwise selects show ids / "No data".
    try {
      if (m.masterDataReader && m.masterDataReader.updateAllMasterData) {
        m.masterDataReader.updateAllMasterData();
      }
    } catch (e) { /* ignore */ }
  }, [nodes === null, load]); // eslint-disable-line react-hooks/exhaustive-deps

  const adapter = () => adapterRef.current || (window.modJsList || {}).tabCompanyStructure;
  const can = (a) => { const m = adapter(); return m && m.hasAccess && m.hasAccess(a); };
  const addNew = () => { const m = adapter(); if (m) m.renderForm(); };
  const edit = (id) => { const m = adapter(); if (m) m.edit(id); };
  // The adapter's deleteRow() relies on the legacy Bootstrap #deleteModel that
  // does not exist in the native shell mount, so confirm with antd and delete
  // via cleanDelete() (a plain $.post to service.php, no Bootstrap/loader), then
  // refresh the cards ourselves.
  const del = (id) => {
    const m = adapter();
    if (!m) return;
    const node = (nodes || []).find((n) => String(n.id) === String(id));
    Modal.confirm({
      title: 'Delete structure',
      content: node
        ? `Are you sure you want to delete “${node.title}”?`
        : 'Are you sure you want to delete this company structure?',
      okText: 'Delete',
      okType: 'danger',
      onOk: () => new Promise((resolve) => {
        try {
          m.cleanDelete(id, (httpStatus, status) => {
            if (httpStatus === 200 && status === 'SUCCESS') {
              message.success('Structure deleted');
              load();
            } else {
              message.error('Could not delete this structure. It may be in use.', 5);
            }
            resolve();
          });
        } catch (e) {
          message.error('Could not delete this structure', 5);
          resolve();
        }
      }),
    });
  };
  const view = (id) => { const m = adapter(); if (m && m.showDetailsModal) m.showDetailsModal(id); };
  const copy = (id) => { const m = adapter(); if (m && m.copyRow) m.copyRow(id); };

  const filtered = useMemo(() => {
    const all = nodes || [];
    const q = search.trim().toLowerCase();
    if (!q) return all;
    return all.filter((n) => `${n.title} ${n.type} ${n.country} ${n.parentTitle || ''}`.toLowerCase().includes(q));
  }, [nodes, search]);

  const paged = useMemo(
    () => filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE),
    [filtered, page],
  );

  useEffect(() => { setPage(1); }, [search]);

  if (err) return <Empty description="Could not load company structure" />;
  if (!nodes) return <div style={{ display: 'flex', justifyContent: 'center', padding: 60 }}><Spin size="large" /></div>;

  const metaItem = (icon, text) => (text ? (
    <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, maxWidth: 280, overflow: 'hidden' }}>
      {icon}
      <span style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{text}</span>
    </span>
  ) : null);

  return (
    <div>
      {/* Hidden mount points the legacy adapter renders its modals into */}
      <div id="CompanyStructureForm" style={{ display: 'none' }} />
      <div id="CompanyStructureFilterForm" style={{ display: 'none' }} />

      <div style={{
        display: 'flex', justifyContent: 'space-between', alignItems: 'center',
        gap: 12, marginBottom: 16, flexWrap: 'wrap',
      }}>
        <div>
          {can('save') && (
            <Button type="primary" icon={<PlusOutlined />} onClick={addNew}>Add Structure</Button>
          )}
        </div>
        <Input.Search
          allowClear
          placeholder="Search structures…"
          style={{ maxWidth: 280 }}
          onChange={(e) => setSearch(e.target.value)}
        />
      </div>

      {filtered.length === 0 ? (
        <Empty description="No matching company structures" />
      ) : (
        <>
          <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
            {paged.map((n) => {
              const st = typeStyle(n.type);
              return (
                <Card
                  key={n.id}
                  hoverable
                  onClick={() => view(n.id)}
                  style={{ borderRadius: 10, boxShadow: MUI_SHADOW, cursor: 'pointer' }}
                  styles={{ body: { padding: '12px 16px' } }}
                >
                  <div style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
                    <div style={{
                      width: 40, height: 40, borderRadius: 10, flex: '0 0 auto',
                      background: `${st.color}18`, color: st.color, display: 'flex',
                      alignItems: 'center', justifyContent: 'center', fontSize: 18,
                    }}>
                      {st.icon}
                    </div>

                    <div style={{ minWidth: 0, flex: 1 }}>
                      <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                        <span style={{ fontWeight: 600, fontSize: 14 }}>{n.title}</span>
                        <Tag style={{ borderRadius: 6, margin: 0 }}>{n.type || 'Unit'}</Tag>
                        {n.headcount > 0 && (
                          <Tag color="blue" style={{ borderRadius: 6, margin: 0 }}>
                            <UserOutlined />
                            {' '}
                            {n.headcount}
                          </Tag>
                        )}
                      </div>
                      <div style={{
                        display: 'flex', alignItems: 'center', gap: 16, marginTop: 4,
                        color: token.colorTextSecondary, fontSize: 12.5, flexWrap: 'wrap',
                      }}>
                        {metaItem(<PartitionOutlined />, n.parentTitle ? `Reports to ${n.parentTitle}` : 'Top level')}
                        {metaItem(<EnvironmentOutlined />, n.address)}
                        {metaItem(<GlobalOutlined />, n.country)}
                        {metaItem(<ClockCircleOutlined />, n.timezone)}
                      </div>
                    </div>

                    <div
                      style={{ display: 'flex', gap: 10, flex: '0 0 auto' }}
                      onClick={(e) => e.stopPropagation()}
                    >
                      {can('save') && (
                        <Tooltip title="Edit">
                          <Button icon={<EditOutlined style={{ color: '#2e7d32' }} />} onClick={() => edit(n.id)} />
                        </Tooltip>
                      )}
                      {can('save') && (
                        <Tooltip title="Copy">
                          <Button icon={<CopyOutlined style={{ color: '#546e7a' }} />} onClick={() => copy(n.id)} />
                        </Tooltip>
                      )}
                      {can('delete') && (
                        <Tooltip title="Delete">
                          <Button icon={<DeleteOutlined style={{ color: '#d32f2f' }} />} onClick={() => del(n.id)} />
                        </Tooltip>
                      )}
                    </div>
                  </div>
                </Card>
              );
            })}
          </div>

          <div style={{ display: 'flex', justifyContent: 'flex-end', marginTop: 16 }}>
            <Pagination
              current={page}
              pageSize={PAGE_SIZE}
              total={filtered.length}
              onChange={setPage}
              showSizeChanger={false}
              showTotal={(t) => `${t} structures`}
            />
          </div>
        </>
      )}
    </div>
  );
}
