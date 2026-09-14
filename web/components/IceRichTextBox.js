import React, {
  useEffect, useRef, useState,
} from 'react';
import { Button, Space, Tooltip, Divider, theme } from 'antd';
import {
  BoldOutlined,
  ItalicOutlined,
  UnderlineOutlined,
  StrikethroughOutlined,
  UnorderedListOutlined,
  OrderedListOutlined,
  ClearOutlined,
} from '@ant-design/icons';

// The sanitizer lives in its own module so consumers (e.g. the esign document viewer)
// can import it without pulling in this editor component. Re-exported here so the
// existing `from './IceRichTextBox'` import sites keep working.
import { sanitizeRichText, isRichTextEmpty } from './sanitizeRichText';

export { sanitizeRichText, isRichTextEmpty };

/**
 * IceRichTextBox — a small, dependency-free rich text editor.
 *
 * Props:
 *   value      controlled HTML string
 *   onChange   fn(html) — receives sanitized HTML on every edit
 *   readOnly   render the content without toolbar or editing
 *   placeholder shown while empty (edit mode only)
 *   minHeight  editing area height (default 160)
 *
 * The value/onChange contract matches antd Form.Item children, so it can also
 * be mounted inside a form. Content is sanitized on the way in AND out.
 */
function IceRichTextBox({
  value, onChange, readOnly = false, placeholder = '', minHeight = 160,
}) {
  const { token } = theme.useToken();
  const editorRef = useRef(null);
  // The HTML we last pushed to / received from the editable div. Lets the
  // component stay controlled without resetting the caret on every keystroke:
  // we only write value -> DOM when the change came from outside.
  const lastHtmlRef = useRef(null);
  const [empty, setEmpty] = useState(isRichTextEmpty(value));

  useEffect(() => {
    const el = editorRef.current;
    if (!el) {
      return;
    }
    const incoming = sanitizeRichText(value || '');
    if (incoming !== lastHtmlRef.current) {
      el.innerHTML = incoming;
      lastHtmlRef.current = incoming;
      setEmpty(isRichTextEmpty(incoming));
    }
  }, [value, readOnly]);

  const emitChange = () => {
    const el = editorRef.current;
    if (!el) {
      return;
    }
    const html = sanitizeRichText(el.innerHTML);
    lastHtmlRef.current = html;
    setEmpty(isRichTextEmpty(html));
    if (onChange) {
      onChange(html);
    }
  };

  const exec = (command) => {
    const el = editorRef.current;
    if (!el) {
      return;
    }
    el.focus();
    document.execCommand(command, false, null);
    emitChange();
  };

  if (readOnly) {
    return (
      <div
        className="ice-rich-text-readonly"
        style={{ color: token.colorText, lineHeight: 1.7 }}
        // Stored content is sanitized again on the way in, so markup that
        // predates the sanitizer (or was written by another client) stays safe.
        dangerouslySetInnerHTML={{ __html: sanitizeRichText(value || '') }}
      />
    );
  }

  const tools = [
    { key: 'bold', icon: <BoldOutlined />, tip: 'Bold' },
    { key: 'italic', icon: <ItalicOutlined />, tip: 'Italic' },
    { key: 'underline', icon: <UnderlineOutlined />, tip: 'Underline' },
    { key: 'strikeThrough', icon: <StrikethroughOutlined />, tip: 'Strikethrough' },
    { key: 'insertUnorderedList', icon: <UnorderedListOutlined />, tip: 'Bullet list' },
    { key: 'insertOrderedList', icon: <OrderedListOutlined />, tip: 'Numbered list' },
    { key: 'removeFormat', icon: <ClearOutlined />, tip: 'Clear formatting' },
  ];

  return (
    <div
      style={{
        border: `1px solid ${token.colorBorder}`,
        borderRadius: token.borderRadius,
        background: token.colorBgContainer,
        overflow: 'hidden',
      }}
    >
      <div
        style={{
          padding: '4px 8px',
          borderBottom: `1px solid ${token.colorBorderSecondary}`,
          background: token.colorFillQuaternary,
        }}
      >
        <Space size={0} split={null}>
          {tools.map((t, i) => (
            <React.Fragment key={t.key}>
              {(t.key === 'insertUnorderedList' || t.key === 'removeFormat') && (
                <Divider type="vertical" style={{ margin: '0 6px' }} />
              )}
              <Tooltip title={t.tip}>
                <Button
                  type="text"
                  size="small"
                  icon={t.icon}
                  // preventDefault keeps the editor selection alive so the
                  // command applies to it instead of a collapsed caret.
                  onMouseDown={(e) => e.preventDefault()}
                  onClick={() => exec(t.key)}
                />
              </Tooltip>
            </React.Fragment>
          ))}
        </Space>
      </div>
      <div style={{ position: 'relative' }}>
        {empty && placeholder && (
          <div
            style={{
              position: 'absolute',
              top: 12,
              left: 12,
              color: token.colorTextPlaceholder,
              pointerEvents: 'none',
            }}
          >
            {placeholder}
          </div>
        )}
        <div
          ref={editorRef}
          contentEditable
          suppressContentEditableWarning
          role="textbox"
          aria-multiline="true"
          style={{
            minHeight,
            padding: 12,
            outline: 'none',
            color: token.colorText,
            lineHeight: 1.7,
            overflowY: 'auto',
          }}
          onInput={emitChange}
          onBlur={emitChange}
          // Normalize pasted content to plain text; the toolbar re-applies any
          // formatting the author wants. This keeps word/web junk markup out.
          onPaste={(e) => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
          }}
        />
      </div>
    </div>
  );
}

export default IceRichTextBox;
