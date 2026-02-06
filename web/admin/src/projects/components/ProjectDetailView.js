import React, { useState, useEffect, useRef } from 'react';
import {
  Card,
  Row,
  Col,
  Statistic,
  Spin,
  Empty,
  Typography,
  Divider,
  Space,
  Tag,
  Button,
  List,
  Avatar,
  Select,
  Popconfirm,
  message,
} from 'antd';
import {
  ClockCircleOutlined,
  TeamOutlined,
  ProjectOutlined,
  CheckCircleOutlined,
  HourglassOutlined,
  ArrowLeftOutlined,
  UserAddOutlined,
  DeleteOutlined,
  UserOutlined,
} from '@ant-design/icons';
import { GroupedBar } from '@antv/g2plot';
import IceApiClient from '../../../../api/IceApiClient';

const { Title, Text } = Typography;

const getApiClient = () => {
  if (window.modJs?.apiClient) {
    return window.modJs.apiClient;
  }
  const clientBaseUrl = typeof window !== 'undefined' ? window.CLIENT_BASE_URL : '';
  return new IceApiClient('', '', clientBaseUrl, true);
};

const ProjectDetailView = ({ projectId, onBack }) => {
  const [loading, setLoading] = useState(true);
  const [data, setData] = useState(null);
  const [availableEmployees, setAvailableEmployees] = useState([]);
  const [selectedEmployee, setSelectedEmployee] = useState(null);
  const [addingEmployee, setAddingEmployee] = useState(false);
  const [removingEmployee, setRemovingEmployee] = useState(null);

  // Chart refs
  const employeeChartRef = useRef(null);
  const monthlyChartRef = useRef(null);
  const employeeChartInstance = useRef(null);
  const monthlyChartInstance = useRef(null);

  useEffect(() => {
    if (projectId) {
      fetchProjectStats();
      fetchAvailableEmployees();
    }

    return () => {
      // Cleanup charts on unmount
      if (employeeChartInstance.current) {
        employeeChartInstance.current.destroy();
        employeeChartInstance.current = null;
      }
      if (monthlyChartInstance.current) {
        monthlyChartInstance.current.destroy();
        monthlyChartInstance.current = null;
      }
    };
  }, [projectId]);

  useEffect(() => {
    if (data && !loading) {
      // Small delay to ensure DOM is ready
      setTimeout(() => {
        renderEmployeeChart();
        renderMonthlyChart();
      }, 100);
    }
  }, [data, loading]);

  const fetchProjectStats = async (showLoading = true) => {
    if (showLoading) {
      setLoading(true);

      // Cleanup existing charts
      if (employeeChartInstance.current) {
        employeeChartInstance.current.destroy();
        employeeChartInstance.current = null;
      }
      if (monthlyChartInstance.current) {
        monthlyChartInstance.current.destroy();
        monthlyChartInstance.current = null;
      }
    }

    const apiClient = getApiClient();
    try {
      const response = await apiClient.get(`projects/${projectId}/time-stats`);
      if (response.data) {
        setData(response.data);
      }
    } catch (error) {
      console.error('Failed to fetch project time stats', error);
    } finally {
      if (showLoading) {
        setLoading(false);
      }
    }
  };

  const fetchAvailableEmployees = async () => {
    const apiClient = getApiClient();
    try {
      const response = await apiClient.get(`projects/${projectId}/available-employees`);
      if (response.data) {
        setAvailableEmployees(response.data);
      }
    } catch (error) {
      console.error('Failed to fetch available employees', error);
    }
  };

  const handleAddEmployee = async () => {
    if (!selectedEmployee) return;

    setAddingEmployee(true);
    const apiClient = getApiClient();
    try {
      const response = await apiClient.post(`projects/${projectId}/employees`, {
        employee_id: selectedEmployee,
      });
      const result = response.data || response;
      // Check for success: either status === 'SUCCESS' or response has an id (employee added)
      if (result.status === 'SUCCESS' || result.id) {
        message.success('Employee added to project');
        setSelectedEmployee(null);
        await fetchProjectStats(false);
        await fetchAvailableEmployees();
      } else {
        const errorMsg = typeof result.data === 'string' ? result.data : 'Failed to add employee';
        message.error(errorMsg);
      }
    } catch (error) {
      console.error('Failed to add employee', error);
      message.error('Failed to add employee');
    } finally {
      setAddingEmployee(false);
    }
  };

  const handleRemoveEmployee = async (employeeId) => {
    setRemovingEmployee(employeeId);
    const apiClient = getApiClient();
    try {
      const response = await apiClient.delete(`projects/${projectId}/employees/${employeeId}`);
      const result = response.data || response;
      // Check for success: status === 'SUCCESS' or HTTP 200 (response.status)
      if (result.status === 'SUCCESS' || response.status === 200) {
        message.success('Employee removed from project');
        await fetchProjectStats(false);
        await fetchAvailableEmployees();
      } else {
        const errorMsg = typeof result.data === 'string' ? result.data : 'Failed to remove employee';
        message.error(errorMsg);
      }
    } catch (error) {
      console.error('Failed to remove employee', error);
      message.error('Failed to remove employee');
    } finally {
      setRemovingEmployee(null);
    }
  };

  const renderEmployeeChart = () => {
    if (!data?.employee_breakdown?.length || !employeeChartRef.current) {
      return;
    }

    // Destroy existing chart
    if (employeeChartInstance.current) {
      employeeChartInstance.current.destroy();
      employeeChartInstance.current = null;
    }

    // Transform data for grouped horizontal bar chart
    const chartData = [];
    data.employee_breakdown.forEach((emp) => {
      const totalHours = Math.round((emp.approved_hours + emp.pending_hours) * 100) / 100;
      chartData.push({
        employee: emp.employee_name,
        value: totalHours,
        type: 'Total',
      });
      chartData.push({
        employee: emp.employee_name,
        value: Math.round(emp.approved_hours * 100) / 100,
        type: 'Approved',
      });
      chartData.push({
        employee: emp.employee_name,
        value: Math.round(emp.pending_hours * 100) / 100,
        type: 'Pending',
      });
    });

    employeeChartInstance.current = new GroupedBar(employeeChartRef.current, {
      data: chartData,
      xField: 'value',
      yField: 'employee',
      groupField: 'type',
      color: ['#1677ff', '#52c41a', '#faad14'],
      label: {
        position: 'right',
        formatter: (v) => v.value > 0 ? `${v.value}h` : '',
      },
      legend: {
        position: 'top-right',
      },
    });

    employeeChartInstance.current.render();
  };

  const renderMonthlyChart = () => {
    if (!data?.monthly_breakdown?.length || !monthlyChartRef.current) {
      return;
    }

    // Destroy existing chart
    if (monthlyChartInstance.current) {
      monthlyChartInstance.current.destroy();
      monthlyChartInstance.current = null;
    }

    // Transform data for grouped horizontal bar chart
    const chartData = [];
    data.monthly_breakdown.forEach((month) => {
      chartData.push({
        month: month.month_label,
        value: Math.round(month.total_hours * 100) / 100,
        type: 'Total',
      });
      chartData.push({
        month: month.month_label,
        value: Math.round(month.approved_hours * 100) / 100,
        type: 'Approved',
      });
      chartData.push({
        month: month.month_label,
        value: Math.round(month.pending_hours * 100) / 100,
        type: 'Pending',
      });
    });

    monthlyChartInstance.current = new GroupedBar(monthlyChartRef.current, {
      data: chartData,
      xField: 'value',
      yField: 'month',
      groupField: 'type',
      color: ['#1677ff', '#52c41a', '#faad14'],
      label: {
        position: 'right',
        formatter: (v) => v.value > 0 ? `${v.value}h` : '',
      },
      legend: {
        position: 'top-right',
      },
    });

    monthlyChartInstance.current.render();
  };

  const handleBack = () => {
    // Cleanup charts before going back
    if (employeeChartInstance.current) {
      employeeChartInstance.current.destroy();
      employeeChartInstance.current = null;
    }
    if (monthlyChartInstance.current) {
      monthlyChartInstance.current.destroy();
      monthlyChartInstance.current = null;
    }
    onBack();
  };

  const formatHours = (hours) => {
    const h = Math.floor(hours);
    const m = Math.round((hours - h) * 60);
    return `${h}h ${m}m`;
  };

  const getEmployeeAvatar = (employee) => {
    if (employee.image) {
      // Image is already a full URL from the API (S3 URL, secure URL, or generated image)
      return employee.image;
    }
    return null;
  };

  const getStatusColor = (status) => {
    switch (status) {
      case 'Current':
        return 'green';
      case 'Completed':
        return 'blue';
      case 'Inactive':
        return 'default';
      default:
        return 'default';
    }
  };

  return (
    <div style={{ padding: '0 5px' }}>
      {/* Header with Back Button */}
      <Card size="small" style={{ marginBottom: 16 }}>
        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <Space>
            <ProjectOutlined style={{ fontSize: 18 }} />
            <Title level={4} style={{ margin: 0 }}>
              {data?.project?.name || 'Project Details'}
            </Title>
            {data?.project?.status && (
              <Tag color={data.project.status === 'Active' ? 'green' : 'default'}>
                {data.project.status}
              </Tag>
            )}
          </Space>
          <Button
            type="text"
            icon={<ArrowLeftOutlined />}
            onClick={handleBack}
          >
            Back to Projects
          </Button>
        </div>
      </Card>

      {loading ? (
        <div style={{ textAlign: 'center', padding: 50 }}>
          <Spin size="large" />
        </div>
      ) : data ? (
        <Row gutter={[16, 16]}>
          {/* Left Side - Assigned Employees */}
          <Col xs={24} md={8}>
            <Card
              size="small"
              title={
                <Space>
                  <TeamOutlined />
                  <span>Assigned Employees ({data.assigned_employees?.length || 0})</span>
                </Space>
              }
              style={{ height: '100%' }}
            >
              {/* Add Employee Section */}
              <div style={{ marginBottom: 16 }}>
                <Space.Compact style={{ width: '100%' }}>
                  <Select
                    showSearch
                    placeholder="Select employee to add"
                    style={{ width: '100%' }}
                    value={selectedEmployee}
                    onChange={setSelectedEmployee}
                    optionFilterProp="children"
                    filterOption={(input, option) =>
                      (option?.label ?? '').toLowerCase().includes(input.toLowerCase())
                    }
                    options={availableEmployees.map((emp) => ({
                      value: emp.id,
                      label: `${emp.first_name} ${emp.last_name}`,
                    }))}
                  />
                  <Button
                    type="primary"
                    icon={<UserAddOutlined />}
                    onClick={handleAddEmployee}
                    loading={addingEmployee}
                    disabled={!selectedEmployee}
                  >
                    Add
                  </Button>
                </Space.Compact>
              </div>

              {/* Employee List */}
              {data.assigned_employees?.length > 0 ? (
                <List
                  itemLayout="horizontal"
                  dataSource={data.assigned_employees}
                  renderItem={(employee) => (
                    <List.Item
                      actions={[
                        <Popconfirm
                          key="remove"
                          title="Remove employee from project?"
                          description="This will remove the employee assignment but not their time entries."
                          onConfirm={() => handleRemoveEmployee(employee.id)}
                          okText="Remove"
                          cancelText="Cancel"
                        >
                          <Button
                            type="text"
                            danger
                            size="small"
                            icon={<DeleteOutlined />}
                            loading={removingEmployee === employee.id}
                          />
                        </Popconfirm>,
                      ]}
                    >
                      <List.Item.Meta
                        avatar={
                          <Avatar
                            src={getEmployeeAvatar(employee)}
                            icon={!employee.image && <UserOutlined />}
                          />
                        }
                        title={`${employee.first_name} ${employee.last_name}`}
                        description={
                          <Space size="small">
                            <Tag color={getStatusColor(employee.status)} size="small">
                              {employee.status}
                            </Tag>
                            {employee.date_start && (
                              <Text type="secondary" style={{ fontSize: 12 }}>
                                Since {employee.date_start}
                              </Text>
                            )}
                          </Space>
                        }
                      />
                    </List.Item>
                  )}
                />
              ) : (
                <Empty
                  image={Empty.PRESENTED_IMAGE_SIMPLE}
                  description="No employees assigned"
                />
              )}
            </Card>
          </Col>

          {/* Right Side - Statistics and Charts */}
          <Col xs={24} md={16}>
            {/* Summary Statistics */}
            <Row gutter={[16, 16]}>
              <Col xs={24} sm={8}>
                <Card size="small">
                  <Statistic
                    title="Total Hours"
                    value={formatHours(data.summary?.total_hours || 0)}
                    prefix={<ClockCircleOutlined />}
                  />
                </Card>
              </Col>
              <Col xs={24} sm={8}>
                <Card size="small">
                  <Statistic
                    title="Approved Hours"
                    value={formatHours(data.summary?.total_approved_hours || 0)}
                    prefix={<CheckCircleOutlined style={{ color: '#52c41a' }} />}
                    valueStyle={{ color: '#52c41a' }}
                  />
                </Card>
              </Col>
              <Col xs={24} sm={8}>
                <Card size="small">
                  <Statistic
                    title="Pending Hours"
                    value={formatHours(data.summary?.total_pending_hours || 0)}
                    prefix={<HourglassOutlined style={{ color: '#faad14' }} />}
                    valueStyle={{ color: '#faad14' }}
                  />
                </Card>
              </Col>
            </Row>

            <Divider style={{ margin: '16px 0' }} />

            {/* Employee Time Chart */}
            <Card
              size="small"
              title={
                <Space>
                  <TeamOutlined />
                  <span>Time by Employee</span>
                </Space>
              }
              style={{ marginBottom: 16 }}
            >
              {data.employee_breakdown?.length > 0 ? (
                <div ref={employeeChartRef} style={{ height: 300 }} />
              ) : (
                <Empty
                  image={Empty.PRESENTED_IMAGE_SIMPLE}
                  description="No time entries recorded"
                />
              )}
            </Card>

            {/* Monthly Breakdown Chart */}
            <Card
              size="small"
              title={
                <Space>
                  <ClockCircleOutlined />
                  <span>Monthly Time Breakdown</span>
                </Space>
              }
            >
              {data.monthly_breakdown?.length > 0 ? (
                <div ref={monthlyChartRef} style={{ height: 300 }} />
              ) : (
                <Empty
                  image={Empty.PRESENTED_IMAGE_SIMPLE}
                  description="No time entries recorded"
                />
              )}
            </Card>
          </Col>
        </Row>
      ) : (
        <Empty description="Failed to load project data" />
      )}
    </div>
  );
};

export default ProjectDetailView;
