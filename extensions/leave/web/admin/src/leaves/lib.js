/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import React from 'react';
import ReactDOM from 'react-dom';
import { Space, Tag, Form, Avatar, Modal, Table, Button, Typography, Divider, Select, message, List, Popconfirm, Input, Empty } from 'antd';
import {
  EditOutlined, DeleteOutlined, InfoCircleOutlined, SettingOutlined, MonitorOutlined, CopyOutlined, DownloadOutlined, UserOutlined, UsergroupAddOutlined
} from '@ant-design/icons';
import ReactifiedAdapterBase from '../../../../../../web/api/ReactifiedAdapterBase';
import { shellThemeWrap } from '../../../../../../web/api/ReactModalAdapterBase';
import IceLabel from '../../../../../../web/components/IceLabel';
import LeaveTypeWizard from './LeaveTypeWizard';
import LeavePeriodForm from './LeavePeriodForm';
import WorkDayForm from './WorkDayForm';
import ImportHolidaysModal from './ImportHolidaysModal';
import LeaveAdjustmentForm from './LeaveAdjustmentForm';

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

    const leaveCount = this.props.adapter ? this.props.adapter.calculateNumberOfLeaves(days || []) : 0;
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

const leaveTypeCommonFields = [
  [
    'default_per_year',
    {
      label: 'Leaves Per Leave Period',
      type: 'text',
      validation: 'float',
      mask: '9{1,3}.{0,1}9{0,3}',
      help: 'This is the number of leave days that can be applied by an employee per year (or the current leave period). If the leave period is less than a Year this is the number of leaves for the leave period.',
    },
  ],
  [
    'supervisor_leave_assign',
    {
      label: 'Admin can assign leave to employees',
      type: 'select',
      sort: 'none',
      source: [['Yes', 'Yes'], ['No', 'No']],
      help: 'If [ Yes ] is selected, an Admin or a Manager is able to login as an employee (Please check switch employee concept explained in employee module) and apply this type of leaves behalf of the employee.',
    },
  ],
  [
    'employee_can_apply',
    {
      label: 'Employees can apply for this leave type',
      type: 'select',
      sort: 'none',
      source: [['Yes', 'Yes'], ['No', 'No']],
      help: 'If [ No ] is selected, only an Admin or a Manager is allowed to assign this type of leave to an employee. (An employee won\'t be able to apply this type of leave).',
    },
  ],
  [
    'apply_beyond_current',
    {
      label: 'Employees can apply beyond the current leave balance',
      type: 'select',
      source: [['No', 'No'], ['Yes', 'Yes']],
      help: 'If [ Yes ], employees can apply for this leave type even if they don\'t have enough leave balance. This can be useful for medical or sick leave',
    },
  ],
  [
    'leave_accrue',
    {
      label: 'Leave Accrue Enabled',
      type: 'select',
      source: [['No', 'No'], ['Yes', 'Yes']],
      help: 'If this is set to [ Yes ], employees won\'t have all the leave days added to their leave balance at the beginning of the leave period. Instead leave days get accrued for every passing day in leave period. For an example if for a particular leave type number of leaves per period is defined as 24 and leave period (having 12 months) is stating from January, at the end of January an employee will be able apply for 2 leaves of this leave type (24/12)',
    },
  ],
  [
    'carried_forward',
    {
      label: 'Leave Carried Forward',
      type: 'select',
      source: [['No', 'No'], ['Yes', 'Yes']],
      help: 'If an employee has some leave balance remaining in previous leave period, that amount will get add to the current leave period.',
    },
  ],
  [
    'carried_forward_percentage',
    {
      label: 'Percentage of Leave Carried Forward',
      type: 'text',
      validation: 'number',
      default: '100',
      mask: '9{1,3}',
      help: 'If only a percentage of remaining leave days should be carried forward to next leave period. Should be between 0 to 100. Effective only when leave carry forwarding is enabled',
    },
  ],
  [
    'max_carried_forward_amount',
    {
      label: 'Maximum Carried Forward Amount',
      type: 'text',
      validation: 'float',
      default: '0',
      mask: '9{1,3}.{0,1}9{0,3}',
      help: 'Maximum number of leave days which can be carried forwarded from one year to another. Set to 0 for unlimited',
    }],
  [
    'carried_forward_leave_availability',
    {
      label: 'Carried Forward Leave Availability Period',
      type: 'select',
      sort: 'none',
      source: [['30', '1 Month'], ['60', '2 Months'], ['90', '3 Months'], ['120', '4 Months'], ['150', '5 Months'], ['180', '6 Months'], ['210', '7 Months'], ['240', '8 Months'], ['270', '9 Months'], ['300', '10 Months'], ['330', '11 Months'], ['365', '1 Year'], ['0', 'No Limit']],
      help: 'For how many days carried forward leaves are available from start date of next leave period.',
    },
  ],
  [
    'propotionate_on_joined_date',
    {
      label: 'Proportionate leaves on Joined Date',
      type: 'select',
      sort: 'none',
      source: [['Yes', 'Yes'], ['No', 'No']],
      help: 'Whether the available number of leaves should be calculated based on number of days employee work in a given leave period. (e.g if an employee joined in end of June, he/she will only get half of the number of leave days specified for given leave type.',
    }],
];

class LeaveTypeAdapter extends ReactifiedAdapterBase {
  // Drive the brand-new guided wizard instead of the generic step modal.
  // The wizard uses this adapter purely as the data/save backend, so the
  // server-side add/edit behaviour is unchanged. (See LeaveTypeWizard.)
  initWizard() {
    if (this.wizardInitialized) {
      return;
    }
    this.wizardRef = React.createRef();
    const container = document.createElement('div');
    document.body.appendChild(container);
    this.leaveTypeWizardContainer = container;
    ReactDOM.render(
      shellThemeWrap(<LeaveTypeWizard ref={this.wizardRef} adapter={this} />),
      container,
    );
    this.wizardInitialized = true;
  }

  renderForm(object = null, viewOnly = false) {
    if (object == null) {
      this.currentId = null;
      this.currentElement = null;
      // eslint-disable-next-line no-param-reassign
      object = this.getDefaultValues();
    }
    this.setTableLoading(false);
    this.initWizard();
    this.wizardRef.current.setViewOnly(viewOnly);
    this.wizardRef.current.show(object);
  }

  getDataMapping() {
    return [
      'id',
      'name',
      'leave_accrue',
      'carried_forward',
      'default_per_year',
      'leave_group',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID' },
      { sTitle: 'Leave Name' },
      { sTitle: 'Leave Accrue Enabled' },
      { sTitle: 'Leave Carried Forward' },
      { sTitle: 'Leaves Per Year' },
      { sTitle: 'Leaves Group' },
    ];
  }

  getFormFields() {
    const idFields = [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['name', { label: 'Leave Name', type: 'text', validation: '' }],
    ];

    const leaveFields = [...leaveTypeCommonFields];
    leaveFields.push([
      'employee_leave_period',
      {
        label: 'Use Employee Leave Period',
        type: 'select',
        sort: 'none',
        source: [['No', 'No'], ['Yes', 'Yes']],
        help: 'When Yes is selected leave period for this type of leave will start from the joined date of each employee',
      }]);

    const otherFields = [
      [
        'send_notification_emails',
        {
          label: 'Send Notification Emails',
          type: 'select',
          sort: 'none',
          source: [['Yes', 'Yes'], ['No', 'No']],
          help: 'Send leave emails or not. For some leave types you might not want to send email notifications',
        },
      ],
      ['leave_group', {
        label: 'Leave Group', type: 'select2', 'allow-null': true, 'remote-source': ['LeaveGroup', 'id', 'name'],
      }],
      [
        'leave_lock_period',
        {
          label: 'Leave Lock Period',
          type: 'select',
          sort: 'none',
          source: [['0', 'None'],['1', '1 Month'], ['2', '2 Months'], ['3', '3 Months'], ['4', '4 Months'], ['5', '5 Months'], ['6', '6 Months'], ['7', '7 Months'], ['8', '8 Months'], ['9', '9 Months'], ['10', '10 Months'], ['11', '11 Months'], ['12', '1 Year']],
          help: 'For how many months employees are not allowed to apply for the leave after joining.',
        },
      ],
      [
        'notice_period',
        {
          label: 'Leave Notice Period',
          type: 'text',
          validation: 'number',
          default: '0',
          help: 'For how many days in advance employees should apply for a leave.',
        },
      ],
      [
        'attachment_mandatory',
        {
          label: 'Require Attachment',
          type: 'select',
          sort: 'none',
          source: [['No', 'No'], ['Yes', 'Yes']],
          help: 'Is it mandatory for the employee need to attach a file when applying for the leave.',
        },
      ],
      [
        'sandwich_leave',
        {
          label: 'Sandwich Leave',
          type: 'switch',
          validation: 'none',
          help: 'Enable sandwich leave for this leave type. When enabled, employees can apply for leave that falls between two other leave periods.',
        },
      ],
      ['notes', { label: 'Notes', type: 'textarea', validation: 'none' }],
    ];

    return idFields.concat(leaveFields).concat(otherFields);
  }

  getDefaultValues() {
    return {
      employee_leave_period: 'No',
      carried_forward_percentage: 0,
      max_carried_forward_amount: 0,
      carried_forward_leave_availability: 0,
      leave_accrue: 'No',
      propotionate_on_joined_date: 'No',
      send_notification_emails: 'Yes',
      sandwich_leave: '0',
      leave_color: this.getRandomColor(500, parseInt(Math.random() * 500)),
    };
  }

  /**
   * @param numOfSteps: Total number steps to get color, means total colors
   * @param step: The step number, means the order of the color
   */
  getRandomColor(numOfSteps, step) {
    // This function generates vibrant, "evenly spaced" colours (i.e. no clustering). This is ideal for creating easily distinguishable vibrant markers in Google Maps and other apps.
    // Adam Cole, 2011-Sept-14
    // HSV to RBG adapted from: http://mjijackson.com/2008/02/rgb-to-hsl-and-rgb-to-hsv-color-model-conversion-algorithms-in-javascript
    let r, g, b;
    const h = step / numOfSteps;
    const i = ~~(h * 6);
    const f = h * 6 - i;
    const q = 1 - f;
    switch(i % 6){
      case 0: r = 1; g = f; b = 0; break;
      case 1: r = q; g = 1; b = 0; break;
      case 2: r = 0; g = 1; b = f; break;
      case 3: r = 0; g = q; b = 1; break;
      case 4: r = f; g = 0; b = 1; break;
      case 5: r = 1; g = 0; b = q; break;
    }
    return "#" + ("00" + (~ ~(r * 255)).toString(16)).slice(-2) + ("00" + (~ ~(g * 255)).toString(16)).slice(-2) + ("00" + (~ ~(b * 255)).toString(16)).slice(-2);
  }

  getMappedFields() {
    const fields = this.getFormFields();
    const steps = [
      {
        title: this.gt('Basic'),
        description: this.gt('Basic leave information'),
        fields: [
          'id',
          'name',
          'default_per_year',
          'supervisor_leave_assign',
          'employee_can_apply',
          'apply_beyond_current',
        ],
      },
      {
        title: this.gt('Carry Forward'),
        description: this.gt('Rules for carry forwarding leave'),
        fields: [
          'carried_forward',
          'carried_forward_percentage',
          'max_carried_forward_amount',
          'carried_forward_leave_availability',
        ],
      },
      {
        title: this.gt('Advanced'),
        description: this.gt('Advanced leave configurations'),
        fields: [
          'leave_group',
          'leave_accrue',
          'propotionate_on_joined_date',
          'leave_lock_period',
          'notice_period',
          'attachment_mandatory',
          'sandwich_leave',
          'employee_leave_period',
          'send_notification_emails',
          'notes',
        ],
      },
    ];

    return this.addActualFieldsForStepModal(steps, fields);
  }

  doCustomValidation(params) {
    if (params.carried_forward_percentage > 100 || params.carried_forward_percentage < 0) {
      return 'Percentage of leave carried forward should be between 0 and 100';
    }
    return null;
  }

  getFilters() {
    return [
      ['leave_group', {
        label: 'Leave Group', type: 'select2', 'allow-null': true, 'remote-source': ['LeaveGroup', 'id', 'name'],
      }],
    ];
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/leave-management/leavemanagement#leave-types';
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        {adapter.hasAccess('save') && adapter.showEdit
        && (
          <Tag color="green" onClick={() => modJs.edit(record.id)} style={{ cursor: 'pointer' }}>
            <EditOutlined />
            {` ${adapter.gt('Edit')}`}
          </Tag>
        )}
        {adapter.hasAccess('delete') && adapter.showDelete
        && (
          <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
            <DeleteOutlined />
            {` ${adapter.gt('Delete')}`}
          </Tag>
        )}
      </Space>
    );
  }
}

/*
 * Leave rules
 */

class LeaveRuleAdapter extends ReactifiedAdapterBase {
  getDataMapping() {
    return [
      'id',
      'leave_type',
      'leave_group',
      'leave_period',
      'department',
      'job_title',
      'employment_status',
      'employee',
      'exp_days',
      'default_per_year',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID' },
      { sTitle: 'Leave Type' },
      { sTitle: 'Leave Group' },
      { sTitle: 'Leave Period' },
      { sTitle: 'Department' },
      { sTitle: 'Job Title' },
      { sTitle: 'Employment Status' },
      { sTitle: 'Employee' },
      { sTitle: 'Experience (Days)' },
      { sTitle: 'Leaves Per Year' },
    ];
  }

  getFormFields() {
    const leaveFields = [...leaveTypeCommonFields];

    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['leave_type', {
        label: 'Leave Type', type: 'select2', 'allow-null': false, 'remote-source': ['LeaveType', 'id', 'name'],
      }],
      ['leave_group', {
        label: 'Leave Group', type: 'select2', 'allow-null': true, 'remote-source': ['LeaveGroup', 'id', 'name'],
      }],
      ['job_title', {
        label: 'Job Title', type: 'select2', 'allow-null': true, 'remote-source': ['JobTitle', 'id', 'name'],
      }],
      ['employment_status', {
        label: 'Employment Status', type: 'select2', 'allow-null': true, 'remote-source': ['EmploymentStatus', 'id', 'name'],
      }],
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['exp_days', {
        label: 'Required Experience (Days)', type: 'text', validation: 'number', help: 'Number of experience in days required for this rule to be applied to the employee. 0 to discard experience. Experience is calculated by taking difference in days between joined date and start date of current leave period',
      }],
      ['department', {
        label: 'Department', type: 'select', 'allow-null': true, 'null-label': 'None', 'remote-source': ['CompanyStructure', 'id', 'title'],
      }],
      ['leave_period', {
        label: 'Leave Period', type: 'select', 'allow-null': true, 'null-label': 'None', 'remote-source': ['LeavePeriod', 'id', 'name'],
      }],
    ].concat(leaveFields);
  }

  doCustomValidation(params) {
    if (params.carried_forward_percentage > 100 || params.carried_forward_percentage < 0) {
      return 'Percentage of leave carried forward should be between 0 and 100';
    }
    return null;
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'null-label': 'All Employees', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['leave_type', {
        label: 'Leave Type', type: 'select2', 'allow-null': true, 'remote-source': ['LeaveType', 'id', 'name'],
      }],
      ['leave_group', {
        label: 'Leave Group', type: 'select2', 'allow-null': true, 'remote-source': ['LeaveGroup', 'id', 'name'],
      }],
    ];
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/leave-management/leavemanagement#leave-rules-by-examples';
  }
}

/*
 * Leave periods
 */

class LeavePeriodAdapter extends ReactifiedAdapterBase {
  // Use the dedicated Leave Period form: it shows the period's leave-day usage
  // when opened and locks editing for periods that already have employee leaves.
  initPeriodForm() {
    if (this.periodFormInitialized) {
      return;
    }
    this.periodFormRef = React.createRef();
    const container = document.createElement('div');
    document.body.appendChild(container);
    this.leavePeriodFormContainer = container;
    ReactDOM.render(
      shellThemeWrap(<LeavePeriodForm ref={this.periodFormRef} adapter={this} />),
      container,
    );
    this.periodFormInitialized = true;
  }

  renderForm(object = null, viewOnly = false) {
    if (object == null) {
      this.currentId = null;
      this.currentElement = null;
      // eslint-disable-next-line no-param-reassign
      object = this.getDefaultValues();
    }
    this.setTableLoading(false);
    this.initPeriodForm();
    this.periodFormRef.current.setViewOnly(viewOnly);
    this.periodFormRef.current.show(object);
  }

  // Fetch leave-day usage for a period (cached server-side for one day).
  fetchLeavePeriodStats(id) {
    return new Promise((resolve) => {
      const callBackData = [];
      callBackData.callBackData = [];
      callBackData.callBackSuccess = 'leavePeriodStatsSuccess';
      callBackData.callBackFail = 'leavePeriodStatsFail';
      this.leavePeriodStatsResolve = resolve;
      this.customAction('getLeavePeriodStats', 'admin=leaves', JSON.stringify({ id }), callBackData);
    });
  }

  leavePeriodStatsSuccess(data) {
    if (this.leavePeriodStatsResolve) {
      this.leavePeriodStatsResolve(data);
      this.leavePeriodStatsResolve = null;
    }
  }

  leavePeriodStatsFail() {
    if (this.leavePeriodStatsResolve) {
      this.leavePeriodStatsResolve(null);
      this.leavePeriodStatsResolve = null;
    }
  }

  getDataMapping() {
    const columns = [
      'id',
      'name',
      'date_start',
      'date_end',
    ];

    if (this.countryBasedLeavePeriods === true) {
      columns.push('country');
    }

    return columns;
  }

  getHeaders() {
    if (this.countryBasedLeavePeriods === true) {
      return [
        { sTitle: 'ID', bVisible: false },
        { sTitle: 'Name' },
        { sTitle: 'Period Start' },
        { sTitle: 'Period End' },
        { sTitle: 'Country' },
      ];
    }
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Period Start' },
      { sTitle: 'Period End' },
    ];
  }

  getFormFields() {
    const formFields = [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['date_start', { label: 'Period Start', type: 'date', validation: '' }],
      ['date_end', { label: 'Period End', type: 'date', validation: '' }],
    ];

    if (this.countryBasedLeavePeriods === true) {
      formFields.push(['country', {
        label: 'Country', type: 'select', 'allow-null': true, 'null-label': 'For All Countries', 'remote-source': ['Country', 'id', 'name'],
      }]);
    }

    return formFields;
  }

  get(callBackData) {
    callBackData = { callBack: 'checkLeavePeriods' };
    super.get(callBackData);
  }

  checkLeavePeriods() {
    let numberOfActiveLeavePeriods = 0;
    for (let i = 0; i < this.sourceData.length; i++) {
      numberOfActiveLeavePeriods++;
    }

    if (numberOfActiveLeavePeriods < 1) {
      this.showMessage('Error', 'You should at least have one leave period.');
    }
  }

  getLeaveDaysReadonly(leaveId) {
    const object = { leave_id: leaveId };
    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getLeaveDaysReadonlySuccessCallBack';
    callBackData.callBackFail = 'getLeaveDaysReadonlyFailCallBack';

    this.customAction('getLeaveDaysReadonly', 'admin=leaves', reqJson, callBackData);
  }

  getLeaveDaysReadonlySuccessCallBack(callBackData) {
    const days = callBackData[0];
    const leaveInfo = callBackData[1];
    const leaveId = callBackData[2];
    const leave = callBackData[3];
    const leaveLogs = callBackData[4];

    // Clean up any existing modal first
    if (this.leaveDaysReadonlyModalContainer) {
      ReactDOM.unmountComponentAtNode(this.leaveDaysReadonlyModalContainer);
      if (this.leaveDaysReadonlyModalContainer.parentNode) {
        this.leaveDaysReadonlyModalContainer.parentNode.removeChild(this.leaveDaysReadonlyModalContainer);
      }
      this.leaveDaysReadonlyModalContainer = null;
    }

    // Create a container for the modal
    this.leaveDaysReadonlyModalContainer = document.createElement('div');
    document.body.appendChild(this.leaveDaysReadonlyModalContainer);

    // Render the React modal component
    ReactDOM.render(
      shellThemeWrap(
        <LeaveDaysReadonlyModal
          days={days}
          leaveInfo={leaveInfo}
          leaveId={leaveId}
          leave={leave}
          leaveLogs={leaveLogs}
          adapter={this}
        />,
      ),
      this.leaveDaysReadonlyModalContainer,
    );
  }

  getLeaveDaysReadonlyFailCallBack(callBackData) {
    // Clean up modal container if it exists
    if (this.leaveDaysReadonlyModalContainer) {
      ReactDOM.unmountComponentAtNode(this.leaveDaysReadonlyModalContainer);
      if (this.leaveDaysReadonlyModalContainer.parentNode) {
        this.leaveDaysReadonlyModalContainer.parentNode.removeChild(this.leaveDaysReadonlyModalContainer);
      }
      this.leaveDaysReadonlyModalContainer = null;
    }
    this.showMessage('Error', 'Error Occurred while Reading leave days from Server');
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/leave-management/advanced-leave-policy#setup-leave-periods';
  }

  getActionButtonsHtml(id) {

    let html = '<div style="width:150px;">'
      + '<img class="tableActionButton" src="_BASE_images/edit.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>'
      + '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteLeavePeriod(_id_);return false;"></img>'
      + '<img class="tableActionButton" src="_BASE_images/clone.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Duplicate" onclick="modJs.copyRow(_id_);return false;"></img>'
      + '</div>';
    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }

  deleteLeavePeriod(id) {
    if (!confirm('Are you sure you want to delete this leave period?')) {
      return;
    }
    const params = {};
    params.id = id;
    const reqJson = JSON.stringify(params);
    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'deleteLeavePeriodSuccessCallback';
    callBackData.callBackFail = 'deleteLeavePeriodFailCallback';

    this.customAction('deleteLeavePeriod', 'admin=leaves', reqJson, callBackData);
  }

  deleteLeavePeriodSuccessCallback(callBackData) {
    this.showMessage('Success', 'Leave Period Deleted');
    this.get([]);
  }

  deleteLeavePeriodFailCallback(callBackData) {
    this.showMessage('Error occurred while deleting Leave Period', callBackData);
  }

  setCountryBasedLeavePeriods(value) {
    this.countryBasedLeavePeriods = value;
  }
}

/*
 * Work Days
 */

class WorkDayAdapter extends ReactifiedAdapterBase {
  // Use a compact, dedicated form instead of the wide generic modal.
  initWorkDayForm() {
    if (this.workDayFormInitialized) {
      return;
    }
    this.workDayFormRef = React.createRef();
    const container = document.createElement('div');
    document.body.appendChild(container);
    this.workDayFormContainer = container;
    ReactDOM.render(
      shellThemeWrap(<WorkDayForm ref={this.workDayFormRef} adapter={this} />),
      container,
    );
    this.workDayFormInitialized = true;
  }

  renderForm(object = null, viewOnly = false) {
    if (object == null) {
      this.currentId = null;
      this.currentElement = null;
      // eslint-disable-next-line no-param-reassign
      object = this.getDefaultValues();
    }
    this.setTableLoading(false);
    this.initWorkDayForm();
    this.workDayFormRef.current.setViewOnly(viewOnly);
    this.workDayFormRef.current.show(object);
  }

  getDataMapping() {
    return [
      'id',
      'name',
      'status',
      'country',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Day' },
      { sTitle: 'Status' },
      { sTitle: 'Country' },

    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['name', { label: 'Day', type: 'select', source: [['Monday', 'Monday'], ['Tuesday', 'Tuesday'], ['Wednesday', 'Wednesday'], ['Thursday', 'Thursday'], ['Friday', 'Friday'], ['Saturday', 'Saturday'], ['Sunday', 'Sunday']] }],
      ['status', { label: 'Status', type: 'select', source: [['Full Day', 'Full Day'], ['Half Day', 'Half Day'], ['Non-working Day', 'Non-working Day']] }],
      ['country', {
        label: 'Country', type: 'select', 'allow-null': true, 'null-label': 'For All Countries', 'remote-source': ['Country', 'id', 'name'],
      }],
    ];
  }

  getFilters() {
    return [
      ['country', {
        label: 'Country', type: 'select', 'allow-null': true, 'null-label': 'For All Countries', 'remote-source': ['Country', 'id', 'name'],
      }],
    ];
  }

  getActionButtonsHtml(id, data) {
    let html = '<div style="width:50px;"><img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Edit" onclick="modJs.edit(_id_);return false;"></img>__DeleteButton__</div>';
    const deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';


    if (data[3] !== undefined && data[3] !== null && data[3] !== '') {
      html = html.replace(/__DeleteButton__/g, deleteButton);
    } else {
      data[3] = 'For All Countries';
      html = html.replace(/__DeleteButton__/g, '');
    }

    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => {
      return (
        <Space size="middle">
          <Tag color="green" onClick={() => modJs.edit(record.id)} style={{ cursor: 'pointer' }}>
            <EditOutlined />
            {` ${adapter.gt('Edit')}`}
          </Tag>
          {adapter.hasAccess('delete') && adapter.showDelete && record.country
            && (
            <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
                <DeleteOutlined />
                {` ${adapter.gt('Delete')}`}
              </Tag>
            )}
        </Space>
      )
    };
  }

  getCustomTableParams() {
    return {
      bPaginate: false,
      bFilter: false,
      bInfo: false,
    };
  }


  doCustomValidation(params) {
    if ((this.currentId === undefined || this.currentId == null) && params.country === 'NULL') {
      return 'Workday can not be added';
    }
    return null;
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/leave-management/leavemanagement#work-week';
  }

  beforeRenderField(record) {    
    return (fieldName, field, data) => {

      if (record?.id && record.country === null && ['name', 'country'].includes(fieldName)) {
        if (fieldName === 'country') {
          return null;
        }

        return <Form.Item
          label={this.gt(data.label)}
          key={fieldName}
          name={fieldName}
          labelCol={{ span: 6 }}
        >
          <IceLabel />
        </Form.Item>;
      }

      return field;
    }
  }
}

/*
 * Holidays
 */

class HoliDayAdapter extends ReactifiedAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'dateh',
      'status',
      'leave_group',
      'country',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Date' },
      { sTitle: 'Status' },
      { sTitle: 'Leave Group' },
      { sTitle: 'Country' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['dateh', { label: 'Date', type: 'date', validation: '' }],
      ['status', { label: 'Status', type: 'select', source: [['Full Day', 'Full Day'], ['Half Day', 'Half Day']] }],
      ['leave_group', {
        label: 'Leave Group', type: 'select2', 'allow-null': true, 'remote-source': ['LeaveGroup', 'id', 'name'],
      }],
      ['country', {
        label: 'Country', type: 'select', 'allow-null': true, 'null-label': 'For All Countries', 'remote-source': ['Country', 'id', 'name'],
      }],
    ];
  }

  getFilters() {
    return [
      ['country', {
        label: 'Country', type: 'select', 'allow-null': true, 'null-label': 'For All Countries', 'remote-source': ['Country', 'id', 'name'],
      }],
    ];
  }

  getActionButtonsHtml(id, data) {
    if (data[4] === undefined || data[4] === null || data[4] === '') {
      data[4] = 'For All Countries';
    }
    return super.getActionButtonsHtml(id, data);
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/leave-management/leavemanagement#holidays';
  }

  // "Import holidays" action shown next to Add New on the Holidays tab.
  hasCustomTopButtons() {
    return true;
  }

  getCustomTopButtons() {
    return (
      <Button icon={<DownloadOutlined />} onClick={() => this.openImportHolidays()}>
        {this.gt('Import holidays')}
      </Button>
    );
  }

  openImportHolidays() {
    if (this.importHolidaysContainer) {
      ReactDOM.unmountComponentAtNode(this.importHolidaysContainer);
      if (this.importHolidaysContainer.parentNode) {
        this.importHolidaysContainer.parentNode.removeChild(this.importHolidaysContainer);
      }
    }
    const container = document.createElement('div');
    document.body.appendChild(container);
    this.importHolidaysContainer = container;
    ReactDOM.render(
      shellThemeWrap(<ImportHolidaysModal adapter={this} container={container} />),
      container,
    );
  }

  // Countries we have bundled holiday data for (mapped to IceHRM Country ids).
  getHolidayCountries() {
    return new Promise((resolve) => {
      const callBackData = [];
      callBackData.callBackData = [];
      callBackData.callBackSuccess = 'holidayCountriesSuccess';
      callBackData.callBackFail = 'holidayCountriesFail';
      this.holidayCountriesResolve = resolve;
      this.customAction('getHolidayCountries', 'admin=leaves', JSON.stringify({}), callBackData);
    });
  }

  holidayCountriesSuccess(data) {
    if (this.holidayCountriesResolve) { this.holidayCountriesResolve(data); this.holidayCountriesResolve = null; }
  }

  holidayCountriesFail() {
    if (this.holidayCountriesResolve) { this.holidayCountriesResolve(null); this.holidayCountriesResolve = null; }
  }

  // Bundled public holidays for a country + year (each flagged if already added).
  getStoredHolidays(country, year) {
    return new Promise((resolve) => {
      const callBackData = [];
      callBackData.callBackData = [];
      callBackData.callBackSuccess = 'storedHolidaysSuccess';
      callBackData.callBackFail = 'storedHolidaysFail';
      this.storedHolidaysResolve = resolve;
      this.customAction('getStoredHolidays', 'admin=leaves', JSON.stringify({ country, year }), callBackData);
    });
  }

  storedHolidaysSuccess(data) {
    if (this.storedHolidaysResolve) { this.storedHolidaysResolve(data); this.storedHolidaysResolve = null; }
  }

  storedHolidaysFail(data) {
    if (this.storedHolidaysResolve) { this.storedHolidaysResolve({ error: data || true }); this.storedHolidaysResolve = null; }
  }

  // Bulk-insert the chosen holidays (server skips duplicates).
  importHolidays(country, holidays) {
    return new Promise((resolve) => {
      const callBackData = [];
      callBackData.callBackData = [];
      callBackData.callBackSuccess = 'importHolidaysSuccess';
      callBackData.callBackFail = 'importHolidaysFail';
      this.importHolidaysResolve = resolve;
      this.customAction('importHolidays', 'admin=leaves', JSON.stringify({ country, holidays }), callBackData, true);
    });
  }

  importHolidaysSuccess(data) {
    if (this.importHolidaysResolve) {
      this.importHolidaysResolve(data);
      this.importHolidaysResolve = null;
    }
  }

  importHolidaysFail(data) {
    if (this.importHolidaysResolve) {
      this.importHolidaysResolve({ error: data || true });
      this.importHolidaysResolve = null;
    }
  }
}


/**
 * EmployeeLeaveAdapter
 */

class EmployeeLeaveAdapter extends ReactifiedAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.leaveInfo = null;
    this.currentLeaveRule = null;
  }

  getDataMapping() {
    return [
      'id',
      'image',
      'employee',
      'leave_type',
      'date_start',
      'date_end',
      'status',
      'total_leaves',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: '' },
      { sTitle: 'Employee' },
      { sTitle: 'Leave Type' },
      { sTitle: 'Leave Start Date' },
      { sTitle: 'Leave End Date' },
      { sTitle: 'Status' },
      { sTitle: 'Total Leave Days' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: '',
        dataIndex: 'image',
        render: (text, record) => <Avatar src={text} />,
      },
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
      {
        title: 'Leave Type',
        dataIndex: 'leave_type',
        sorter: true,
      },
      {
        title: 'Leave Start Date',
        dataIndex: 'date_start',
        sorter: true,
      },
      {
        title: 'Leave End Date',
        dataIndex: 'date_end',
        sorter: true,
      },
      {
        title: 'Status',
        dataIndex: 'status',
        sorter: true,
      },
      {
        title: 'Total Leave Days',
        dataIndex: 'total_leaves',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee', type: 'select', 'allow-null': false, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['leave_type', { label: 'Leave Type', type: 'select', 'remote-source': ['LeaveType', 'id', 'name'] }],
      ['date_start', { label: 'Leave Start Date', type: 'date', validation: '' }],
      ['date_end', { label: 'Leave Start Date', type: 'date', validation: '' }],
      ['details', { label: 'Reason', type: 'textarea', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'null-label': 'All Employees', 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['leave_type', {
        label: 'Leave Type', type: 'select', 'allow-null': true, 'null-label': 'All Leave Types', 'remote-source': ['LeaveType', 'id', 'name'],
      }],
      ['leave_period', {
        label: 'Leave Period', type: 'select2', 'allow-null': true, 'remote-source': ['LeavePeriod', 'id', 'name'],
      }],
      ['status', {
        label: 'Status', type: 'select', 'allow-null': true, source: [['Approved', 'Approved'], ['Pending', 'Pending'], ['Rejected', 'Rejected'], ['Cancelled', 'Cancelled'], ['Cancellation Requested', 'Cancellation Requested'], ['Processing', 'Processing']],
      }],
    ];
  }


  calculateNumberOfLeaves(days) {
    let sum = 0.0;
    for (let i = 0; i < days.length; i++) {
      if (days[i].leave_type === 'Full Day') {
        sum += 1;
      } else if (days[i].leave_type === 'Half Day - Morning') {
        sum += 0.5;
      } else if (days[i].leave_type === 'Half Day - Afternoon') {
        sum += 0.5;
      } else if (days[i].leave_type === '1 Hour - Morning') {
        sum += 0.125;
      } else if (days[i].leave_type === '2 Hours - Morning') {
        sum += 0.25;
      } else if (days[i].leave_type === '3 Hours - Morning') {
        sum += 0.375;
      } else if (days[i].leave_type === '1 Hour - Afternoon') {
        sum += 0.125;
      } else if (days[i].leave_type === '2 Hours - Afternoon') {
        sum += 0.25;
      } else if (days[i].leave_type === '3 Hours - Afternoon') {
        sum += 0.375;
      }
    }
    return sum;
  }


  getLeaveDaysReadonly(leaveId) {
    const object = { leave_id: leaveId };
    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getLeaveDaysReadonlySuccessCallBack';
    callBackData.callBackFail = 'getLeaveDaysReadonlyFailCallBack';

    this.customAction('getLeaveDaysReadonly', 'admin=leaves', reqJson, callBackData);
  }

  getLeaveDaysReadonlySuccessCallBack(callBackData) {
    const days = callBackData[0];
    const leaveInfo = callBackData[1];
    const leaveId = callBackData[2];
    const leave = callBackData[3];
    const leaveLogs = callBackData[4];

    // Clean up any existing modal first
    if (this.leaveDaysReadonlyModalContainer) {
      ReactDOM.unmountComponentAtNode(this.leaveDaysReadonlyModalContainer);
      if (this.leaveDaysReadonlyModalContainer.parentNode) {
        this.leaveDaysReadonlyModalContainer.parentNode.removeChild(this.leaveDaysReadonlyModalContainer);
      }
      this.leaveDaysReadonlyModalContainer = null;
    }

    // Create a container for the modal
    this.leaveDaysReadonlyModalContainer = document.createElement('div');
    document.body.appendChild(this.leaveDaysReadonlyModalContainer);

    // Render the React modal component
    ReactDOM.render(
      shellThemeWrap(
        <LeaveDaysReadonlyModal
          days={days}
          leaveInfo={leaveInfo}
          leaveId={leaveId}
          leave={leave}
          leaveLogs={leaveLogs}
          adapter={this}
        />,
      ),
      this.leaveDaysReadonlyModalContainer,
    );
  }

  getLeaveDaysReadonlyFailCallBack(callBackData) {
    // Clean up modal container if it exists
    if (this.leaveDaysReadonlyModalContainer) {
      ReactDOM.unmountComponentAtNode(this.leaveDaysReadonlyModalContainer);
      if (this.leaveDaysReadonlyModalContainer.parentNode) {
        this.leaveDaysReadonlyModalContainer.parentNode.removeChild(this.leaveDaysReadonlyModalContainer);
      }
      this.leaveDaysReadonlyModalContainer = null;
    }
    this.showMessage('Error', 'Error Occurred while Reading leave days from Server');
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        <Tag color="green" onClick={() => modJs.getLeaveDaysReadonly(record.id)} style={{ cursor: 'pointer' }}>
          <InfoCircleOutlined />
          {` ${adapter.gt('Leave Days')}`}
        </Tag>

        <Tag color="blue" onClick={() => modJs.openLeaveStatus(record.id, record.status)} style={{ cursor: 'pointer' }}>
          <SettingOutlined />
          {` ${adapter.gt('Leave Status')}`}
        </Tag>

        <Tag color="red" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
          <DeleteOutlined />
          {` ${adapter.gt('Cancel Leave')}`}
        </Tag>
      </Space>);
  }

  getCustomSuccessCallBack(serverData) {
    const data = [];
    const mapping = this.getDataMapping();
    for (let i = 0; i < serverData.length; i++) {
      const row = [];
      for (let j = 0; j < mapping.length; j++) {
        row[j] = serverData[i][mapping[j]];
      }
      data.push(row);
    }

    this.tableData = data;

    this.createTable(this.getTableName());
    $(`#${this.getTableName()}Form`).hide();
    $(`#${this.getTableName()}`).show();
  }

  remoteTableSkipEmployeeRestriction() {
    return true;
  }

  // The statuses a leave can transition to, keyed label->value. Exposed as
  // getStatusOptionsData so the native card list's approve workflow picks it up
  // (it detects approvable rows via this method).
  getStatusOptionsData(currentStatus) {
    const data = {};
    if (currentStatus === 'Approved') {
      data.Approved = 'Approved';
      data.Pending = 'Pending';
      data.Rejected = 'Rejected';
      data.Cancelled = 'Cancelled';
    } else if (currentStatus === 'Pending') {
      data.Approved = 'Approved';
      data.Pending = 'Pending';
      data.Rejected = 'Rejected';
    } else if (currentStatus === 'Rejected') {
      data.Approved = 'Approved';
      data.Rejected = 'Rejected';
    } else if (currentStatus === 'Cancelled') {
      data.Cancelled = 'Cancelled';
    } else {
      data['Cancellation Requested'] = 'Cancellation Requested';
      data.Cancelled = 'Cancelled';
    }
    return data;
  }

  getLeaveOptions(currentStatus) {
    return this.generateOptions(this.getStatusOptionsData(currentStatus));
  }

  openLeaveStatus(leaveId, status) {
    $('#leaveStatusModel').modal('show');
    $('#leave_status').html(this.getLeaveOptions(status));
    $('#leave_status').val(status);
    this.leaveStatusChangeId = leaveId;
  }

  closeLeaveStatus() {
    $('#leaveStatusModel').modal('hide');
  }

  changeLeaveStatus() {
    const leaveStatus = $('#leave_status').val();
    const reason = $('#leave_reason').val();

    if (leaveStatus === undefined || leaveStatus === null || leaveStatus === '') {
      this.showMessage('Error', 'Please select leave status');
      return;
    }

    const reqJson = JSON.stringify({ id: this.leaveStatusChangeId, status: leaveStatus, reason });

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'changeLeaveStatusSuccessCallBack';
    callBackData.callBackFail = 'changeLeaveStatusFailCallBack';

    this.customAction('changeLeaveStatus', 'admin=leaves', reqJson, callBackData);

    this.closeLeaveStatus();
    this.leaveStatusChangeId = null;
  }

  changeLeaveStatusSuccessCallBack(callBackData) {
    this.showMessage('Successful', 'Leave status changed successfully');
    this.get([]);
  }

  changeLeaveStatusFailCallBack(callBackData) {
    this.showMessage('Error', callBackData);
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/leave-management/leavemanagement#employee-leaves';
  }
}


/*
 * Leave groups
 */

// Native "group members" dialog: view a leave group, search/add/remove its
// employees with profile pictures (LeaveGroupEmployee records). Opened by
// clicking a leave-group card in the SPA shell.
function LeaveGroupEmployeesModal({ adapter, group, container }) {
  const [visible, setVisible] = React.useState(true);
  const [members, setMembers] = React.useState(null); // [{ lgeId, empId, name, image }]
  const [employees, setEmployees] = React.useState([]); // [{ id, first_name, last_name, image }]
  const [empMap, setEmpMap] = React.useState({});
  const [selected, setSelected] = React.useState(null);
  const [adding, setAdding] = React.useState(false);
  const [search, setSearch] = React.useState('');

  const lge = (window.modJsList || {}).tabLeaveGroupEmployee;
  const dataBase = lge ? lge.moduleRelativeURL.replace('service.php', 'data.php') : '';
  const scope = (lge && lge.spaModuleGroup)
    ? `&mg=${encodeURIComponent(lge.spaModuleGroup)}&mn=${encodeURIComponent(lge.spaModuleName)}` : '';

  const close = () => {
    setVisible(false);
    setTimeout(() => {
      if (container) {
        ReactDOM.unmountComponentAtNode(container);
        if (container.parentNode) container.parentNode.removeChild(container);
      }
      if (adapter) adapter.leaveGroupEmployeesModalContainer = null;
    }, 200);
  };

  const fetchEmployees = () => fetch(
    `${dataBase}?t=Employee&sm=${encodeURIComponent('{}')}`
      + `&cl=${encodeURIComponent('["id","first_name","last_name","image"]')}`
      + `&ft=${encodeURIComponent('{"status":"Active"}')}`
      + `&iDisplayStart=0&iDisplayLength=3000&version=v2${scope}`,
    { method: 'POST', credentials: 'same-origin' },
  ).then((r) => r.json()).then((j) => {
    const map = {};
    (j.objects || []).forEach((e) => { map[String(e.id)] = e; });
    setEmpMap(map);
    setEmployees(j.objects || []);
    return map;
  }).catch(() => ({}));

  const fetchMembers = (map) => fetch(
    `${dataBase}?t=LeaveGroupEmployee&sm=${encodeURIComponent('{}')}`
      + `&cl=${encodeURIComponent('["id","employee","leave_group"]')}`
      + `&ft=${encodeURIComponent(JSON.stringify({ leave_group: group.id }))}`
      + `&iDisplayStart=0&iDisplayLength=2000&version=v2${scope}`,
    { method: 'POST', credentials: 'same-origin' },
  ).then((r) => r.json()).then((j) => {
    const m = map || empMap;
    setMembers((j.objects || []).map((row) => {
      const e = m[String(row.employee)];
      return {
        lgeId: row.id,
        empId: String(row.employee),
        name: e ? `${e.first_name} ${e.last_name}` : `#${row.employee}`,
        image: e ? e.image : null,
      };
    }));
  }).catch(() => setMembers([]));

  React.useEffect(() => { fetchEmployees().then((map) => fetchMembers(map)); }, []);

  const add = () => {
    if (!selected || !lge) return;
    setAdding(true);
    const obj = {
      a: 'add', t: 'LeaveGroupEmployee', leave_group: group.id, employee: selected,
    };
    if (lge.withSpaScope) lge.withSpaScope(obj);
    // eslint-disable-next-line no-undef
    $.post(lge.moduleRelativeURL, obj, (data) => {
      setAdding(false);
      if (data && data.status === 'SUCCESS') { setSelected(null); message.success('Employee added'); fetchMembers(); }
      else { message.error((data && data.message) || 'Could not add employee', 5); }
    }, 'json').fail(() => { setAdding(false); message.error('Could not add employee', 5); });
  };

  const remove = (lgeId) => {
    if (!lge || !lge.cleanDelete) return;
    lge.cleanDelete(lgeId, (httpStatus, status) => {
      if (httpStatus === 200 && status === 'SUCCESS') { message.success('Employee removed'); fetchMembers(); }
      else message.error('Could not remove employee', 5);
    });
  };

  const inGroup = new Set((members || []).map((mm) => mm.empId));
  const addOptions = employees
    .filter((e) => !inGroup.has(String(e.id)))
    .map((e) => ({
      value: e.id,
      name: `${e.first_name} ${e.last_name}`,
      label: (
        <Space>
          <Avatar size="small" src={e.image} icon={<UserOutlined />} />
          {`${e.first_name} ${e.last_name}`}
        </Space>
      ),
    }));

  const term = search.trim().toLowerCase();
  const shown = (members || []).filter((mm) => mm.name.toLowerCase().includes(term));

  return (
    <Modal
      title={(
        <Space>
          <Avatar style={{ background: '#1677ff' }} icon={<UsergroupAddOutlined />} />
          <span>{group.name || 'Leave Group'}</span>
          {members ? <Tag color="blue">{`${members.length} member${members.length === 1 ? '' : 's'}`}</Tag> : null}
        </Space>
      )}
      open={visible}
      onCancel={close}
      width={620}
      footer={[<Button key="c" onClick={close}>Close</Button>]}
    >
      {group.details ? <Paragraph type="secondary" style={{ marginTop: -4 }}>{group.details}</Paragraph> : null}

      {/* Add an employee */}
      <div style={{ display: 'flex', gap: 8, marginBottom: 12 }}>
        <Select
          showSearch
          placeholder="Add an employee to this group…"
          style={{ flex: 1 }}
          value={selected}
          onChange={setSelected}
          options={addOptions}
          optionLabelProp="label"
          filterOption={(input, option) => (option.name || '').toLowerCase().includes(input.toLowerCase())}
        />
        <Button type="primary" icon={<UsergroupAddOutlined />} loading={adding} disabled={!selected} onClick={add}>Add</Button>
      </div>

      {/* Search within the group */}
      <Input.Search
        allowClear
        placeholder="Search employees in this group…"
        style={{ marginBottom: 8 }}
        onChange={(e) => setSearch(e.target.value)}
      />

      <List
        loading={members === null}
        dataSource={shown}
        locale={{
          emptyText: (
            <Empty
              image={Empty.PRESENTED_IMAGE_SIMPLE}
              description={term ? 'No matching employee in this group' : 'No employees in this group yet'}
            />
          ),
        }}
        renderItem={(m) => (
          <List.Item
            actions={[
              <Popconfirm
                key="rm"
                title="Remove this employee from the group?"
                onConfirm={() => remove(m.lgeId)}
                okText="Remove"
                okButtonProps={{ danger: true }}
              >
                <Button type="text" size="small" danger icon={<DeleteOutlined />} />
              </Popconfirm>,
            ]}
          >
            <List.Item.Meta
              avatar={<Avatar src={m.image} icon={<UserOutlined />} />}
              title={m.name}
            />
          </List.Item>
        )}
      />
    </Modal>
  );
}

class LeaveGroupAdapter extends ReactifiedAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Details' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }

  // Filter groups by an employee — shows only the groups the employee belongs
  // to (handled server-side by LeaveGroup::getCustomFilterQuery).
  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
    ];
  }

  // Open the native "manage employees" dialog for a group (card action).
  manageGroupEmployees(groupId, group) {
    if (this.leaveGroupEmployeesModalContainer) {
      ReactDOM.unmountComponentAtNode(this.leaveGroupEmployeesModalContainer);
      if (this.leaveGroupEmployeesModalContainer.parentNode) {
        this.leaveGroupEmployeesModalContainer.parentNode.removeChild(this.leaveGroupEmployeesModalContainer);
      }
    }
    const container = document.createElement('div');
    document.body.appendChild(container);
    this.leaveGroupEmployeesModalContainer = container;
    ReactDOM.render(
      shellThemeWrap(<LeaveGroupEmployeesModal adapter={this} group={group || { id: groupId }} container={container} />),
      container,
    );
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/leave-management/leavemanagement#leave-groups';
  }
}


/*
 * Leave group employee
 */

class LeaveGroupEmployeeAdapter extends ReactifiedAdapterBase {
  getDataMapping() {
    return [
      'id',
      'employee',
      'leave_group',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Leave Group' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],
      ['leave_group', { label: 'Leave Group', type: 'select2', 'remote-source': ['LeaveGroup', 'id', 'name'] }],
    ];
  }

  getFilters() {
    return [
      ['leave_group', {
        label: 'Leave Group', type: 'select2', 'allow-null': true, 'remote-source': ['LeaveGroup', 'id', 'name'],
      }],
    ];
  }
}


/*
 * LeaveStartingBalance
 */

class LeaveStartingBalanceAdapter extends ReactifiedAdapterBase {
  // Use a dedicated form with an Increase/Decrease toggle; the amount stays a
  // positive decimal and the sign is applied when saving.
  initAdjustmentForm() {
    if (this.adjustmentFormInitialized) {
      return;
    }
    this.adjustmentFormRef = React.createRef();
    const container = document.createElement('div');
    document.body.appendChild(container);
    this.leaveAdjustmentFormContainer = container;
    ReactDOM.render(
      shellThemeWrap(<LeaveAdjustmentForm ref={this.adjustmentFormRef} adapter={this} />),
      container,
    );
    this.adjustmentFormInitialized = true;
  }

  renderForm(object = null, viewOnly = false) {
    if (object == null) {
      this.currentId = null;
      this.currentElement = null;
      // eslint-disable-next-line no-param-reassign
      object = this.getDefaultValues();
    }
    this.setTableLoading(false);
    this.initAdjustmentForm();
    this.adjustmentFormRef.current.setViewOnly(viewOnly);
    this.adjustmentFormRef.current.show(object);
  }

  getDataMapping() {
    return [
      'id',
      'leave_type',
      'employee',
      'leave_period',
      'amount',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Leave Type' },
      { sTitle: 'Employee' },
      { sTitle: 'Leaves Period' },
      { sTitle: 'Adjustment' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['leave_type', {
        label: 'Leave Type', type: 'select2', 'allow-null': false, 'remote-source': ['LeaveType', 'id', 'name'],
      }],
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': false, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['leave_period', {
        label: 'Leave Period', type: 'select2', 'allow-null': false, 'remote-source': ['LeavePeriod', 'id', 'name'],
      }],
      ['amount', { label: 'Adjustment Amount', type: 'text', validation: 'float' }],
      ['note', { label: 'Note', type: 'textarea', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['leave_type', {
        label: 'Leave Type', type: 'select2', 'allow-null': true, 'remote-source': ['LeaveType', 'id', 'name'],
      }],
      ['leave_period', {
        label: 'Leave Period', type: 'select2', 'allow-null': true, 'remote-source': ['LeavePeriod', 'id', 'name'],
      }],
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
    ];
  }

  getHelpLink() {
    return 'https://icehrm.gitbook.io/icehrm/leave-management/leavemanagement#implementing-a-sample-leave-policy';
  }
}

module.exports = {
  LeaveTypeAdapter,
  LeaveRuleAdapter,
  LeavePeriodAdapter,
  WorkDayAdapter,
  HoliDayAdapter,
  EmployeeLeaveAdapter,
  LeaveGroupAdapter,
  LeaveGroupEmployeeAdapter,
  LeaveStartingBalanceAdapter,
};
