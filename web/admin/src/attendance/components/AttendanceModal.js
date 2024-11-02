import {
  Avatar, Button, Card, Col, Modal, Progress, Row, Typography, Space,
} from 'antd';
import React, { useState } from 'react';
import {
  EnvironmentOutlined, MailTwoTone, PhoneTwoTone, SecurityScanTwoTone, ClockCircleTwoTone,
  TagsTwoTone,
} from '@ant-design/icons';

const { Meta } = Card;
const { Text, Title } = Typography;
class AttendanceModal extends React.Component {
  handleCancel() {
    const { adapter } = this.props;
    adapter.tableContainer.current.setCurrentElement(null);
  }

  dateRenderer(text) {
    if (text === '0000-00-00 00:00:00' || text === '' || text === undefined || text == null) {
      return '';
    }
    return (
      <>
        <Text>{Date.parse(text).toString('yyyy MMM d')}</Text>
        {' : '}
        <Text strong>{Date.parse(text).toString('HH:mm')}</Text>
      </>
    );
  }

  render() {
    const { adapter, element } = this.props;
    const goal = {};
    const mode = '';
    return (
      <Modal
        open={element != null}
        title={adapter.gt('Attendance Details')}
        onCancel={() => this.handleCancel()}
        width={800}
        footer={[
          <Button key="back" onClick={() => this.handleCancel()}>
            Close
          </Button>,
        ]}
      >
        <Row direction="vertical" style={{ width: '100%', padding: '10px' }} gutter={24}>
          <Col span={24}>
            <Space size="large">
              <Space direction="vertical">
                <Avatar size={80} src={element.image} />
              </Space>
              <Space direction="vertical">
                <Title level={4}>
                  {`${element.employee_Name}`}
                </Title>
                {element.in_time &&
                  <Space>
                    <ClockCircleTwoTone />
                    <Text underline>{' Clock-in:'}</Text>
                    {this.dateRenderer(element.in_time)}
                  </Space>
                }
                {element.out_time
                  && (
                    <Space>
                      <ClockCircleTwoTone />
                      <Text underline>{' Clock-out:'}</Text>
                      {this.dateRenderer(element.out_time)}
                    </Space>
                  )}
                {element.in_ip
                  && (
                    <Space>
                      <TagsTwoTone />
                      <Text underline>{` Clock-in IP Address:`}</Text>
                      <Text>{element.in_ip}</Text>
                    </Space>
                  )}
                {element.out_ip
                  && (
                    <Space>
                      <TagsTwoTone />
                      <Text underline>{` Clock-out IP Address:`}</Text>
                      <Text>{element.out_ip}</Text>
                    </Space>
                  )}
                {element.map_lat
                  && (
                    <Space>
                      <TagsTwoTone />
                      <Text underline>{` Clock-in Location:`}</Text>
                      <Text>{`${element.map_lat} , ${element.map_lng}`}</Text>
                      <Button size="small" type="primary" icon={<EnvironmentOutlined />} onClick={() => window.open(element.map_link_in, '_blank').focus()}>
                        Open in Google Maps
                      </Button>
                    </Space>
                  )}
                {element.map_out_lat
                  && (
                    <Space>
                      <TagsTwoTone />
                      <Text underline>{` Clock-out Location:`}</Text>
                      <Text>{`${element.map_out_lat} , ${element.map_out_lng}`}</Text>
                      <Button size="small" type="primary" icon={<EnvironmentOutlined />} onClick={() => window.open(element.map_link_out, '_blank').focus()}>
                        Open in Google Maps
                      </Button>
                    </Space>
                  )}
              </Space>
            </Space>
          </Col>
        </Row>
        {element.out_time
          && (
            <Row direction="vertical" style={{ width: '100%', padding: '2px', marginTop: 2 }} gutter={[24, 24]}>
              <Col span={24}>
                <Row direction="vertical">
                  <div style={{ marginTop: 5, marginBottom: 5 }}>
                    <Text strong>Total Hours: </Text>
                    <Text code>{`${element.hours}h / ${adapter.overtimeStart}h`}</Text>
                  </div>
                  <Progress
                    percent={element.hours ? (element.hours / adapter.overtimeStart) * 100 : 0}
                    size="small"
                    format={(percent, successPercent) => ''}
                  />
                </Row>
              </Col>
            </Row>
          )}
      </Modal>
    );
  }
}
export default AttendanceModal;
