/**
 * HTML escaping helpers shared by the React components and the legacy jQuery adapters.
 *
 * Anything that reaches an `innerHTML` / `.html()` / `dangerouslySetInnerHTML` sink and
 * originated from the database must pass through `escapeHtml` first. Almost every string
 * field in IceHRM is employee-editable, so an unescaped render is a stored-XSS sink that
 * fires in whoever views the record — typically an HR admin.
 *
 * For genuinely rich content (editor output) use `sanitizeRichText` from
 * components/IceRichTextBox instead; it keeps an allow-listed subset of markup.
 */

/** Escape the five characters that matter in HTML text and attribute contexts. */
export function escapeHtml(value) {
  if (value === null || value === undefined) {
    return '';
  }
  return (`${value}`)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/**
 * Escape, then turn newlines into <br />. Use where a stored multi-line value is
 * rendered as HTML. Safe because the escape runs first — the <br /> is the only
 * markup that survives.
 */
export function escapeHtmlWithBreaks(value) {
  return escapeHtml(value).replace(/\r\n|\n\r|\r|\n/g, '<br />');
}

/**
 * Substitute `#_key_#` placeholders in a developer-authored HTML template with
 * escaped values. The replacement is a function so `$&`-style sequences in the data
 * are treated literally rather than as replacement patterns.
 */
export function fillTemplate(template, key, value) {
  return template.replace(`#_${key}_#`, () => value);
}

export default escapeHtml;
