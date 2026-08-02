import React, {Component} from 'react';
import {Col, Card, Skeleton, Avatar, Input, Row, Descriptions, Typography, Table, Space, Button, Tag, message, Tabs, Alert, Menu, Statistic, Modal, Upload, Empty} from 'antd';
import {
  FilterOutlined,
  EditOutlined,
  UploadOutlined,
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
  ReadOutlined,
  SolutionOutlined,
} from '@ant-design/icons';
import QualificationsPanel from './QualificationsPanel';
import ApprovalsPanel from './ApprovalsPanel';
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
    // Natively in the SPA shell the legacy iframe-based showUploadDialog has no
    // host modal, so use a React upload modal posting to the same backend
    // (fileupload-new.php with file_group=profile_image, which IS the profile
    // photo store read by FileService::updateProfileImage). In the legacy app
    // keep the original dialog.
    if (typeof window.__shellColorMode !== 'undefined') {
      this.setState({ photoModalOpen: true });
      return;
    }
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

  onPhotoUploadChange = (info) => {
    const f = info.file;
    if (f.status === 'done') {
      const resp = f.response;
      if (resp && resp.status === 'error') {
        message.error(resp.message || 'Upload failed');
        return;
      }
      message.success('Profile photo updated');
      this.setState({ photoModalOpen: false });
      this.reloadEmployee();
      if (this.props.adapter && this.props.adapter.reloadEmployeeList) {
        this.props.adapter.reloadEmployeeList();
      }
    } else if (f.status === 'error') {
      message.error('Upload failed');
    }
  };

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
           onClick={() => {
             const filter = { employee: this.props.element.id };
             // SPA shell: switch this module's antd tab (legacy switchTab clicks
             // a legacy tab anchor that doesn't exist there).
             if (typeof window.iceShellSwitchModuleTab === 'function') {
               window.iceShellSwitchModuleTab(tab, filter);
             } else {
               switchTab(tab, filter);
             }
           }}>
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
      // The directory finished loading with no rows (e.g. a manager with no
      // subordinates) — no employee will ever be auto-selected, so show a
      // message instead of a never-ending loading skeleton.
      if (this.props.listEmpty) {
        return (
          <Row direction="vertical" style={{width: '100%', padding: '10px'}}>
            <Col span={24}>
              <Card style={{ width: '100%' }}>
                <Empty description={adapter && adapter.gt ? adapter.gt('No employees found') : 'No employees found'} />
              </Card>
            </Col>
          </Row>
        );
      }
      return (
        <Row direction="vertical" style={{width: '100%', padding: '10px'}}>
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
      <Modal
        title={adapter.gt('Upload Profile Photo')}
        open={!!this.state.photoModalOpen}
        footer={null}
        onCancel={() => this.setState({ photoModalOpen: false })}
        destroyOnClose
      >
        <Upload
          name="file"
          accept="image/*"
          listType="picture"
          maxCount={1}
          action={`${window.CLIENT_BASE_URL}fileupload-new.php?user=${this.props.element.id}&file_group=profile_image&file_name=profile_${this.props.element.id}_${(new Date()).getTime()}`}
          onChange={this.onPhotoUploadChange}
        >
          <Button icon={<UploadOutlined />}>{adapter.gt('Select Image')}</Button>
        </Upload>
      </Modal>
      <Row direction="vertical" style={{width: '100%', padding: '10px'}}>
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
            <Space size={'large'} wrap>
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
                <Space wrap>
                  <CompassTwoTone />
                  <Text strong>{adapter.gt(gm('Timezone'))+' : '}</Text><Text style={{ whiteSpace: 'nowrap' }}>{` ${employee.timezone || ''}`}</Text>
                  {employee.current_time &&
                    <Space wrap size={4}>
                      <ClockCircleTwoTone />
                      <Text strong>{` Time now:`}</Text>
                      <Text keyboard style={{ whiteSpace: 'nowrap' }}>{Date.parse(employee.current_time).toString('yyyy MMM d')}</Text>
                      <Text keyboard style={{ whiteSpace: 'nowrap' }}>{Date.parse(employee.current_time).toString('HH:mm')}</Text>
                    </Space>
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
    <Row direction="vertical" style={{width: '100%'}}>
      <Tabs type="card" style={{ width: '100%' }} tabBarStyle={{ marginLeft: 10, marginRight: 10 }}>
        <TabPane tab="Basic Information" key="1" style={{ width: '100%' }}>
          <Row direction="vertical" style={{width: '100%', padding: '10px'}} gutter={[0, 16]}>
            <Col span={24}>
              <Card title={this.props.adapter.gt('Personal Information')}
                    extra={this.getEditButtonJsx()}
                    style={{ width: '100%' }}
              >
                <Descriptions title="" size='small' column={{ xs: 1, sm: 2, md: 3 }}>
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
                <Descriptions title="" size='small' column={{ xs: 1, sm: 2, md: 3 }}>
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
                <Descriptions title="" size='small' column={{ xs: 1, sm: 2, md: 3 }}>
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
                <Descriptions title="" size='small' column={{ xs: 1, sm: 2, md: 3 }}>
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
                  <Descriptions title="" size='small' column={{ xs: 1, sm: 2, md: 3 }}>
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
          <Row style={{width: '100%', padding: '10px'}}>
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
                      <Col xs={12} sm={6}>
                        <Statistic
                          title={this.props.adapter.gt('Total Records')}
                          value={this.state.attendanceSummary?.total || 0}
                          prefix={<ClockCircleOutlined />}
                        />
                      </Col>
                      <Col xs={12} sm={6}>
                        <Statistic
                          title={this.props.adapter.gt('This Month')}
                          value={this.state.attendanceSummary?.thisMonth || 0}
                          valueStyle={{ color: '#1890ff' }}
                          prefix={<CalendarOutlined />}
                        />
                      </Col>
                      <Col xs={12} sm={6}>
                        <Statistic
                          title={this.props.adapter.gt('Total Hours')}
                          value={this.state.attendanceSummary?.totalHours || 0}
                          valueStyle={{ color: '#3f8600' }}
                          suffix="hrs"
                        />
                      </Col>
                      <Col xs={12} sm={6}>
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
                      scroll={{ x: 'max-content' }}
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
          <Row style={{width: '100%', padding: '10px'}}>
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
                      <Col xs={12} sm={4}>
                        <Statistic
                          title={this.props.adapter.gt('Total')}
                          value={this.state.leaveSummary?.total || 0}
                          prefix={<CalendarOutlined />}
                        />
                      </Col>
                      <Col xs={12} sm={5}>
                        <Statistic
                          title={this.props.adapter.gt('Approved')}
                          value={this.state.leaveSummary?.approved || 0}
                          valueStyle={{ color: '#3f8600' }}
                          prefix={<CheckCircleOutlined />}
                        />
                      </Col>
                      <Col xs={12} sm={5}>
                        <Statistic
                          title={this.props.adapter.gt('Pending')}
                          value={this.state.leaveSummary?.pending || 0}
                          valueStyle={{ color: '#faad14' }}
                          prefix={<ClockCircleOutlined />}
                        />
                      </Col>
                      <Col xs={12} sm={5}>
                        <Statistic
                          title={this.props.adapter.gt('Rejected')}
                          value={this.state.leaveSummary?.rejected || 0}
                          valueStyle={{ color: '#cf1322' }}
                          prefix={<CloseCircleOutlined />}
                        />
                      </Col>
                      <Col xs={12} sm={5}>
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
                      scroll={{ x: 'max-content' }}
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
        <TabPane tab={<span><ReadOutlined style={{ marginRight: 8 }} />{this.props.adapter.gt('Qualifications')}</span>} key="4" style={{ width: '100%' }}>
          <QualificationsPanel
            key={employee.id}
            apiClient={this.props.adapter.apiClient}
            employeeId={employee.id}
            gt={(s) => this.props.adapter.gt(s)}
            editExtra={(tab) => this.getTabViewEmployeeFilterButtonJsx(tab)}
          />
        </TabPane>
        {this.props.adapter.multiLevelApprovals &&
          <TabPane tab={<span><SolutionOutlined style={{ marginRight: 8 }} />{this.props.adapter.gt('Approvals')}</span>} key="approvals" style={{ width: '100%' }}>
            <Row direction="vertical" style={{ width: '100%', padding: '10px' }} gutter={[0, 16]}>
              <Col span={24}>
                <ApprovalsPanel
                  key={employee.id}
                  employee={employee}
                  adapter={this.props.adapter}
                  apiClient={this.props.apiClient}
                  onSaved={() => this.reloadEmployee()}
                />
              </Col>
            </Row>
          </TabPane>
        }
      </Tabs>
      </Row>
      </>
    )
  }
}

export default EmployeeProfile;
