import React from 'react';
import { Space } from 'antd';

class IceDocumentField extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const { value } = this.props;

    return (
      <Space>
        <div contentEditable="false">{value}</div>
      </Space>
    );
  }
}

export default IceDocumentField;
