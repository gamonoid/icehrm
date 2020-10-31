import React from 'react';
import { Modal } from 'antd';
import IceFormModal from './IceFormModal';
import IceStepForm from './IceStepForm';

class IceStepFormModal extends IceFormModal {
  constructor(props) {
    super(props);
    this.width = 850;
  }

  show(data) {
    if (!data) {
      this.setState({ visible: true });
      if (this.iceFormReference.current) {
        this.iceFormReference.current.resetFields();
      }
    } else {
      this.setState({ visible: true });
      if (this.iceFormReference.current && this.iceFormReference.current.isReady()) {
        this.iceFormReference.current.moveToStep(0);
        this.iceFormReference.current.updateFields(data);
      } else {
        this.waitForIt(
          () => this.iceFormReference.current && this.iceFormReference.current.isReady(),
          () => {
            this.iceFormReference.current.updateFields(data);
            this.iceFormReference.current.moveToStep(0);
          },
          1000,
        );
      }
    }
  }

  hide() {
    this.iceFormReference.current.moveToStep(0);
    this.setState({ visible: false });
  }

  render() {
    const { fields, adapter } = this.props;
    const { width, twoColumnLayout, layout } = this.props.adapter.getFormOptions();
    return (
      <Modal
        visible={this.state.visible}
        title={this.props.adapter.gt(this.props.title || adapter.objectTypeName)}
        maskClosable={false}
        width={width || this.width}
        footer={[]}
        onCancel={() => {
          this.closeModal();
        }}
      >
        <IceStepForm
          ref={this.iceFormReference}
          adapter={adapter}
          fields={fields}
          closeModal={() => { this.closeModal(); }}
          twoColumnLayout={twoColumnLayout || false}
          layout={layout}
        />
      </Modal>
    );
  }
}

export default IceStepFormModal;
