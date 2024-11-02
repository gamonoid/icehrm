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
  Tabs,
  Skeleton
} from 'antd';
import {
  EditOutlined,
  PhoneTwoTone,
  MailTwoTone,
  SyncOutlined,
  LockOutlined, SecurityScanTwoTone, CloudUploadOutlined, CompassTwoTone, ClockCircleTwoTone
} from '@ant-design/icons';
import TagList from "../../../../components/TagList";
import EmployeeStatusDashboard from "../../../../components/EmployeeStatusDashboard";
import UpdatePasswordModal from "../../../../components/UpdatePasswordModal";
const { Title, Text } = Typography;
const { TabPane } = Tabs;

class EmployeeProfile extends React.Component {
  state = {
    loading: true,
    showPasswordResetModal: false,
  };

  constructor(props) {
    super(props);
  }

  setLoading(value) {
    this.setState({ loading: value });
  }

  setShowPasswordUpdate(value) {
    this.setState({ showPasswordResetModal: value });
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

  getEditButtonJsx() {
    return (<>
      {this.state.loading &&
      <Tag icon={<SyncOutlined spin/>} color="processing">
        {this.props.adapter.gt('Edit')}
      </Tag>
      }
      {!this.state.loading &&
      <Tag icon={<EditOutlined/>} color="processing"
           onClick={() => modJs.edit(this.props.element.id)}>
        {this.props.adapter.gt('Edit')}
      </Tag>
      }
    </>);
  }

  getEditButtonJsxWithPassword() {
    return (<>
      {this.state.loading &&
      <Tag icon={<SyncOutlined spin/>} color="processing">
        {this.props.adapter.gt('Edit')}
      </Tag>
      }
      {!this.state.loading &&
      <Tag icon={<EditOutlined/>} color="processing"
           onClick={() => modJs.edit(this.props.element.id)}>
        {this.props.adapter.gt('Edit')}
      </Tag>
      }
      <Tag icon={<CloudUploadOutlined/>} color="green" onClick={() => this.updateProfileImage()}>
        {this.props.adapter.gt('Update Profile Image')}
      </Tag>
      <Tag icon={<LockOutlined/>} color="volcano" onClick={() => this.setShowPasswordUpdate(true)}>
      {this.props.adapter.gt('Update Password')}
      </Tag>
    </>);
  }

  getUpdatePasswordButtonJsx() {
    return (<>
      <Tag icon={<SyncOutlined spin/>} color="processing">
        {this.props.adapter.gt('Update Password')}
      </Tag>
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
    const gm = (text) => adapter.getMappedText(text);
    if (this.state.loading || !this.props.element) {
      return <div style={{padding: '20px'}}><Skeleton active /></div>
    }
    const employee = this.props.element;
    return (
      <>
      <UpdatePasswordModal
          visible={this.state.showPasswordResetModal}
          closeModal={() => {this.setState({ showPasswordResetModal: false })}}
          adapter={this.props.adapter}
      />
      <Row direction="vertical" style={{width: '100%', padding: '10px'}} gutter={24}>
        <Col span={24}>
          <Card title={this.props.adapter.gt('Employee Profile')}
                extra={this.getEditButtonJsxWithPassword()}
                style={{ width: '100%' }}
          >
            <Space size={'large'}>
              <Avatar size={140} src={this.props.element.image} onClick={() => this.updateProfileImage()}/>
              <Space direction={'vertical'}>
                <Title level={4}>{`${this.props.element.first_name} ${this.props.element.last_name}`}</Title>
                <Space>
                  <PhoneTwoTone />
                  <Text copyable>{` ${this.props.element.mobile_phone || ''}`}</Text>
                </Space>
                <Space>
                  <MailTwoTone />
                  <Text copyable>{` ${this.props.element.email || ''}`}</Text>
                </Space>
                <Space>
                  <SecurityScanTwoTone />
                  <Text strong>{adapter.gt(gm('Employee Number'))+' : '}</Text><Text copyable>{` ${employee.employee_id || ''}`}</Text>
                </Space>
                <Space>
                  <CompassTwoTone />
                  <Text strong>{adapter.gt(gm('Timezone'))+' : '}</Text><Text>{` ${employee.timezone || ''}`}</Text>
                </Space>
                {employee.current_time &&
                <Space>
                  <>
                      <ClockCircleTwoTone />
                      <Text strong>{` Time now:`}</Text>
                      <>
                        <Text keyboard>{Date.parse(employee.current_time).toString('yyyy MMM d')}</Text>
                        {' : '}
                        <Text keyboard>{Date.parse(employee.current_time).toString('HH:mm')}</Text>
                      </>
                    </>
                </Space>
                }
              </Space>
              <EmployeeStatusDashboard
                adapter={this.props.adapter}
                apiClient={this.props.adapter.apiClient}
                employee={this.props.element.id}
              />
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
                    <Text strong>{employee.nationality_Name}</Text>
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
                    <Text strong>{employee.country_Name}</Text>
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
                    <Text strong>{employee.job_title_Name}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Employment Status'))}</Text>}>
                    <Text strong>{employee.employment_status_Name}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Department'))}</Text>}>
                    <Text strong>{employee.department_Name}</Text>
                  </Descriptions.Item>
                  <Descriptions.Item label={<Text underline>{adapter.gt(gm('Manager'))}</Text>}>
                    <Text strong>{employee.supervisor_Name}</Text>
                  </Descriptions.Item>
                </Descriptions>
              </Card>
            </Col>
          </Row>
        </TabPane>
        <TabPane tab={this.props.adapter.gt('Qualifications')} key="2" style={{ width: '100%' }}>
          <Row style={{width: '100%', padding: '10px'}} gutter={24}>
            <Col span={6}>
              <Card title={this.props.adapter.gt('Skills')}
                    // extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeSkill')}
                    style={{ width: '100%' }}
              >
                <TagList
                  color="geekblue"
                  apiClient={this.props.adapter.apiClient}
                  url={`employees/${this.props.element.id}/skills`}
                  extractTag = {(item) => item.skill_id.display}
                />
              </Card>
            </Col>
            <Col span={6}>
              <Card title={this.props.adapter.gt('Education')}
                    // extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeEducation')}
                    style={{ width: '100%' }}
              >
                <TagList
                  color="cyan"
                  apiClient={this.props.adapter.apiClient}
                  url={`employees/${this.props.element.id}/educations`}
                  extractTag = {(item) => item.education_id.display}
                />
              </Card>
            </Col>
            <Col span={6}>
              <Card title={this.props.adapter.gt('Certifications')}
                    // extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeCertification')}
                    style={{ width: '100%' }}
              >
                <TagList
                  color="volcano"
                  apiClient={this.props.adapter.apiClient}
                  url={`employees/${this.props.element.id}/certifications`}
                  extractTag = {(item) => item.certification_id.display}
                />
              </Card>
            </Col>
            <Col span={6}>
              <Card title={this.props.adapter.gt('Languages')}
                    // extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeLanguage')}
                    style={{ width: '100%' }}
              >
                <TagList
                  color="orange"
                  apiClient={this.props.adapter.apiClient}
                  url={`employees/${this.props.element.id}/languages`}
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
