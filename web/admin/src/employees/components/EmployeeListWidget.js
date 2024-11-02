import React from 'react';
import { SearchOutlined } from '@ant-design/icons';
import {
  Avatar, Space, Tooltip, Button, Col,
} from 'antd';


class EmployeeListWidget extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    return (
      <Space>
        <>
          {this.props.employees.map((emp) => {
            return (
              <Tooltip title={`${emp.first_name} ${emp.last_name}`} color="#00c0ef" key={emp.id}>
                <Avatar size={40} src={emp.image} />
              </Tooltip>
            );
          })}
        </>
        {this.props.url
        && (
        <Tooltip title={this.props.adapter.gt('Visit Employee Directory')}>
          <Button shape="circle" icon={<SearchOutlined />} size="large" onClick={() => { window.location = this.props.url; }} />
        </Tooltip>
        )}
      </Space>
    );
  }
}
export default EmployeeListWidget;
