/**
 * The single client-side HTML sanitizer.
 *
 * Extracted from IceRichTextBox so any consumer can import it without pulling in the
 * editor component (and therefore antd) — there must be exactly one allow-list in the
 * codebase, and this is it.
 *
 * Use this for genuinely rich content: editor output, and stored document bodies
 * rendered through dangerouslySetInnerHTML. For plain-text values that merely need to
 * be displayed, use escapeHtml from api-common/htmlEscape instead — escaping is
 * stricter and cheaper than sanitizing.
 */

// Tags the sanitizer lets through. Everything else is unwrapped (its children survive)
// and every attribute except a[href] is dropped, so pasted or stored markup can never
// carry scripts, event handlers or styles into the page.
//
// The block/table/code tags are needed by the esign document viewer, whose documents
// are rendered from Editor.js blocks (headings, tables, code, checklists). They are
// inert elements — with all attributes stripped they carry no execution surface.
const ALLOWED_TAGS = [
  // inline
  'BR', 'B', 'STRONG', 'I', 'EM', 'U', 'S', 'STRIKE', 'A', 'SPAN', 'MARK', 'SUB', 'SUP',
  // block
  'P', 'DIV', 'BLOCKQUOTE', 'CITE', 'HR', 'PRE', 'CODE',
  'H1', 'H2', 'H3', 'H4', 'H5', 'H6',
  // lists
  'UL', 'OL', 'LI',
  // tables
  'TABLE', 'THEAD', 'TBODY', 'TFOOT', 'TR', 'TD', 'TH', 'CAPTION', 'COLGROUP', 'COL',
];

// Dropped entirely, children included — unwrapping these would surface their text
// content (e.g. the body of a <script>) as visible page text.
const DROPPED_TAGS = ['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'EMBED', 'NOSCRIPT', 'TEMPLATE'];

function sanitizeNode(node, doc) {
  const out = doc.createDocumentFragment();
  Array.from(node.childNodes).forEach((child) => {
    if (child.nodeType === Node.TEXT_NODE) {
      out.appendChild(doc.createTextNode(child.textContent));
      return;
    }
    if (child.nodeType !== Node.ELEMENT_NODE) {
      return; // comments, CDATA, etc.
    }
    const tag = child.tagName;
    if (DROPPED_TAGS.indexOf(tag) !== -1) {
      return; // drop entirely, children included
    }
    const cleanChildren = sanitizeNode(child, doc);
    if (ALLOWED_TAGS.indexOf(tag) === -1) {
      out.appendChild(cleanChildren); // unwrap unknown tags, keep their content
      return;
    }
    const el = doc.createElement(tag.toLowerCase());
    if (tag === 'A') {
      const href = child.getAttribute('href') || '';
      // Block javascript: and other active schemes.
      if (/^(https?:|mailto:|\/|#)/i.test(href.trim())) {
        el.setAttribute('href', href);
        el.setAttribute('rel', 'noopener noreferrer');
        el.setAttribute('target', '_blank');
      }
    }
    el.appendChild(cleanChildren);
    out.appendChild(el);
  });
  return out;
}

/**
 * Reduce an HTML string to a safe allow-listed subset. Use this both before
 * persisting editor output and before rendering stored rich text with
 * dangerouslySetInnerHTML.
 */
export function sanitizeRichText(html) {
  if (!html || typeof html !== 'string') {
    return '';
  }
  // A detached document: parsing here does not run scripts or fetch subresources,
  // so an <img src=x onerror> in the input never fires while being cleaned.
  const doc = document.implementation.createHTMLDocument('');
  const container = doc.createElement('div');
  container.innerHTML = html;
  const clean = doc.createElement('div');
  clean.appendChild(sanitizeNode(container, doc));
  return clean.innerHTML;
}

/** True when the (sanitized) content has no visible text. */
export function isRichTextEmpty(html) {
  if (!html) {
    return true;
  }
  const probe = document.createElement('div');
  probe.innerHTML = html;
  return probe.textContent.trim() === '';
}

export default sanitizeRichText;
