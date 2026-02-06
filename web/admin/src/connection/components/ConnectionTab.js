import React from 'react';
import {
  Row,
  Col,
  Space,
} from 'antd';
import IceHrmProData from './IceHrmProData';
import SystemData from './SystemData';

function ConnectionTab(props) {
  const { employeeCount, systemData } = props;
  return (
    <Space direction="vertical" style={{ width: '100%' }}>
      <SystemData {...systemData}/>
    </Space>
  );
}

export default ConnectionTab;
