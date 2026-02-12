import React from 'react';
import {
  Col,
  Card,
  Avatar,
  Row,
  Descriptions,
  Typography,
  Space,
  Tag,
  Divider,
  Button,
  Timeline,
  Modal,
  message,
} from 'antd';
import {
  CloseOutlined,
  EnvironmentOutlined,
  CalendarOutlined,
  DollarOutlined,
  FileTextOutlined,
  PaperClipOutlined,
  CarOutlined,
  GlobalOutlined,
  HistoryOutlined,
  CheckOutlined,
  ExclamationCircleOutlined,
} from '@ant-design/icons';
import CustomAction from '../../../../api/CustomAction';

const { Title, Text } = Typography;

class TravelRequestView extends React.Component {
  constructor(props) {
    super(props);
    const {
      employee,
      travelRequest,
      attachments,
      statusLogs,
    } = this.props.element;
    this.state = {
      employee,
      travelRequest,
      attachments,
      statusLogs: statusLogs || [],
      loading: false,
    };
  }

  isAdmin() {
    return this.props.adapter?.user?.user_level === 'Admin';
  }

  handleStatusChange(newStatus) {
    const { travelRequest } = this.state;
    const statusText = newStatus === 'Approved' ? 'approve' : 'reject';
    const statusAction = newStatus === 'Approved' ? 'Approve' : 'Reject';

    Modal.confirm({
      title: `${statusAction} Travel Request`,
      icon: <ExclamationCircleOutlined />,
      content: `Are you sure you want to ${statusText} this travel request?`,
      okText: statusAction,
      okType: newStatus === 'Approved' ? 'primary' : 'danger',
      cancelText: 'Cancel',
      onOk: () => {
        this.setState({ loading: true });

        const customAction = new CustomAction(this.props.adapter);
        const req = { id: travelRequest.id, status: newStatus };
        const reqJson = JSON.stringify(req);

        customAction.execute('changeStatus', 'admin=travel', reqJson)
          .then((response) => {
            const data = response.data;
            if (data.status === 'SUCCESS') {
              message.success(`Travel request ${statusText}d successfully`);
              // Update local state
              this.setState({
                travelRequest: { ...travelRequest, status: newStatus },
                loading: false,
              });
              // Refresh the table if callback provided
              if (this.props.onStatusChange) {
                this.props.onStatusChange();
              }
            } else {
              message.error(data.message || `Failed to ${statusText} travel request`);
              this.setState({ loading: false });
            }
          })
          .catch((error) => {
            message.error(`Error: ${error.message || 'Failed to change status'}`);
            this.setState({ loading: false });
          });
      },
    });
  }

  getStatusColor(status) {
    const statusColors = {
      'Pending': 'warning',
      'Processing': 'processing',
      'Approved': 'success',
      'Rejected': 'error',
      'Cancelled': 'default',
      'Cancellation Requested': 'warning',
    };
    return statusColors[status] || 'default';
  }

  getTripTypeIcon(tripType) {
    const icons = {
      'Domestic': '🏠',
      'International': '🌍',
      'Regional': '🗺️',
    };
    return icons[tripType] || '';
  }

  parseLocation(locationData) {
    if (!locationData) return '-';

    try {
      const parsed = JSON.parse(locationData);
      const country = parsed.country || '';
      const city = parsed.city || '';

      if (country && city) {
        return `${city}, ${country}`;
      } else if (city) {
        return city;
      } else if (country) {
        return country;
      }
      return '-';
    } catch (e) {
      // Backward compatibility: if not JSON, display as-is (city only)
      return locationData;
    }
  }

  render() {
    const {
      employee,
      travelRequest,
      attachments,
      statusLogs,
    } = this.state;

    return (
      <>
        <Row style={{ width: '100%' }} gutter={[16, 16]}>
          {/* Header Card - Employee Information */}
          <Col span={24}>
            <Card
              title={
                <Space>
                  <GlobalOutlined />
                  <span>{this.props.adapter.gt('Travel Request Details')}</span>
                </Space>
              }
              extra={
                <Tag color={this.getStatusColor(travelRequest.status)}>
                  {travelRequest.status}
                </Tag>
              }
              style={{ width: '100%' }}
            >
              <Space size="large" align="start">
                <Avatar size={40} src={employee.image} />
                <Space direction="vertical" size="small">
                  <Title level={4} style={{ margin: 0 }}>
                    {`${employee.first_name} ${employee.last_name}`}
                  </Title>
                  <Text type="secondary">{employee.job_title || ''}</Text>
                  <Text type="secondary">{employee.department || ''}</Text>
                  {employee.work_email && (
                    <Text copyable>{employee.work_email}</Text>
                  )}
                </Space>
              </Space>
            </Card>
          </Col>

          {/* Travel Details Card */}
          <Col span={24}>
            <Card
              title={
                <Space>
                  <CarOutlined />
                  <span>{this.props.adapter.gt('Travel Details')}</span>
                </Space>
              }
              style={{ width: '100%' }}
            >
              <Descriptions bordered column={2}>
                <Descriptions.Item label={this.props.adapter.gt('Trip Type')} span={1}>
                  <Space>
                    <span>{this.getTripTypeIcon(travelRequest.trip_type)}</span>
                    <Text strong>{travelRequest.trip_type || '-'}</Text>
                  </Space>
                </Descriptions.Item>
                <Descriptions.Item label={this.props.adapter.gt('Transportation')} span={1}>
                  <Text>{travelRequest.type || '-'}</Text>
                </Descriptions.Item>
                <Descriptions.Item label={this.props.adapter.gt('Traveling From')} span={1}>
                  <Space>
                    <EnvironmentOutlined />
                    <Text strong>{this.parseLocation(travelRequest.travel_from)}</Text>
                  </Space>
                </Descriptions.Item>
                <Descriptions.Item label={this.props.adapter.gt('Traveling To')} span={1}>
                  <Space>
                    <EnvironmentOutlined />
                    <Text strong>{this.parseLocation(travelRequest.travel_to)}</Text>
                  </Space>
                </Descriptions.Item>
                <Descriptions.Item label={this.props.adapter.gt('Departure Date & Time')} span={1}>
                  <Space>
                    <CalendarOutlined />
                    <Text>
                      {travelRequest.travel_date
                        ? Date.parse(travelRequest.travel_date).toString('MMM dd, yyyy HH:mm')
                        : '-'}
                    </Text>
                  </Space>
                </Descriptions.Item>
                <Descriptions.Item label={this.props.adapter.gt('Return Date & Time')} span={1}>
                  <Space>
                    <CalendarOutlined />
                    <Text>
                      {travelRequest.return_date
                        ? Date.parse(travelRequest.return_date).toString('MMM dd, yyyy HH:mm')
                        : '-'}
                    </Text>
                  </Space>
                </Descriptions.Item>
                {travelRequest.purpose && (
                  <Descriptions.Item label={this.props.adapter.gt('Travel Details')} span={2}>
                    <Text>{travelRequest.purpose}</Text>
                  </Descriptions.Item>
                )}
              </Descriptions>
            </Card>
          </Col>

          {/* Booking Information Card */}
          <Col span={24}>
            <Card
              title={
                <Space>
                  <FileTextOutlined />
                  <span>{this.props.adapter.gt('Booking Information')}</span>
                </Space>
              }
              style={{ width: '100%' }}
            >
              <Descriptions bordered column={2}>
                {travelRequest.confirmation_number && (
                  <Descriptions.Item label={this.props.adapter.gt('Confirmation Number')} span={2}>
                    <Text copyable>{travelRequest.confirmation_number}</Text>
                  </Descriptions.Item>
                )}
                {travelRequest.airline_name && (
                  <Descriptions.Item label={this.props.adapter.gt('Airline Name')} span={1}>
                    <Text>{travelRequest.airline_name}</Text>
                  </Descriptions.Item>
                )}
                {travelRequest.flight_number && (
                  <Descriptions.Item label={this.props.adapter.gt('Flight Number')} span={1}>
                    <Text>{travelRequest.flight_number}</Text>
                  </Descriptions.Item>
                )}
                {!travelRequest.confirmation_number && !travelRequest.airline_name && !travelRequest.flight_number && (
                  <Descriptions.Item span={2}>
                    <Text type="secondary">{this.props.adapter.gt('No booking information available')}</Text>
                  </Descriptions.Item>
                )}
              </Descriptions>
            </Card>
          </Col>

          {/* Project & Budget Card */}
          <Col span={24}>
            <Card
              title={
                <Space>
                  <DollarOutlined />
                  <span>{this.props.adapter.gt('Project & Budget')}</span>
                </Space>
              }
              style={{ width: '100%' }}
            >
              <Descriptions bordered column={2}>
                <Descriptions.Item label={this.props.adapter.gt('Travel Project')} span={2}>
                  <Text strong>{travelRequest.project_code || '-'}</Text>
                </Descriptions.Item>
                <Descriptions.Item label={this.props.adapter.gt('Funding Currency')} span={1}>
                  <Text>{travelRequest.currency_code || '-'}</Text>
                </Descriptions.Item>
                <Descriptions.Item label={this.props.adapter.gt('Total Funding Proposed')} span={1}>
                  <Text strong>
                    {travelRequest.funding
                      ? `${travelRequest.currency_code || ''} ${parseFloat(travelRequest.funding).toFixed(2)}`
                      : '-'}
                  </Text>
                </Descriptions.Item>
                {travelRequest.details && (
                  <Descriptions.Item label={this.props.adapter.gt('Additional Notes')} span={2}>
                    <Text>{travelRequest.details}</Text>
                  </Descriptions.Item>
                )}
              </Descriptions>
            </Card>
          </Col>

          {/* Attachments Card */}
          {(attachments && attachments.length > 0) && (
            <Col span={24}>
              <Card
                title={
                  <Space>
                    <PaperClipOutlined />
                    <span>{this.props.adapter.gt('Attachments')}</span>
                  </Space>
                }
                style={{ width: '100%' }}
              >
                <Space direction="vertical" style={{ width: '100%' }}>
                  {attachments.map((attachment, index) => (
                    <Card.Grid
                      key={index}
                      hoverable
                      style={{ width: '100%', cursor: 'pointer' }}
                      onClick={() => window.open(attachment.url, '_blank')}
                    >
                      <Space>
                        <PaperClipOutlined style={{ fontSize: '20px' }} />
                        <div>
                          <Text strong>{attachment.name}</Text>
                          <br />
                          <Text type="secondary" style={{ fontSize: '12px' }}>
                            {this.props.adapter.gt('Click to view or download')}
                          </Text>
                        </div>
                      </Space>
                    </Card.Grid>
                  ))}
                </Space>
              </Card>
            </Col>
          )}

          {/* Status Change History */}
          {(statusLogs && statusLogs.length > 0) && (
            <Col span={24}>
              <Card
                title={
                  <Space>
                    <HistoryOutlined />
                    <span>{this.props.adapter.gt('Status Change History')}</span>
                  </Space>
                }
                style={{ width: '100%' }}
              >
                <Timeline
                  items={statusLogs.map((log, index) => ({
                    color: this.getStatusColor(log.status_to) === 'success' ? 'green' :
                           this.getStatusColor(log.status_to) === 'error' ? 'red' :
                           this.getStatusColor(log.status_to) === 'warning' ? 'orange' : 'blue',
                    children: (
                      <div key={index}>
                        <Space direction="vertical" size={0}>
                          <Space>
                            <Tag color={this.getStatusColor(log.status_from)}>{log.status_from}</Tag>
                            <span>→</span>
                            <Tag color={this.getStatusColor(log.status_to)}>{log.status_to}</Tag>
                          </Space>
                          {log.note && (
                            <Text type="secondary" style={{ fontSize: '12px' }}>
                              {log.note}
                            </Text>
                          )}
                          <Text type="secondary" style={{ fontSize: '12px' }}>
                            {log.time ? Date.parse(log.time).toString('MMM dd, yyyy HH:mm') : ''}
                          </Text>
                        </Space>
                      </div>
                    ),
                  }))}
                />
              </Card>
            </Col>
          )}

          {/* Audit Information */}
          <Col span={24}>
            <Card size="small" style={{ width: '100%', backgroundColor: '#fafafa' }}>
              <Descriptions size="small" column={2}>
                <Descriptions.Item label={this.props.adapter.gt('Created')}>
                  <Text type="secondary">
                    {travelRequest.created
                      ? Date.parse(travelRequest.created).toString('MMM dd, yyyy HH:mm')
                      : '-'}
                  </Text>
                </Descriptions.Item>
                <Descriptions.Item label={this.props.adapter.gt('Last Updated')}>
                  <Text type="secondary">
                    {travelRequest.updated
                      ? Date.parse(travelRequest.updated).toString('MMM dd, yyyy HH:mm')
                      : '-'}
                  </Text>
                </Descriptions.Item>
              </Descriptions>
            </Card>
          </Col>

          {/* Admin Actions */}
          {this.isAdmin() && (
            <Col span={24}>
              <Card size="small" style={{ width: '100%' }}>
                <Space style={{ width: '100%', justifyContent: 'flex-end' }}>
                  <Button
                    type="primary"
                    icon={<CheckOutlined />}
                    loading={this.state.loading}
                    onClick={() => this.handleStatusChange('Approved')}
                  >
                    {this.props.adapter.gt('Approve')}
                  </Button>
                  <Button
                    danger
                    icon={<CloseOutlined />}
                    loading={this.state.loading}
                    onClick={() => this.handleStatusChange('Rejected')}
                  >
                    {this.props.adapter.gt('Reject')}
                  </Button>
                </Space>
              </Card>
            </Col>
          )}
        </Row>
      </>
    );
  }
}

export default TravelRequestView;
