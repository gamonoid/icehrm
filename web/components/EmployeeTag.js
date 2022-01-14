import React from 'react';
import {
  Space, Avatar, Row, Col, Typography,
} from 'antd';

const { Title, Text } = Typography;

function EmployeeTag(props) {
  const { employee } = props;
  return (
    <Row>
      <Col span={24}>
        <Space>
          <Avatar size={64} src={employee.image} />
          <Row>
            <Col span={24}>
              <Title
                level={4}
                style={{ 'margin-bottom': '0px' }}
              >
                {`${employee.first_name} ${employee.last_name}`}
              </Title>
            </Col>
            <Col span={24}>
              <Text type="secondary">{employee.sub_title}</Text>
            </Col>
          </Row>
        </Space>
      </Col>
    </Row>
  );
}

export default EmployeeTag;
