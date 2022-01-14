import React from 'react';
import { Space } from 'antd';

import EmployeeStatus from './EmployeeStatus';
import EmployeeStatusModal from './EmployeeStatusModal';

class EmployeeStatusDashboard extends React.Component {
  constructor(props) {
    super(props);
    this.employeeStatusModalRef = React.createRef();
    this.employeeStatusRef = React.createRef();
  }

  render() {
    return (
      <Space>
        <EmployeeStatus
          ref={(ref) => {
            this.employeeStatusRef = ref;
          }}
          adapter={this.props.adapter}
          apiClient={this.props.apiClient}
          employee={this.props.employee}
          showStatusSelect
          showInput={false}
          openModelCallback={() => {
            this.employeeStatusModalRef.setModalVisible(true);
          }}
        />
        <EmployeeStatusModal
          ref={(ref) => {
            this.employeeStatusModalRef = ref;
          }}
          adapter={this.props.adapter}
          title={this.props.adapter.gt('Set Your Goal')}
          apiClient={this.props.adapter.apiClient}
          employee={this.props.employee}
          saveCallback={() => {
            this.employeeStatusRef.fetch();
          }}
        />
      </Space>
    );
  }
}

export default EmployeeStatusDashboard;
