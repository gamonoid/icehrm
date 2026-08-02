import React, { useMemo, useState } from 'react';
import { Select } from 'antd';
import { SearchOutlined } from '@ant-design/icons';

/**
 * GA-style global module search for the top bar. A searchable dropdown of every
 * module the user can reach (from the shell's route map), grouped by area.
 * Selecting one navigates there. Filtering matches the module label and its area
 * name, so "leave" or "time" both surface the right entries.
 */
export default function ModuleSearch({ items, areas, onSelect, dark }) {
  const [value, setValue] = useState(undefined);

  const areaLabel = useMemo(() => {
    const m = {};
    (areas || []).forEach((a) => { m[a.id] = a.label; });
    return m;
  }, [areas]);

  const sectionLabel = (s) => (s === 'mine' ? 'Personal' : 'Manage');

  // Group options by area. Two modules can share a label within an area — the
  // admin (Manage) twin and the self-service (Personal) twin — so tag each option
  // with its section to disambiguate (e.g. "Task Lists" Manage vs Personal). The
  // search string includes label + area + section so any of them matches.
  const options = useMemo(() => {
    const byArea = {};
    (items || []).forEach((it) => {
      const a = it.area || 'more';
      if (!byArea[a]) byArea[a] = [];
      const aLabel = areaLabel[a] || a;
      const sec = sectionLabel(it.section);
      byArea[a].push({
        value: it.key,
        name: it.label,
        section: sec,
        // antd renders this for the option + selected value.
        label: (
          <span style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 12 }}>
            <span style={{ overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{it.label}</span>
            <span style={{
              flex: '0 0 auto', fontSize: 11, fontWeight: 500, padding: '0 7px', lineHeight: '17px',
              borderRadius: 9, color: it.section === 'mine' ? '#0958d9' : '#389e0d',
              background: it.section === 'mine' ? '#e6f0ff' : '#f0fbe6',
            }}>
              {sec}
            </span>
          </span>
        ),
        search: `${it.label} ${aLabel} ${sec}`.toLowerCase(),
      });
    });
    return Object.keys(byArea)
      .sort((x, y) => (areaLabel[x] || x).localeCompare(areaLabel[y] || y))
      .map((a) => ({
        label: areaLabel[a] || a,
        options: byArea[a].sort((p, q) => (p.name).localeCompare(q.name)),
      }));
  }, [items, areaLabel]);

  return (
    <Select
      showSearch
      value={value}
      placeholder="Search for a module…"
      suffixIcon={<SearchOutlined style={{ fontSize: 15 }} />}
      options={options}
      // Match against our combined label+area search string.
      filterOption={(input, option) => {
        if (!option || option.options) return false; // skip group headers
        return (option.search || '').includes((input || '').toLowerCase());
      }}
      onChange={(key) => { if (key && onSelect) onSelect(key); setValue(undefined); }}
      onSelect={() => setValue(undefined)}
      allowClear
      style={{ width: '100%' }}
      popupMatchSelectWidth={false}
      listHeight={420}
      variant="filled"
      aria-label="Search modules"
    />
  );
}
