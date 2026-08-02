import React from 'react';
import { Space } from 'antd';
import { escapeHtmlWithBreaks } from '../api-common/htmlEscape';

/**
 * Read-only renderer for a single field value.
 *
 * IceForm routes every view-only `text` / `textarea` / `password` / `switch` field
 * through this component, so it sees plain strings straight out of the database —
 * profile fields, custom fields, notes. It must therefore never treat its input as
 * markup: a low-privilege employee could otherwise store a payload in their own
 * profile and have it execute in an HR admin's session when the record is viewed.
 *
 * escapeHtmlWithBreaks escapes first and only then turns newlines into <br />, which
 * is the sole reason this uses dangerouslySetInnerHTML at all. (The previous nl2br
 * regex also dropped the character immediately before each newline, because its
 * capture group was never re-emitted.)
 */
class IceLabel extends React.Component {
  render() {
    const { value } = this.props;

    return (
      <Space>
        <div
          contentEditable="false"
          dangerouslySetInnerHTML={{ __html: escapeHtmlWithBreaks(value) }}
        />
      </Space>
    );
  }
}

export default IceLabel;
