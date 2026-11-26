import React from 'react';
import {
  Col,
  Card,
  Skeleton,
  Avatar,
  Row,
  Typography,
  Table,
  Space,
  Button,
  Tag,
  message,
  Alert,
  Modal,
  Upload,
  Empty,
  Tooltip,
  Segmented,
  ConfigProvider,
} from 'antd';
import {
  EditOutlined,
  KeyOutlined,
  UploadOutlined,
  CameraOutlined,
  PhoneOutlined,
  MailOutlined,
  IdcardOutlined,
  GlobalOutlined,
  CalendarOutlined,
  CheckCircleOutlined,
  ClockCircleOutlined,
  CloseCircleOutlined,
  StopOutlined,
  TeamOutlined,
  ReadOutlined,
  SolutionOutlined,
  ProfileOutlined,
  SafetyCertificateOutlined,
  UserOutlined,
  ContactsOutlined,
  ApartmentOutlined,
  AppstoreOutlined,
  UserAddOutlined,
  LockOutlined,
} from '@ant-design/icons';
import QualificationsPanel from './QualificationsPanel';
import ApprovalsPanel from './ApprovalsPanel';
import { t } from '../../../../api/i18n';

// Fields shown in each profile section, used to open the edit wizard on the step
// that holds them. Several per section, in case a field is hidden or renamed.
export const SECTION_FIELDS = {
  personal: ['first_name', 'birthday', 'gender', 'nationality', 'marital_status'],
  identification: ['nic_num', 'ssn_num', 'tax_id', 'other_id', 'driving_license'],
  contact: ['address1', 'city', 'country', 'mobile_phone', 'home_phone', 'work_email', 'private_email'],
  job: ['job_title', 'employment_status', 'department', 'work_location', 'joined_date'],
};

const { Title, Text } = Typography;

const isEmpty = (v) => v === null || v === undefined || v === '' || v === '-';

// "YYYY-MM-DD" (optionally with a time) as a readable date. Built from the parts
// so a date-only value is not shifted a day by the browser's time zone.
function fmtDay(str) {
  if (isEmpty(str) || String(str).indexOf('0000-00-00') === 0) return null;
  const m = String(str).match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (!m) return str;
  const d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
  return d.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });
}
function fmtTime(str) {
  if (!str) return '';
  const d = new Date(String(str).replace(' ', 'T'));
  if (Number.isNaN(d.getTime())) return '';
  return d.toLocaleTimeString(undefined, { hour: '2-digit', minute: '2-digit' });
}
// Decimal columns come back as "20.00" / "0.500"; show them as 20 / 0.5.
const fmtNumber = (v) => (isEmpty(v) ? null : String(Number(v)));

const LEAVE_STATUS_COLORS = {
  Approved: 'green',
  Pending: 'orange',
  Rejected: 'red',
  Cancelled: 'default',
  'Cancellation Requested': 'default',
};

/**
 * Admin view of an employee, shown next to the employee directory. Same layout as
 * the self-service profile (modules/employees), plus the admin actions: user
 * account, resignation, copy, switch to employee, qualifications and approvals.
 */
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
    section: 'Basic',
    isMobile: typeof window !== 'undefined' && window.innerWidth < 768,
  };

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

  getApiClient() {
    return this.props.adapter?.apiClient || this.props.apiClient;
  }

  fetchLeaveData(employeeId) {
    this.setState({ leavesLoading: true });
    const apiClient = this.getApiClient();
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
    const apiClient = this.getApiClient();
    if (!apiClient) { this.setState({ attendanceLoading: false }); return; }
    apiClient.get(`employees/${employeeId}/attendance`)
      .then((r) => this.setState({ attendance: r.data.data || [] }))
      .catch(() => this.setState({ attendance: [] }));
    apiClient.get(`employees/${employeeId}/attendance/summary`)
      .then((r) => this.setState({ attendanceSummary: r.data, attendanceLoading: false }))
      .catch(() => this.setState({ attendanceSummary: null, attendanceLoading: false }));
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
      t('Upload Profile Image'),
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
      if (resp && resp.status === 'error') {
        message.error(resp.message || t('Upload failed'));
        return;
      }
      message.success(t('Profile photo updated'));
      this.setState({ photoModalOpen: false });
      this.reloadEmployee();
      if (this.props.adapter && this.props.adapter.reloadEmployeeList) {
        this.props.adapter.reloadEmployeeList();
      }
    } else if (f.status === 'error') {
      message.error(t('Upload failed'));
    }
  };

  reloadEmployee(id) {
    const url = `employees/${id == null ? this.props.element.id : id}`;
    this.props.apiClient.get(url)
      .then((response) => {
        this.setState({ employee: response.data });
        this.props.setEmployee(response.data);
      });
  }

  // Opens the edit wizard on the step that holds these fields (the section's
  // fields, in order of preference); without fields it opens on the first step.
  editAt(fields) {
    if (fields && modJs.editAtFields) {
      modJs.editAtFields(this.props.element.id, fields);
      return;
    }
    modJs.edit(this.props.element.id);
  }

  getTabViewEmployeeFilterButtonJsx(tab) {
    return (
      <Button
        type="text"
        size="small"
        icon={<EditOutlined />}
        onClick={() => {
          const filter = { employee: this.props.element.id };
          // SPA shell: switch this module's antd tab (legacy switchTab clicks
          // a legacy tab anchor that doesn't exist there).
          if (typeof window.iceShellSwitchModuleTab === 'function') {
            window.iceShellSwitchModuleTab(tab, filter);
          } else {
            switchTab(tab, filter);
          }
        }}
      >
        {this.props.adapter.gt('Edit')}
      </Button>
    );
  }

  // --- small render helpers -------------------------------------------------
  field = (label, value, copyable = false) => (
    <div style={{ marginBottom: 16 }}>
      <Text type="secondary" style={{ fontSize: 12, display: 'block', marginBottom: 2 }}>{label}</Text>
      {isEmpty(value)
        ? <Text type="secondary" style={{ fontSize: 14 }}>—</Text>
        : (
          <Text strong style={{ fontSize: 14, whiteSpace: 'pre-line' }} copyable={copyable ? { text: String(value) } : false}>
            {value}
          </Text>
        )}
    </div>
  );

  infoCard(title, icon, fields, editFields = null) {
    const { adapter } = this.props;
    const canEdit = adapter.hasAccess ? adapter.hasAccess('save') : true;
    return (
      <Card
        size="small"
        style={{ borderRadius: 14, height: '100%' }}
        styles={{ header: { borderBottom: 'none', paddingTop: 12 }, body: { paddingTop: 4 } }}
        title={<Space size={8}>{icon}<span>{title}</span></Space>}
        extra={canEdit ? (
          <Button
            type="text"
            size="small"
            icon={<EditOutlined />}
            disabled={this.props.loading}
            onClick={() => this.editAt(editFields)}
          >
            {adapter.gt('Edit')}
          </Button>
        ) : null}
      >
        <Row gutter={[16, 0]}>
          {fields.filter(Boolean).map((f, i) => (
            <Col xs={24} sm={f.full ? 24 : 12} key={i}>
              {this.field(adapter.gt(f.label), f.value, f.copyable)}
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
  renderHero(employee) {
    const { adapter } = this.props;
    const gm = (k) => adapter.getMappedText(k);
    const gt = (k) => adapter.gt(k);
    const e = employee;
    const { isMobile } = this.state;
    const isAdmin = adapter?.user?.user_level === 'Admin';
    const name = `${e.first_name || ''} ${e.last_name || ''}`.trim();
    const subtitle = [e.job_title?.display, e.department?.display].filter(Boolean).join(' · ');
    const aka = [
      e.name_preferred ? `${gt(gm('Preferred Name'))}: ${e.name_preferred}` : '',
      e.name_pronunciation ? `${gt(gm('Name Pronunciation'))}: ${e.name_pronunciation}` : '',
    ].filter(Boolean).join(' · ');

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
              <Tooltip title={gt('Upload Photo')}>
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
              <Space size={8} wrap style={{ justifyContent: isMobile ? 'center' : 'flex-start' }}>
                <Title level={3} style={{ color: '#fff', margin: 0 }}>{name}</Title>
                {e.pronouns ? <Text style={{ color: 'rgba(255,255,255,0.8)' }}>{`(${e.pronouns})`}</Text> : null}
                {e.status && e.status !== 'Active' ? (
                  <Tag color="orange" style={{ marginInlineEnd: 0 }}>
                    {/* Stored as 'Terminated'; the UI calls these employees Resigned. */}
                    {e.status === 'Terminated' ? gt('Resigned') : gt(e.status)}
                  </Tag>
                ) : null}
              </Space>
              {subtitle ? <div><Text style={{ color: 'rgba(255,255,255,0.85)' }}>{subtitle}</Text></div> : null}
              {aka ? <div><Text style={{ color: 'rgba(255,255,255,0.75)', fontSize: 13 }}>{aka}</Text></div> : null}
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
                  ? chip(<ClockCircleOutlined />, gt('Time now'), `${fmtDay(e.current_time)} · ${fmtTime(e.current_time)}`)
                  : null}
                {e.supervisor ? (
                  <Space size={8}>
                    <span style={{ color: '#fff', opacity: 0.85 }}><TeamOutlined /></span>
                    <Text style={{ color: 'rgba(255,255,255,0.95)' }}>
                      <span style={{ opacity: 0.8 }}>{`${gt(gm('Manager'))}: `}</span>
                    </Text>
                    <Avatar size={22} src={e.supervisor?.image} icon={<UserOutlined />} />
                    <Text style={{ color: 'rgba(255,255,255,0.95)' }}>{e.supervisor?.display}</Text>
                  </Space>
                ) : null}
              </div>
            </div>
          </div>
        </div>

        {/* Admin actions and the employee's user account. */}
        <div style={{
          padding: isMobile ? '12px 16px' : '14px 28px',
          display: 'flex',
          flexWrap: 'wrap',
          gap: 12,
          alignItems: 'center',
          justifyContent: 'space-between',
        }}
        >
          <div>
            {adapter.getTableActionButtonJsx(adapter, e.id, this.props.loading)}
          </div>
          <Space size={8} wrap>
            {e.can_login === 1 ? (
              <>
                <Text type="secondary">{`${gt(gm('Access Level'))}:`}</Text>
                <Tag color="blue" style={{ marginInlineEnd: 0 }}>{e.user_level}</Tag>
                {isAdmin && e.user_name ? (
                  <>
                    <Text type="secondary">{`${gt('Login')}:`}</Text>
                    <Text code>{e.user_name}</Text>
                    <Button size="small" icon={<EditOutlined />} onClick={() => adapter.editUser(e)}>
                      {gt('Edit Login')}
                    </Button>
                    <Button size="small" icon={<KeyOutlined />} onClick={() => adapter.changeUserPassword(e)}>
                      {gt('Change Password')}
                    </Button>
                  </>
                ) : null}
              </>
            ) : null}
            {/* A resigned employee is not offered a new login. */}
            {e.can_login === 0 && isAdmin && e.status !== 'Terminated' ? (
              <Button size="small" icon={<UserAddOutlined />} onClick={() => adapter.createUser(this.props.element)}>
                {gt('Create Login')}
              </Button>
            ) : null}
          </Space>
        </div>
      </Card>
    );
  }

  renderBasic(employee) {
    const { adapter } = this.props;
    const gm = (k) => adapter.getMappedText(k);
    const e = employee;
    const terminated = e.status === 'Terminated';
    const customFields = e.customFields && Object.keys(e.customFields).length > 0 ? e.customFields : null;
    return (
      <Row gutter={[16, 16]}>
        <Col xs={24} xl={12}>
          {this.infoCard(adapter.gt('Personal Information'), <UserOutlined />, [
            { label: gm('Date of Birth'), value: fmtDay(e.birthday) },
            { label: gm('Gender'), value: e.gender },
            { label: gm('Nationality'), value: e.nationality?.display },
            { label: gm('Marital Status'), value: e.marital_status },
            { label: gm('Joined Date'), value: fmtDay(e.joined_date) },
            { label: gm('About'), value: e.about, full: true },
          ], SECTION_FIELDS.personal)}
        </Col>
        <Col xs={24} xl={12}>
          {this.infoCard(adapter.gt('Identification'), <SafetyCertificateOutlined />, [
            { label: gm('National ID'), value: e.nic_num },
            { label: gm('Social Insurance'), value: e.ssn_num },
            { label: gm('Personal Tax ID'), value: e.tax_id },
            { label: gm('Health Insurance'), value: e.health_insurance },
            { label: gm('Additional IDs'), value: e.other_id },
            { label: gm('Driving License'), value: e.driving_license },
            { label: gm('Driving License Expiry'), value: fmtDay(e.driving_license_exp_date) },
          ], SECTION_FIELDS.identification)}
        </Col>
        <Col xs={24} xl={12}>
          {this.infoCard(adapter.gt('Contact Information'), <ContactsOutlined />, [
            { label: 'Address', value: [e.address1, e.address2].filter(Boolean).join(', '), full: true },
            { label: gm('City'), value: e.city },
            { label: gm('Country'), value: e.country?.display },
            { label: gm('Postal/Zip Code'), value: e.postal_code },
            { label: gm('Home Phone'), value: e.home_phone, copyable: true },
            { label: gm('Work Phone'), value: e.work_phone, copyable: true },
            { label: gm('Private Email'), value: e.private_email, copyable: true },
            { label: gm('LinkedIn'), value: e.linkedin_url, copyable: true },
          ], SECTION_FIELDS.contact)}
        </Col>
        <Col xs={24} xl={12}>
          {this.infoCard(adapter.gt('Job Details'), <ApartmentOutlined />, [
            { label: gm('Job Title'), value: e.job_title?.display },
            { label: gm('Employment Status'), value: e.employment_status?.display },
            { label: gm('Department'), value: e.department?.display },
            { label: gm('Employment Type'), value: e.employment_type },
            { label: gm('Work Location'), value: e.work_location?.display },
            { label: gm('Work Arrangement'), value: e.work_mode },
            { label: gm('Weekly Hours'), value: fmtNumber(e.weekly_hours) },
            { label: gm('Full-time Equivalent (FTE)'), value: fmtNumber(e.fte) },
            { label: gm('Cost Center'), value: e.cost_center?.display },
            { label: gm('Original Hire Date'), value: fmtDay(e.original_hire_date) },
            { label: gm('Probation End Date'), value: fmtDay(e.confirmation_date) },
            { label: gm('Contract End Date'), value: fmtDay(e.contract_end_date) },
            terminated ? { label: gm('Last Working Day'), value: fmtDay(e.last_working_day) } : null,
            terminated ? { label: gm('Termination Type'), value: e.termination_type } : null,
            terminated ? { label: gm('Eligible for Rehire'), value: e.eligible_for_rehire } : null,
          ], SECTION_FIELDS.job)}
        </Col>
        {customFields ? (
          <Col xs={24}>
            {this.infoCard(
              adapter.gt('Other Information'),
              <AppstoreOutlined />,
              Object.keys(customFields).map((key) => ({ label: key, value: customFields[key][0] })),
              (adapter.customFields || []).map((f) => f[0]),
            )}
          </Col>
        ) : null}
      </Row>
    );
  }

  renderAttendance() {
    const gt = (k) => this.props.adapter.gt(k);
    const { attendance, attendanceSummary, attendanceLoading } = this.state;
    if (attendanceLoading) return <Card style={{ borderRadius: 14 }}><Skeleton active /></Card>;
    const s = attendanceSummary || {};
    return (
      <>
        <Row gutter={[12, 12]} style={{ marginBottom: 16 }}>
          <Col xs={12} md={6}>{this.statTile(gt('Total Records'), s.total || 0, '#1677ff', <ClockCircleOutlined />)}</Col>
          <Col xs={12} md={6}>{this.statTile(gt('This Month'), s.thisMonth || 0, '#13a8a8', <CalendarOutlined />)}</Col>
          <Col xs={12} md={6}>{this.statTile(gt('Total Hours'), s.totalHours || 0, '#3f8600', <ClockCircleOutlined />, 'hrs')}</Col>
          <Col xs={12} md={6}>{this.statTile(gt('Avg Hours/Day'), s.avgHours || 0, '#722ed1', <ClockCircleOutlined />, 'hrs')}</Col>
        </Row>
        <Card title={gt('Recent Attendance')} style={{ borderRadius: 14 }} styles={{ header: { borderBottom: 'none' } }}>
          {attendance.length === 0 ? <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description={gt('No records')} /> : (
            <Table
              dataSource={attendance}
              rowKey="id"
              pagination={false}
              size="small"
              scroll={{ x: 'max-content' }}
              columns={[
                { title: gt('Date'), dataIndex: 'in_time', key: 'date', render: (v) => (v ? v.split(' ')[0] : '-') },
                { title: gt('Clock-in'), dataIndex: 'in_time', key: 'in_time', render: (v) => (v ? v.split(' ')[1] : '-') },
                { title: gt('Clock-out'), dataIndex: 'out_time', key: 'out_time', render: (v) => (v ? v.split(' ')[1] : <Tag color="orange">{t('Working')}</Tag>) },
                { title: gt('Note'), dataIndex: 'note', key: 'note', ellipsis: true },
              ]}
            />
          )}
        </Card>
      </>
    );
  }

  renderLeave() {
    const gt = (k) => this.props.adapter.gt(k);
    const { leaves, leaveSummary, leavesLoading } = this.state;
    if (leavesLoading) return <Card style={{ borderRadius: 14 }}><Skeleton active /></Card>;
    const s = leaveSummary || {};
    return (
      <>
        <Row gutter={[12, 12]} style={{ marginBottom: 16 }}>
          <Col xs={12} md={8} xl={4}>{this.statTile(gt('Total'), s.total || 0, '#1677ff', <CalendarOutlined />)}</Col>
          <Col xs={12} md={8} xl={5}>{this.statTile(gt('Approved'), s.approved || 0, '#3f8600', <CheckCircleOutlined />)}</Col>
          <Col xs={12} md={8} xl={5}>{this.statTile(gt('Pending'), s.pending || 0, '#d48806', <ClockCircleOutlined />)}</Col>
          <Col xs={12} md={8} xl={5}>{this.statTile(gt('Rejected'), s.rejected || 0, '#cf1322', <CloseCircleOutlined />)}</Col>
          <Col xs={12} md={8} xl={5}>{this.statTile(gt('Cancelled'), s.cancelled || 0, '#8c8c8c', <StopOutlined />)}</Col>
        </Row>
        <Card title={gt('Recent Leave Requests')} style={{ borderRadius: 14 }} styles={{ header: { borderBottom: 'none' } }}>
          {leaves.length === 0 ? <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} description={gt('No records')} /> : (
            <Table
              dataSource={leaves}
              rowKey="id"
              pagination={false}
              size="small"
              scroll={{ x: 'max-content' }}
              columns={[
                { title: gt('Leave Type'), dataIndex: ['leave_type', 'display'], key: 'leave_type' },
                { title: gt('Start Date'), dataIndex: 'date_start', key: 'date_start' },
                { title: gt('End Date'), dataIndex: 'date_end', key: 'date_end' },
                {
                  title: gt('Status'),
                  dataIndex: 'status',
                  key: 'status',
                  render: (status) => <Tag color={LEAVE_STATUS_COLORS[status] || 'default'}>{status}</Tag>,
                },
                { title: gt('Details'), dataIndex: 'details', key: 'details', ellipsis: true },
              ]}
            />
          )}
        </Card>
      </>
    );
  }

  render() {
    const { adapter } = this.props;
    let employee = this.state.employee;
    if (employee == null || employee.id != this.props.element?.id) {
      employee = this.props.element;
    }
    const gm = (text) => adapter.getMappedText(text);
    const gt = (k) => adapter.gt(k);

    if (!employee || !employee.id) {
      // The directory finished loading with no rows (e.g. a manager with no
      // subordinates) — no employee will ever be auto-selected, so show a
      // message instead of a never-ending loading skeleton.
      if (this.props.listEmpty) {
        return (
          <div style={{ padding: 10 }}>
            <Card style={{ borderRadius: 14 }}>
              <Empty description={adapter && adapter.gt ? adapter.gt('No employees found') : 'No employees found'} />
            </Card>
          </div>
        );
      }
      return (
        <div style={{ padding: 10 }}>
          <Card style={{ borderRadius: 14 }}>
            <Skeleton active avatar paragraph={{ rows: 6 }} />
          </Card>
        </div>
      );
    }

    const { isMobile, section } = this.state;
    const sections = {
      Basic: () => this.renderBasic(employee),
      Attendance: () => this.renderAttendance(),
      Leave: () => this.renderLeave(),
      Qualifications: () => (
        <QualificationsPanel
          key={employee.id}
          apiClient={adapter.apiClient}
          employeeId={employee.id}
          gt={(s) => adapter.gt(s)}
          editExtra={(tab) => this.getTabViewEmployeeFilterButtonJsx(tab)}
        />
      ),
      Approvals: () => (
        <ApprovalsPanel
          key={employee.id}
          employee={employee}
          adapter={adapter}
          apiClient={this.props.apiClient}
          onSaved={() => this.reloadEmployee()}
        />
      ),
    };
    const segLabel = (icon, txt) => (
      <Space size={6} style={{ padding: '2px 4px' }}>{icon}<span>{txt}</span></Space>
    );
    const segOptions = [
      { label: segLabel(<ProfileOutlined />, gt('Basic Information')), value: 'Basic' },
      { label: segLabel(<ClockCircleOutlined />, gt('Attendance')), value: 'Attendance' },
      { label: segLabel(<CalendarOutlined />, gt('Leave')), value: 'Leave' },
      { label: segLabel(<ReadOutlined />, gt('Qualifications')), value: 'Qualifications' },
    ];
    if (adapter.multiLevelApprovals) {
      segOptions.push({ label: segLabel(<SolutionOutlined />, gt('Approvals')), value: 'Approvals' });
    }
    // Sections added by installed extensions (meta.json native.profileSections).
    (adapter.profileSections || []).forEach((ext) => {
      const View = typeof window !== 'undefined' ? window[ext.viewGlobal] : null;
      if (!View) return;
      const value = `ext:${ext.key}`;
      segOptions.push({ label: segLabel(<AppstoreOutlined />, gt(ext.label)), value });
      sections[value] = () => (
        <View
          key={`${ext.key}-${employee.id}`}
          employeeId={employee.id}
          employeeName={`${employee.first_name || ''} ${employee.last_name || ''}`.trim()}
          apiClient={adapter.apiClient}
        />
      );
    });
    const activeSection = segOptions.some((o) => o.value === section) ? section : 'Basic';

    return (
      <div style={{ width: '100%', boxSizing: 'border-box', padding: isMobile ? 4 : '10px 10px 16px' }}>
        <Modal
          title={gt('Upload Profile Photo')}
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
            action={`${window.CLIENT_BASE_URL}fileupload-new.php?user=${employee.id}&file_group=profile_image&file_name=profile_${employee.id}_${(new Date()).getTime()}`}
            onChange={this.onPhotoUploadChange}
          >
            <Button icon={<UploadOutlined />}>{gt('Select Image')}</Button>
          </Upload>
        </Modal>

        {employee.can_login === 0 && employee.status !== 'Terminated' && adapter?.user?.user_level === 'Admin' ? (
          <Alert
            message={gt("This employee can't sign in yet because they don't have a login.")}
            showIcon
            icon={<LockOutlined />}
            type="warning"
            action={(
              <Button size="small" onClick={() => adapter.createUser(this.props.element)}>
                {t('Create Login')}
              </Button>
            )}
            style={{ width: '100%', marginBottom: 16, borderRadius: 12 }}
          />
        ) : null}

        {this.renderHero(employee)}

        <div className="emp-profile-sections" style={{ overflowX: 'auto', marginBottom: 16, paddingBottom: 2 }}>
          {/* Many sections (extensions add their own) wrap onto a second row instead of being cut off. */}
          <style>{'.emp-profile-sections .ant-segmented{max-width:100%}.emp-profile-sections .ant-segmented-group{flex-wrap:wrap;row-gap:4px}'}</style>
          <ConfigProvider
            theme={{
              components: {
                Segmented: {
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
              value={activeSection}
              onChange={(v) => this.setState({ section: v })}
              options={segOptions}
            />
          </ConfigProvider>
        </div>

        {sections[activeSection]()}
      </div>
    );
  }
}

export default EmployeeProfile;
