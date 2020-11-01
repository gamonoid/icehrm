import React from 'react';
import { Space } from 'antd';

class IceLabel extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { value } = this.props;

    return (
      <Space>
        <div contentEditable='true' dangerouslySetInnerHTML={{ __html: this.nl2br(value || '') }}></div>
      </Space>
    );
  }

  nl2br(str) {
    return (`${str}`).replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '<br />');
  }
}

export default IceLabel;
