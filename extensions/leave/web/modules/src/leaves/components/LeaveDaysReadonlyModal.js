/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import React from 'react';
import ReactDOM from 'react-dom';
import { Space, Tag, Modal, Table, Button, Typography, Divider, Avatar } from 'antd';
import {
  DownloadOutlined, CheckCircleOutlined, ClockCircleOutlined,
  CloseCircleOutlined, EllipsisOutlined,
} from '@ant-design/icons';

const { Text, Paragraph } = Typography;

// Leave Days Readonly Modal Component
class LeaveDaysReadonlyModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      visible: true,
    };
  }

  handleCancel = () => {
    this.setState({ visible: false });
    const { adapter } = this.props;
    if (adapter && adapter.leaveDaysReadonlyModalContainer) {
      ReactDOM.unmountComponentAtNode(adapter.leaveDaysReadonlyModalContainer);
      if (adapter.leaveDaysReadonlyModalContainer.parentNode) {
        adapter.leaveDaysReadonlyModalContainer.parentNode.removeChild(adapter.leaveDaysReadonlyModalContainer);
      }
      adapter.leaveDaysReadonlyModalContainer = null;
    }
  }

  handleDownload = () => {
    const { leaveInfo, leaveId, adapter } = this.props;
    if (leaveInfo && leaveInfo.attachment) {
      // Call the global download function
      if (typeof window.download === 'function') {
        // Close modal first, then download
        this.handleCancel();
        window.download(leaveInfo.attachment, adapter.getLeaveDaysReadonly, [leaveId]);
      } else {
        // Fallback: try to open download URL directly
        const downloadUrl = adapter.getCustomActionUrl('download', { file: leaveInfo.attachment });
        window.open(downloadUrl, '_blank');
      }
    }
  }

  // Render the approval chain: the Manager (initial approver) followed by any
  // multi-level approvers, each with avatar, name and current state (approved /
  // waiting now / upcoming / rejected).
  renderApprovalChain() {
    const { approvalChain } = this.props;
    const nodes = (approvalChain && approvalChain.nodes) || [];
    if (!nodes.length) {
      return null;
    }

    const ordinals = { 1: 'First', 2: 'Second', 3: 'Third' };
    const roleLabel = (node) => {
      if (node.role === 'Manager') {
        return 'Manager · initial approver';
      }
      return `${ordinals[node.level] || `Level ${node.level}`} level approver`;
    };

    const stateMeta = {
      approved: { color: 'success', text: 'Approved', icon: <CheckCircleOutlined /> },
      pending: { color: 'processing', text: 'Waiting for approval', icon: <ClockCircleOutlined /> },
      upcoming: { color: 'default', text: 'Upcoming', icon: <EllipsisOutlined /> },
      rejected: { color: 'error', text: 'Rejected', icon: <CloseCircleOutlined /> },
    };

    return (
      <>
        <Divider />
        <div>
          <Text strong>Approval</Text>
          <div style={{ marginTop: 12 }}>
            {nodes.map((node, i) => {
              const meta = stateMeta[node.state] || stateMeta.upcoming;
              const isPending = node.state === 'pending';
              return (
                <div
                  key={`${node.role}-${node.level}-${node.id}-${i}`}
                  style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    padding: '8px 10px',
                    marginBottom: 8,
                    borderRadius: 6,
                    border: isPending ? '1px solid #91caff' : '1px solid transparent',
                    background: isPending ? 'rgba(22, 119, 255, 0.06)' : 'transparent',
                  }}
                >
                  <Space size={12}>
                    <Avatar src={node.image}>
                      {(node.name || ' ').trim().charAt(0).toUpperCase()}
                    </Avatar>
                    <span>
                      <div><Text strong>{node.name}</Text></div>
                      <Text type="secondary" style={{ fontSize: 12 }}>{roleLabel(node)}</Text>
                    </span>
                  </Space>
                  <Tag color={meta.color} icon={meta.icon}>{meta.text}</Tag>
                </div>
              );
            })}
          </div>
        </div>
      </>
    );
  }

  render() {
    const { visible } = this.state;
    const { days, leaveInfo, leave, leaveLogs } = this.props;

    // Prepare table data for leave days
    const leaveDaysData = (days || []).map((day, index) => ({
      key: index,
      date: Date.parse(day.leave_date).toString('MMM d, yyyy (dddd)'),
      leaveType: day.leave_type,
    }));

    // Prepare table data for leave logs
    const leaveLogsData = (leaveLogs || []).map((log, index) => ({
      key: index,
      time: log.time,
      status: `${log.status_from} -> ${log.status_to}`,
      note: log.note,
    }));

    const leaveDaysColumns = [
      {
        title: 'Leave Date',
        dataIndex: 'date',
        key: 'date',
      },
      {
        title: 'Leave Type',
        dataIndex: 'leaveType',
        key: 'leaveType',
      },
    ];

    const leaveLogsColumns = [
      {
        title: 'Notes',
        key: 'notes',
        render: (text, record) => (
          <div>
            <Text type="secondary" style={{ fontSize: '12px' }}>{record.time}</Text>
            <br />
            <Text strong>{record.status}</Text>
            {record.note && (
              <>
                <br />
                <Text>{record.note}</Text>
              </>
            )}
          </div>
        ),
      },
    ];

    const leaveCount = this.props.adapter ? this.props.adapter.calculateNumberOfLeavesObject(days || []) : 0;
    const availableLeaves = leaveInfo ? parseFloat(leaveInfo.availableLeaves) : 0;

    return (
      <Modal
        title="Leave Days"
        open={visible}
        onCancel={this.handleCancel}
        width={800}
        footer={[
          <Button key="close" onClick={this.handleCancel}>
            Close
          </Button>,
        ]}
      >
        <Space direction="vertical" size="middle" style={{ width: '100%' }}>
          {/* Leave Summary */}
          <div>
            <Tag color={leaveCount > availableLeaves ? 'warning' : 'success'}>
              Number of Leaves requested: {leaveCount}
            </Tag>
          </div>

          {/* Approval chain */}
          {this.renderApprovalChain()}

          {/* Reason for Leave */}
          {leave && leave.details && (
            <>
              <div>
                <Text strong>Reason for Applying leave:</Text>
                <Paragraph>{leave.details}</Paragraph>
              </div>
              <Divider />
            </>
          )}

          {/* Leave Days Table */}
          <Table
            columns={leaveDaysColumns}
            dataSource={leaveDaysData}
            pagination={false}
            size="small"
            title={() => <Text strong>Leave Dates</Text>}
          />

          {/* Leave Logs Table */}
          {leaveLogsData.length > 0 && (
            <>
              <Divider />
              <Table
                columns={leaveLogsColumns}
                dataSource={leaveLogsData}
                pagination={false}
                size="small"
                title={() => <Text strong>Leave History</Text>}
              />
            </>
          )}

          {/* Attachment */}
          {leaveInfo && leaveInfo.attachment && (
            <>
              <Divider />
              <Button
                type="link"
                icon={<DownloadOutlined />}
                onClick={this.handleDownload}
                style={{ padding: 0 }}
              >
                View Attachment
              </Button>
            </>
          )}
        </Space>
      </Modal>
    );
  }
}

export default LeaveDaysReadonlyModal;
