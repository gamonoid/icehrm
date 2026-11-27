import React, { useEffect, useRef, useState } from 'react';
import { Input, Spin, Typography, theme } from 'antd';
import { SearchOutlined } from '@ant-design/icons';
import { api } from '../api';
import Person from './Person';

import { t } from '../../../../../../web/api/i18n';
const { Text } = Typography;

/**
 * Find a colleague to message. Results drop down under the box; picking one hands
 * the employee to the parent, which opens (or creates) the conversation.
 */
export default function EmployeeSearch({ onPick }) {
  const { token } = theme.useToken();
  const [term, setTerm] = useState('');
  const [results, setResults] = useState(null); // null = closed
  const [loading, setLoading] = useState(false);
  const timer = useRef(null);
  const seq = useRef(0);

  useEffect(() => {
    const q = term.trim();
    if (timer.current) clearTimeout(timer.current);
    if (q.length < 2) { setResults(null); setLoading(false); return undefined; }
    setLoading(true);
    timer.current = setTimeout(async () => {
      const mine = ++seq.current;
      try {
        const res = await api().post('inbox/employees/search', { q });
        if (mine === seq.current) setResults(Array.isArray(res.data) ? res.data : []);
      } catch (e) {
        if (mine === seq.current) setResults([]);
      } finally {
        if (mine === seq.current) setLoading(false);
      }
    }, 250);
    return () => { if (timer.current) clearTimeout(timer.current); };
  }, [term]);

  const pick = (emp) => { setTerm(''); setResults(null); onPick(emp); };

  return (
    <div style={{ position: 'relative' }}>
      <Input
        allowClear
        size="small"
        placeholder={t('Find a colleague…')}
        prefix={<SearchOutlined style={{ color: token.colorTextTertiary }} />}
        suffix={loading ? <Spin size="small" /> : null}
        value={term}
        onChange={(e) => setTerm(e.target.value)}
        onKeyDown={(e) => { if (e.key === 'Escape') { setTerm(''); setResults(null); } }}
      />
      {results !== null && (
        <div style={{
          position: 'absolute', top: '100%', left: 0, right: 0, zIndex: 20, marginTop: 4,
          background: token.colorBgElevated, border: `1px solid ${token.colorBorderSecondary}`,
          borderRadius: 8, boxShadow: token.boxShadowSecondary, maxHeight: 320, overflowY: 'auto',
        }}>
          {results.length === 0 && (
            <Text type="secondary" style={{ display: 'block', padding: '10px 12px', fontSize: 13 }}>{t('Nobody matches that name.')}</Text>
          )}
          {results.map((emp) => (
            <div
              key={emp.id}
              role="button"
              tabIndex={0}
              onClick={() => pick(emp)}
              onKeyDown={(e) => { if (e.key === 'Enter') pick(emp); }}
              style={{ padding: '8px 12px', cursor: 'pointer' }}
              onMouseEnter={(e) => { e.currentTarget.style.background = token.colorFillTertiary; }}
              onMouseLeave={(e) => { e.currentTarget.style.background = 'transparent'; }}
            >
              <Person person={emp} size={30} />
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
