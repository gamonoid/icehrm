import React from 'react';
import { Modal, Button } from 'antd';

import EmployeeStatus from "./EmployeeStatus";

class EmployeeStatusModal extends React.Component {
  state = {
    isModalVisible: false,
  }

  constructor(props) {
    super(props);
    this.employeeStatusRef = React.createRef();
  }

  setModalVisible(value) {
    this.setState({
      isModalVisible: value,
    })
  };

  handleOk() {
    this.employeeStatusRef.syncState();
    this.setModalVisible(false);
    this.props.saveCallback();
  };

  handleCancel() {
    this.setModalVisible(false);
  };

  render() {
    return (<>
      <Modal title={this.props.title} visible={this.state.isModalVisible}
             onOk={this.handleOk.bind(this)}
             onCancel={this.handleCancel.bind(this)}
      >
        <EmployeeStatus
          ref={(ref) => {
            this.employeeStatusRef = ref;
          }}
          adapter={this.props.adapter}
          apiClient={this.props.apiClient}
          employee={this.props.employee}
          showInput={true}
          showStatusSelect={false}
        />
      </Modal>
    </> );
  }
}

export default EmployeeStatusModal;
