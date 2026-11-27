import React from 'react';
import { Typography, Checkbox, theme } from 'antd';
import MentionChip from '../../../../components/MentionChip';

const { Paragraph, Title, Text } = Typography;

/**
 * Renders a post's EditorJS block content in the feed.
 *
 * Inline formatting is stored as HTML strings (<b>, <a>, <mark>, mention spans…).
 * That HTML is NOT sanitised server-side — the discussions endpoints validate the
 * block types and size, nothing more — so it is never handed to the browser raw.
 * It is parsed and rebuilt from a short whitelist instead, exactly as the
 * Discussions module's BlockRenderer does; anything not on the list degrades to
 * plain text. (An earlier version of this file used dangerouslySetInnerHTML on the
 * strength of a sanitiser that does not exist — hence this note.)
 *
 * The one extra tag over plain formatting is the mention:
 *   <span class="ice-mention" data-employee="42">@Jane Doe</span>
 * which the server rebuilds from the database on every save, so the id and name
 * here are trustworthy.
 */

const INLINE_TAGS = {
  b: 'b', strong: 'strong', i: 'i', em: 'em', u: 'u', mark: 'mark', code: 'code', br: 'br', a: 'a',
};

function safeHref(href) {
  const h = String(href || '').trim();
  return /^(https?:\/\/|mailto:)/i.test(h) ? h : null;
}

let keySeq = 0;

function nodeToReact(node, token) {
  if (node.nodeType === 3) return node.nodeValue;      // text
  if (node.nodeType !== 1) return null;                // comments etc.
  const tag = node.tagName.toLowerCase();
  const children = Array.from(node.childNodes)
    .map((c) => nodeToReact(c, token))
    .filter((c) => c !== null);
  keySeq += 1;
  const key = `i${keySeq}`;

  // A mention renders as a highlighted chip with a hover card for the person.
  if (tag === 'span' && node.getAttribute('class') === 'ice-mention') {
    return (
      <MentionChip key={key} employeeId={node.getAttribute('data-employee')}>
        {children}
      </MentionChip>
    );
  }

  if (!INLINE_TAGS[tag]) {
    return children.length ? <React.Fragment key={key}>{children}</React.Fragment> : null;
  }
  if (tag === 'br') return <br key={key} />;
  if (tag === 'a') {
    const href = safeHref(node.getAttribute('href'));
    if (!href) return <React.Fragment key={key}>{children}</React.Fragment>;
    return <a key={key} href={href} target="_blank" rel="noopener noreferrer">{children}</a>;
  }
  if (tag === 'mark') {
    return (
      <mark key={key} style={{ background: token.colorWarningBg, color: 'inherit', padding: '0 2px', borderRadius: 2 }}>
        {children}
      </mark>
    );
  }
  if (tag === 'code') {
    return (
      <code key={key} style={{ background: token.colorFillSecondary, padding: '1px 5px', borderRadius: 4, fontSize: '0.92em' }}>
        {children}
      </code>
    );
  }
  return React.createElement(tag, { key }, children);
}

/** Parse an inline HTML string into React nodes, dropping anything unrecognised. */
function Inline({ html, token }) {
  if (html === null || html === undefined) return null;
  const text = String(html);
  if (text.indexOf('<') < 0 && text.indexOf('&') < 0) return text;
  try {
    const doc = new DOMParser().parseFromString(`<div>${text}</div>`, 'text/html');
    const root = doc.body.firstChild;
    return <>{Array.from(root.childNodes).map((c) => nodeToReact(c, token))}</>;
  } catch (e) {
    return text;
  }
}

function listItemText(item) {
  if (typeof item === 'string') return item;
  if (item && typeof item.text === 'string') return item.text;
  if (item && typeof item.content === 'string') return item.content;
  return '';
}

function Block({ block, token }) {
  const type = block && block.type;
  const d = (block && block.data) || {};

  switch (type) {
    case 'header': {
      const level = Math.min(Math.max(Number(d.level) || 3, 2), 5);
      return (
        <Title level={level} style={{ marginTop: 6, marginBottom: 6 }}>
          <Inline html={d.text} token={token} />
        </Title>
      );
    }
    case 'list': {
      const items = Array.isArray(d.items) ? d.items : [];
      const ordered = d.style === 'ordered';
      const Tag = ordered ? 'ol' : 'ul';
      return (
        <Tag style={{ margin: '6px 0', paddingLeft: 22 }}>
          {items.map((it, i) => (
            // eslint-disable-next-line react/no-array-index-key
            <li key={i}><Inline html={listItemText(it)} token={token} /></li>
          ))}
        </Tag>
      );
    }
    case 'checklist': {
      const items = Array.isArray(d.items) ? d.items : [];
      return (
        <div style={{ margin: '6px 0' }}>
          {items.map((it, i) => (
            // eslint-disable-next-line react/no-array-index-key
            <div key={i} style={{ display: 'flex', gap: 8, alignItems: 'flex-start' }}>
              <Checkbox checked={!!it.checked} disabled />
              <span style={{
                textDecoration: it.checked ? 'line-through' : 'none',
                opacity: it.checked ? 0.65 : 1,
              }}
              >
                <Inline html={listItemText(it)} token={token} />
              </span>
            </div>
          ))}
        </div>
      );
    }
    case 'quote':
      return (
        <blockquote style={{
          margin: '8px 0',
          padding: '6px 14px',
          borderLeft: `3px solid ${token.colorPrimary}`,
          background: token.colorFillQuaternary,
          borderRadius: 4,
        }}
        >
          <Inline html={d.text} token={token} />
          {d.caption ? (
            <div>
              <Text type="secondary" style={{ fontSize: 12 }}>
                <Inline html={d.caption} token={token} />
              </Text>
            </div>
          ) : null}
        </blockquote>
      );
    case 'code':
      return (
        <pre style={{
          margin: '8px 0',
          padding: 12,
          borderRadius: 8,
          overflowX: 'auto',
          background: token.colorFillQuaternary,
          fontSize: 12.5,
        }}
        >
          <code>{d.code || ''}</code>
        </pre>
      );
    case 'delimiter':
      return <div style={{ textAlign: 'center', margin: '10px 0', letterSpacing: 6, opacity: 0.5 }}>***</div>;
    case 'table': {
      const rows = Array.isArray(d.content) ? d.content : [];
      if (rows.length === 0) return null;
      const hasHeadings = !!d.withHeadings;
      return (
        <div style={{ overflowX: 'auto', margin: '8px 0' }}>
          <table style={{ borderCollapse: 'collapse', width: '100%', fontSize: 13 }}>
            <tbody>
              {rows.map((row, ri) => (
                // eslint-disable-next-line react/no-array-index-key
                <tr key={ri}>
                  {(Array.isArray(row) ? row : []).map((cell, ci) => {
                    const CellTag = (hasHeadings && ri === 0) ? 'th' : 'td';
                    return (
                      <CellTag
                        // eslint-disable-next-line react/no-array-index-key
                        key={ci}
                        style={{
                          border: `1px solid ${token.colorBorderSecondary}`,
                          padding: '6px 8px',
                          textAlign: 'left',
                        }}
                      >
                        <Inline html={cell} token={token} />
                      </CellTag>
                    );
                  })}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      );
    }
    case 'paragraph':
    default:
      if (!d.text) return null;
      return (
        <Paragraph style={{ marginBottom: 6 }}>
          <Inline html={d.text} token={token} />
        </Paragraph>
      );
  }
}

export default function PostBlocks({ content, limit }) {
  const { token } = theme.useToken();
  const all = (content && content.blocks) || [];
  const blocks = limit ? all.slice(0, limit) : all;
  if (blocks.length === 0) return null;
  return (
    <div>
      {blocks.map((b, i) => (
        // eslint-disable-next-line react/no-array-index-key
        <Block key={(b && b.id) || i} block={b} token={token} />
      ))}
    </div>
  );
}
