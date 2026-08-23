/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import React from 'react';
import ReactDOM from 'react-dom';
import { Space, Tag, Modal, Table, Button, Row, Col, Select } from 'antd';

// Leave Days Selection Modal Component
class LeaveDaysSelectionModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      visible: true,
      selectedDays: {},
    };
    this.allLeaveTypes = ['Full Day', 'Half Day - Morning', 'Half Day - Afternoon', '1 Hour - Morning', '3 Hours - Morning', '2 Hours - Morning', '1 Hour - Afternoon', '3 Hours - Afternoon', '2 Hours - Afternoon'];
  }

  componentDidMount() {
    // Initialize selected days with default values
    const selectedDays = {};
    const { days, partialLeave } = this.props;
    
    // Determine available types for full and half days
    let allowedLeaveTypesForFullDays = [...this.allLeaveTypes];
    let allowedLeaveTypesForHalfDays = this.allLeaveTypes.filter((item) => item !== 'Full Day');
    
    // Handle partialLeave setting
    if (partialLeave != null && partialLeave !== '') {
      try {
        let parsedPartialLeave = partialLeave;
        if (typeof partialLeave === 'string') {
          parsedPartialLeave = JSON.parse(partialLeave);
        }
        
        if (parsedPartialLeave != null && Array.isArray(parsedPartialLeave) && parsedPartialLeave.length > 0) {
          const filteredTypes = this.allLeaveTypes.filter((x) => parsedPartialLeave.includes(x));
          allowedLeaveTypesForFullDays = filteredTypes.length > 0 ? filteredTypes : [...this.allLeaveTypes];
          if (this.allLeaveTypes.includes('Full Day') && !allowedLeaveTypesForFullDays.includes('Full Day')) {
            allowedLeaveTypesForFullDays = ['Full Day', ...allowedLeaveTypesForFullDays];
          }
          allowedLeaveTypesForHalfDays = filteredTypes.length > 0 
            ? filteredTypes.filter((item) => item !== 'Full Day')
            : this.allLeaveTypes.filter((item) => item !== 'Full Day');
        }
      } catch (e) {
        console.warn('Error parsing partialLeave:', e);
      }
    }
    
    // Safety check: ensure full days always have "Full Day" option
    if (allowedLeaveTypesForFullDays.length === 0 || 
        (this.allLeaveTypes.includes('Full Day') && !allowedLeaveTypesForFullDays.includes('Full Day'))) {
      allowedLeaveTypesForFullDays = ['Full Day', ...this.allLeaveTypes.filter((item) => item !== 'Full Day')];
    }
    
    Object.keys(days).forEach((key) => {
      const value = parseInt(days[key]);
      if (value !== 2) {
        const isFullDay = value === 1;
        const availableTypes = isFullDay ? allowedLeaveTypesForFullDays : allowedLeaveTypesForHalfDays;
        
        // For full days, prefer "Full Day" if available, otherwise use first available option
        // For half days, prefer "Half Day - Morning" if available, otherwise use first available option
        let defaultValue;
        if (isFullDay) {
          defaultValue = availableTypes.includes('Full Day') ? 'Full Day' : (availableTypes[0] || 'Full Day');
        } else {
          defaultValue = availableTypes.includes('Half Day - Morning') 
            ? 'Half Day - Morning' 
            : (availableTypes[0] || 'Half Day - Morning');
        }
        
        selectedDays[key] = defaultValue;
      }
    });
    this.setState({ selectedDays });
  }

  handleDayChange = (dateKey, value) => {
    this.setState((prevState) => ({
      selectedDays: {
        ...prevState.selectedDays,
        [dateKey]: value,
      },
    }));
  }

  handleApply = () => {
    const { adapter } = this.props;
    const { selectedDays } = this.state;
    
    // Convert selectedDays to the format expected by saveLeaveRequest
    // The backend expects keys without dashes (e.g., "20240101" instead of "2024-01-01")
    const days = {};
    Object.keys(selectedDays).forEach((dateKey) => {
      // Remove dashes and time portion from the date key
      // dateKey format: "2024-01-01" or "2024-01-01T00:00:00Z"
      // Expected format: "20240101"
      const tkey = dateKey.replace(/-/g, '').split('T')[0];
      days[tkey] = selectedDays[dateKey];
    });

    // Store days in adapter and call saveLeaveRequest
    adapter.selectedLeaveDays = days;
    adapter.saveLeaveRequest();
    this.handleCancel();
  }

  handleCancel = () => {
    this.setState({ visible: false });
    const { adapter } = this.props;
    if (adapter.leaveDaysModalContainer) {
      ReactDOM.unmountComponentAtNode(adapter.leaveDaysModalContainer);
      adapter.leaveDaysModalContainer = null;
    }
  }

  handleBack = () => {
    this.handleCancel();
    const { adapter } = this.props;
    adapter.showLeaveView();
  }

  render() {
    const { visible, selectedDays } = this.state;
    const { days, leaveInfo, partialLeave } = this.props;

    // Prepare table data
    const tableData = [];
    let numberOfWorkingDays = 0;

    // Parse partialLeave if it's a string - matching original logic but ensuring Full Day is always available for full days
    let allowedLeaveTypesForFullDays = [...this.allLeaveTypes];
    let allowedLeaveTypesForHalfDays = this.allLeaveTypes.filter((item) => item !== 'Full Day');
    
    // Handle partialLeave setting (matches original code logic)
    if (partialLeave != null && partialLeave !== '') {
      try {
        let parsedPartialLeave = partialLeave;
        if (typeof partialLeave === 'string') {
          parsedPartialLeave = JSON.parse(partialLeave);
        }
        
        if (parsedPartialLeave != null && Array.isArray(parsedPartialLeave) && parsedPartialLeave.length > 0) {
          // Filter allLeaveTypes to only include types in partialLeave
          const filteredTypes = this.allLeaveTypes.filter((x) => parsedPartialLeave.includes(x));
          // For full days: use the filtered types, but always ensure "Full Day" is available
          allowedLeaveTypesForFullDays = filteredTypes.length > 0 ? filteredTypes : [...this.allLeaveTypes];
          // Always ensure "Full Day" is available for full days if it exists in the original list
          if (this.allLeaveTypes.includes('Full Day') && !allowedLeaveTypesForFullDays.includes('Full Day')) {
            allowedLeaveTypesForFullDays = ['Full Day', ...allowedLeaveTypesForFullDays];
          }
          // For half days: filtered types without "Full Day" (matches selectH in original code)
          allowedLeaveTypesForHalfDays = filteredTypes.length > 0 
            ? filteredTypes.filter((item) => item !== 'Full Day')
            : this.allLeaveTypes.filter((item) => item !== 'Full Day');
        }
      } catch (e) {
        // If parsing fails, use default values
        console.warn('Error parsing partialLeave:', e);
      }
    }

    // Safety check: ensure full days always have "Full Day" option
    if (allowedLeaveTypesForFullDays.length === 0 || 
        (this.allLeaveTypes.includes('Full Day') && !allowedLeaveTypesForFullDays.includes('Full Day'))) {
      allowedLeaveTypesForFullDays = ['Full Day', ...this.allLeaveTypes.filter((item) => item !== 'Full Day')];
    }

    Object.keys(days).forEach((key) => {
      const value = parseInt(days[key]);
      if (value !== 2) {
        numberOfWorkingDays++;
        const isFullDay = value === 1;
        const dateStr = Date.parse(key.replace('Z', '').replace(/\.\d+/, '')).toString('MMM d, yyyy (dddd)');
        
        // Determine which leave types to show based on whether it's a full day or half day
        const availableTypes = isFullDay 
          ? allowedLeaveTypesForFullDays 
          : allowedLeaveTypesForHalfDays;

        // Determine default value: prefer "Full Day" for full days if available, otherwise first available option
        let defaultValue;
        if (isFullDay) {
          defaultValue = availableTypes.includes('Full Day') ? 'Full Day' : (availableTypes[0] || 'Full Day');
        } else {
          defaultValue = availableTypes.includes('Half Day - Morning') 
            ? 'Half Day - Morning' 
            : (availableTypes[0] || 'Half Day - Morning');
        }
        
        // Use selected value if it exists and is valid, otherwise use default
        const currentSelected = selectedDays[key];
        const selectedValue = (currentSelected && availableTypes.includes(currentSelected)) 
          ? currentSelected 
          : defaultValue;

        tableData.push({
          key,
          date: dateStr,
          isFullDay,
          availableTypes,
          selectedValue,
        });
      }
    });

    const columns = [
      {
        title: 'Leave Date',
        dataIndex: 'date',
        key: 'date',
      },
      {
        title: 'Leave Type',
        key: 'leaveType',
        render: (text, record) => {
          // Convert date key to format expected by backend (remove dashes, keep only date part)
          const dateKey = record.key;
          const tkey = dateKey.replace(/-/g, '').split('T')[0];
          return (
            <Select
              value={record.selectedValue}
              style={{ width: '100%' }}
              onChange={(value) => this.handleDayChange(record.key, value)}
              className="days"
              id={tkey}
            >
              {record.availableTypes.map((type) => (
                <Select.Option key={type} value={type}>
                  {type}
                </Select.Option>
              ))}
            </Select>
          );
        },
      },
    ];

    return (
      <Modal
        title="Select Leave Days"
        open={visible}
        onCancel={this.handleBack}
        width={800}
        footer={[
          <Button key="back" onClick={this.handleBack}>
            Back
          </Button>,
          <Button key="apply" type="primary" onClick={this.handleApply}>
            Apply
          </Button>,
        ]}
      >
        <Row gutter={16} style={{ marginBottom: 16 }}>
          <Col span={24}>
            <Space direction="vertical" size="small">
              <Tag color="default">
                Approved Leave Count: {leaveInfo?.approvedLeaves || 0}
              </Tag>
              <Tag color="warning">
                Pending Leave Count: {leaveInfo?.pendingLeaves || 0}
              </Tag>
              <Tag color="success">
                Available Leave Count: {leaveInfo?.availableLeaves || 0}
              </Tag>
            </Space>
          </Col>
        </Row>
        {numberOfWorkingDays === 0 ? (
          <div>No working days are selected in leave period. Please change leave period.</div>
        ) : (
          <Table
            columns={columns}
            dataSource={tableData}
            pagination={false}
            size="small"
          />
        )}
      </Modal>
    );
  }
}

export default LeaveDaysSelectionModal;
