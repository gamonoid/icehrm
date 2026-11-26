import React from 'react';
import { Typography, theme } from 'antd';
import MentionChip from '../../../../../../web/components/MentionChip';

const { Text } = Typography;

/**
 * Renders a stored EditorJS document as React — without ever handing the browser
 * raw HTML. EditorJS keeps inline formatting as HTML strings (<b>, <a>, <mark>…),
 * so those are parsed and rebuilt from a short whitelist; every other tag becomes
 * plain text. Block types the server allows are exactly the ones drawn here.
 */

const INLINE_TAGS = { b: 'b', strong: 'strong', i: 'i', em: 'em', u: 'u', mark: 'mark', code: 'code', br: 'br', a: 'a' };

function safeHref(href) {
  const h = String(href || '').trim();
  return /^(https?:\/\/|mailto:)/i.test(h) ? h : null;
}

let keySeq = 0;
function nodeToReact(node, tokens) {
  if (node.nodeType === 3) return node.nodeValue;
  if (node.nodeType !== 1) return null;
  const tag = node.tagName.toLowerCase();
  const children = Array.from(node.childNodes).map((c) => nodeToReact(c, tokens)).filter((c) => c !== null);
  keySeq += 1;
  const key = `i${keySeq}`;
  // An @mention — the one span the whitelist admits. The server rebuilds these
  // from the database on every save, so the id and name here are trustworthy.
  if (tag === 'span' && node.getAttribute('class') === 'ice-mention') {
    return (
      <MentionChip key={key} employeeId={node.getAttribute('data-employee')}>
        {children}
      </MentionChip>
    );
  }
  if (!INLINE_TAGS[tag]) return children.length ? <React.Fragment key={key}>{children}</React.Fragment> : null;
  if (tag === 'br') return <br key={key} />;
  if (tag === 'a') {
    const href = safeHref(node.getAttribute('href'));
    if (!href) return <React.Fragment key={key}>{children}</React.Fragment>;
    return <a key={key} href={href} target="_blank" rel="noopener noreferrer">{children}</a>;
  }
  if (tag === 'mark') {
    return <mark key={key} style={{ background: tokens.colorWarningBg, color: 'inherit', padding: '0 2px', borderRadius: 2 }}>{children}</mark>;
  }
  if (tag === 'code') {
    return <code key={key} style={{ background: tokens.colorFillSecondary, padding: '1px 5px', borderRadius: 4, fontSize: '0.92em' }}>{children}</code>;
  }
  return React.createElement(tag, { key }, children);
}

export function inline(html, tokens) {
  if (html === null || html === undefined) return null;
  const text = String(html);
  if (text.indexOf('<') < 0 && text.indexOf('&') < 0) return text;
  try {
    const doc = new DOMParser().parseFromString(`<div>${text}</div>`, 'text/html');
    const root = doc.body.firstChild;
    return Array.from(root.childNodes).map((c) => nodeToReact(c, tokens));
  } catch (e) {
    return text;
  }
}

function ListBlock({ data, tokens }) {
  const ordered = data.style === 'ordered';
  const Tag = ordered ? 'ol' : 'ul';
  const items = Array.isArray(data.items) ? data.items : [];
  const renderItems = (list, depth) => (
    <Tag style={{ margin: depth ? '2px 0' : '4px 0', paddingLeft: 22 }}>
      {list.map((it, i) => {
        const content = (it && typeof it === 'object') ? it.content : it;
        const nested = (it && typeof it === 'object' && Array.isArray(it.items) && it.items.length) ? it.items : null;
        return (
          <li key={i} style={{ marginBottom: 2 }}>
            {inline(content, tokens)}
            {nested ? renderItems(nested, depth + 1) : null}
          </li>
        );
      })}
    </Tag>
  );
  return renderItems(items, 0);
}

function Block({ block, tokens }) {
  const data = block.data || {};
  switch (block.type) {
    case 'paragraph':
      return <p style={{ margin: '0 0 8px', lineHeight: 1.6 }}>{inline(data.text, tokens)}</p>;
    case 'header': {
      const level = Math.min(6, Math.max(1, parseInt(data.level, 10) || 3));
      const size = { 1: 24, 2: 20, 3: 17, 4: 15, 5: 14, 6: 13 }[level];
      return React.createElement(`h${level}`, { style: { fontSize: size, fontWeight: 600, margin: '10px 0 6px', lineHeight: 1.35 } }, inline(data.text, tokens));
    }
    case 'list':
      return <ListBlock data={data} tokens={tokens} />;
    case 'checklist':
      return (
        <div style={{ margin: '4px 0 8px' }}>
          {(Array.isArray(data.items) ? data.items : []).map((it, i) => (
            <div key={i} style={{ display: 'flex', gap: 8, alignItems: 'flex-start', marginBottom: 3 }}>
              <span style={{ color: it.checked ? tokens.colorSuccess : tokens.colorTextTertiary, lineHeight: 1.6 }}>{it.checked ? '☑' : '☐'}</span>
              <span style={{ lineHeight: 1.6, textDecoration: it.checked ? 'line-through' : 'none', opacity: it.checked ? 0.7 : 1 }}>{inline(it.text, tokens)}</span>
            </div>
          ))}
        </div>
      );
    case 'quote':
      return (
        <blockquote style={{ margin: '8px 0', padding: '6px 14px', borderLeft: `3px solid ${tokens.colorPrimary}`, background: tokens.colorFillQuaternary, borderRadius: 4 }}>
          <div style={{ lineHeight: 1.6 }}>{inline(data.text, tokens)}</div>
          {data.caption ? <Text type="secondary" style={{ fontSize: 12 }}>— {inline(data.caption, tokens)}</Text> : null}
        </blockquote>
      );
    case 'code':
      return (
        <pre style={{ margin: '8px 0', padding: 12, background: tokens.colorFillSecondary, borderRadius: 6, overflowX: 'auto', fontSize: 13, lineHeight: 1.5 }}>
          <code>{String(data.code || '')}</code>
        </pre>
      );
    case 'delimiter':
      return <div style={{ textAlign: 'center', color: tokens.colorTextTertiary, letterSpacing: 8, margin: '10px 0' }}>***</div>;
    case 'table': {
      const rows = Array.isArray(data.content) ? data.content : [];
      const withHeadings = !!data.withHeadings;
      return (
        <div style={{ overflowX: 'auto', margin: '8px 0' }}>
          <table style={{ borderCollapse: 'collapse', width: '100%', fontSize: 13 }}>
            <tbody>
              {rows.map((row, r) => (
                <tr key={r}>
                  {(Array.isArray(row) ? row : []).map((cell, c) => {
                    const Cell = withHeadings && r === 0 ? 'th' : 'td';
                    return (
                      <Cell key={c} style={{ border: `1px solid ${tokens.colorBorderSecondary}`, padding: '6px 10px', textAlign: 'left', background: Cell === 'th' ? tokens.colorFillQuaternary : 'transparent' }}>
                        {inline(cell, tokens)}
                      </Cell>
                    );
                  })}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      );
    }
    default:
      return null;
  }
}

export default function BlockRenderer({ content }) {
  const { token } = theme.useToken();
  const blocks = content && Array.isArray(content.blocks) ? content.blocks : [];
  return (
    <div style={{ color: token.colorText, fontSize: 14, wordBreak: 'break-word' }}>
      {blocks.map((b, i) => <Block key={b.id || i} block={b} tokens={token} />)}
    </div>
  );
}
