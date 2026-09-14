import React from 'react';
import {
  Card,
  Avatar,
  Typography,
  Space,
  Tag,
  Skeleton,
  Table,
  Modal,
  Upload,
  Button,
  message,
  Row,
  Col,
  Empty,
  Tooltip,
  Segmented,
  ConfigProvider,
  Popconfirm,
} from 'antd';
import {
  EditOutlined,
  LockOutlined,
  CloudUploadOutlined,
  UploadOutlined,
  CameraOutlined,
  DeleteOutlined,
  PhoneOutlined,
  MailOutlined,
  IdcardOutlined,
  GlobalOutlined,
  ClockCircleOutlined,
  CalendarOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
  StopOutlined,
  TeamOutlined,
  EnvironmentOutlined,
  SolutionOutlined,
  ReadOutlined,
  ProfileOutlined,
  SafetyCertificateOutlined,
  UserOutlined,
  ContactsOutlined,
  ApartmentOutlined,
} from '@ant-design/icons';
import TagList from '../../../../components/TagList';
import EmployeeStatus from '../../../../components/EmployeeStatus';
import UpdatePasswordModal from '../../../../components/UpdatePasswordModal';

const { Title, Text } = Typography;

const isEmpty = (v) => v === null || v === undefined || v === '' || v === '-';

// Format a "YYYY-MM-DD HH:mm:ss" string without depending on datejs.
function fmtDate(str) {
  if (!str) return '';
  const d = new Date(String(str).replace(' ', 'T'));
  if (Number.isNaN(d.getTime())) return str;
  return d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
}
function fmtTime(str) {
  if (!str) return '';
  const d = new Date(String(str).replace(' ', 'T'));
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
}

const LEAVE_STATUS_COLORS = {
  Approved: 'green',
  Pending: 'orange',
  Rejected: 'red',
  Cancelled: 'default',
  'Cancellation Requested': 'default',
};

/**
 * Reimagined, mobile-first self-service profile for modules::employees.
 * Same data + actions as the original (edit modal, photo upload, password,
 * status/goal, attendance, leave, qualifications) with a responsive layout.
 * Kept as a class so the legacy adapter's ref contract (setLoading) still works.
 */
class EmployeeProfile extends React.Component {
  state = {
    loading: true,
    showPasswordResetModal: false,
    photoModalOpen: false,
    leaves: [],
    leaveSummary: null,
    leavesLoading: true,
    attendance: [],
    attendanceSummary: null,
    attendanceLoading: true,
    section: 'Basic',
    isMobile: typeof window !== 'undefined' && window.innerWidth < 768,
  };

  constructor(props) {
    super(props);
    // Derive the initial loading state from whether we already have the
    // employee. A dark/light theme toggle swaps the shellThemeWrap wrapper type,
    // which remounts this component fresh — if we always started "loading" it
    // would get stuck on the skeleton (the post-render setLoading never landed).
    if (props.element) this.state = { ...this.state, loading: false };
  }

  componentDidMount() {
    if (this.props.element?.id) {
      this.fetchLeaveData(this.props.element.id);
      this.fetchAttendanceData(this.props.element.id);
    }
    this.handleResize = () => {
      const isMobile = window.innerWidth < 768;
      if (isMobile !== this.state.isMobile) this.setState({ isMobile });
    };
    window.addEventListener('resize', this.handleResize);
  }

  componentDidUpdate(prevProps) {
    if (this.props.element?.id && this.props.element.id !== prevProps.element?.id) {
      this.fetchLeaveData(this.props.element.id);
      this.fetchAttendanceData(this.props.element.id);
    }
  }

  componentWillUnmount() {
    if (this.handleResize) window.removeEventListener('resize', this.handleResize);
  }

  fetchLeaveData(employeeId) {
    this.setState({ leavesLoading: true });
    const apiClient = this.props.adapter?.apiClient;
    if (!apiClient) { this.setState({ leavesLoading: false }); return; }
    apiClient.get(`employees/${employeeId}/leaves`)
      .then((r) => this.setState({ leaves: r.data.data || [] }))
      .catch(() => this.setState({ leaves: [] }));
    apiClient.get(`employees/${employeeId}/leaves/summary`)
      .then((r) => this.setState({ leaveSummary: r.data, leavesLoading: false }))
      .catch(() => this.setState({ leaveSummary: null, leavesLoading: false }));
  }

  fetchAttendanceData(employeeId) {
    this.setState({ attendanceLoading: true });
    const apiClient = this.props.adapter?.apiClient;
    if (!apiClient) { this.setState({ attendanceLoading: false }); return; }
    apiClient.get(`employees/${employeeId}/attendance`)
      .then((r) => this.setState({ attendance: r.data.data || [] }))
      .catch(() => this.setState({ attendance: [] }));
    apiClient.get(`employees/${employeeId}/attendance/summary`)
      .then((r) => this.setState({ attendanceSummary: r.data, attendanceLoading: false }))
      .catch(() => this.setState({ attendanceSummary: null, attendanceLoading: false }));
  }

  setLoading(value) { this.setState({ loading: value }); }

  setShowPasswordUpdate(value) { this.setState({ showPasswordResetModal: value }); }

  edit() {
    // Opens the legacy employee edit (steps) modal.
    if (typeof modJs !== 'undefined' && modJs.edit) modJs.edit(this.props.element.id);
  }

  updateProfileImage() {
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
      'image',
    );
  }

  onPhotoUploadChange = (info) => {
    const f = info.file;
    if (f.status === 'done') {
      const resp = f.response;
      if (resp && resp.status === 'error') { message.error(resp.message || 'Upload failed'); return; }
      message.success('Profile photo updated');
      this.setState({ photoModalOpen: false });
      if (this.props.adapter && this.props.adapter.viewElement) this.props.adapter.viewElement();
    } else if (f.status === 'error') {
      message.error('Upload failed');
    }
  };

  // --- small render helpers -------------------------------------------------
  field = (label, value, copyable = false) => (
    <div style={{ marginBottom: 16 }}>
      <Text type="secondary" style={{ fontSize: 12, display: 'block', marginBottom: 2 }}>{label}</Text>
      {isEmpty(value)
        ? <Text type="secondary" style={{ fontSize: 14 }}>—</Text>
        : (
          <Text strong style={{ fontSize: 14 }} copyable={copyable ? { text: String(value) } : false}>
            {value}
          </Text>
        )}
    </div>
  );

  infoCard(title, icon, fields) {
    const gt = (t) => this.props.adapter.gt(t);
    return (
      <Card
        size="small"
        style={{ borderRadius: 14, height: '100%' }}
        styles={{ header: { borderBottom: 'none', paddingTop: 12 }, body: { paddingTop: 4 } }}
        title={<Space size={8}>{icon}<span>{title}</span></Space>}
        extra={(
          <Button type="text" size="small" icon={<EditOutlined />} onClick={() => this.edit()}>
            {gt('Edit')}
          </Button>
        )}
      >
        <Row gutter={[16, 0]}>
          {fields.map((f, i) => (
            <Col xs={24} sm={f.full ? 24 : 12} key={i}>
              {this.field(f.label, f.value, f.copyable)}
            </Col>
          ))}
        </Row>
      </Card>
    );
  }

  statTile(label, value, color, icon, suffix) {
    return (
      <div style={{
        borderRadius: 14,
        padding: '14px 16px',
        background: `${color}14`,
        border: `1px solid ${color}30`,
        height: '100%',
      }}
      >
        <Space size={6} style={{ color, fontSize: 13 }}>{icon}<span>{label}</span></Space>
        <div style={{ fontSize: 26, fontWeight: 700, lineHeight: 1.2, marginTop: 4 }}>
          {value}
          {suffix ? <span style={{ fontSize: 13, fontWeight: 500, marginLeft: 4 }}>{suffix}</span> : null}
        </div>
      </div>
    );
  }

  // --- sections -------------------------------------------------------------
  renderHero() {
    const { adapter } = this.props;
    const gm = (t) => adapter.getMappedText(t);
    const gt = (t) => adapter.gt(t);
    const e = this.props.element;
    const { isMobile } = this.state;
    const name = `${e.first_name || ''} ${e.last_name || ''}`.trim();
    const subtitle = [e.job_title_Name, e.department_Name].filter(Boolean).join(' · ');

    const chip = (icon, label, value, copyable) => (isEmpty(value) ? null : (
      <Space size={8} style={{ minWidth: 0 }}>
        <span style={{ color: '#fff', opacity: 0.85, display: 'inline-flex' }}>{icon}</span>
        <Text style={{ color: 'rgba(255,255,255,0.95)' }} copyable={copyable ? { text: String(value) } : false}>
          {label ? <span style={{ opacity: 0.8 }}>{`${label}: `}</span> : null}
          {value}
        </Text>
      </Space>
    ));

    return (
      <Card
        style={{ borderRadius: 18, overflow: 'hidden', marginBottom: 16 }}
        styles={{ body: { padding: 0 } }}
      >
        <div style={{
          background: 'linear-gradient(120deg, #1668dc 0%, #4096ff 100%)',
          padding: isMobile ? '20px 16px' : '24px 28px',
        }}
        >
          <div style={{
            display: 'flex',
            flexDirection: isMobile ? 'column' : 'row',
            alignItems: isMobile ? 'center' : 'flex-start',
            gap: isMobile ? 16 : 24,
            textAlign: isMobile ? 'center' : 'left',
          }}
          >
            <div style={{ position: 'relative', flex: '0 0 auto' }}>
              <Avatar
                size={isMobile ? 92 : 112}
                src={e.image}
                icon={<UserOutlined />}
                style={{ border: '3px solid rgba(255,255,255,0.7)', cursor: 'pointer' }}
                onClick={() => this.updateProfileImage()}
              />
              <Tooltip title={gt('Update Profile Image')}>
                <Button
                  shape="circle"
                  size="small"
                  icon={<CameraOutlined />}
                  onClick={() => this.updateProfileImage()}
                  style={{ position: 'absolute', right: -2, bottom: -2, boxShadow: '0 2px 6px rgba(0,0,0,.2)' }}
                />
              </Tooltip>
            </div>

            <div style={{ minWidth: 0, flex: 1 }}>
              <Title level={3} style={{ color: '#fff', margin: 0 }}>{name}</Title>
              {subtitle ? <Text style={{ color: 'rgba(255,255,255,0.85)' }}>{subtitle}</Text> : null}
              <div style={{
                display: 'flex',
                flexWrap: 'wrap',
                gap: isMobile ? '8px 18px' : '8px 24px',
                marginTop: 14,
                justifyContent: isMobile ? 'center' : 'flex-start',
              }}
              >
                {chip(<PhoneOutlined />, null, e.mobile_phone, true)}
                {chip(<MailOutlined />, null, e.email, true)}
                {chip(<IdcardOutlined />, gt(gm('Employee Number')), e.employee_id, true)}
                {chip(<GlobalOutlined />, gt(gm('Timezone')), e.timezone)}
                {e.current_time
                  ? chip(<ClockCircleOutlined />, gt('Time now'), `${fmtDate(e.current_time)} · ${fmtTime(e.current_time)}`)
                  : null}
                {e.supervisor ? (
                  <Space size={8}>
                    <span style={{ color: '#fff', opacity: 0.85 }}><TeamOutlined /></span>
                    <Text style={{ color: 'rgba(255,255,255,0.95)' }}>
                      <span style={{ opacity: 0.8 }}>{`${gt(gm('Manager'))}: `}</span>
                    </Text>
                    <Avatar size={22} src={e.supervisor_image} icon={<UserOutlined />} />
                    <Text style={{ color: 'rgba(255,255,255,0.95)' }}>{e.supervisor_Name}</Text>
                  </Space>
                ) : null}
              </div>
            </div>

            <Space
              wrap
              direction={isMobile ? 'horizontal' : 'vertical'}
              style={{ flex: '0 0 auto', justifyContent: 'center' }}
            >
              <Button type="primary" icon={<EditOutlined />} onClick={() => this.edit()} disabled={this.state.loading}>
                {gt('Edit')}
              </Button>
              <Button icon={<CloudUploadOutlined />} onClick={() => this.updateProfileImage()}>
                {gt('Photo')}
              </Button>
              {/* Only offer removal when a real photo is uploaded — the backend
                  serves a generated `data:` SVG placeholder (initials) otherwise. */}
              {e.image && e.image.indexOf('data:') !== 0 ? (
                <Popconfirm
                  title={gt('Remove Photo')}
                  description={gt('Are you sure you want to remove your profile picture?')}
                  okText={gt('Remove')}
                  cancelText={gt('Cancel')}
                  onConfirm={() => this.props.adapter.deleteProfileImage(e.id)}
                >
                  <Button icon={<DeleteOutlined />}>
                    {gt('Remove Photo')}
                  </Button>
                </Popconfirm>
              ) : null}
              <Button icon={<LockOutlined />} onClick={() => this.setShowPasswordUpdate(true)}>
                {gt('Password')}
              </Button>
            </Space>
          </div>
        </div>

        {/* Status selectors — the user can set their feeling / availability here. */}
        <div style={{ padding: isMobile ? '14px 16px' : '16px 28px' }}>
          <EmployeeStatus
            adapter={this.props.adapter}
            apiClient={this.props.adapter.apiClient}
            employee={this.props.element.id}
            showStatusSelect
            hideDailyPlan
            openModelCallback={() => {}}
          />
        </div>
      </Card>
    );
  }

  renderBasic() {
    const { adapter } = this.props;
    const gm = (t) => adapter.getMappedText(t);
    const e = this.props.element;
    return (
      <Row gutter={[16, 16]}>
        <Col xs={24} lg={12}>
          {this.infoCard(adapter.gt('Personal Information'), <UserOutlined />, [
            { label: gm('Date of Birth'), value: e.birthday },
            { label: gm('Gender'), value: e.gender },
            { label: gm('Nationality'), value: e.nationality_Name },
            { label: gm('Marital Status'), value: e.marital_status },
            { label: gm('Joined Date'), value: e.joined_date },
          ])}
        </Col>
        <Col xs={24} lg={12}>
          {this.infoCard(adapter.gt('Identification'), <SafetyCertificateOutlined />, [
            { label: gm('National ID'), value: e.nic_num },
            { label: gm('Social Insurance'), value: e.ssn_num },
            { label: gm('Personal Tax ID'), value: e.tax_id },
            { label: gm('Health Insurance'), value: e.health_insurance },
            { label: gm('Additional IDs'), value: e.other_id },
            { label: gm('Driving License'), value: e.driving_license },
          ])}
        </Col>
        <Col xs={24} lg={12}>
          {this.infoCard(adapter.gt('Contact Information'), <ContactsOutlined />, [
            { label: adapter.gt('Address'), value: [e.address1, e.address2].filter(Boolean).join(', '), full: true },
            { label: gm('City'), value: e.city },
            { label: gm('Country'), value: e.country_Name },
            { label: gm('Postal/Zip Code'), value: e.postal_code },
            { label: gm('Home Phone'), value: e.home_phone, copyable: true },
            { label: gm('Work Phone'), value: e.work_phone, copyable: true },
            { label: gm('Private Email'), value: e.private_email, copyable: true },
          ])}
        </Col>
        <Col xs={24} lg={12}>
          {this.infoCard(adapter.gt('Job Details'), <ApartmentOutlined />, [
            { label: gm('Job Title'), value: e.job_title_Name },
            { label: gm('Employment Status'), value: e.employment_status_Name },
            { label: gm('Department'), value: e.department_Name },
          ])}
        </Col>
      </Row>
    );
  }

  renderAttendance() {
    const gt = (t) => this.props.adapter.gt(t);
    const { attendance, attendanceSummary, attendanceLoading, isMobile } = this.state;
    if (attendanceLoading) return <Card style={{ borderRadius: 14 }}><Skeleton active /></Card>;
    const s = attendanceSummary || {};
    return (
      <>
        <Row gutter={[12, 12]} style={{ marginBottom: 16 }}>
          <Col xs={12} sm={6}>{this.statTile(gt('Total Records'), s.total || 0, '#1677ff', <ClockCircleOutlined />)}</Col>
          <Col xs={12} sm={6}>{this.statTile(gt('This Month'), s.thisMonth || 0, '#13a8a8', <CalendarOutlined />)}</Col>
          <Col xs={12} sm={6}>{this.statTile(gt('Total Hours'), s.totalHours || 0, '#3f8600', <ClockCircleOutlined />, 'hrs')}</Col>
          <Col xs={12} sm={6}>{this.statTile(gt('Avg Hours/Day'), s.avgHours || 0, '#722ed1', <ClockCircleOutlined />, 'hrs')}</Col>
        </Row>
        <Card title={gt('Recent Attendance')} style={{ borderRadius: 14 }} styles={{ header: { borderBottom: 'none' } }}>
          {attendance.length === 0 ? <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description={gt('No records')} />
            : isMobile ? (
              <Space direction="vertical" style={{ width: '100%' }} size={8}>
                {attendance.map((a) => (
                  <Card key={a.id} size="small" style={{ borderRadius: 10 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                      <Text strong>{a.in_time ? a.in_time.split(' ')[0] : '-'}</Text>
                      <Space size={6}>
                        <Tag color="green">{a.in_time ? a.in_time.split(' ')[1] : '-'}</Tag>
                        {a.out_time ? <Tag color="blue">{a.out_time.split(' ')[1]}</Tag> : <Tag color="orange">{gt('Working')}</Tag>}
                      </Space>
                    </div>
                    {a.note ? <Text type="secondary" style={{ fontSize: 12 }}>{a.note}</Text> : null}
                  </Card>
                ))}
              </Space>
            ) : (
              <Table
                dataSource={attendance}
                rowKey="id"
                pagination={false}
                size="small"
                scroll={{ x: 'max-content' }}
                columns={[
                  { title: gt('Date'), dataIndex: 'in_time', render: (t) => (t ? t.split(' ')[0] : '-') },
                  { title: gt('Punch In'), dataIndex: 'in_time', render: (t) => (t ? t.split(' ')[1] : '-') },
                  { title: gt('Punch Out'), dataIndex: 'out_time', render: (t) => (t ? t.split(' ')[1] : <Tag color="orange">{gt('Working')}</Tag>) },
                  { title: gt('Note'), dataIndex: 'note', ellipsis: true },
                ]}
              />
            )}
        </Card>
      </>
    );
  }

  renderLeave() {
    const gt = (t) => this.props.adapter.gt(t);
    const { leaves, leaveSummary, leavesLoading, isMobile } = this.state;
    if (leavesLoading) return <Card style={{ borderRadius: 14 }}><Skeleton active /></Card>;
    const s = leaveSummary || {};
    return (
      <>
        <Row gutter={[12, 12]} style={{ marginBottom: 16 }}>
          <Col xs={12} sm={8} lg={4}>{this.statTile(gt('Total'), s.total || 0, '#1677ff', <CalendarOutlined />)}</Col>
          <Col xs={12} sm={8} lg={5}>{this.statTile(gt('Approved'), s.approved || 0, '#3f8600', <CheckCircleOutlined />)}</Col>
          <Col xs={12} sm={8} lg={5}>{this.statTile(gt('Pending'), s.pending || 0, '#d48806', <ClockCircleOutlined />)}</Col>
          <Col xs={12} sm={8} lg={5}>{this.statTile(gt('Rejected'), s.rejected || 0, '#cf1322', <CloseCircleOutlined />)}</Col>
          <Col xs={12} sm={8} lg={5}>{this.statTile(gt('Cancelled'), s.cancelled || 0, '#8c8c8c', <StopOutlined />)}</Col>
        </Row>
        <Card title={gt('Recent Leave Requests')} style={{ borderRadius: 14 }} styles={{ header: { borderBottom: 'none' } }}>
          {leaves.length === 0 ? <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description={gt('No records')} />
            : isMobile ? (
              <Space direction="vertical" style={{ width: '100%' }} size={8}>
                {leaves.map((l) => (
                  <Card key={l.id} size="small" style={{ borderRadius: 10 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 4 }}>
                      <Text strong>{l.leave_type?.display || ''}</Text>
                      <Tag color={LEAVE_STATUS_COLORS[l.status] || 'default'}>{l.status}</Tag>
                    </div>
                    <Text type="secondary" style={{ fontSize: 12 }}>{`${l.date_start} → ${l.date_end}`}</Text>
                    {l.details ? <div><Text style={{ fontSize: 12 }}>{l.details}</Text></div> : null}
                  </Card>
                ))}
              </Space>
            ) : (
              <Table
                dataSource={leaves}
                rowKey="id"
                pagination={false}
                size="small"
                scroll={{ x: 'max-content' }}
                columns={[
                  { title: gt('Leave Type'), dataIndex: ['leave_type', 'display'] },
                  { title: gt('Start Date'), dataIndex: 'date_start' },
                  { title: gt('End Date'), dataIndex: 'date_end' },
                  {
                    title: gt('Status'),
                    dataIndex: 'status',
                    render: (status) => <Tag color={LEAVE_STATUS_COLORS[status] || 'default'}>{status}</Tag>,
                  },
                  { title: gt('Details'), dataIndex: 'details', ellipsis: true },
                ]}
              />
            )}
        </Card>
      </>
    );
  }

  render() {
    const { adapter } = this.props;
    if (this.state.loading || !this.props.element) {
      return <div style={{ padding: 20 }}><Skeleton active avatar paragraph={{ rows: 6 }} /></div>;
    }
    const gt = (t) => adapter.gt(t);
    const { isMobile, section } = this.state;
    const e = this.props.element;

    const sections = {
      Basic: this.renderBasic(),
      Attendance: this.renderAttendance(),
      Leave: this.renderLeave(),
    };
    const segLabel = (icon, txt) => (
      <Space size={6} style={{ padding: '2px 4px' }}>{icon}<span>{txt}</span></Space>
    );
    const segOptions = [
      { label: segLabel(<ProfileOutlined />, gt('Basic Information')), value: 'Basic' },
      { label: segLabel(<ClockCircleOutlined />, gt('Attendance')), value: 'Attendance' },
      { label: segLabel(<CalendarOutlined />, gt('Leave')), value: 'Leave' },
    ];

    return (
      <div style={{ width: '100%', padding: isMobile ? 12 : '16px 20px' }}>
        <UpdatePasswordModal
          visible={this.state.showPasswordResetModal}
          closeModal={() => this.setState({ showPasswordResetModal: false })}
          adapter={this.props.adapter}
        />
        <Modal
          title={gt('Upload Profile Photo')}
          open={!!this.state.photoModalOpen}
          footer={null}
          onCancel={() => this.setState({ photoModalOpen: false })}
        >
          <Upload
            name="file"
            accept="image/*"
            maxCount={1}
            showUploadList={false}
            action={`${window.CLIENT_BASE_URL}fileupload-new.php?user=${e.id}&file_group=profile_image&file_name=profile_${e.id}_${(new Date()).getTime()}`}
            onChange={this.onPhotoUploadChange}
          >
            <Button icon={<UploadOutlined />}>{gt('Select Image')}</Button>
          </Upload>
        </Modal>

        {this.renderHero()}

        {/* A segmented switcher reads better than card tabs on mobile (it scrolls
            horizontally and keeps a big tap target). */}
        <div style={{ overflowX: 'auto', marginBottom: 16, paddingBottom: 2 }}>
          <ConfigProvider
            theme={{
              components: {
                Segmented: {
                  // Clear primary selected, subtle blue-tint hover (the default
                  // grey hover read almost like a second selected tab).
                  itemSelectedBg: '#1677ff',
                  itemSelectedColor: '#ffffff',
                  itemHoverBg: 'rgba(22,119,255,0.12)',
                  itemHoverColor: '#1677ff',
                  itemActiveBg: 'rgba(22,119,255,0.20)',
                  trackPadding: 4,
                  borderRadius: 12,
                },
              },
            }}
          >
            <Segmented
              size={isMobile ? 'middle' : 'large'}
              block={!isMobile}
              value={section}
              onChange={(v) => this.setState({ section: v })}
              options={segOptions}
            />
          </ConfigProvider>
        </div>

        {sections[section]}
      </div>
    );
  }
}

export default EmployeeProfile;
