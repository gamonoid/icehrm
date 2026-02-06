import React, { useState, useEffect, useRef } from 'react';
import {
  Modal,
  Card,
  Row,
  Col,
  Avatar,
  List,
  Tag,
  Typography,
  Spin,
  Empty,
  Divider,
  Space,
  Statistic,
} from 'antd';
import {
  ClockCircleOutlined,
  TeamOutlined,
  ApartmentOutlined,
  EnvironmentOutlined,
  GlobalOutlined,
  UserOutlined,
  MailOutlined,
  CrownOutlined,
} from '@ant-design/icons';
import IceApiClient from '../../../../api/IceApiClient';

const { Title, Text } = Typography;

// Analog Clock Component
const AnalogClock = ({ timezone }) => {
  const canvasRef = useRef(null);
  const [time, setTime] = useState(new Date());

  useEffect(() => {
    const timer = setInterval(() => {
      setTime(new Date());
    }, 1000);

    return () => clearInterval(timer);
  }, []);

  useEffect(() => {
    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const size = 160;
    const radius = size / 2 - 10;
    const centerX = size / 2;
    const centerY = size / 2;

    // Get time in the specified timezone
    let localTime;
    try {
      const options = { timeZone: timezone, hour: 'numeric', minute: 'numeric', second: 'numeric', hour12: false };
      const formatter = new Intl.DateTimeFormat('en-US', options);
      const parts = formatter.formatToParts(time);
      const hours = parseInt(parts.find(p => p.type === 'hour')?.value || '0');
      const minutes = parseInt(parts.find(p => p.type === 'minute')?.value || '0');
      const seconds = parseInt(parts.find(p => p.type === 'second')?.value || '0');
      localTime = { hours, minutes, seconds };
    } catch (e) {
      const now = new Date();
      localTime = { hours: now.getHours(), minutes: now.getMinutes(), seconds: now.getSeconds() };
    }

    // Clear canvas
    ctx.clearRect(0, 0, size, size);

    // Draw clock face
    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
    ctx.fillStyle = '#f5f5f5';
    ctx.fill();
    ctx.strokeStyle = '#1677ff';
    ctx.lineWidth = 3;
    ctx.stroke();

    // Draw hour markers
    for (let i = 0; i < 12; i++) {
      const angle = (i * 30 - 90) * Math.PI / 180;
      const innerRadius = i % 3 === 0 ? radius - 15 : radius - 10;
      const outerRadius = radius - 5;

      ctx.beginPath();
      ctx.moveTo(
        centerX + innerRadius * Math.cos(angle),
        centerY + innerRadius * Math.sin(angle)
      );
      ctx.lineTo(
        centerX + outerRadius * Math.cos(angle),
        centerY + outerRadius * Math.sin(angle)
      );
      ctx.strokeStyle = i % 3 === 0 ? '#333' : '#999';
      ctx.lineWidth = i % 3 === 0 ? 2 : 1;
      ctx.stroke();
    }

    // Draw hour hand
    const hourAngle = ((localTime.hours % 12) * 30 + localTime.minutes * 0.5 - 90) * Math.PI / 180;
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.lineTo(
      centerX + (radius - 40) * Math.cos(hourAngle),
      centerY + (radius - 40) * Math.sin(hourAngle)
    );
    ctx.strokeStyle = '#333';
    ctx.lineWidth = 4;
    ctx.lineCap = 'round';
    ctx.stroke();

    // Draw minute hand
    const minuteAngle = (localTime.minutes * 6 - 90) * Math.PI / 180;
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.lineTo(
      centerX + (radius - 25) * Math.cos(minuteAngle),
      centerY + (radius - 25) * Math.sin(minuteAngle)
    );
    ctx.strokeStyle = '#1677ff';
    ctx.lineWidth = 3;
    ctx.stroke();

    // Draw second hand
    const secondAngle = (localTime.seconds * 6 - 90) * Math.PI / 180;
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.lineTo(
      centerX + (radius - 20) * Math.cos(secondAngle),
      centerY + (radius - 20) * Math.sin(secondAngle)
    );
    ctx.strokeStyle = '#ff4d4f';
    ctx.lineWidth = 1;
    ctx.stroke();

    // Draw center dot
    ctx.beginPath();
    ctx.arc(centerX, centerY, 5, 0, 2 * Math.PI);
    ctx.fillStyle = '#1677ff';
    ctx.fill();

  }, [time, timezone]);

  // Get formatted time string
  const getFormattedTime = () => {
    try {
      return new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
      }).format(time);
    } catch (e) {
      return time.toLocaleTimeString();
    }
  };

  // Get formatted date string
  const getFormattedDate = () => {
    try {
      return new Intl.DateTimeFormat('en-US', {
        timeZone: timezone,
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
      }).format(time);
    } catch (e) {
      return time.toLocaleDateString();
    }
  };

  return (
    <div style={{ textAlign: 'center' }}>
      <canvas ref={canvasRef} width={160} height={160} />
      <div style={{ marginTop: 8 }}>
        <Text strong style={{ fontSize: 20 }}>{getFormattedTime()}</Text>
      </div>
      <div>
        <Text type="secondary">{getFormattedDate()}</Text>
      </div>
      <div style={{ marginTop: 4 }}>
        <Tag icon={<GlobalOutlined />} color="blue">{timezone}</Tag>
      </div>
    </div>
  );
};

const getApiClient = () => {
  // Try to get from window.modJs first
  if (window.modJs?.apiClient) {
    return window.modJs.apiClient;
  }

  // Fallback: create a new IceApiClient with legacy wrapper
  const clientBaseUrl = typeof window !== 'undefined' ? window.CLIENT_BASE_URL : '';
  return new IceApiClient('', '', clientBaseUrl, true);
};

const CompanyStructureDetailView = ({ visible, onClose, structureId }) => {
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState(null);

  useEffect(() => {
    if (visible && structureId) {
      fetchDetails();
    }
  }, [visible, structureId]);

  const fetchDetails = async () => {
    setLoading(true);
    const apiClient = getApiClient();
    if (!apiClient) {
      console.error('API client not available');
      setLoading(false);
      return;
    }
    try {
      const response = await apiClient.get(`company/structures/${structureId}/details`);
      if (response.data) {
        setData(response.data);
      }
    } catch (error) {
      console.error('Failed to fetch company structure details', error);
    } finally {
      setLoading(false);
    }
  };

  const renderEmployeeList = (employees, title, icon, emptyText) => (
    <Card
      size="small"
      title={
        <Space>
          {icon}
          <span>{title} ({employees?.length || 0})</span>
        </Space>
      }
      style={{ height: '100%' }}
    >
      {employees && employees.length > 0 ? (
        <List
          dataSource={employees}
          style={{ maxHeight: 300, overflow: 'auto' }}
          renderItem={(emp) => (
            <List.Item>
              <List.Item.Meta
                avatar={
                  <Avatar
                    src={emp.image}
                    icon={<UserOutlined />}
                    size={40}
                  />
                }
                title={
                  <Space>
                    <span>{emp.first_name} {emp.last_name}</span>
                    {emp.employee_id && (
                      <Tag size="small">{emp.employee_id}</Tag>
                    )}
                  </Space>
                }
                description={
                  <div>
                    {emp.job_title && <div><Text type="secondary">{emp.job_title}</Text></div>}
                    {emp.work_email && (
                      <div>
                        <MailOutlined style={{ marginRight: 4 }} />
                        <Text type="secondary" style={{ fontSize: 12 }}>{emp.work_email}</Text>
                      </div>
                    )}
                  </div>
                }
              />
            </List.Item>
          )}
        />
      ) : (
        <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description={emptyText} />
      )}
    </Card>
  );

  const renderChildStructures = () => (
    <Card
      size="small"
      title={
        <Space>
          <ApartmentOutlined />
          <span>Child Structures ({data?.children?.length || 0})</span>
        </Space>
      }
      style={{ height: '100%' }}
    >
      {data?.children && data.children.length > 0 ? (
        <List
          dataSource={data.children}
          style={{ maxHeight: 200, overflow: 'auto' }}
          renderItem={(child) => (
            <List.Item>
              <List.Item.Meta
                avatar={
                  <Avatar
                    icon={<ApartmentOutlined />}
                    style={{ backgroundColor: '#1677ff' }}
                  />
                }
                title={child.title}
                description={
                  <Space>
                    <Tag>{child.type}</Tag>
                    {child.timezone && (
                      <Tag icon={<ClockCircleOutlined />} color="blue">
                        {child.timezone}
                      </Tag>
                    )}
                  </Space>
                }
              />
            </List.Item>
          )}
        />
      ) : (
        <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description="No child structures" />
      )}
    </Card>
  );

  return (
    <Modal
      title={
        <Space>
          <ApartmentOutlined />
          <span>{data?.title || 'Company Structure Details'}</span>
          {data?.type && <Tag color="blue">{data.type}</Tag>}
        </Space>
      }
      open={visible}
      onCancel={onClose}
      footer={null}
      width={900}
      styles={{ body: { maxHeight: '75vh', overflow: 'auto' } }}
    >
      {loading ? (
        <div style={{ textAlign: 'center', padding: 50 }}>
          <Spin size="large" />
        </div>
      ) : data ? (
        <div>
          {/* Header with Clock and Stats */}
          <Row gutter={[16, 16]}>
            <Col xs={24} md={8}>
              <Card size="small" style={{ height: '100%' }}>
                <AnalogClock timezone={data.timezone || 'UTC'} />
              </Card>
            </Col>
            <Col xs={24} md={16}>
              <Card size="small" style={{ height: '100%' }}>
                <Row gutter={16}>
                  <Col span={8}>
                    <Statistic
                      title="Employees"
                      value={data.employeeCount || 0}
                      prefix={<TeamOutlined />}
                    />
                  </Col>
                  <Col span={8}>
                    <Statistic
                      title="Child Units"
                      value={data.childCount || 0}
                      prefix={<ApartmentOutlined />}
                    />
                  </Col>
                  <Col span={8}>
                    <Statistic
                      title="Leads"
                      value={data.heads?.length || 0}
                      prefix={<CrownOutlined />}
                    />
                  </Col>
                </Row>
                <Divider style={{ margin: '16px 0' }} />
                <div>
                  {data.description && (
                    <div style={{ marginBottom: 8 }}>
                      <Text>{data.description}</Text>
                    </div>
                  )}
                  {data.address && (
                    <div>
                      <EnvironmentOutlined style={{ marginRight: 8 }} />
                      <Text type="secondary">{data.address}</Text>
                    </div>
                  )}
                  {data.country && (
                    <div style={{ marginTop: 8 }}>
                      <GlobalOutlined style={{ marginRight: 8 }} />
                      <Text type="secondary">Country: {data.country}</Text>
                    </div>
                  )}
                </div>
              </Card>
            </Col>
          </Row>

          <Divider />

          {/* Leads Section */}
          {data.heads && data.heads.length > 0 && (
            <>
              <Row gutter={[16, 16]}>
                <Col span={24}>
                  {renderEmployeeList(
                    data.heads,
                    'Department Leads',
                    <CrownOutlined style={{ color: '#faad14' }} />,
                    'No leads assigned'
                  )}
                </Col>
              </Row>
              <Divider />
            </>
          )}

          {/* Child Structures and Employees */}
          <Row gutter={[16, 16]}>
            <Col xs={24} md={12}>
              {renderChildStructures()}
            </Col>
            <Col xs={24} md={12}>
              {renderEmployeeList(
                data.employees,
                'Employees',
                <TeamOutlined style={{ color: '#1677ff' }} />,
                'No employees in this structure'
              )}
            </Col>
          </Row>
        </div>
      ) : (
        <Empty description="Failed to load details" />
      )}
    </Modal>
  );
};

export default CompanyStructureDetailView;
