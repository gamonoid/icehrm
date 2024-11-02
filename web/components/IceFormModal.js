import React from 'react';
import {
  Button, Col, Modal, Row, Space,
} from 'antd';
import IceForm from './IceForm';

class IceFormModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      visible: false,
      viewOnly: false,
      loading: false,
    };
    this.iceFormReference = React.createRef();
  }

  setViewOnly(value) {
    this.setState({ viewOnly: value });
  }

  getWidth() {
    return this.props.adapter.getWidth();
  }

  show(data) {
    this.props.adapter.beforeRenderFieldHook = this.props.adapter.beforeRenderField ? this.props.adapter.beforeRenderField(data) : (fieldName, field) => field;
    if (!data) {
      this.setState({ visible: true });
      if (this.iceFormReference.current) {
        this.iceFormReference.current.resetFields();
      }
    } else {
      this.setState({ visible: true });
      if (this.iceFormReference.current && this.iceFormReference.current.isReady()) {
        this.iceFormReference.current.updateFields(data);
      } else {
        this.waitForIt(
          () => this.iceFormReference.current && this.iceFormReference.current.isReady(),
          () => { this.iceFormReference.current.updateFields(data); },
          1000,
        );
      }
    }
  }

  waitForIt(condition, callback, time) {
    setTimeout(() => {
      if (condition()) {
        callback();
      } else {
        this.waitForIt(condition, callback, time);
      }
    }, time);
  }

  hide() {
    this.setState({ visible: false });
  }

  save(params) {
    const { saveCompleteCallback } = this.props;
    this.iceFormReference.current.save(params, () => {
      this.closeModal();
      if (saveCompleteCallback) {
        saveCompleteCallback();
      }
    });
  }

  closeModal() {
    this.hide();
    this.iceFormReference.current.showError(false);
  }

  render() {
    const {
      fields, adapter,
    } = this.props;

    let {
      saveCallback, cancelCallback,
    } = this.props;

    // if properties are passed check if the adapter has these
    if (!saveCallback && adapter.saveCallback) {
      saveCallback = adapter.saveCallback;
    }

    if (!cancelCallback && adapter.cancelCallback) {
      cancelCallback = adapter.cancelCallback;
    }

    const additionalProps = {};
    additionalProps.footer = (
      <Row gutter={16}>
        <Col className="gutter-row" span={12} style={{}} />
        <Col className="gutter-row" span={12} style={{ textAlign: 'right' }}>
          <Space>
            <Button onClick={() => {
              if (cancelCallback) {
                cancelCallback();
              } else {
                this.closeModal();
              }
            }}
            >
              {this.props.adapter.gt('Cancel')}
            </Button>
            <Button
              loading={this.state.loading}
              type="primary"
              onClick={() => {
                this.setState({ loading: true });
                const iceFrom = this.iceFormReference.current;
                iceFrom
                  .validateFields()
                  .then((values) => {
                    if (!iceFrom.isValid()) {
                      this.setState({ loading: false });
                      return;
                    }
                    if (saveCallback) {
                      saveCallback(values, iceFrom.showError.bind(this), this.closeModal.bind(this), adapter);
                    } else {
                      this.save(values);
                    }
                    this.setState({ loading: false });
                  })
                  .catch((info) => {
                    this.setState({ loading: false });
                  });
              }}
            >
              {this.state.viewOnly ? this.props.adapter.gt('Done') : this.props.adapter.gt('Save')}
            </Button>
          </Space>
        </Col>
      </Row>
    );

    if (this.state.viewOnly) {
      additionalProps.footer = null;
    }

    return (
      <Modal
        visible={this.state.visible}
        title={this.props.adapter.gt(this.props.title || adapter.objectTypeName)}
        maskClosable={false}
        width={this.getWidth()}
        onCancel={() => {
          if (cancelCallback) {
            cancelCallback();
          } else {
            this.closeModal();
          }
        }}
        {...additionalProps}
      >
        <IceForm
          ref={this.iceFormReference}
          adapter={adapter}
          fields={fields}
          viewOnly={this.state.viewOnly}
        />
      </Modal>
    );
  }
}


export default IceFormModal;
