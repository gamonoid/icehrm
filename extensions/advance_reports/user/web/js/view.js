import React from 'react';
import {
  Card,
  Row,
  Col,
  List,
  Typography,
  Skeleton,
  Empty,
  Space,
  Tabs,
  Form,
  Select,
  DatePicker,
  Button,
  message,
  Collapse,
  Tag,
  Divider,
  Result,
  Spin,
  Table,
} from 'antd';
import {
  FileTextOutlined,
  DownloadOutlined,
  FileExcelOutlined,
  FolderOutlined,
  RightOutlined,
  ArrowLeftOutlined,
  SearchOutlined,
} from '@ant-design/icons';

const { Title, Text, Paragraph } = Typography;
const { Panel } = Collapse;
const { Option } = Select;

class Advance_reportsUserExtensionView extends React.Component {
  state = {
    reports: {},
    loading: true,
    selectedReport: null,
    formLoading: false,
    generating: false,
    remoteSources: {},
    reportData: null,
    reportColumns: [],
    downloadUrl: null,
  };

  componentDidMount() {
    this.fetchReports();
  }

  getApiClient() {
    return window.advance_reportsExtensionController.getApiClient();
  }

  fetchReports() {
    this.getApiClient()
      .get('user/advance_reports/reports')
      .then((response) => {
        this.setState({
          reports: response.data || {},
          loading: false,
        });
      })
      .catch((error) => {
        message.error('Failed to load reports');
        this.setState({ loading: false });
      });
  }

  fetchRemoteSource(model) {
    if (this.state.remoteSources[model]) {
      return Promise.resolve(this.state.remoteSources[model]);
    }

    return this.getApiClient()
      .get(`user/advance_reports/source/${model}`)
      .then((response) => {
        this.setState((prevState) => ({
          remoteSources: {
            ...prevState.remoteSources,
            [model]: response.data || [],
          },
        }));
        return response.data || [];
      })
      .catch(() => {
        message.error(`Failed to load ${model} data`);
        return [];
      });
  }

  selectReport(report) {
    // Scroll to top when a report is selected
    window.scrollTo({ top: 0, behavior: 'smooth' });

    this.setState({
      selectedReport: report,
      formLoading: true,
      reportData: null,
      reportColumns: [],
      downloadUrl: null,
    });

    // Pre-fetch remote sources for this report
    const remoteSourcePromises = [];
    if (report.parameters) {
      report.parameters.forEach((param) => {
        if (param.remoteSource) {
          const modelName = param.remoteSource[0];
          remoteSourcePromises.push(this.fetchRemoteSource(modelName));
        }
      });
    }

    Promise.all(remoteSourcePromises).then(() => {
      this.setState({ formLoading: false });
    });
  }

  generateReport(values) {
    const { selectedReport } = this.state;
    this.setState({ generating: true, reportData: null, reportColumns: [], downloadUrl: null });

    // Transform date values
    const params = {};
    Object.keys(values).forEach((key) => {
      if (values[key] && values[key].format) {
        // DatePicker value is a dayjs object with format method
        params[key] = values[key].format('YYYY-MM-DD');
      } else if (Array.isArray(values[key])) {
        params[key] = JSON.stringify(values[key]);
      } else {
        params[key] = values[key];
      }
    });

    this.getApiClient()
      .post(`user/advance_reports/reports/${selectedReport.id}/generate`, params)
      .then((response) => {
        this.setState({ generating: false });
        if (response.data.success) {
          if (response.data.count === 0) {
            message.warning('No data found for the given parameters');
            this.setState({ reportData: [], reportColumns: [] });
          } else {
            message.success(`Found ${response.data.count} records`);
            // Parse the data and create columns for the table
            const data = response.data.data || [];
            if (data.length > 0) {
              const headers = data[0];
              const columns = headers.map((header, index) => ({
                title: header,
                dataIndex: `col_${index}`,
                key: `col_${index}`,
                ellipsis: true,
              }));

              // Convert array data to objects for the table
              const tableData = data.slice(1).map((row, rowIndex) => {
                const rowObj = { key: rowIndex };
                row.forEach((cell, cellIndex) => {
                  rowObj[`col_${cellIndex}`] = cell;
                });
                return rowObj;
              });

              this.setState({
                reportData: tableData,
                reportColumns: columns,
                downloadUrl: response.data.url,
              });
            }
          }
        } else {
          message.error(response.data.message || 'Failed to generate report');
        }
      })
      .catch((error) => {
        this.setState({ generating: false });
        message.error('Failed to generate report');
      });
  }

  downloadReport() {
    const { downloadUrl } = this.state;
    if (downloadUrl) {
      window.open(downloadUrl, '_blank');
    }
  }

  renderReportList() {
    const { reports, loading } = this.state;

    if (loading) {
      return <Skeleton active />;
    }

    const groups = Object.keys(reports);
    if (groups.length === 0) {
      return <Empty description="No reports available" />;
    }

    return (
      <Collapse
        defaultActiveKey={groups}
        expandIcon={({ isActive }) => <RightOutlined rotate={isActive ? 90 : 0} />}
      >
        {groups.map((group) => (
          <Panel
            header={
              <Space>
                <FolderOutlined style={{ color: '#1890ff' }} />
                <Text strong>{group}</Text>
                <Tag color="blue">{reports[group].length}</Tag>
              </Space>
            }
            key={group}
          >
            <List
              itemLayout="horizontal"
              dataSource={reports[group]}
              renderItem={(report) => (
                <List.Item
                  style={{ cursor: 'pointer', padding: '12px 16px' }}
                  onClick={() => this.selectReport(report)}
                >
                  <List.Item.Meta
                    avatar={<FileExcelOutlined style={{ fontSize: 24, color: '#52c41a' }} />}
                    title={report.name}
                    description={report.description}
                  />
                </List.Item>
              )}
            />
          </Panel>
        ))}
      </Collapse>
    );
  }

  renderFormField(param) {
    const { remoteSources } = this.state;

    switch (param.type) {
      case 'date':
        return <DatePicker style={{ width: '100%' }} />;

      case 'select':
        const selectOptions = Array.isArray(param.source) ? param.source : [];
        return (
          <Select
            placeholder={param.nullLabel || `Select ${param.label}`}
            allowClear={param.allowNull}
            style={{ width: '100%' }}
          >
            {selectOptions.map((opt) => (
              <Option key={opt.value} value={opt.value}>
                {opt.label}
              </Option>
            ))}
          </Select>
        );

      case 'select2':
      case 'select2multi':
        const modelName = param.remoteSource && Array.isArray(param.remoteSource) ? param.remoteSource[0] : null;
        const remoteOptions = modelName && remoteSources[modelName];
        const options = Array.isArray(remoteOptions) ? remoteOptions : [];
        return (
          <Select
            placeholder={param.nullLabel || `Select ${param.label}`}
            allowClear={param.allowNull}
            mode={param.type === 'select2multi' ? 'multiple' : undefined}
            showSearch
            optionFilterProp="children"
            style={{ width: '100%' }}
          >
            {options.map((opt) => (
              <Option key={opt.value} value={opt.value}>
                {opt.label}
              </Option>
            ))}
          </Select>
        );

      default:
        return <Select placeholder={`Select ${param.label}`} style={{ width: '100%' }} />;
    }
  }

  renderReportForm() {
    const { selectedReport, formLoading, generating, reportData, reportColumns, downloadUrl } = this.state;

    if (!selectedReport) {
      return (
        <Result
          icon={<FileTextOutlined style={{ color: '#1890ff' }} />}
          title="Select a Report"
          subTitle="Choose a report from the list to configure and generate"
        />
      );
    }

    if (formLoading) {
      return (
        <div style={{ textAlign: 'center', padding: '50px' }}>
          <Spin size="large" />
          <Text style={{ display: 'block', marginTop: 16 }}>Loading report configuration...</Text>
        </div>
      );
    }

    return (
      <div>
        <Card
          title={
            <Space>
              <Button
                type="text"
                icon={<ArrowLeftOutlined />}
                onClick={() => this.setState({ selectedReport: null, reportData: null, reportColumns: [], downloadUrl: null })}
              />
              <FileExcelOutlined style={{ color: '#52c41a' }} />
              {selectedReport.name}
            </Space>
          }
        >
          <Paragraph type="secondary" style={{ marginBottom: 24 }}>
            {selectedReport.description}
          </Paragraph>

          <Form
            layout="vertical"
            onFinish={(values) => this.generateReport(values)}
            initialValues={{}}
          >
            <Row gutter={16}>
              {selectedReport.parameters &&
                selectedReport.parameters.map((param, index) => (
                  <Col xs={24} sm={12} md={8} key={param.name || index}>
                    <Form.Item
                      label={param.label}
                      name={param.name}
                      rules={
                        param.required
                          ? [{ required: true, message: `${param.label} is required` }]
                          : []
                      }
                    >
                      {this.renderFormField(param)}
                    </Form.Item>
                  </Col>
                ))}
            </Row>

            <Divider />

            <Form.Item>
              <Button
                type="primary"
                htmlType="submit"
                icon={<SearchOutlined />}
                loading={generating}
                size="large"
              >
                {generating ? 'Generating...' : 'Preview Report'}
              </Button>
            </Form.Item>
          </Form>
        </Card>

        {reportData !== null && (
          <Card
            style={{ marginTop: 16 }}
            title={
              <Space>
                <Text strong>Report Results</Text>
                <Tag color="blue">{reportData.length} records</Tag>
              </Space>
            }
            extra={
              downloadUrl && (
                <Button
                  type="primary"
                  icon={<DownloadOutlined />}
                  onClick={() => this.downloadReport()}
                >
                  Download CSV
                </Button>
              )
            }
          >
            {reportData.length === 0 ? (
              <Empty description="No data found for the selected criteria" />
            ) : (
              <Table
                columns={reportColumns}
                dataSource={reportData}
                scroll={{ x: 'max-content' }}
                size="small"
                pagination={{
                  pageSize: 20,
                  showSizeChanger: true,
                  showTotal: (total, range) => `${range[0]}-${range[1]} of ${total} records`,
                }}
              />
            )}
          </Card>
        )}
      </div>
    );
  }

  render() {
    const { selectedReport } = this.state;

    return (
      <div style={{ padding: 16 }}>

        <Row gutter={24}>
          <Col xs={24} lg={selectedReport ? 8 : 24}>
            <Card
              title="Available Reports"
              style={{ marginBottom: 16 }}
              bodyStyle={{ padding: selectedReport ? '12px' : '24px' }}
            >
              {this.renderReportList()}
            </Card>
          </Col>

          {selectedReport && (
            <Col xs={24} lg={16}>
              {this.renderReportForm()}
            </Col>
          )}
        </Row>
      </div>
    );
  }
}

export default Advance_reportsUserExtensionView;
