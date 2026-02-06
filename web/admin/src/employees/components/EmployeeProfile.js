import React, {Component} from 'react';
import {Col, Card, Skeleton, Avatar, Input, Row, Descriptions, Typography, Table, Space, Button, Tag, message, Tabs, Alert, Menu, Statistic} from 'antd';
import {
  FilterOutlined,
  EditOutlined,
  PhoneTwoTone,
  MailTwoTone,
  SyncOutlined,
  DownOutlined,
  SecurityScanTwoTone,
  ClockCircleTwoTone,
  CompassTwoTone,
  IdcardTwoTone,
  CalendarOutlined,
  CheckCircleOutlined,
  ClockCircleOutlined,
  CloseCircleOutlined,
  StopOutlined,
  TeamOutlined,
} from '@ant-design/icons';
import TagList from "../../../../components/TagList";
const { Search } = Input;
const { Title, Text } = Typography;
const { TabPane } = Tabs;

class EmployeeProfile extends React.Component {
  state = {
    loading: true,
    employee: null,
    leaves: [],
    leaveSummary: null,
    leavesLoading: true,
    attendance: [],
    attendanceSummary: null,
    attendanceLoading: true,
  };

  constructor(props) {
    super(props);
    this.setState({employee: props.element});
  }

  componentDidMount() {
    if (this.props.element?.id) {
      this.fetchLeaveData(this.props.element.id);
      this.fetchAttendanceData(this.props.element.id);
    }
  }

  componentDidUpdate(prevProps) {
    if (this.props.element?.id && this.props.element.id !== prevProps.element?.id) {
      this.fetchLeaveData(this.props.element.id);
      this.fetchAttendanceData(this.props.element.id);
    }
  }

  fetchLeaveData(employeeId) {
    this.setState({ leavesLoading: true });

    const apiClient = this.props.adapter?.apiClient || this.props.apiClient;
    if (!apiClient) {
      this.setState({ leavesLoading: false });
      return;
    }

    // Fetch leaves
    apiClient.get(`employees/${employeeId}/leaves`)
      .then((response) => {
        this.setState({ leaves: response.data.data || [] });
      })
      .catch(() => {
        this.setState({ leaves: [] });
      });

    // Fetch summary
    apiClient.get(`employees/${employeeId}/leaves/summary`)
      .then((response) => {
        this.setState({ leaveSummary: response.data, leavesLoading: false });
      })
      .catch(() => {
        this.setState({ leaveSummary: null, leavesLoading: false });
      });
  }

  fetchAttendanceData(employeeId) {
    this.setState({ attendanceLoading: true });

    const apiClient = this.props.adapter?.apiClient || this.props.apiClient;
    if (!apiClient) {
      this.setState({ attendanceLoading: false });
      return;
    }

    // Fetch attendance
    apiClient.get(`employees/${employeeId}/attendance`)
      .then((response) => {
        this.setState({ attendance: response.data.data || [] });
      })
      .catch(() => {
        this.setState({ attendance: [] });
      });

    // Fetch summary
    apiClient.get(`employees/${employeeId}/attendance/summary`)
      .then((response) => {
        this.setState({ attendanceSummary: response.data, attendanceLoading: false });
      })
      .catch(() => {
        this.setState({ attendanceSummary: null, attendanceLoading: false });
      });
  }

  updateProfileImage() {
    showUploadDialog(
      `profile_image_${this.props.element.id}_${(new Date()).getTime()}`,
      'Upload Profile Image',
      'profile_image',
      this.props.element.id,
      `profile_image_${this.props.element.id}`,
      'function',
      'reloadCurrentElement',
      'image'
    );
  }

  reloadEmployee(id) {
    let url;
    if (id == null) {
      url = `employees/${this.props.element.id}`;
    } else {
      url = `employees/${id}`;
    }
    this.props.apiClient.get(url)
      .then((response) => {
        this.setState({employee: response.data});
        this.props.setEmployee(response.data);
      });
  }

  getEditButtonJsx() {
    return (<>
      {this.props.loading &&
      <Tag icon={<SyncOutlined spin/>} color="processing">
        {this.props.adapter.gt('Edit')}
      </Tag>
      }
      {!this.props.loading &&
      <Tag icon={<EditOutlined/>} color="processing"
           onClick={() => modJs.edit(this.props.element.id)}>
        {this.props.adapter.gt('Edit')}
      </Tag>
      }
    </>);
  }

  getTabViewEmployeeFilterButtonJsx(tab) {
    return (
      <Tag icon={<EditOutlined/>} color="processing"
           onClick={() => {switchTab(tab, {employee: this.props.element.id})}}>
        {this.props.adapter.gt('Edit')}
      </Tag>
    );
  }

  render() {
    const { adapter } = this.props;
    let employee = this.state.employee;
    if (employee == null || employee.id != this.props.element.id) {
      employee = this.props.element;
    }
    const gm = (text) => adapter.getMappedText(text);

    if (!employee || !employee.id ) {
      return (
        <Row direction="vertical" style={{width: '100%', padding: '10px'}} gutter={24}>
          <Col span={24}>
            <Card title={<SyncOutlined spin />}
                  style={{ width: '100%' }}
            >
              <Skeleton active />
            </Card>
          </Col>
        </Row>
      );
    }

    return (
      <>
      <Row direction="vertical" style={{width: '100%', padding: '10px'}} gutter={24}>
        <Col span={24}>
          <Card title={''}
                extra={this.props.adapter.getTableActionButtonJsx(this.props.adapter,this.props.element.id, this.props.loading)}
                style={{ width: '100%' }}
          >
            { 0 === employee.can_login && adapter?.user?.user_level === 'Admin' &&
            <Alert
                message={adapter.gt(gm("This employee can't login as there is no user added for this employee."))}
                showIcon
                type="warning"
                action={
                  <Space>
                    <Button size="small" type="ghost" onClick={() => {this.props.adapter.createUser(this.props.element);}}>
                      Create a User
                    </Button>
                  </Space>
                }
                style={{ width: '100%', marginBottom: '15px' }}
            />
            }
            <Space size={'large'}>
              <Avatar size={140} src={employee.image} onClick={() => this.updateProfileImage()}/>
              <Space direction={'vertical'}>
                <Title level={4}>{`${employee.first_name} ${employee.last_name}`}
                </Title>
                <Space>
                  <PhoneTwoTone />
                  <Text copyable>{` ${employee.mobile_phone || ''}`}</Text>
                </Space>
                <Space>
                  <MailTwoTone />
                  <Text copyable>{` ${employee.email || ''}`}</Text>
                </Space>
                <Space>
                  <SecurityScanTwoTone />
                  <Text strong>{adapter.gt(gm('Employee Number'))+' : '}</Text><Text copyable>{` ${employee.employee_id || ''}`}</Text>
                </Space>
                <Space>
                  <CompassTwoTone />
                  <Text strong>{adapter.gt(gm('Timezone'))+' : '}</Text><Text>{` ${employee.timezone || ''}`}</Text>
                  {employee.current_time &&
                    <>
                      <ClockCircleTwoTone />
                      <Text strong>{` Time now:`}</Text>
                      <>
                        <Text keyboard>{Date.parse(employee.current_time).toString('yyyy MMM d')}</Text>
                        {' : '}
                        <Text keyboard>{Date.parse(employee.current_time).toString('HH:mm')}</Text>
                      </>
                    </>
                  }
                </Space>
                { 1 === employee.can_login &&
                    <Space>
                      <IdcardTwoTone />
                      <Text strong>{adapter.gt(gm('Access Level'))+' : '}</Text><Text keyboard>{employee.user_level}</Text>
                    </Space>
                }
                { 0 === employee.can_login && adapter?.user?.user_level === 'Admin' &&
                    <Space>
                      <IdcardTwoTone />
                      <Text strong>{adapter.gt(gm('Access Level'))+' : '}</Text><Tag onClick={() => {this.props.adapter.createUser(this.props.element);}} color="orange" style={{ cursor: 'pointer' }}>{` ${adapter.gt('Create a User for this Employee')}`}</Tag>
                    </Space>
                }
                {employee.supervisor &&
                    <Space align="center">
                      <TeamOutlined style={{ color: '#1890ff', fontSize: '16px' }} />
                      <Text strong>{adapter.gt(gm('Manager'))+' : '}</Text>
                      <Avatar size={28} src={employee.supervisor?.image} style={{ marginLeft: 4 }} />
                      <Text>{employee.supervisor?.display}</Text>
                    </Space>
                }

              </Space>
            </Space>
          </Card>
        </Col>
      </Row>
    <Row direction="vertical" style={{width: '100%', padding: '10px'}} gutter={24}>
      <Tabs type="card" style={{ width: '100%' }}>
        <TabPane tab="Basic Information" key="1" style={{ width: '100%' }}>
          <Row direction="vertical" style={{width: '100%', padding: '10px'}} gutter={24}>
            <Col span={24}>
              <Card title={this.props.adapter.gt('Personal Information')}
                    extra={this.getEditButtonJsx()}
                    style={{ width: '100%' }}
              >
                <Descriptions title="" size='small'>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Date of Birth'))}</Text>}>
                    <Text strong>{employee.birthday || ''}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Gender'))}</Text>}>
                    <Text strong>{employee.gender}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Nationality'))}</Text>}>
                    <Text strong>{employee.nationality?.display}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Marital Status'))}</Text>}>
                    <Text strong>{employee.marital_status}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Joined Date'))}</Text>}>
                    <Text strong>{employee.joined_date}</Text>
                  </Descriptions.Item>
                </Descriptions>
              </Card>
            </Col>
            <Col span={24}>
              <Card title={this.props.adapter.gt('Identification')}
                    extra={this.getEditButtonJsx()}
                    style={{ width: '100%' }}
              >
                <Descriptions title="" size='small'>
                <Descriptions.Item label={<Text underline>{adapter.gt(gm('National ID'))}</Text>}>
                  <Text strong>{employee.nic_num || ''}</Text>
                </Descriptions.Item>
                <Descriptions.Item label={<Text underline>{adapter.gt(gm('Social Insurance'))}</Text>}>
                  <Text strong>{employee.ssn_num || ''}</Text>
                </Descriptions.Item>
                <Descriptions.Item label={<Text underline>{adapter.gt(gm('Personal Tax ID'))}</Text>}>
                  <Text strong>{employee.tax_id || ''}</Text>
                </Descriptions.Item>
                <Descriptions.Item label={<Text underline>{adapter.gt(gm('Health Insurance'))}</Text>}>
                  <Text strong>{employee.health_insurance || ''}</Text>
                </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Additional IDs'))}</Text>}>
                    <Text strong>{employee.other_id || ''}</Text>
                  </Descriptions.Item>
                <Descriptions.Item label={<Text underline>{adapter.gt(gm('Driving License'))}</Text>}>
                  <Text strong>{employee.driving_license || ''}</Text>
                </Descriptions.Item>
                </Descriptions>
              </Card>
            </Col>
            <Col span={24}>
              <Card title={this.props.adapter.gt('Contact Information')}
                    extra={this.getEditButtonJsx()}
                    style={{ width: '100%' }}
              >
                <Descriptions title="" size='small'>
                  <Descriptions.Item label={<Text underline>{adapter.gt('Address')}</Text>} span={3}>
                    <Text strong>{`${employee.address1}, ${employee.address2 || ''}`}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('City'))}</Text>}>
                    <Text strong>{employee.city}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Country'))}</Text>}>
                    <Text strong>{employee.country?.display}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Postal/Zip Code'))}</Text>}>
                    <Text strong>{employee.postal_code}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Home Phone'))}</Text>}>
                    <Space>
                      <PhoneTwoTone />
                      <Text strong copyable>{` ${employee.home_phone || ''}`}</Text>
                    </Space>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Work Phone'))}</Text>}>
                    <Space>
                      <PhoneTwoTone />
                      <Text strong copyable>{` ${employee.work_phone || ''}`}</Text>
                    </Space>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Private Email'))}</Text>}>
                    <Space>
                      <MailTwoTone />
                      <Text strong copyable>{` ${employee.private_email || ''}`}</Text>
                    </Space>
                  </Descriptions.Item>
                </Descriptions>
              </Card>
            </Col>
            <Col span={24}>
              <Card title={this.props.adapter.gt('Job Details')}
                    extra={this.getEditButtonJsx()}
                    style={{ width: '100%' }}
              >
                <Descriptions title="" size='small'>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Job Title'))}</Text>}>
                    <Text strong>{employee.job_title?.display}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Employment Status'))}</Text>}>
                    <Text strong>{employee.employment_status?.display}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Department'))}</Text>}>
                    <Text strong>{employee.department?.display}</Text>
                  </Descriptions.Item>
                </Descriptions>
              </Card>
            </Col>
            {employee.customFields && Object.keys(employee.customFields).length > 0 &&
              <Col span={24}>
                <Card title={this.props.adapter.gt('Other Information')}
                      extra={this.getEditButtonJsx()}
                      style={{ width: '100%' }}
                >
                  <Descriptions title="" size='small'>
                    {Object.keys(employee.customFields).map((key, index) => {
                      return (
                        <Descriptions.Item label={<Text underline>{adapter.gt(key)}</Text>} key={index}>
                          <Text strong>{employee.customFields[key][0]}</Text>
                        </Descriptions.Item>
                      )
                    })}
                  </Descriptions>
                </Card>
              </Col>
            }
          </Row>
        </TabPane>
        <TabPane tab={<span><ClockCircleOutlined style={{ marginRight: 8 }} />{this.props.adapter.gt('Attendance')}</span>} key="2" style={{ width: '100%' }}>
          <Row style={{width: '100%', padding: '10px'}} gutter={24}>
            {this.state.attendanceLoading ? (
              <Col span={24}>
                <Card>
                  <Skeleton active />
                </Card>
              </Col>
            ) : (
              <>
                <Col span={24}>
                  <Card title={this.props.adapter.gt('Attendance Summary')} style={{ width: '100%', marginBottom: '16px' }}>
                    <Row gutter={16}>
                      <Col span={6}>
                        <Statistic
                          title={this.props.adapter.gt('Total Records')}
                          value={this.state.attendanceSummary?.total || 0}
                          prefix={<ClockCircleOutlined />}
                        />
                      </Col>
                      <Col span={6}>
                        <Statistic
                          title={this.props.adapter.gt('This Month')}
                          value={this.state.attendanceSummary?.thisMonth || 0}
                          valueStyle={{ color: '#1890ff' }}
                          prefix={<CalendarOutlined />}
                        />
                      </Col>
                      <Col span={6}>
                        <Statistic
                          title={this.props.adapter.gt('Total Hours')}
                          value={this.state.attendanceSummary?.totalHours || 0}
                          valueStyle={{ color: '#3f8600' }}
                          suffix="hrs"
                        />
                      </Col>
                      <Col span={6}>
                        <Statistic
                          title={this.props.adapter.gt('Avg Hours/Day')}
                          value={this.state.attendanceSummary?.avgHours || 0}
                          valueStyle={{ color: '#722ed1' }}
                          suffix="hrs"
                        />
                      </Col>
                    </Row>
                  </Card>
                </Col>
                <Col span={24}>
                  <Card title={this.props.adapter.gt('Recent Attendance')} style={{ width: '100%' }}>
                    <Table
                      dataSource={this.state.attendance}
                      rowKey="id"
                      pagination={false}
                      size="small"
                      columns={[
                        {
                          title: this.props.adapter.gt('Date'),
                          dataIndex: 'in_time',
                          key: 'date',
                          render: (inTime) => inTime ? inTime.split(' ')[0] : '-',
                        },
                        {
                          title: this.props.adapter.gt('Punch In'),
                          dataIndex: 'in_time',
                          key: 'in_time',
                          render: (inTime) => inTime ? inTime.split(' ')[1] : '-',
                        },
                        {
                          title: this.props.adapter.gt('Punch Out'),
                          dataIndex: 'out_time',
                          key: 'out_time',
                          render: (outTime) => outTime ? outTime.split(' ')[1] : <Tag color="orange">Working</Tag>,
                        },
                        {
                          title: this.props.adapter.gt('Note'),
                          dataIndex: 'note',
                          key: 'note',
                          ellipsis: true,
                        },
                      ]}
                    />
                  </Card>
                </Col>
              </>
            )}
          </Row>
        </TabPane>
        <TabPane tab={<span><CalendarOutlined style={{ marginRight: 8 }} />{this.props.adapter.gt('Leave')}</span>} key="3" style={{ width: '100%' }}>
          <Row style={{width: '100%', padding: '10px'}} gutter={24}>
            {this.state.leavesLoading ? (
              <Col span={24}>
                <Card>
                  <Skeleton active />
                </Card>
              </Col>
            ) : (
              <>
                <Col span={24}>
                  <Card title={this.props.adapter.gt('Leave Summary')} style={{ width: '100%', marginBottom: '16px' }}>
                    <Row gutter={16}>
                      <Col span={4}>
                        <Statistic
                          title={this.props.adapter.gt('Total')}
                          value={this.state.leaveSummary?.total || 0}
                          prefix={<CalendarOutlined />}
                        />
                      </Col>
                      <Col span={5}>
                        <Statistic
                          title={this.props.adapter.gt('Approved')}
                          value={this.state.leaveSummary?.approved || 0}
                          valueStyle={{ color: '#3f8600' }}
                          prefix={<CheckCircleOutlined />}
                        />
                      </Col>
                      <Col span={5}>
                        <Statistic
                          title={this.props.adapter.gt('Pending')}
                          value={this.state.leaveSummary?.pending || 0}
                          valueStyle={{ color: '#faad14' }}
                          prefix={<ClockCircleOutlined />}
                        />
                      </Col>
                      <Col span={5}>
                        <Statistic
                          title={this.props.adapter.gt('Rejected')}
                          value={this.state.leaveSummary?.rejected || 0}
                          valueStyle={{ color: '#cf1322' }}
                          prefix={<CloseCircleOutlined />}
                        />
                      </Col>
                      <Col span={5}>
                        <Statistic
                          title={this.props.adapter.gt('Cancelled')}
                          value={this.state.leaveSummary?.cancelled || 0}
                          valueStyle={{ color: '#8c8c8c' }}
                          prefix={<StopOutlined />}
                        />
                      </Col>
                    </Row>
                  </Card>
                </Col>
                <Col span={24}>
                  <Card title={this.props.adapter.gt('Recent Leave Requests')} style={{ width: '100%' }}>
                    <Table
                      dataSource={this.state.leaves}
                      rowKey="id"
                      pagination={false}
                      size="small"
                      columns={[
                        {
                          title: this.props.adapter.gt('Leave Type'),
                          dataIndex: ['leave_type', 'display'],
                          key: 'leave_type',
                        },
                        {
                          title: this.props.adapter.gt('Start Date'),
                          dataIndex: 'date_start',
                          key: 'date_start',
                        },
                        {
                          title: this.props.adapter.gt('End Date'),
                          dataIndex: 'date_end',
                          key: 'date_end',
                        },
                        {
                          title: this.props.adapter.gt('Status'),
                          dataIndex: 'status',
                          key: 'status',
                          render: (status) => {
                            let color = 'default';
                            if (status === 'Approved') color = 'green';
                            else if (status === 'Pending') color = 'orange';
                            else if (status === 'Rejected') color = 'red';
                            else if (status === 'Cancelled' || status === 'Cancellation Requested') color = 'default';
                            return <Tag color={color}>{status}</Tag>;
                          },
                        },
                        {
                          title: this.props.adapter.gt('Details'),
                          dataIndex: 'details',
                          key: 'details',
                          ellipsis: true,
                        },
                      ]}
                    />
                  </Card>
                </Col>
              </>
            )}
          </Row>
        </TabPane>
        <TabPane tab={this.props.adapter.gt('Qualifications')} key="4" style={{ width: '100%' }}>
          <Row style={{width: '100%', padding: '10px'}} gutter={24}>
            <Col span={6}>
              <Card title={this.props.adapter.gt('Skills')}
                    extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeSkill')}
                    style={{ width: '100%' }}
              >
                <TagList
                  color="geekblue"
                  apiClient={this.props.adapter.apiClient}
                  url={`employees/${employee.id}/skills`}
                  extractTag = {(item) => item.skill_id.display}
                />
              </Card>
            </Col>
            <Col span={6}>
              <Card title={this.props.adapter.gt('Education')}
                    extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeEducation')}
                    style={{ width: '100%' }}
              >
                <TagList
                  color="cyan"
                  apiClient={this.props.adapter.apiClient}
                  url={`employees/${employee.id}/educations`}
                  extractTag = {(item) => item.education_id.display}
                />
              </Card>
            </Col>
            <Col span={6}>
              <Card title={this.props.adapter.gt('Certifications')}
                    extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeCertification')}
                    style={{ width: '100%' }}
              >
                <TagList
                  color="volcano"
                  apiClient={this.props.adapter.apiClient}
                  url={`employees/${employee.id}/certifications`}
                  extractTag = {(item) => item.certification_id.display}
                />
              </Card>
            </Col>
            <Col span={6}>
              <Card title={this.props.adapter.gt('Languages')}
                    extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeLanguage')}
                    style={{ width: '100%' }}
              >
                <TagList
                  color="orange"
                  apiClient={this.props.adapter.apiClient}
                  url={`employees/${employee.id}/languages`}
                  extractTag = {(item) => item.language_id.display}
                />
              </Card>
            </Col>
          </Row>
        </TabPane>
      </Tabs>
      </Row>
      </>
    )
  }
}

export default EmployeeProfile;
