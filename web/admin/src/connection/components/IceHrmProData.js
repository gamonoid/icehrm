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
  const { count, allowed, validUntil, licenseId } = props;
  return (
    <>
      <Row gutter={16}>
        <Col span={12}>
          <Statistic title="Number of Employees" value={`${count} / ${allowed}`} />
          <Space />
          <Progress type="circle" percent={parseInt((count * 100) / allowed, 10)} width={80} />
        </Col>
        <Col span={12}>
          <Statistic title="License Valid Until" value={dayjs(validUntil).format('MMM D, YYYY')}/>
          <Button style={{ marginTop: 16 }} type="primary" onClick={() => {
            window.open(`https://icehrm.com/renew-icehrmpro-license/${licenseId}`, '_blank');
          }}>
            Renew
          </Button>
        </Col>
      </Row>
    </>
  );
}

export default IceHrmProData;
