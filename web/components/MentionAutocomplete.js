/**
 * "@" mention autocomplete for a contenteditable composer (EditorJS).
 *
 * Framework-free on purpose: EditorJS owns the contenteditable, so this attaches
 * to the DOM rather than trying to control it through React. Shared by the Home
 * composer and the Discussions composer, so tagging behaves the same in every
 * channel.
 *
 * How it works: after each keystroke, look backwards from the caret for an "@"
 * that starts a word and take what follows as the query. Matching people are shown
 * in a floating list; picking one replaces the "@query" text with
 *
 *     <span class="ice-mention" data-employee="42">@Jane Doe</span>
 *
 * The server re-derives that markup from the employee id on save, so this is only
 * ever a convenience — what the composer emits is never trusted.
 *
 * Usage:
 *   const mentions = attachMentions({ root, search, getTheme });
 *   …
 *   mentions.destroy();
 */

const MAX_QUERY = 32;

/** The "@query" immediately before the caret, or null. */
function queryAtCaret() {
  const sel = window.getSelection();
  if (!sel || !sel.rangeCount || !sel.isCollapsed) return null;
  const node = sel.anchorNode;
  if (!node || node.nodeType !== 3) return null;        // text nodes only
  const upto = node.nodeValue.slice(0, sel.anchorOffset);
  // "@" must start a word (line start or after whitespace), and the query itself
  // may not contain whitespace — so "a@b" and "@ x" never trigger.
  const m = upto.match(/(^|\s)@([^\s@]{0,32})$/);
  if (!m) return null;
  return {
    query: m[2],
    node,
    // where the "@" sits inside the text node
    start: sel.anchorOffset - m[2].length - 1,
    end: sel.anchorOffset,
  };
}

function el(tag, style, text) {
  const n = document.createElement(tag);
  Object.assign(n.style, style);
  if (text !== undefined) n.textContent = text;
  return n;
}

export function attachMentions(opts) {
  const { root, search } = opts;
  const getTheme = opts.getTheme || (() => ({}));
  if (!root || typeof search !== 'function') return { destroy() {} };

  let people = [];
  let active = 0;
  let ctx = null;          // the current {query, node, start, end}
  let seq = 0;             // guards against out-of-order search responses
  let open = false;

  const list = el('div', {
    position: 'absolute',
    zIndex: 2000,
    minWidth: '240px',
    maxHeight: '260px',
    overflowY: 'auto',
    borderRadius: '8px',
    padding: '4px',
    display: 'none',
    boxShadow: '0 6px 24px rgba(0,0,0,0.25)',
  });
  document.body.appendChild(list);

  // Row elements are kept so the highlight can move without rebuilding them.
  // Rebuilding on hover would destroy the element the user is about to click,
  // and the mousedown would never land — keyboard selection worked, the mouse
  // did not. Only `render` touches the DOM structure; `highlight` restyles.
  let rows = [];

  const highlight = () => {
    const t = getTheme();
    rows.forEach((row, i) => {
      row.style.background = i === active ? (t.colorFillTertiary || '#f5f5f5') : 'transparent';
    });
  };

  const render = () => {
    const t = getTheme();
    list.style.background = t.colorBgElevated || '#fff';
    list.style.border = `1px solid ${t.colorBorder || '#d9d9d9'}`;
    list.innerHTML = '';
    rows = people.map((p, i) => {
      const row = el('div', {
        display: 'flex',
        alignItems: 'center',
        gap: '8px',
        padding: '6px 8px',
        borderRadius: '6px',
        cursor: 'pointer',
        background: 'transparent',
        color: t.colorText || '#000',
        fontSize: '13px',
      });
      if (p.image) {
        const img = el('img', {
          width: '22px', height: '22px', borderRadius: '50%', objectFit: 'cover', flex: '0 0 auto',
        });
        img.src = p.image;
        row.appendChild(img);
      }
      row.appendChild(el('span', { whiteSpace: 'nowrap' }, p.name));
      // mousedown (not click): it fires before the editor loses focus, and
      // preventDefault keeps the caret where it is so the mention can be placed.
      row.addEventListener('mousedown', (e) => { e.preventDefault(); e.stopPropagation(); choose(i); });
      row.addEventListener('mouseenter', () => {
        if (active === i) return;
        active = i;
        highlight();
      });
      list.appendChild(row);
      return row;
    });
    highlight();
    list.style.display = people.length ? 'block' : 'none';
  };

  const position = () => {
    const sel = window.getSelection();
    if (!sel || !sel.rangeCount) return;
    const rect = sel.getRangeAt(0).getBoundingClientRect();
    const top = (rect.bottom || 0) + window.scrollY + 6;
    const left = (rect.left || 0) + window.scrollX;
    list.style.top = `${top}px`;
    list.style.left = `${left}px`;
  };

  const close = () => {
    open = false;
    people = [];
    rows = [];
    ctx = null;
    list.style.display = 'none';
  };

  /** Replace the "@query" text with a mention element. */
  function choose(index) {
    const person = people[index];
    if (!person || !ctx) return;
    const { node, start, end } = ctx;
    try {
      const range = document.createRange();
      range.setStart(node, start);
      range.setEnd(node, Math.min(end, node.nodeValue.length));
      range.deleteContents();

      const chip = document.createElement('span');
      chip.className = 'ice-mention';
      chip.setAttribute('data-employee', String(person.employee_id));
      chip.textContent = `@${person.name}`;
      range.insertNode(chip);

      // A trailing space, and the caret after it, so typing continues normally.
      const space = document.createTextNode(' ');
      chip.parentNode.insertBefore(space, chip.nextSibling);
      const after = document.createRange();
      after.setStartAfter(space);
      after.collapse(true);
      const sel = window.getSelection();
      sel.removeAllRanges();
      sel.addRange(after);
      // EditorJS tracks changes through input events.
      root.dispatchEvent(new Event('input', { bubbles: true }));
    } catch (e) { /* a lost selection just means no mention was inserted */ }
    close();
  }

  const onKeyUp = (e) => {
    // Navigation keys are handled on keydown; don't re-query for them.
    if (['ArrowDown', 'ArrowUp', 'Enter', 'Tab', 'Escape'].indexOf(e.key) >= 0) return;
    const found = queryAtCaret();
    if (!found) { close(); return; }
    ctx = found;
    const mine = ++seq;
    Promise.resolve(search(found.query.slice(0, MAX_QUERY)))
      .then((res) => {
        if (mine !== seq) return;       // a newer keystroke already won
        people = Array.isArray(res) ? res.slice(0, 8) : [];
        active = 0;
        open = people.length > 0;
        position();
        render();
      })
      .catch(() => close());
  };

  const onKeyDown = (e) => {
    if (!open || people.length === 0) return;
    if (e.key === 'ArrowDown') {
      e.preventDefault(); active = (active + 1) % people.length; highlight();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault(); active = (active - 1 + people.length) % people.length; highlight();
    } else if (e.key === 'Enter' || e.key === 'Tab') {
      // Take the highlighted person rather than letting EditorJS make a new block.
      e.preventDefault(); e.stopPropagation(); choose(active);
    } else if (e.key === 'Escape') {
      e.preventDefault(); close();
    }
  };

  const onBlur = () => { setTimeout(close, 150); };   // let a click land first

  root.addEventListener('keyup', onKeyUp);
  // Capture phase: EditorJS also handles Enter/Tab, and the picker must win.
  root.addEventListener('keydown', onKeyDown, true);
  root.addEventListener('blur', onBlur, true);

  return {
    destroy() {
      root.removeEventListener('keyup', onKeyUp);
      root.removeEventListener('keydown', onKeyDown, true);
      root.removeEventListener('blur', onBlur, true);
      if (list.parentNode) list.parentNode.removeChild(list);
    },
  };
}

export default attachMentions;
