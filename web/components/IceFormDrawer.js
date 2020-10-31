import React from 'react';
import { Button, Drawer } from 'antd';
import IceFormModal from './IceFormModal';

class IceFormDrawer extends IceFormModal {
  render() {
    const { fields, adapter } = this.props;

    return (
      <Drawer
        title={this.props.adapter.gt(adapter.objectTypeName)}
        width={720}
        onClose={() => this.hide()}
        visible={this.state.visible}
        bodyStyle={{ paddingBottom: 80 }}
        zIndex={1200}
        maskClosable={false}
        footer={(
          <div
            style={{
              textAlign: 'right',
            }}
          >
            <Button
              onClick={() => this.hide()}
              style={{ marginRight: 8 }}
            >
              Cancel
            </Button>
            <Button
              onClick={() => {
                const form = this.formReference.current;
                form
                  .validateFields()
                  .then((values) => {
                    this.save(values);
                  })
                  .catch((info) => {
                    // this.showError(`Validate Failed: ${info.errorFields[0].errors[0]}`);
                  });
              }}
              type="primary"
            >
              Submit
            </Button>
          </div>
        )}
      >
        {this.createForm(fields, adapter)}
      </Drawer>
    );
  }
}

export default IceFormDrawer;
