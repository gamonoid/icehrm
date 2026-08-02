import React from 'react';
import {
  Card,
  Row,
  Col,
  List,
  Avatar,
  Tag,
  Typography,
  Skeleton,
  Empty,
  Statistic,
  Space,
  Tabs,
} from 'antd';
import {
  CalendarOutlined,
  TeamOutlined,
  ClockCircleOutlined,
  TrophyOutlined,
  ApartmentOutlined,
} from '@ant-design/icons';

const { Title, Text } = Typography;
const { TabPane } = Tabs;

class Company_overviewUserExtensionView extends React.Component {
  state = {
    upcomingLeaves: [],
    leavesLoading: true,
    companyStructure: null,
    structureLoading: true,
    managerHierarchy: null,
    hierarchyLoading: true,
    topAttendance: null,
    attendanceLoading: true,
  };

  componentDidMount() {
    this.fetchUpcomingLeaves();
    this.fetchCompanyStructure();
    this.fetchManagerHierarchy();
    this.fetchTopAttendance();
  }

  getApiClient() {
    return window.company_overviewExtensionController.getApiClient();
  }

  fetchUpcomingLeaves() {
    this.getApiClient()
      .get('company_overview/leaves/upcoming')
      .then((response) => {
        this.setState({
          upcomingLeaves: response.data || [],
          leavesLoading: false,
        });
      })
      .catch(() => {
        this.setState({ leavesLoading: false });
      });
  }

  fetchCompanyStructure() {
    this.getApiClient()
      .get('company_overview/company-structure')
      .then((response) => {
        this.setState({
          companyStructure: response.data,
          structureLoading: false,
        });
      })
      .catch(() => {
        this.setState({ structureLoading: false });
      });
  }

  fetchManagerHierarchy() {
    this.getApiClient()
      .get('company_overview/manager-hierarchy')
      .then((response) => {
        this.setState({
          managerHierarchy: response.data,
          hierarchyLoading: false,
        });
      })
      .catch(() => {
        this.setState({ hierarchyLoading: false });
      });
  }

  fetchTopAttendance() {
    this.getApiClient()
      .get('company_overview/top-attendance')
      .then((response) => {
        this.setState({
          topAttendance: response.data,
          attendanceLoading: false,
        });
      })
      .catch(() => {
        this.setState({ attendanceLoading: false });
      });
  }

  renderUpcomingLeaves() {
    const { upcomingLeaves, leavesLoading } = this.state;

    if (leavesLoading) {
      return <Skeleton active />;
    }

    if (!upcomingLeaves || upcomingLeaves.length === 0) {
      return <Empty description="No upcoming leaves" />;
    }

    return (
      <List
        itemLayout="horizontal"
        dataSource={upcomingLeaves}
        renderItem={(item) => (
          <List.Item>
            <List.Item.Meta
              avatar={<Avatar src={item.employee_image} />}
              title={item.employee_name}
              description={
                <Space direction="vertical" size={0}>
                  <Text type="secondary">
                    <CalendarOutlined style={{ marginRight: 4 }} />
                    {item.date_start} - {item.date_end}
                  </Text>
                  {item.details && (
                    <Text type="secondary" style={{ fontSize: '12px' }}>
                      {item.details}
                    </Text>
                  )}
                </Space>
              }
            />
          </List.Item>
        )}
      />
    );
  }

  renderOrgChart(data, title, icon) {
    if (!data) {
      return <Empty description={`No ${title.toLowerCase()} data available`} />;
    }

    // Theme-aware node colours: the hardcoded light backgrounds made the (white)
    // text unreadable in the SPA shell's dark mode.
    const dark = typeof window !== 'undefined' && window.__shellColorMode === 'dark';
    const nodeStyle = (level) => (level === 0
      ? {
        backgroundColor: dark ? 'rgba(22,119,255,0.18)' : '#e6f7ff',
        border: `1px solid ${dark ? 'rgba(22,119,255,0.45)' : '#91d5ff'}`,
      }
      : {
        backgroundColor: dark ? 'rgba(255,255,255,0.06)' : '#fafafa',
        border: `1px solid ${dark ? 'rgba(255,255,255,0.12)' : '#f0f0f0'}`,
      });
    const connectorColor = dark ? 'rgba(255,255,255,0.18)' : '#e8e8e8';

    // Recursive function to render tree nodes
    const renderNode = (node, level = 0) => {
      if (!node) return null;

      const hasChildren = node.children && node.children.length > 0;

      return (
        <div key={node.id} style={{ marginLeft: level > 0 ? 24 : 0 }}>
          <div
            style={{
              display: 'flex',
              alignItems: 'center',
              padding: '8px 12px',
              marginBottom: 4,
              borderRadius: 6,
              ...nodeStyle(level),
            }}
          >
            {node.image ? (
              <Avatar src={node.image} size={32} style={{ marginRight: 8 }} />
            ) : (
              <Avatar
                style={{ backgroundColor: '#1890ff', marginRight: 8 }}
                size={32}
              >
                {node.name ? node.name.charAt(0) : '?'}
              </Avatar>
            )}
            <div>
              <Text strong style={{ display: 'block' }}>
                {node.name}
              </Text>
              {node.title && (
                <Text type="secondary" style={{ fontSize: '12px' }}>
                  {node.title}
                </Text>
              )}
            </div>
          </div>
          {hasChildren && (
            <div style={{ borderLeft: `2px solid ${connectorColor}`, marginLeft: 16 }}>
              {node.children.map((child) => renderNode(child, level + 1))}
            </div>
          )}
        </div>
      );
    };

    return (
      <div style={{ padding: '8px 0' }}>
        {renderNode(data)}
      </div>
    );
  }

  renderTopAttendance() {
    const { topAttendance, attendanceLoading } = this.state;

    if (attendanceLoading) {
      return <Skeleton active />;
    }

    if (!topAttendance || !topAttendance.employees || topAttendance.employees.length === 0) {
      return <Empty description="No attendance data for last week" />;
    }

    return (
      <>
        <Text type="secondary" style={{ marginBottom: 16, display: 'block' }}>
          Period: {topAttendance.period?.start} to {topAttendance.period?.end}
        </Text>
        <List
          itemLayout="horizontal"
          dataSource={topAttendance.employees}
          renderItem={(item, index) => (
            <List.Item>
              <List.Item.Meta
                avatar={
                  <Space>
                    {index < 3 && (
                      <TrophyOutlined
                        style={{
                          color: index === 0 ? '#ffd700' : index === 1 ? '#c0c0c0' : '#cd7f32',
                          fontSize: 18,
                        }}
                      />
                    )}
                    <Avatar src={item.employee_image} />
                  </Space>
                }
                title={
                  <Space>
                    <Text strong>{item.employee_name}</Text>
                    <Tag color="blue">{item.total_hours} hrs</Tag>
                  </Space>
                }
                description={
                  <Text type="secondary">
                    {item.attendance_count} attendance records
                  </Text>
                }
              />
            </List.Item>
          )}
        />
      </>
    );
  }

  render() {
    const { structureLoading, hierarchyLoading, companyStructure, managerHierarchy } = this.state;

    return (
      <div style={{ padding: 16 }}>
        <Tabs defaultActiveKey="1" type="card">
          <TabPane
            tab={
              <span>
                <TeamOutlined style={{ marginRight: 8 }} />
                Overview
              </span>
            }
            key="1"
          >
            <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
              {/* Upcoming Leaves */}
              <Col xs={24} lg={12}>
                <Card
                  title={
                    <Space>
                      <CalendarOutlined />
                      <span>Upcoming Leaves</span>
                    </Space>
                  }
                  style={{ height: '100%' }}
                >
                  {this.renderUpcomingLeaves()}
                </Card>
              </Col>

              {/* Top Attendance */}
              <Col xs={24} lg={12}>
                <Card
                  title={
                    <Space>
                      <ClockCircleOutlined />
                      <span>Top Attendance (Last 7 Days)</span>
                    </Space>
                  }
                  style={{ height: '100%' }}
                >
                  {this.renderTopAttendance()}
                </Card>
              </Col>
            </Row>
          </TabPane>

          <TabPane
            tab={
              <span>
                <ApartmentOutlined style={{ marginRight: 8 }} />
                Organization
              </span>
            }
            key="2"
          >
            <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
              <Col xs={24} lg={12}>
                <Card
                  title={
                    <Space>
                      <ApartmentOutlined />
                      <span>Company Structure</span>
                    </Space>
                  }
                  style={{ height: '100%' }}
                >
                  {structureLoading ? (
                    <Skeleton active />
                  ) : (
                    this.renderOrgChart(companyStructure, 'Company Structure', <ApartmentOutlined />)
                  )}
                </Card>
              </Col>
              <Col xs={24} lg={12}>
                <Card
                  title={
                    <Space>
                      <TeamOutlined />
                      <span>Manager Hierarchy</span>
                    </Space>
                  }
                  style={{ height: '100%' }}
                >
                  {hierarchyLoading ? (
                    <Skeleton active />
                  ) : (
                    this.renderOrgChart(managerHierarchy, 'Manager Hierarchy', <TeamOutlined />)
                  )}
                </Card>
              </Col>
            </Row>
          </TabPane>
        </Tabs>
      </div>
    );
  }
}

export default Company_overviewUserExtensionView;
