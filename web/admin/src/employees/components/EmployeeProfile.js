import React, {Component} from 'react';
import {Col, Card, Skeleton, Avatar, Input, Row, Descriptions, Typography, Table, Space, Button, Tag, message, Tabs} from 'antd';
import {
  FilterOutlined,
  EditOutlined,
  PhoneTwoTone,
  MailTwoTone,
  SyncOutlined,
} from '@ant-design/icons';
import TagList from "../../../../components/TagList";
const { Search } = Input;
const { Title, Text } = Typography;
const { TabPane } = Tabs;

class EmployeeProfile extends React.Component {
  state = {
    loading: true,
  };

  constructor(props) {
    super(props);
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
    return (
      <>
      <Row direction="vertical" style={{width: '100%', padding: '10px'}} gutter={24}>
        <Col span={24}>
          <Card title={this.props.adapter.gt('Employee Profile')}
                extra={this.getEditButtonJsx()}
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
                  <Text copyable>{` ${this.props.element.work_email || ''}`}</Text>
                </Space>
              </Space>
              <Descriptions title="" bordered style={{width: '100%', padding: '10px'}}>
                <Descriptions.Item label={this.props.adapter.gt('Employee Number')} span={3}>
                  {this.props.element.employee_id}
                </Descriptions.Item>
                <Descriptions.Item label={this.props.adapter.gt('ID Number')} span={3}>
                  {this.props.element.nic_num || ''}
                </Descriptions.Item>
                {this.props.element.ssn_num && this.props.element.ssn_num !== '' &&
                <Descriptions.Item label={this.props.adapter.gt('Social Security Number')} span={3}>
                  {this.props.element.ssn_num || ''}
                </Descriptions.Item>
                }
              </Descriptions>
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
                <Descriptions title="" bordered>
                  <Descriptions.Item label={this.props.adapter.gt('Date of Birth')}>
                    {this.props.element.birthday || ''}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Gender')}>
                    {this.props.element.gender}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Nationality')}>
                    {this.props.element.nationality_Name}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Marital Status')}>
                    {this.props.element.marital_status}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Joined Date')}>
                    {this.props.element.joined_date}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Driving License No')}>
                    {this.props.element.driving_license || ''}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Other ID')}>
                    {this.props.element.other_id || ''}
                  </Descriptions.Item>
                </Descriptions>
              </Card>
            </Col>
            <Col span={24}>
              <Card title={this.props.adapter.gt('Contact Information')}
                    extra={this.getEditButtonJsx()}
                    style={{ width: '100%' }}
              >
                <Descriptions title="" bordered>
                  <Descriptions.Item label={this.props.adapter.gt('Address')} span={3}>
                    {`${this.props.element.address1}, ${this.props.element.address2 || ''}`}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('City')}>
                    {this.props.element.city}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Country')}>
                    {this.props.element.country_Name}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Postal/Zip Code')}>
                    {this.props.element.postal_code}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Home Phone')} span={2}>
                    <Space>
                      <PhoneTwoTone />
                      <Text copyable>{` ${this.props.element.home_phone || ''}`}</Text>
                    </Space>
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Work Phone')} span={2}>
                    <Space>
                      <PhoneTwoTone />
                      <Text copyable>{` ${this.props.element.work_phone || ''}`}</Text>
                    </Space>
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Private Email')} span={2}>
                    <Space>
                      <MailTwoTone />
                      <Text copyable>{` ${this.props.element.private_email || ''}`}</Text>
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
                <Descriptions title="" bordered>
                  <Descriptions.Item label={this.props.adapter.gt('Job Title')} span={2}>
                    {this.props.element.job_title_Name}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Employment Status')}>
                    {this.props.element.employment_status_Name}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Department')}>
                    {this.props.element.department_Name}
                  </Descriptions.Item>
                  <Descriptions.Item label={this.props.adapter.gt('Supervisor')}>
                    {this.props.element.supervisor_Name}
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
                    extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeSkill')}
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
                    extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeEducation')}
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
                    extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeCertification')}
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
                    extra={this.getTabViewEmployeeFilterButtonJsx('tabEmployeeLanguage')}
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
