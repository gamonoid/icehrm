import React from 'react';
import {
  Card,
  Row,
  Col,
  Statistic,
  Button,
  message,
  Modal,
  Spin,
  Space,
  InputNumber,
  DatePicker,
  Input,
  Divider,
  Alert,
  Typography,
  Checkbox,
} from 'antd';
import {
  UserOutlined,
  ClockCircleOutlined,
  CalendarOutlined,
  FileTextOutlined,
  DollarOutlined,
  DeleteOutlined,
  PlusOutlined,
  ExclamationCircleOutlined,
  ReloadOutlined,
  CheckCircleOutlined,
  StopOutlined,
  WarningOutlined,
  RocketOutlined,
  ProjectOutlined,
  TeamOutlined,
  CheckSquareOutlined,
  AuditOutlined,
  UserAddOutlined,
  ContactsOutlined,
  TrophyOutlined,
} from '@ant-design/icons';

const { Title, Text } = Typography;
const { RangePicker } = DatePicker;

class DemoModeAdminExtensionView extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: false,
      demoModeEnabled: false,
      stats: {
        employee: 0,
        attendance: 0,
        timesheet: 0,
        timesheet_entry: 0,
        leave: 0,
        payroll: 0,
        payroll_data: 0,
        project: 0,
        client: 0,
        expense: 0,
        overtime: 0,
        job: 0,
        candidate: 0,
        application: 0,
        performance_review: 0,
        employee_goal: 0,
        review_feedback: 0,
        modules: {},
      },
      generatingAll: false,
      employeeCount: 10,
      attendanceDays: 30,
      todayAttendancePercentage: 80,
      familyEmployeeCount: 10,
      teamCount: 5,
      teamMembersPerTeam: 6,
      taskListCount: 8,
      timesheetWeeks: 4,
      leaveCount: 5,
      futureLeaveOnly: false,
      expenseCount: 3,
      overtimeCount: 3,
      jobCount: 5,
      candidateCount: 3,
      performanceReviewCount: 10,
      payrollName: '',
      payrollDateRange: null,
      generating: {},
      deleting: {},
      togglingDemoMode: false,
    };
  }

  componentDidMount() {
    this.loadStats();
    this.loadDemoModeStatus();
  }

  getApiClient = () => {
    return window.demoModeExtensionController.getApiClient();
  };

  // Helper to extract data from IceHRM API response
  parseResponse = (response) => {
    const result = response.data || response;
    // Check for error response format
    if (result.error) {
      const errorMsg = result.error[0]?.[0]?.message || 'An error occurred';
      return { success: false, error: errorMsg };
    }
    // Check for success response format
    if (result.status === 'SUCCESS') {
      return { success: true, data: result.data };
    }
    // Fallback - if we have data directly
    if (result && !result.error) {
      return { success: true, data: result };
    }
    return { success: false, error: 'Unknown response format' };
  };

  loadStats = () => {
    this.setState({ loading: true });
    this.getApiClient()
      .get('demo-mode/stats')
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success && parsed.data) {
          this.setState({ stats: parsed.data, loading: false });
        } else {
          console.error('Stats load error:', parsed.error);
          this.setState({ loading: false });
        }
      })
      .catch((error) => {
        console.error('Error loading stats:', error);
        this.setState({ loading: false });
      });
  };

  loadDemoModeStatus = () => {
    this.getApiClient()
      .get('demo-mode/status')
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success && parsed.data) {
          this.setState({ demoModeEnabled: parsed.data.enabled });
        }
      })
      .catch((error) => {
        console.error('Error loading demo mode status:', error);
      });
  };

  handleGenerateAll = () => {
    const { demoModeEnabled } = this.state;

    const generateData = () => {
      this.setState({ generatingAll: true });

      this.getApiClient()
        .post('demo-mode/generate-all', { futureLeaveOnly: this.state.futureLeaveOnly })
        .then((response) => {
          const parsed = this.parseResponse(response);
          if (parsed.success) {
            message.success('Sample data generated successfully!');
            this.loadStats();
            this.loadDemoModeStatus();
          } else {
            message.error(parsed.error || 'Failed to generate sample data');
          }
          this.setState({ generatingAll: false });
        })
        .catch((error) => {
          console.error('Error:', error);
          const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate sample data';
          message.error(errorMsg);
          this.setState({ generatingAll: false });
        });
    };

    const contentItems = [
      <p key="intro">This will create a complete sample dataset including:</p>,
      <ul key="list">
        <li>20 sample employees</li>
        <li>5 clients and 5 projects</li>
        <li>2 years of attendance records</li>
        <li>Leave requests for employees</li>
        <li>2 years of timesheet entries</li>
      </ul>,
      <p key="time">This may take a few minutes to complete.</p>,
    ];

    // Add warning about demo mode being enabled if it's currently disabled
    if (!demoModeEnabled) {
      contentItems.unshift(
        <Alert
          key="warning"
          message="Demo Mode will be enabled"
          description="Demo Mode will be automatically enabled to track all generated data for easy cleanup later."
          type="warning"
          showIcon
          style={{ marginBottom: 16 }}
        />
      );
    }

    Modal.confirm({
      title: 'Generate All Sample Data?',
      icon: <RocketOutlined style={{ color: '#52c41a' }} />,
      content: <div>{contentItems}</div>,
      okText: 'Generate All',
      okType: 'primary',
      cancelText: 'Cancel',
      onOk: generateData,
    });
  };

  handleToggleDemoMode = () => {
    const { demoModeEnabled } = this.state;
    const endpoint = demoModeEnabled ? 'demo-mode/disable' : 'demo-mode/enable';

    this.setState({ togglingDemoMode: true });
    this.getApiClient()
      .post(endpoint, {})
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          this.setState({
            demoModeEnabled: !demoModeEnabled,
            togglingDemoMode: false,
          });
          message.success(demoModeEnabled ? 'Demo mode disabled' : 'Demo mode enabled');
        } else {
          message.error(parsed.error || 'Failed to toggle demo mode');
          this.setState({ togglingDemoMode: false });
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        message.error('Failed to toggle demo mode');
        this.setState({ togglingDemoMode: false });
      });
  };

  handleGenerateEmployees = () => {
    const { employeeCount } = this.state;
    this.setState({ generating: { ...this.state.generating, employees: true } });

    this.getApiClient()
      .post('demo-mode/employees', { count: employeeCount })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          message.success(`Created ${parsed.data?.created || employeeCount} demo employees`);
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate employees');
        }
        this.setState({ generating: { ...this.state.generating, employees: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate employees';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, employees: false } });
      });
  };

  handleGenerateAttendance = () => {
    const { attendanceDays } = this.state;
    this.setState({ generating: { ...this.state.generating, attendance: true } });

    this.getApiClient()
      .post('demo-mode/attendance', { daysBack: attendanceDays })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          message.success(`Created ${parsed.data?.created || 0} attendance records`);
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate attendance');
        }
        this.setState({ generating: { ...this.state.generating, attendance: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate attendance';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, attendance: false } });
      });
  };

  handleGenerateTodayAttendance = () => {
    const { todayAttendancePercentage } = this.state;
    this.setState({ generating: { ...this.state.generating, attendanceToday: true } });

    this.getApiClient()
      .post('demo-mode/attendance-today', { percentage: todayAttendancePercentage })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          const d = parsed.data || {};
          const skippedNote = d.skipped ? ` (${d.skipped} already had a record)` : '';
          message.success(`Created today's attendance for ${d.created || 0} of ${d.totalActive || 0} employees${skippedNote}`);
          this.loadStats();
        } else {
          message.error(parsed.error || "Failed to generate today's attendance");
        }
        this.setState({ generating: { ...this.state.generating, attendanceToday: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || "Failed to generate today's attendance";
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, attendanceToday: false } });
      });
  };

  handleGenerateFamilyData = () => {
    const { familyEmployeeCount } = this.state;
    this.setState({ generating: { ...this.state.generating, family: true } });

    this.getApiClient()
      .post('demo-mode/family-data', { count: familyEmployeeCount })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          const d = parsed.data || {};
          message.success(`Created ${d.dependents || 0} dependents and ${d.emergencyContacts || 0} emergency contacts for ${d.employees || 0} employees`);
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate family data');
        }
        this.setState({ generating: { ...this.state.generating, family: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate family data';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, family: false } });
      });
  };

  handleGenerateTeams = () => {
    const { teamCount, teamMembersPerTeam } = this.state;
    this.setState({ generating: { ...this.state.generating, team: true } });

    this.getApiClient()
      .post('demo-mode/teams', { teams: teamCount, membersPerTeam: teamMembersPerTeam })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          const d = parsed.data || {};
          message.success(`Created ${d.teams || 0} teams with ${d.members || 0} members`);
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate teams');
        }
        this.setState({ generating: { ...this.state.generating, team: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate teams';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, team: false } });
      });
  };

  handleGenerateTaskLists = () => {
    const { taskListCount } = this.state;
    this.setState({ generating: { ...this.state.generating, tasklist: true } });

    this.getApiClient()
      .post('demo-mode/task-lists', { count: taskListCount })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          const d = parsed.data || {};
          message.success(
            `Created ${d.taskLists || 0} task lists with ${d.tasks || 0} tasks assigned to ${d.assignments || 0} employees`,
          );
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate task lists');
        }
        this.setState({ generating: { ...this.state.generating, tasklist: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate task lists';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, tasklist: false } });
      });
  };

  handleGenerateTimesheets = () => {
    const { timesheetWeeks } = this.state;
    this.setState({ generating: { ...this.state.generating, timesheets: true } });

    this.getApiClient()
      .post('demo-mode/timesheets', { weeksBack: timesheetWeeks })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          message.success(`Created ${parsed.data?.timesheets || 0} timesheets with ${parsed.data?.entries || 0} entries`);
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate timesheets');
        }
        this.setState({ generating: { ...this.state.generating, timesheets: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate timesheets';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, timesheets: false } });
      });
  };

  handleGenerateLeave = () => {
    const { leaveCount, futureLeaveOnly } = this.state;
    this.setState({ generating: { ...this.state.generating, leave: true } });

    this.getApiClient()
      .post('demo-mode/leave', { count: leaveCount, futureOnly: futureLeaveOnly })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          message.success(`Created ${parsed.data?.created || 0} leave requests`);
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate leave requests');
        }
        this.setState({ generating: { ...this.state.generating, leave: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate leave requests';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, leave: false } });
      });
  };

  handleGenerateExpenses = () => {
    const { expenseCount } = this.state;
    this.setState({ generating: { ...this.state.generating, expense: true } });

    this.getApiClient()
      .post('demo-mode/expenses', { count: expenseCount })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          message.success(`Created ${parsed.data?.created || 0} expense requests`);
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate expense requests');
        }
        this.setState({ generating: { ...this.state.generating, expense: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate expense requests';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, expense: false } });
      });
  };

  handleGenerateOvertime = () => {
    const { overtimeCount } = this.state;
    this.setState({ generating: { ...this.state.generating, overtime: true } });

    this.getApiClient()
      .post('demo-mode/overtime', { count: overtimeCount })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          message.success(`Created ${parsed.data?.created || 0} overtime requests`);
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate overtime requests');
        }
        this.setState({ generating: { ...this.state.generating, overtime: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate overtime requests';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, overtime: false } });
      });
  };

  handleGenerateJobs = () => {
    const { jobCount } = this.state;
    this.setState({ generating: { ...this.state.generating, job: true } });

    this.getApiClient()
      .post('demo-mode/jobs', { count: jobCount })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          message.success(`Created ${parsed.data?.created || 0} job positions`);
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate job positions');
        }
        this.setState({ generating: { ...this.state.generating, job: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate job positions';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, job: false } });
      });
  };

  handleGenerateCandidates = () => {
    const { candidateCount } = this.state;
    this.setState({ generating: { ...this.state.generating, candidate: true } });

    this.getApiClient()
      .post('demo-mode/candidates', { count: candidateCount })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          message.success(`Created ${parsed.data?.created || 0} candidates`);
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate candidates');
        }
        this.setState({ generating: { ...this.state.generating, candidate: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate candidates';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, candidate: false } });
      });
  };

  handleGeneratePerformanceReviews = () => {
    const { performanceReviewCount } = this.state;
    this.setState({ generating: { ...this.state.generating, performance: true } });

    this.getApiClient()
      .post('demo-mode/performance-reviews', { count: performanceReviewCount })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          const d = parsed.data || {};
          message.success(
            `Created ${d.reviews || 0} reviews, ${d.goals || 0} goals, ${d.peerFeedback || 0} peer feedback`
          );
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate performance reviews');
        }
        this.setState({ generating: { ...this.state.generating, performance: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate performance reviews';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, performance: false } });
      });
  };

  handleGeneratePayroll = () => {
    const { payrollName, payrollDateRange } = this.state;

    if (!payrollName || !payrollDateRange || payrollDateRange.length !== 2) {
      message.error('Please enter payroll name and select date range');
      return;
    }

    this.setState({ generating: { ...this.state.generating, payroll: true } });

    this.getApiClient()
      .post('demo-mode/payroll', {
        name: payrollName,
        dateStart: payrollDateRange[0].format('YYYY-MM-DD'),
        dateEnd: payrollDateRange[1].format('YYYY-MM-DD'),
      })
      .then((response) => {
        const parsed = this.parseResponse(response);
        if (parsed.success) {
          message.success(`Created payroll with ${parsed.data?.payrollDataCount || 0} data entries`);
          this.setState({ payrollName: '', payrollDateRange: null });
          this.loadStats();
        } else {
          message.error(parsed.error || 'Failed to generate payroll');
        }
        this.setState({ generating: { ...this.state.generating, payroll: false } });
      })
      .catch((error) => {
        console.error('Error:', error);
        const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to generate payroll';
        message.error(errorMsg);
        this.setState({ generating: { ...this.state.generating, payroll: false } });
      });
  };

  // Delete several demo data types in order under one confirmation — used for
  // recruitment data where Applications cascade from Candidates/Job but their
  // tracking entries still need cleaning up.
  handleDeleteTypes = (label, types, stateKey) => {
    Modal.confirm({
      title: `Delete ${label}?`,
      icon: <ExclamationCircleOutlined />,
      content: `This will permanently delete all demo ${label.toLowerCase()}. This action cannot be undone.`,
      okText: 'Delete',
      okType: 'danger',
      cancelText: 'Cancel',
      onOk: async () => {
        this.setState({ deleting: { ...this.state.deleting, [stateKey]: true } });
        try {
          let total = 0;
          for (const type of types) {
            // eslint-disable-next-line no-await-in-loop
            const response = await this.getApiClient().delete(`demo-mode/data/${type}`);
            const parsed = this.parseResponse(response);
            if (parsed.success) {
              total += parsed.data?.deleted || 0;
            }
          }
          message.success(`Deleted ${total} records`);
          this.loadStats();
        } catch (error) {
          console.error('Error:', error);
          const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to delete data';
          message.error(errorMsg);
        }
        this.setState({ deleting: { ...this.state.deleting, [stateKey]: false } });
      },
    });
  };

  handleDeleteByType = (dataType) => {
    Modal.confirm({
      title: `Delete ${dataType} data?`,
      icon: <ExclamationCircleOutlined />,
      content: `This will permanently delete all demo ${dataType} data. This action cannot be undone.`,
      okText: 'Delete',
      okType: 'danger',
      cancelText: 'Cancel',
      onOk: () => {
        this.setState({ deleting: { ...this.state.deleting, [dataType]: true } });

        this.getApiClient()
          .delete(`demo-mode/data/${dataType}`)
          .then((response) => {
            const parsed = this.parseResponse(response);
            if (parsed.success) {
              message.success(`Deleted ${parsed.data?.deleted || 0} ${dataType} records`);
              this.loadStats();
            } else {
              message.error(parsed.error || 'Failed to delete data');
            }
            this.setState({ deleting: { ...this.state.deleting, [dataType]: false } });
          })
          .catch((error) => {
            console.error('Error:', error);
            const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to delete data';
            message.error(errorMsg);
            this.setState({ deleting: { ...this.state.deleting, [dataType]: false } });
          });
      },
    });
  };

  handleDeleteAll = () => {
    Modal.confirm({
      title: 'Delete ALL demo data?',
      icon: <ExclamationCircleOutlined />,
      content: (
        <div>
          <p>This will permanently delete ALL demo data including:</p>
          <ul>
            <li>Employees</li>
            <li>Attendance records</li>
            <li>Timesheets</li>
            <li>Leave requests</li>
            <li>Payroll records</li>
            <li>Projects and Clients</li>
          </ul>
          <p><strong>This action cannot be undone!</strong></p>
        </div>
      ),
      okText: 'Delete All',
      okType: 'danger',
      cancelText: 'Cancel',
      onOk: () => {
        this.setState({ deleting: { ...this.state.deleting, all: true } });

        this.getApiClient()
          .delete('demo-mode/data')
          .then((response) => {
            const parsed = this.parseResponse(response);
            if (parsed.success) {
              message.success('All demo data deleted successfully');
              this.loadStats();
            } else {
              message.error(parsed.error || 'Failed to delete data');
            }
            this.setState({ deleting: { ...this.state.deleting, all: false } });
          })
          .catch((error) => {
            console.error('Error:', error);
            const errorMsg = error.response?.data?.error?.[0]?.[0]?.message || 'Failed to delete data';
            message.error(errorMsg);
            this.setState({ deleting: { ...this.state.deleting, all: false } });
          });
      },
    });
  };

  render() {
    const {
      loading,
      demoModeEnabled,
      stats,
      employeeCount,
      attendanceDays,
      todayAttendancePercentage,
      familyEmployeeCount,
      teamCount,
      teamMembersPerTeam,
      taskListCount,
      timesheetWeeks,
      leaveCount,
      futureLeaveOnly,
      expenseCount,
      overtimeCount,
      jobCount,
      candidateCount,
      performanceReviewCount,
      payrollName,
      payrollDateRange,
      generating,
      deleting,
      togglingDemoMode,
      generatingAll,
    } = this.state;

    // stats also carries the non-numeric `modules` map — only sum the counters.
    const totalRecords = Object.values(stats)
      .filter((v) => typeof v === 'number')
      .reduce((a, b) => a + b, 0);

    return (
      <div style={{ padding: '24px' }}>
        <Title level={3}>Demo Mode Manager</Title>
        <Text type="secondary">
          Generate and manage demo data for testing and demonstrations.
          All generated data is tracked and can be easily removed when moving to production.
        </Text>

        {/* Quick Setup Section */}
        {totalRecords === 0 && (
          <Alert
            message="Quick Setup - Add Sample Data"
            description={
              <div>
                <p style={{ marginBottom: 12 }}>
                  Create 20 employees, 5 projects, 2 years of attendance, leave requests, and timesheets.
                  You can clear all sample data with one click when you're ready to go live.
                </p>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  onClick={this.handleGenerateAll}
                  loading={generatingAll}
                >
                  {generatingAll ? 'Generating...' : 'Add Sample Data'}
                </Button>
              </div>
            }
            type="success"
            icon={<RocketOutlined />}
            showIcon
            style={{ marginBottom: 24 }}
          />
        )}

        <Divider />

        {/* Demo Mode Status */}
        <Card
          size="small"
          style={{ marginBottom: 24 }}
        >
          <Row align="middle" justify="space-between">
            <Col>
              <Space>
                {demoModeEnabled ? (
                  <CheckCircleOutlined style={{ fontSize: 24, color: '#52c41a' }} />
                ) : (
                  <StopOutlined style={{ fontSize: 24, color: '#d9d9d9' }} />
                )}
                <div>
                  <Text strong style={{ fontSize: 16 }}>
                    Demo Mode: {demoModeEnabled ? 'Enabled' : 'Disabled'}
                  </Text>
                  <br />
                  <Text type="secondary">
                    {demoModeEnabled
                      ? 'All new data entries are being tracked for cleanup.'
                      : 'Enable to automatically track all new data entries.'}
                  </Text>
                </div>
              </Space>
            </Col>
            <Col>
              <Button
                type={demoModeEnabled ? 'default' : 'primary'}
                onClick={this.handleToggleDemoMode}
                loading={togglingDemoMode}
              >
                {demoModeEnabled ? 'Disable Demo Mode' : 'Enable Demo Mode'}
              </Button>
            </Col>
          </Row>
        </Card>

        {/* Demo Mode Disabled Warning */}
        {!demoModeEnabled && (
          <Alert
            message="Demo Mode is Disabled"
            description="Enable Demo Mode above to generate sample data. When enabled, all new data entries will be tracked for easy cleanup."
            type="info"
            showIcon
            style={{ marginBottom: 24 }}
          />
        )}

        {/* Production Ready Alert */}
        {totalRecords > 0 && (
          <Alert
            message="Ready to Go Live?"
            description={
              <div>
                <p>
                  You have <strong>{totalRecords}</strong> demo data entries.
                  Before moving to production, you should remove all demo data.
                </p>
                <Button
                  type="primary"
                  danger
                  icon={<DeleteOutlined />}
                  onClick={this.handleDeleteAll}
                  loading={deleting.all}
                  style={{ marginTop: 8 }}
                >
                  Wipe All Demo Data ({totalRecords} records)
                </Button>
              </div>
            }
            type="warning"
            icon={<WarningOutlined />}
            showIcon
            style={{ marginBottom: 24 }}
          />
        )}

        <Divider />

        {/* Stats Section */}
        <div style={{ marginBottom: 24 }}>
          <Space style={{ marginBottom: 16 }}>
            <Title level={4} style={{ margin: 0 }}>Demo Data Statistics</Title>
            <Button
              icon={<ReloadOutlined />}
              size="small"
              onClick={this.loadStats}
              loading={loading}
            >
              Refresh
            </Button>
          </Space>

          <Spin spinning={loading}>
            <Row gutter={[16, 16]}>
              <Col xs={24} sm={12} md={8} lg={6}>
                <Card size="small">
                  <Statistic
                    title="Employees"
                    value={stats.employee}
                    prefix={<UserOutlined style={{ color: '#1890ff' }} />}
                  />
                </Card>
              </Col>
              <Col xs={24} sm={12} md={8} lg={6}>
                <Card size="small">
                  <Statistic
                    title="Attendance"
                    value={stats.attendance}
                    prefix={<ClockCircleOutlined style={{ color: '#52c41a' }} />}
                  />
                </Card>
              </Col>
              <Col xs={24} sm={12} md={8} lg={6}>
                <Card size="small">
                  <Statistic
                    title="Timesheets"
                    value={stats.timesheet}
                    suffix={`/ ${stats.timesheet_entry} entries`}
                    prefix={<FileTextOutlined style={{ color: '#722ed1' }} />}
                  />
                </Card>
              </Col>
              {stats.modules && stats.modules.leave && (
              <Col xs={24} sm={12} md={8} lg={6}>
                <Card size="small">
                  <Statistic
                    title="Leave Requests"
                    value={stats.leave}
                    prefix={<CalendarOutlined style={{ color: '#fa8c16' }} />}
                  />
                </Card>
              </Col>
              )}
              {stats.modules && stats.modules.payroll && (
              <Col xs={24} sm={12} md={8} lg={6}>
                <Card size="small">
                  <Statistic
                    title="Payrolls"
                    value={stats.payroll}
                    suffix={`/ ${stats.payroll_data} data`}
                    prefix={<DollarOutlined style={{ color: '#13c2c2' }} />}
                  />
                </Card>
              </Col>
              )}
              <Col xs={24} sm={12} md={8} lg={6}>
                <Card size="small">
                  <Statistic
                    title="Projects"
                    value={stats.project}
                    prefix={<ProjectOutlined style={{ color: '#eb2f96' }} />}
                  />
                </Card>
              </Col>
              <Col xs={24} sm={12} md={8} lg={6}>
                <Card size="small">
                  <Statistic
                    title="Clients"
                    value={stats.client}
                    prefix={<TeamOutlined style={{ color: '#faad14' }} />}
                  />
                </Card>
              </Col>
              {stats.modules && stats.modules.performance && (
              <Col xs={24} sm={12} md={8} lg={6}>
                <Card size="small">
                  <Statistic
                    title="Performance Reviews"
                    value={stats.performance_review}
                    suffix={`/ ${stats.employee_goal} goals / ${stats.review_feedback} feedback`}
                    prefix={<TrophyOutlined style={{ color: '#f5222d' }} />}
                  />
                </Card>
              </Col>
              )}
            </Row>
          </Spin>
        </div>

        <Divider />

        {/* Generate Data Section */}
        <Title level={4}>Generate Demo Data</Title>
        <Row gutter={[16, 16]}>
          {/* Employees */}
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><UserOutlined /> Employees</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Number of employees:</Text>
                  <InputNumber
                    min={1}
                    max={100}
                    value={employeeCount}
                    onChange={(value) => this.setState({ employeeCount: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.employees}
                  onClick={this.handleGenerateEmployees}
                  disabled={!demoModeEnabled}
                  block
                >
                  Generate Employees
                </Button>
              </Space>
            </Card>
          </Col>

          {/* Attendance */}
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><ClockCircleOutlined /> Attendance</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Days of history:</Text>
                  <InputNumber
                    min={1}
                    max={365}
                    value={attendanceDays}
                    onChange={(value) => this.setState({ attendanceDays: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.attendance}
                  onClick={this.handleGenerateAttendance}
                  disabled={!demoModeEnabled || stats.employee === 0}
                  block
                >
                  Generate Attendance
                </Button>
                {stats.employee === 0 && demoModeEnabled && (
                  <Text type="warning">Generate employees first</Text>
                )}
              </Space>
            </Card>
          </Col>

          {/* Today's attendance for a percentage of active employees */}
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><ClockCircleOutlined /> Today&apos;s Attendance</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Percentage of employees to clock in today:</Text>
                  <InputNumber
                    min={1}
                    max={100}
                    value={todayAttendancePercentage}
                    formatter={(value) => `${value}%`}
                    parser={(value) => value.replace('%', '')}
                    onChange={(value) => this.setState({ todayAttendancePercentage: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>
                  Creates today&apos;s clock-in/out for a random {todayAttendancePercentage || 0}% of active employees.
                  Anyone who already has a record for today is skipped.
                </Text>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.attendanceToday}
                  onClick={this.handleGenerateTodayAttendance}
                  disabled={!demoModeEnabled}
                  block
                >
                  Generate Today&apos;s Attendance
                </Button>
              </Space>
            </Card>
          </Col>

          {/* Dependents + emergency contacts for a set of employees */}
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><ContactsOutlined /> Dependents &amp; Emergency Contacts</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Number of employees:</Text>
                  <InputNumber
                    min={1}
                    max={500}
                    value={familyEmployeeCount}
                    onChange={(value) => this.setState({ familyEmployeeCount: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>
                  Adds 1–3 dependents and 1–2 emergency contacts to each of the
                  randomly selected active employees.
                </Text>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.family}
                  onClick={this.handleGenerateFamilyData}
                  disabled={!demoModeEnabled}
                  block
                >
                  Generate Family Data
                </Button>
              </Space>
            </Card>
          </Col>

          {/* Teams + members — only when the team extension is installed */}
          {stats.modules && stats.modules.team && (
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><TeamOutlined /> Teams</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Number of teams:</Text>
                  <InputNumber
                    min={1}
                    max={50}
                    value={teamCount}
                    onChange={(value) => this.setState({ teamCount: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <div>
                  <Text type="secondary">Members per team:</Text>
                  <InputNumber
                    min={1}
                    max={50}
                    value={teamMembersPerTeam}
                    onChange={(value) => this.setState({ teamMembersPerTeam: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>
                  Creates named teams with a random lead and random active
                  employees as members.
                </Text>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.team}
                  onClick={this.handleGenerateTeams}
                  disabled={!demoModeEnabled}
                  block
                >
                  Generate Teams
                </Button>
              </Space>
            </Card>
          </Col>
          )}

          {/* Task lists — only when the tasks + editor extensions are installed */}
          {stats.modules && stats.modules.tasks && (
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><CheckSquareOutlined /> Task Lists</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Number of task lists:</Text>
                  <InputNumber
                    min={1}
                    max={30}
                    value={taskListCount}
                    onChange={(value) => this.setState({ taskListCount: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>
                  Creates task lists (onboarding, offboarding, audits, launches…)
                  with a title, description and a checklist of realistic tasks
                  assigned to different employees — each backed by a real editor
                  document.
                </Text>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.tasklist}
                  onClick={this.handleGenerateTaskLists}
                  disabled={!demoModeEnabled || stats.employee === 0}
                  block
                >
                  Generate Task Lists
                </Button>
              </Space>
            </Card>
          </Col>
          )}

          {/* Timesheets */}
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><FileTextOutlined /> Timesheets</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Weeks of history:</Text>
                  <InputNumber
                    min={1}
                    max={52}
                    value={timesheetWeeks}
                    onChange={(value) => this.setState({ timesheetWeeks: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.timesheets}
                  onClick={this.handleGenerateTimesheets}
                  disabled={!demoModeEnabled || stats.employee === 0}
                  block
                >
                  Generate Timesheets
                </Button>
                {stats.employee === 0 && demoModeEnabled && (
                  <Text type="warning">Generate employees first</Text>
                )}
              </Space>
            </Card>
          </Col>

          {/* Leave — only on a Pro build (leave_and_performance) */}
          {stats.modules && stats.modules.leave && (
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><CalendarOutlined /> Leave Requests</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Requests per employee:</Text>
                  <InputNumber
                    min={1}
                    max={20}
                    value={leaveCount}
                    onChange={(value) => this.setState({ leaveCount: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Checkbox
                  checked={futureLeaveOnly}
                  onChange={(e) => this.setState({ futureLeaveOnly: e.target.checked })}
                  disabled={!demoModeEnabled}
                >
                  Generate only future leave requests
                </Checkbox>
                <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>
                  When off, about half the generated requests are in the future.
                </Text>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.leave}
                  onClick={this.handleGenerateLeave}
                  disabled={!demoModeEnabled || stats.employee === 0}
                  block
                >
                  Generate Leave Requests
                </Button>
                {stats.employee === 0 && demoModeEnabled && (
                  <Text type="warning">Generate employees first</Text>
                )}
              </Space>
            </Card>
          </Col>
          )}

          {/* Expenses — only when the Expenses module is installed */}
          {stats.modules && stats.modules.expense && (
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><DollarOutlined /> Expense Requests</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Requests per employee:</Text>
                  <InputNumber
                    min={1}
                    max={20}
                    value={expenseCount}
                    onChange={(value) => this.setState({ expenseCount: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>
                  Uses the existing expense categories and payment methods.
                </Text>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.expense}
                  onClick={this.handleGenerateExpenses}
                  disabled={!demoModeEnabled || stats.employee === 0}
                  block
                >
                  Generate Expense Requests
                </Button>
                {stats.employee === 0 && demoModeEnabled && (
                  <Text type="warning">Generate employees first</Text>
                )}
              </Space>
            </Card>
          </Col>
          )}

          {/* Overtime — only when the Overtime module is installed */}
          {stats.modules && stats.modules.overtime && (
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><ClockCircleOutlined /> Overtime Requests</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Requests per employee:</Text>
                  <InputNumber
                    min={1}
                    max={20}
                    value={overtimeCount}
                    onChange={(value) => this.setState({ overtimeCount: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>
                  Evening overtime shifts using the existing overtime categories.
                </Text>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.overtime}
                  onClick={this.handleGenerateOvertime}
                  disabled={!demoModeEnabled || stats.employee === 0}
                  block
                >
                  Generate Overtime Requests
                </Button>
                {stats.employee === 0 && demoModeEnabled && (
                  <Text type="warning">Generate employees first</Text>
                )}
              </Space>
            </Card>
          </Col>
          )}

          {/* Job positions — only when the recruitment module is installed */}
          {stats.modules && stats.modules.jobposition && (
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><AuditOutlined /> Job Positions</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Number of job positions:</Text>
                  <InputNumber
                    min={1}
                    max={20}
                    value={jobCount}
                    onChange={(value) => this.setState({ jobCount: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>
                  Uses the existing recruitment setup data (employment types, experience levels, job functions, education levels).
                </Text>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.job}
                  onClick={this.handleGenerateJobs}
                  disabled={!demoModeEnabled}
                  block
                >
                  Generate Job Positions
                </Button>
              </Space>
            </Card>
          </Col>
          )}

          {/* Candidates — only when the recruitment candidates module is installed */}
          {stats.modules && stats.modules.candidate && (
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><UserAddOutlined /> Candidates</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Candidates per job position:</Text>
                  <InputNumber
                    min={1}
                    max={20}
                    value={candidateCount}
                    onChange={(value) => this.setState({ candidateCount: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>
                  Creates candidates applying to the generated demo job positions.
                </Text>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.candidate}
                  onClick={this.handleGenerateCandidates}
                  disabled={!demoModeEnabled || stats.job === 0}
                  block
                >
                  Generate Candidates
                </Button>
                {stats.job === 0 && demoModeEnabled && (
                  <Text type="warning">Generate job positions first</Text>
                )}
              </Space>
            </Card>
          </Col>
          )}

          {/* Performance Reviews — only when the leave_and_performance Performance module is installed */}
          {stats.modules && stats.modules.performance && (
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><TrophyOutlined /> Performance Reviews</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Number of reviews:</Text>
                  <InputNumber
                    min={1}
                    max={50}
                    value={performanceReviewCount}
                    onChange={(value) => this.setState({ performanceReviewCount: value })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>
                  Creates 6-month reviews for existing employees, randomly Pending, Submitted or Completed.
                  Each review gets 1–3 linked goals and 1–2 peer-feedback entries.
                </Text>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.performance}
                  onClick={this.handleGeneratePerformanceReviews}
                  disabled={!demoModeEnabled}
                  block
                >
                  Generate Performance Reviews
                </Button>
              </Space>
            </Card>
          </Col>
          )}

          {/* Payroll — only on a Pro build (payroll_config) */}
          {stats.modules && stats.modules.payroll && (
          <Col xs={24} md={12} lg={8}>
            <Card
              title={<><DollarOutlined /> Payroll</>}
              size="small"
            >
              <Space direction="vertical" style={{ width: '100%' }}>
                <div>
                  <Text type="secondary">Payroll name:</Text>
                  <Input
                    placeholder="e.g., January 2024 Payroll"
                    value={payrollName}
                    onChange={(e) => this.setState({ payrollName: e.target.value })}
                    style={{ marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <div>
                  <Text type="secondary">Pay period:</Text>
                  <RangePicker
                    value={payrollDateRange}
                    onChange={(dates) => this.setState({ payrollDateRange: dates })}
                    style={{ width: '100%', marginTop: 8 }}
                    disabled={!demoModeEnabled}
                  />
                </div>
                <Button
                  type="primary"
                  icon={<PlusOutlined />}
                  loading={generating.payroll}
                  onClick={this.handleGeneratePayroll}
                  disabled={!demoModeEnabled || stats.employee === 0}
                  block
                >
                  Generate Payroll
                </Button>
                {stats.employee === 0 && demoModeEnabled && (
                  <Text type="warning">Generate employees first</Text>
                )}
              </Space>
            </Card>
          </Col>
          )}
        </Row>

        <Divider />

        {/* Delete Data Section */}
        <Title level={4}>Delete Demo Data</Title>
        <Alert
          message="Production Mode Cleanup"
          description="When you're ready to move to production, use these options to remove all demo data.
            Data is deleted in the correct order to maintain referential integrity."
          type="info"
          showIcon
          style={{ marginBottom: 16 }}
        />

        <Row gutter={[16, 16]}>
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.employee}
              onClick={() => this.handleDeleteByType('employee')}
              disabled={stats.employee === 0}
              block
            >
              Delete Employees ({stats.employee})
            </Button>
          </Col>
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.attendance}
              onClick={() => this.handleDeleteByType('attendance')}
              disabled={stats.attendance === 0}
              block
            >
              Delete Attendance ({stats.attendance})
            </Button>
          </Col>
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.timesheet}
              onClick={() => this.handleDeleteByType('timesheet')}
              disabled={stats.timesheet === 0}
              block
            >
              Delete Timesheets ({stats.timesheet})
            </Button>
          </Col>
          {stats.modules && stats.modules.leave && (
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.leave}
              onClick={() => this.handleDeleteByType('leave')}
              disabled={stats.leave === 0}
              block
            >
              Delete Leave ({stats.leave})
            </Button>
          </Col>
          )}
          {stats.modules && stats.modules.expense && (
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.expense}
              onClick={() => this.handleDeleteByType('expense')}
              disabled={stats.expense === 0}
              block
            >
              Delete Expenses ({stats.expense})
            </Button>
          </Col>
          )}
          {stats.modules && stats.modules.overtime && (
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.overtime}
              onClick={() => this.handleDeleteByType('overtime')}
              disabled={stats.overtime === 0}
              block
            >
              Delete Overtime ({stats.overtime})
            </Button>
          </Col>
          )}
          {stats.modules && stats.modules.jobposition && (
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.job}
              onClick={() => this.handleDeleteTypes('Job Positions', ['application', 'job'], 'job')}
              disabled={stats.job === 0}
              block
            >
              Delete Job Positions ({stats.job})
            </Button>
          </Col>
          )}
          {stats.modules && stats.modules.candidate && (
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.candidate}
              onClick={() => this.handleDeleteTypes('Candidates', ['application', 'candidate'], 'candidate')}
              disabled={stats.candidate === 0}
              block
            >
              Delete Candidates ({stats.candidate})
            </Button>
          </Col>
          )}
          {stats.modules && stats.modules.performance && (
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.performance}
              onClick={() => this.handleDeleteTypes('Performance Reviews', ['review_feedback', 'employee_goal', 'performance_review'], 'performance')}
              disabled={(stats.performance_review || 0) === 0}
              block
            >
              Delete Performance Reviews ({stats.performance_review || 0})
            </Button>
          </Col>
          )}
          {stats.modules && stats.modules.team && (
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.team}
              onClick={() => this.handleDeleteTypes('Teams', ['team_member', 'team'], 'team')}
              disabled={(stats.team || 0) === 0}
              block
            >
              Delete Teams ({stats.team || 0})
            </Button>
          </Col>
          )}
          {stats.modules && stats.modules.tasks && (
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.tasklist}
              onClick={() => this.handleDeleteTypes('Task Lists', ['task_assignment', 'task_content', 'task_list'], 'tasklist')}
              disabled={(stats.task_list || 0) === 0}
              block
            >
              Delete Task Lists ({stats.task_list || 0})
            </Button>
          </Col>
          )}
          {stats.modules && stats.modules.payroll && (
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.payroll}
              onClick={() => this.handleDeleteByType('payroll')}
              disabled={stats.payroll === 0}
              block
            >
              Delete Payroll ({stats.payroll})
            </Button>
          </Col>
          )}
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.project}
              onClick={() => this.handleDeleteByType('project')}
              disabled={stats.project === 0}
              block
            >
              Delete Projects ({stats.project})
            </Button>
          </Col>
          <Col xs={24} sm={12} md={8} lg={6}>
            <Button
              danger
              icon={<DeleteOutlined />}
              loading={deleting.client}
              onClick={() => this.handleDeleteByType('client')}
              disabled={stats.client === 0}
              block
            >
              Delete Clients ({stats.client})
            </Button>
          </Col>
        </Row>

        <Divider />

        <Button
          type="primary"
          danger
          size="large"
          icon={<DeleteOutlined />}
          loading={deleting.all}
          onClick={this.handleDeleteAll}
          disabled={totalRecords === 0}
        >
          Delete ALL Demo Data ({totalRecords} records)
        </Button>
      </div>
    );
  }
}

export default DemoModeAdminExtensionView;
