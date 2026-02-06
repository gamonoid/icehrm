import React from 'react';
import SignatureCanvas from 'react-signature-canvas';
import { Button, Modal, Tag } from 'antd';
import { VerifiedOutlined } from '@ant-design/icons';

class IceSignature extends React.Component {
  constructor(props) {
    super(props);
    this.onChange = props.onChange;
    this.state = {
      visible: false,
    };
    this.signature = React.createRef();
  }

  componentDidMount() {
  }

  show() {
    this.setState({ visible: true });
  }

  setSignature(ref) {
    if (ref == null) {
      return;
    }
    const { value } = this.props;
    if (value != null && value.length > 10) {
      ref.fromDataURL(value);
    }
  }

  hide() {
    this.setState({ visible: false });
  }

  clear() {
    this.signature.clear();
  }

  save() {
    const data = this.signature.toDataURL('image/png');
    this.onChange(data);
    this.setState({ visible: false });
  }

  render() {
    const { readOnly } = this.props;

    return (
      <>
        <Modal
          open={this.state.visible}
          title="Signature"
          maskClosable={false}
          centered
          width={300}
          onCancel={() => { this.hide(); }}
          footer={[
            <Button key="cancel" onClick={() => { this.hide(); }}>
              Cancel
            </Button>,
            <Button key="clear" disabled={readOnly} type="dashed" onClick={() => { if (!readOnly) { this.clear(); } }}>
              Clear
            </Button>,
            <Button key="ok" disabled={readOnly} type="primary" onClick={() => { if (!readOnly) { this.save(); } }}>
              Submit
            </Button>,
          ]}
        >
          <SignatureCanvas ref={(ref) => { this.signature = ref; this.setSignature(ref); }} canvasProps={{ width: 250, height: 200, className: 'sigCanvas', ...( readOnly ? { readOnly } : {}), }} />
        </Modal>
        <Tag color="blue" style={{ cursor: 'pointer' }} onClick={() => { this.show(); }}>
          <VerifiedOutlined />
          {' '}
          Sign
        </Tag>
      </>
    );
  }
}

export default IceSignature;
