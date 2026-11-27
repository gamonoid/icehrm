import React, { useEffect, useState } from 'react';
import {
  Button, Input, InputNumber, List, Popconfirm, Skeleton, Space, Tag, Tooltip, Typography, message,
} from 'antd';
import {
  CheckOutlined, CloseOutlined, DeleteOutlined, EditOutlined, PlusOutlined,
} from '@ant-design/icons';
import { t, tp } from '../../../../../../web/api/i18n';
import { errorMessage } from '../api';

const { Text } = Typography;

/**
 * Edit one of the wizard's name lists (company-onboarding/lists/<listKey>).
 * - type one name, or paste several (one per line, e.g. a column copied from Excel);
 * - click a suggestion to add it;
 * - rename, or remove an entry nobody uses yet.
 * `withDays` adds a "days per year" number (leave types); suggestions are then
 * {name, days}.
 */
export default function ListEditor({
  api, listKey, suggestions = [], withDays = false, placeholder, onChange,
}) {
  const [items, setItems] = useState(null);
  const [text, setText] = useState('');
  const [days, setDays] = useState(null);
  const [editing, setEditing] = useState(null);
  const [working, setWorking] = useState(false);

  const load = () => api.get(`company-onboarding/lists/${listKey}`).then((list) => {
    setItems(list);
    if (onChange) onChange(list);
  }).catch((e) => message.error(errorMessage(e, t('The list could not be loaded'))));
  useEffect(() => { load(); }, [listKey]);

  if (!items) return <Skeleton active paragraph={{ rows: 3 }} />;

  const have = new Set(items.map((i) => i.name.trim().toLowerCase()));
  const open = suggestions
    .map((s) => (typeof s === 'string' ? { name: s } : s))
    .filter((s) => !have.has(s.name.toLowerCase()));

  const addEntries = (entries) => {
    if (entries.length === 0) return;
    setWorking(true);
    api.post(`company-onboarding/lists/${listKey}`, { entries })
      .then((res) => {
        setItems(res.items);
        if (onChange) onChange(res.items);
        setText('');
        setDays(null);
      })
      .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))))
      .finally(() => setWorking(false));
  };

  // A paste of several lines adds one entry per line.
  const addTyped = () => {
    const names = text.split(/\r?\n/).map((n) => n.split('\t')[0].trim()).filter(Boolean);
    addEntries(names.map((name) => (withDays ? { name, days: days || 0 } : { name })));
  };

  const saveEdit = () => {
    setWorking(true);
    api.post(`company-onboarding/lists/${listKey}/${editing.id}`, editing)
      .then(() => { setEditing(null); return load(); })
      .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))))
      .finally(() => setWorking(false));
  };

  const remove = (item) => {
    setWorking(true);
    api.del(`company-onboarding/lists/${listKey}/${item.id}`)
      .then(load)
      .catch((e) => message.error(errorMessage(e, t('That did not work. Please try again.'))))
      .finally(() => setWorking(false));
  };

  return (
    <div>
      <Space.Compact style={{ width: '100%', maxWidth: 560 }}>
        <Input.TextArea
          value={text}
          onChange={(e) => setText(e.target.value)}
          onPressEnter={(e) => { if (!e.shiftKey && text.indexOf('\n') === -1) { e.preventDefault(); addTyped(); } }}
          autoSize={{ minRows: 1, maxRows: 6 }}
          placeholder={placeholder || t('Type a name, or paste a list (one per line)')}
        />
        {withDays ? (
          <InputNumber min={0} max={366} value={days} onChange={setDays} placeholder={t('Days')} style={{ width: 90 }} />
        ) : null}
        <Button type="primary" icon={<PlusOutlined />} loading={working} disabled={!text.trim()} onClick={addTyped}>{t('Add')}</Button>
      </Space.Compact>

      {open.length > 0 ? (
        <div style={{ marginTop: 10 }}>
          <Text type="secondary" style={{ fontSize: 12, marginRight: 6 }}>{t('Suggestions:')}</Text>
          {open.map((s) => (
            <Tag
              key={s.name}
              icon={<PlusOutlined />}
              style={{ cursor: 'pointer', marginBottom: 6 }}
              onClick={() => !working && addEntries([s])}
            >
              {withDays ? `${s.name} (${s.days})` : s.name}
            </Tag>
          ))}
          {open.length > 1 ? (
            <Button size="small" type="link" onClick={() => addEntries(open)} disabled={working}>{t('Add all')}</Button>
          ) : null}
        </div>
      ) : null}

      <List
        size="small"
        style={{ marginTop: 12 }}
        locale={{ emptyText: t('Nothing added yet') }}
        dataSource={items}
        renderItem={(item) => (
          <List.Item
            actions={editing && editing.id === item.id ? [
              <Button key="save" size="small" type="text" icon={<CheckOutlined />} onClick={saveEdit} loading={working} />,
              <Button key="cancel" size="small" type="text" icon={<CloseOutlined />} onClick={() => setEditing(null)} />,
            ] : [
              <Tooltip key="edit" title={t('Edit')}><Button size="small" type="text" icon={<EditOutlined />} onClick={() => setEditing({ ...item })} /></Tooltip>,
              item.used ? (
                <Tooltip key="used" title={t('In use, so it cannot be removed')}><Button size="small" type="text" icon={<DeleteOutlined />} disabled /></Tooltip>
              ) : (
                <Popconfirm key="del" title={t('Remove {name}?', { name: item.name })} okText={t('Remove')} okButtonProps={{ danger: true }} cancelText={t('Cancel')} onConfirm={() => remove(item)}>
                  <Button size="small" type="text" danger icon={<DeleteOutlined />} />
                </Popconfirm>
              ),
            ]}
          >
            {editing && editing.id === item.id ? (
              <Space>
                <Input size="small" value={editing.name} onChange={(e) => setEditing({ ...editing, name: e.target.value })} onPressEnter={saveEdit} maxLength={100} />
                {withDays ? <InputNumber size="small" min={0} max={366} value={editing.days} onChange={(v) => setEditing({ ...editing, days: v })} /> : null}
              </Space>
            ) : (
              <Space>
                <Text>{item.name}</Text>
                {withDays ? <Text type="secondary">{tp(Number(item.days), '{count} day a year', '{count} days a year')}</Text> : null}
              </Space>
            )}
          </List.Item>
        )}
      />
    </div>
  );
}
