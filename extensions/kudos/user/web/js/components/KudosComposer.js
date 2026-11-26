import React, { useEffect, useMemo, useRef, useState } from 'react';
import {
  Avatar, Button, Input, Modal, Select, Space, Spin, Tag, Typography, message, theme,
} from 'antd';
import { SendOutlined, TrophyOutlined, UserOutlined } from '@ant-design/icons';
import { api, errorMessage } from '../api';
import { t } from '../../../../../../web/api/i18n';

const { Text } = Typography;
const { TextArea } = Input;

const AUDIENCES = [
  { value: 'all', label: 'Everyone' },
  { value: 'department', label: 'My department' },
  { value: 'team', label: 'My team' },
];

/**
 * The "give kudos" form: a single-recipient picker (searched server-side, so the
 * full directory never ships to the client), the message, a company value, the
 * audience, an optional image link, and a live preview. `initialRecipient` may be
 * an employee id — e.g. from a "Send wishes" deep link — and is looked up on open.
 *
 * Props: open, onClose, onPosted(kudos), values ([{name, description}]),
 *        initialRecipient (id|null)
 */
export default function KudosComposer({
  open, onClose, onPosted, values, initialRecipient,
}) {
  const { token } = theme.useToken();
  const [recipient, setRecipient] = useState(null); // {employee_id, name, image}
  const [options, setOptions] = useState([]);
  const [searching, setSearching] = useState(false);
  const [messageText, setMessageText] = useState('');
  const [valueTag, setValueTag] = useState(undefined);
  const [audience, setAudience] = useState('all');
  const [image, setImage] = useState('');
  const [posting, setPosting] = useState(false);
  const searchTimer = useRef(null);

  const search = (term) => {
    if (searchTimer.current) clearTimeout(searchTimer.current);
    searchTimer.current = setTimeout(async () => {
      setSearching(true);
      try {
        const res = await api().get('kudos/people', { params: { search: term || '' } });
        setOptions(Array.isArray(res.data) ? res.data : []);
      } catch (e) {
        setOptions([]);
      } finally {
        setSearching(false);
      }
    }, 250);
  };

  // Reset on open; pre-load a suggested list and resolve an initial recipient.
  useEffect(() => {
    if (!open) return;
    setMessageText('');
    setValueTag(undefined);
    setAudience('all');
    setImage('');
    setRecipient(null);
    search('');
    if (initialRecipient) {
      (async () => {
        try {
          const res = await api().get('kudos/people', { params: { search: '' } });
          const list = Array.isArray(res.data) ? res.data : [];
          const hit = list.find((p) => Number(p.employee_id) === Number(initialRecipient));
          if (hit) setRecipient(hit);
        } catch (e) { /* ignore */ }
      })();
    }
  }, [open, initialRecipient]); // eslint-disable-line react-hooks/exhaustive-deps

  const selectOptions = useMemo(() => options.map((p) => ({
    value: p.employee_id,
    label: (
      <Space size={8}>
        <Avatar size={22} src={p.image || undefined} icon={<UserOutlined />}>{p.name ? p.name.charAt(0) : null}</Avatar>
        <span>{p.name}</span>
      </Space>
    ),
    person: p,
  })), [options]);

  const canPost = !!recipient && messageText.trim().length > 0 && !posting;

  const post = async () => {
    if (!canPost) return;
    setPosting(true);
    try {
      const res = await api().post('kudos/post', {
        recipient: recipient.employee_id,
        message: messageText.trim(),
        value_tag: valueTag || '',
        audience,
        image: image.trim(),
      });
      message.success(t('Kudos sent to {name}', { name: recipient.name }));
      if (onPosted) onPosted(res.data);
      if (onClose) onClose();
    } catch (e) {
      message.error(errorMessage(e, t('Could not send the kudos')));
    } finally {
      setPosting(false);
    }
  };

  return (
    <Modal
      title={<Space><TrophyOutlined />{t('Give kudos')}</Space>}
      open={open}
      onCancel={onClose}
      width={720}
      destroyOnClose
      footer={(
        <Space>
          <Button onClick={onClose}>{t('Cancel')}</Button>
          <Button type="primary" icon={<SendOutlined />} loading={posting} disabled={!canPost} onClick={post}>
            {t('Send kudos')}
          </Button>
        </Space>
      )}
    >
      <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 20, marginTop: 8 }}>
        {/* form */}
        <div>
          <Text type="secondary">{t('Who')}</Text>
          <Select
            showSearch
            filterOption={false}
            placeholder={t('Search a colleague')}
            notFoundContent={searching ? <Spin size="small" /> : t('No one found')}
            onSearch={search}
            value={recipient ? recipient.employee_id : undefined}
            options={selectOptions}
            onChange={(val, opt) => setRecipient(opt && opt.person ? opt.person : null)}
            style={{ width: '100%', marginTop: 4, marginBottom: 12 }}
          />

          <Text type="secondary">{t('For')}</Text>
          <TextArea
            value={messageText}
            onChange={(e) => setMessageText(e.target.value)}
            placeholder={t('What did they do? Be specific — it means more.')}
            autoSize={{ minRows: 4, maxRows: 8 }}
            maxLength={2000}
            showCount
            style={{ marginTop: 4, marginBottom: 12 }}
          />

          <Text type="secondary">{t('Company value')}</Text>
          <Select
            allowClear
            placeholder={t('Optional')}
            value={valueTag}
            onChange={setValueTag}
            options={(values || []).map((v) => ({ value: v.name, label: v.name }))}
            style={{ width: '100%', marginTop: 4, marginBottom: 12 }}
          />

          <Text type="secondary">{t('Who can see it')}</Text>
          <Select
            value={audience}
            onChange={setAudience}
            options={AUDIENCES.map((o) => ({ ...o, label: t(o.label) }))}
            style={{ width: '100%', marginTop: 4, marginBottom: 12 }}
          />

          <Text type="secondary">{t('Image link (optional)')}</Text>
          <Input
            value={image}
            onChange={(e) => setImage(e.target.value)}
            placeholder="https://…"
            maxLength={500}
            style={{ marginTop: 4 }}
          />
        </div>

        {/* preview */}
        <div>
          <Text type="secondary">{t('Preview')}</Text>
          <div
            style={{
              marginTop: 4,
              border: `1px solid ${token.colorBorderSecondary}`,
              borderRadius: 12,
              overflow: 'hidden',
              background: token.colorBgContainer,
            }}
          >
            <div
              style={{
                position: 'relative',
                margin: 12,
                borderRadius: 10,
                padding: '18px 16px',
                background: 'linear-gradient(135deg, #fde68a 0%, #fbcfe8 45%, #c7d2fe 100%)',
                color: '#1f2937',
              }}
            >
              <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <Avatar size={48} src={recipient && recipient.image ? recipient.image : undefined} icon={<UserOutlined />} style={{ border: '3px solid #fff' }}>
                  {recipient && recipient.name ? recipient.name.charAt(0).toUpperCase() : null}
                </Avatar>
                <div>
                  <div style={{ fontSize: 22, fontWeight: 800, letterSpacing: 1 }}>{t('KUDOS!')}</div>
                  <div style={{ fontSize: 12, opacity: 0.85 }}>{t('to {name}', { name: recipient ? recipient.name : '…' })}</div>
                </div>
              </div>
              {valueTag && <Tag color="magenta" style={{ position: 'absolute', right: 12, top: 12, margin: 0, fontWeight: 600 }}>#{valueTag}</Tag>}
            </div>
            <div style={{ padding: '0 14px 14px', whiteSpace: 'pre-wrap', minHeight: 40 }}>
              {messageText.trim() ? messageText : <Text type="secondary">{t('Your message will appear here.')}</Text>}
            </div>
          </div>
        </div>
      </div>
    </Modal>
  );
}
