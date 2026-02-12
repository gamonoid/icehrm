import React from 'react';
import {
  Statistic,
  Row,
  Col,
  Button,
  Progress, Space,
} from 'antd';

const dayjs = require('dayjs');

function IceHrmProData(props) {
  const { count, allowed, } = props;
  return (
    <>
      <Row gutter={16}>
        <Col span={12}>
          <Statistic title="Number of Employees" value={`${count}`} />
          <Space />
          <Progress type="circle" percent={parseInt((count * 100) / allowed, 10)} width={80} />
        </Col>
      </Row>
    </>
  );
}

export default IceHrmProData;
