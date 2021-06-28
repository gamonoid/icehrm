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
      {employeeCount.isIceHrmPro
      &&
      <Row>
        <Col span={8}>
          <IceHrmProData {...employeeCount}/>
        </Col>
        <Col span={8}/>
        <Col span={8}/>
      </Row>
      }
      <SystemData {...systemData}/>
    </Space>
  );
}

export default ConnectionTab;
