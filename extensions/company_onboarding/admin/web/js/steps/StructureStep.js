import React, { useEffect, useState } from 'react';
import {
  Button, Card, Col, Empty, Input, Popconfirm, Row, Select, Skeleton, Space, Tag, Tooltip, Typography, message, theme,
} from 'antd';
import {
  ApartmentOutlined, BankOutlined, DeleteOutlined, EditOutlined, HomeOutlined, PlusOutlined, TeamOutlined,
} from '@ant-design/icons';
import { t, tp } from '../../../../../../web/api/i18n';
import { errorMessage } from '../api';
import StepFooter from '../components/StepFooter';

const { Text } = Typography;

const OFFICE_TYPES = ['Head Office', 'Regional Office'];
const filterOption = (input, option) => String(option.label).toLowerCase().includes(input.toLowerCase());

function typeLabel(type) {
  switch (type) {
    case 'Company': return t('Company');
    case 'Head Office': return t('Head Office');
    case 'Regional Office': return t('Regional Office');
    case 'Department': return t('Department');
    case 'Unit': return t('Unit');
    case 'Sub Unit': return t('Sub Unit');
    default: return t('Other');
  }
}

function typeIcon(type) {
  if (type === 'Company') return <BankOutlined />;
  if (OFFICE_TYPES.includes(type)) return <HomeOutlined />;
  if (type === 'Department') return <ApartmentOutlined />;
  return <TeamOutlined />;
}

const PRESETS = [
  { key: 'small', title: () => t('Departments only'), text: () => t('Management, HR, Finance, Sales and Operations directly under the company.') },
  { key: 'office', title: () => t('One office'), text: () => t('A head office with the usual departments.') },
  { key: 'regional', title: () => t('Head office and a regional office'), text: () => t('Two offices, with departments in the head office.') },
];

/** Step 2 (optional): offices and departments, as a tree under the company. */
export default function StructureStep({
  api, step, busy, onContinue, onSkip,
}) {
  const { token } = theme.useToken();
  const [units, setUnits] = useState(null);
  const [lookups, setLookups] = useState({ countries: [], timezones: [], unit_types: [] });
  const [adding, setAdding] = useState(null); // parent id
  const [draft, setDraft] = useState({ title: '', type: 'Department' });
  const [editing, setEditing] = useState(null); // unit being edited
  const [working, setWorking] = useState(false);

  const load = () => api.get('company-onboarding/structure').then(setUnits)
    .catch((e) => message.error(errorMessage(e, t('The structure could not be loaded'))));
  useEffect(() => {
    load();
    api.get('company-onboarding/lookups').then(setLookups).catch(() => {});
  }, []);

  if (!units) return <Skeleton active />;

  const root = units.find((u) => u.parent === null && u.type === 'Company') || units.find((u) => u.parent === null);
  const childrenOf = (id) => units.filter((u) => u.parent === id);
  const typeOptions = (lookups.unit_types || []).map((v) => ({ value: v, label: typeLabel(v) }));

  // Resolves true when the change was saved, false when it failed (the error is shown),
  // so a failed add or rename keeps its editor open with the typed text.
  const run = (promise, success) => {
    setWorking(true);
    return promise
      .then(() => {
        if (success) message.success(success);
        return load().then(() => true);
      }, (e) => {
        message.error(errorMessage(e, t('That did not work. Please try again.')));
        return false;
      })
      .finally(() => setWorking(false));
  };

  const applyPreset = (key) => run(api.post(`company-onboarding/structure-preset/${key}`, {}), t('Added'));
  const startAdd = (parent) => {
    setEditing(null);
    setAdding(parent.id);
    setDraft({ title: '', type: 'Department' });
  };
  const add = () => {
    if (!draft.title.trim()) return;
    run(api.post('company-onboarding/structure', { ...draft, parent: adding }), null).then((ok) => { if (ok) setDraft({ ...draft, title: '' }); });
  };
  const saveEdit = () => run(api.post(`company-onboarding/structure/${editing.id}`, editing), t('Saved')).then((ok) => { if (ok) setEditing(null); });
  const remove = (unit) => run(api.del(`company-onboarding/structure/${unit.id}`), t('Removed'));

  const renderNode = (unit, depth) => {
    const kids = childrenOf(unit.id);
    const isEditing = editing && editing.id === unit.id;
    return (
      <div key={unit.id}>
        <div style={{
          display: 'flex', alignItems: 'center', gap: 8, padding: '6px 8px', marginLeft: depth * 24,
          borderRadius: 8, background: depth === 0 ? token.colorFillQuaternary : 'transparent',
        }}
        >
          <span style={{ color: token.colorTextSecondary }}>{typeIcon(unit.type)}</span>
          {isEditing ? (
            <Space wrap size={6} style={{ flex: 1 }}>
              <Input size="small" value={editing.title} onChange={(e) => setEditing({ ...editing, title: e.target.value })} onPressEnter={saveEdit} style={{ width: 220 }} maxLength={100} />
              {unit.type !== 'Company' ? (
                <Select size="small" value={editing.type} options={typeOptions} onChange={(v) => setEditing({ ...editing, type: v })} style={{ width: 160 }} />
              ) : null}
              {OFFICE_TYPES.includes(editing.type) || unit.type === 'Company' ? (
                <>
                  <Select size="small" showSearch filterOption={filterOption} value={editing.country || undefined} options={lookups.countries} onChange={(v) => setEditing({ ...editing, country: v })} style={{ width: 180 }} placeholder={t('Country')} />
                  <Select size="small" showSearch filterOption={filterOption} value={editing.timezone || undefined} options={(lookups.timezones || []).map((z) => ({ value: z, label: z }))} onChange={(v) => setEditing({ ...editing, timezone: v })} style={{ width: 200 }} placeholder={t('Time zone')} />
                </>
              ) : null}
              <Button size="small" type="primary" loading={working} onClick={saveEdit}>{t('Save')}</Button>
              <Button size="small" onClick={() => setEditing(null)}>{t('Cancel')}</Button>
            </Space>
          ) : (
            <>
              <Text strong={depth === 0} style={{ flex: 1, minWidth: 0 }}>{unit.title}</Text>
              <Tag style={{ margin: 0 }}>{typeLabel(unit.type)}</Tag>
              {OFFICE_TYPES.includes(unit.type) && unit.country ? <Tag style={{ margin: 0 }}>{unit.country}</Tag> : null}
              {unit.employees > 0 ? <Text type="secondary" style={{ fontSize: 12 }}>{tp(Number(unit.employees), '{count} person', '{count} people')}</Text> : null}
              <Tooltip title={t('Add inside')}><Button size="small" type="text" icon={<PlusOutlined />} onClick={() => startAdd(unit)} /></Tooltip>
              <Tooltip title={t('Edit')}><Button size="small" type="text" icon={<EditOutlined />} onClick={() => { setAdding(null); setEditing({ ...unit }); }} /></Tooltip>
              {unit.parent !== null ? (
                <Popconfirm title={t('Remove {name}?', { name: unit.title })} okText={t('Remove')} okButtonProps={{ danger: true }} cancelText={t('Cancel')} onConfirm={() => remove(unit)}>
                  <Tooltip title={t('Remove')}><Button size="small" type="text" danger icon={<DeleteOutlined />} /></Tooltip>
                </Popconfirm>
              ) : null}
            </>
          )}
        </div>
        {adding === unit.id ? (
          <div style={{ marginLeft: (depth + 1) * 24, padding: '6px 8px' }}>
            <Space wrap size={6}>
              <Input
                size="small"
                autoFocus
                value={draft.title}
                onChange={(e) => setDraft({ ...draft, title: e.target.value })}
                onPressEnter={add}
                placeholder={t('Name, e.g. Finance')}
                style={{ width: 220 }}
                maxLength={100}
              />
              <Select size="small" value={draft.type} options={typeOptions} onChange={(v) => setDraft({ ...draft, type: v })} style={{ width: 160 }} />
              <Button size="small" type="primary" loading={working} disabled={!draft.title.trim()} onClick={add}>{t('Add')}</Button>
              <Button size="small" onClick={() => setAdding(null)}>{t('Done')}</Button>
            </Space>
          </div>
        ) : null}
        {kids.map((k) => renderNode(k, depth + 1))}
      </div>
    );
  };

  const onlyRoot = root && childrenOf(root.id).length === 0;

  return (
    <>
      {!root ? (
        <Empty description={t('Save the company details first.')} />
      ) : (
        <>
          {onlyRoot ? (
            <>
              <Text strong>{t('Start from a common layout')}</Text>
              <Row gutter={[12, 12]} style={{ margin: '8px 0 16px' }}>
                {PRESETS.map((p) => (
                  <Col xs={24} md={8} key={p.key}>
                    <Card size="small" hoverable onClick={() => !working && applyPreset(p.key)} style={{ height: '100%' }}>
                      <Text strong>{p.title()}</Text>
                      <div><Text type="secondary" style={{ fontSize: 12 }}>{p.text()}</Text></div>
                    </Card>
                  </Col>
                ))}
              </Row>
              <Text type="secondary">{t('Or add your own with the + button. You can change everything later.')}</Text>
            </>
          ) : (
            <Text type="secondary">{t('Use + to add an office or department inside another, and the pencil to rename it.')}</Text>
          )}
          <div style={{ marginTop: 12 }}>{renderNode(root, 0)}</div>
        </>
      )}
      <StepFooter step={step} busy={busy || working} onSkip={onSkip} onContinue={onContinue} />
    </>
  );
}
