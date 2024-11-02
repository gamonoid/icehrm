import React, { useEffect, useState } from 'react';
import {Avatar, Button, List, Skeleton, Space, Typography, Input, Pagination, Divider, Tooltip, Tag} from 'antd';
const { Search } = Input;
const { Paragraph } = Typography;
import { FilterOutlined, UserAddOutlined, SendOutlined } from '@ant-design/icons';

class TopBar extends React.Component {
  state = {
    filterData: null,
  };

  setFilterData = (value) => {
    this.setState({filterData: value});
  }

  render() {
    return (
      <div style={{padding: '10px'}}>
        <Space direction={'horizontal'} style={{width:'100%', textAlign: 'Right'}}>
          {this.props.ice.hasAccess('save') &&
          <Button type="primary" onClick={() => this.props.ice.inviteEmployee() } icon={<SendOutlined />} >
            {this.props.ice.gt('Invite an Employee')}
          </Button>
          }
          {this.props.ice.hasAccess('save') &&
          <Button onClick={() => this.props.ice.renderForm() } icon={<UserAddOutlined />} >
            {this.props.ice.gt('Add a New Employee')}
          </Button>
          }
          <Button type="dashed" onClick={() => this.props.ice.showFilters() } icon={<FilterOutlined />} >
            {this.props.ice.gt('Filter Employees')}
          </Button>
          { this.state.filterData != null && this.state.filterData !== [] && this.state.filterData !== '' &&
            <Tag closable
                 style={{'lineHeight': '30px'}}
                 color="blue"
                 onClose={() => {this.props.ice.resetFilters(); this.setFilterData(null);}}
            >
              {this.props.ice.getFilterString(this.state.filterData)}
            </Tag>
          }
        </Space>
      </div>
    );
  }
}

export default TopBar;
