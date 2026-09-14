/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
/* eslint-disable prefer-template,no-underscore-dangle */
/* global timeUtils */
import React from 'react';
import ReactDOM from 'react-dom';
import {
  Space, Tag, Avatar, Modal, message,
} from 'antd';
import { InfoCircleOutlined, DeleteOutlined, SettingOutlined } from '@ant-design/icons';
import ReactModalAdapterBase, { shellThemeWrap } from '../../../../../../web/api/ReactModalAdapterBase';
import FormValidation from '../../../../../../web/api/FormValidation';
import ObjectAdapter from '../../../../../../web/api/ObjectAdapter';
import IceFormModal from '../../../../../../web/components/IceFormModal';
import IceStepFormModal from '../../../../../../web/components/IceStepFromModal';
import LeaveDaysSelectionModal from './components/LeaveDaysSelectionModal';
import LeaveDaysReadonlyModal from './components/LeaveDaysReadonlyModal';

class EmployeeLeaveAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.leaveInfo = null;
    this.currentLeaveRule = null;
  }

  getDataMapping() {
    return [
      'id',
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
        title: 'Leave Type',
        dataIndex: 'leave_type',
        sorter: true,
      },
      {
        title: 'Leave Start Date',
        dataIndex: 'date_start',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy') : '',
        sorter: true,
      },
      {
        title: 'Leave End Date',
        dataIndex: 'date_end',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy') : '',
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
      ['leave_type',
        { label: 'Leave Type', type: 'select', 'remote-source': ['LeaveType', 'id', 'name', 'getUserLeaveTypes'] }],
      ['date_start', { label: 'Leave Start Date', type: 'date', validation: '' }],
      ['date_end', { label: 'Leave End Date', type: 'date', validation: '' }],
      ['details', { label: 'Reason', type: 'textarea', validation: 'none' }],
      ['attachment', { label: 'Attachment', type: 'fileupload', validation: 'none' }],
    ];
  }

  getAddNewLabel() {
    return 'Apply Leave';
  }

  getSaveButtonLabel() {
    return 'Next';
  }

  initForm() {
    if (this.formInitialized) {
      return false;
    }
    this.formContainer = React.createRef();
    // Use getContainerEl so the SPA shell's detached containers are honoured
    // (in legacy this resolves to the same #<tab>Form element). shellThemeWrap
    // is a no-op outside dark mode.
    if (this.modalType === this.MODAL_TYPE_NORMAL) {
      ReactDOM.render(
        shellThemeWrap(
          <IceFormModal
            title={this.title || undefined}
            ref={this.formContainer}
            fields={this.getFormFields()}
            adapter={this}
            formReference={this.formReference}
            saveCallback={(values, showError, closeModal, adapter) => {
              // For new leave requests, call getLeaveDays first
              // For editing existing leaves, use default save behavior
              if (!values.id || values.id === '') {
                // Keep loading state - will be removed when Select Leave Days modal is shown or on error
                values.date_start = values.date_start.format('YYYY-MM-DD');
                values.date_end = values.date_end.format('YYYY-MM-DD');
                this.getLeaveDaysFromReactForm(values, showError, closeModal);
              } else {
                // Edit existing leave - use default save
                this.save(values);
                closeModal();
              }
            }}
          />,
        ),
        this.getContainerEl('Form'),
      );
    } else {
      ReactDOM.render(
        shellThemeWrap(
          <IceStepFormModal
            ref={this.formContainer}
            fields={this.getMappedFields()}
            adapter={this}
            formReference={this.formReference}
          />,
        ),
        this.getContainerEl('Form'),
      );
    }

    const filterDom = this.getContainerEl('FilterForm');
    if (filterDom && this.getFilters()) {
      this.filtersContainer = React.createRef();
      ReactDOM.render(
        shellThemeWrap(
          <IceFormModal
            title={this.title || undefined}
            ref={this.filtersContainer}
            fields={this.getFilters()}
            adapter={this}
            saveCallback={(values, showError, closeModal) => {
              this.setFilter(values);
              this.filtersAlreadySet = true;
              this.get([]);
              this.setTableContainerFilterData(values);
              closeModal();
            }}
          />,
        ),
        filterDom,
      );
    }

    this.formInitialized = true;
    return true;
  }

  getLeaveDaysFromReactForm(values, showError, closeModal) {
    // Validate form values
    const msg = this.doCustomValidation(values);
    if (msg != null) {
      showError(msg);
      // Remove loading state if validation fails
      if (this.formContainer && this.formContainer.current) {
        this.formContainer.current.setState({ loading: false });
      }
      return;
    }

    // Store closeModal function to call it later after Select Leave Days modal is shown
    this.closeLeaveRequestModal = closeModal;

    // Keep the modal open and loading state active
    // Call getLeaveDays with form values
    this.getLeaveDaysWithValues(values);
  }

  getLeaveDaysWithValues(params) {
    // Ensure we have required fields
    if (!params.date_start || !params.date_end || !params.leave_type) {
      // Remove loading state and close modal on error
      if (this.formContainer && this.formContainer.current) {
        this.formContainer.current.setState({ loading: false });
      }
      if (this.closeLeaveRequestModal) {
        this.closeLeaveRequestModal();
        this.closeLeaveRequestModal = null;
      }
      this.showMessage('Error', 'Please fill in all required fields');
      return;
    }

    const object = {
      start_date: params.date_start,
      end_date: params.date_end,
      leave_type: params.leave_type,
    };
    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getLeaveDaysSuccessCallBack';
    callBackData.callBackFail = 'getLeaveDaysFailCallBack';

    // Store form values for later use in add()
    this.pendingFormValues = params;

    this.customAction('getLeaveDays', 'modules=leaves', reqJson, callBackData);
  }

  saveLeaveRequest() {
    // Get selected days from adapter (set by LeaveDaysSelectionModal)
    const days = this.selectedLeaveDays || {};
    
    // Fallback to jQuery collection if selectedLeaveDays is not set (for backward compatibility)
    if (Object.keys(days).length === 0) {
      $('.days').each(function () {
        days[$(this).attr('id')] = $(this).val();
      });
    }

    // Merge with pending form values
    const object = Object.assign({}, this.pendingFormValues || {});
    object.days = JSON.stringify(days);

    // Clean up modal container
    if (this.leaveDaysModalContainer) {
      ReactDOM.unmountComponentAtNode(this.leaveDaysModalContainer);
      this.leaveDaysModalContainer = null;
    }

    // Call add with the merged object
    this.add(object, []);
  }

  // eslint-disable-next-line no-unused-vars
  add(_object, _callBackData) {
    const object = _object;
    let days = {};
    
    // If days is already a string, parse it
    if (object.days && typeof object.days === 'string') {
      days = JSON.parse(object.days);
    } else if (this.selectedLeaveDays && Object.keys(this.selectedLeaveDays).length > 0) {
      // Use days from React modal
      days = this.selectedLeaveDays;
      object.days = JSON.stringify(days);
    } else {
      // Fallback to jQuery collection (for backward compatibility)
      $('.days').each(function () {
        days[$(this).attr('id')] = $(this).val();
      });
      object.days = JSON.stringify(days);
    }

    const numberOfLeaves = this.calculateNumberOfLeaves(days);
    const availableLeaves = parseFloat(this.leaveInfo ? this.leaveInfo.availableLeaves : 0);

    const that = this;
    const callback = () => {
      const reqJson = JSON.stringify(object);
      const callBackData = [];
      callBackData.callBackData = [];
      callBackData.callBackSuccess = 'addSuccessCallBack';
      callBackData.callBackFail = 'addFailCallBack';
      that.showLoader();
      that.customAction('addLeave', 'modules=leaves', reqJson, callBackData);
    };

    if (this.currentLeaveRule && numberOfLeaves > availableLeaves && this.currentLeaveRule.apply_beyond_current === 'No') {
      this.showMessage(
        'Error Applying Leave',
        `You are trying to apply ${numberOfLeaves} leave days.
        But you are only allowed to apply for ${availableLeaves} leave days.`,
      );
      return;
    }
    callback();
  }

  // eslint-disable-next-line no-unused-vars
  addSuccessCallBack(callBackData) {
    this.hideLoader();
    // Clean up pending form values and modal
    this.pendingFormValues = null;
    this.selectedLeaveDays = null;
    
    // Clean up modal container
    if (this.leaveDaysModalContainer) {
      ReactDOM.unmountComponentAtNode(this.leaveDaysModalContainer);
      this.leaveDaysModalContainer = null;
    }
    
    // Hide any legacy DOM elements
    $('#leave_days_table_cont').hide();
    
    this.showMessage(
      'Successful', 'Leave application successful. You will be notified once your supervisor approve your leaves.',
    );
    this.get([]);
  }

  addFailCallBack(callBackData) {
    this.hideLoader();
    this.showMessage('Error Occurred while Applying Leave', callBackData);
  }

  calculateNumberOfLeaves(days) {
    let sum = 0.0;
    for (const prop in days) {
      if (days.hasOwnProperty(prop)) {
        if (days[prop] === 'Full Day') {
          sum += 1;
        } else {
          sum += 0.5;
        }
      }
    }
    return sum;
  }

  calculateNumberOfLeavesObject(days) {
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


  getLeaveDays() {
    const validator = new FormValidation(
      `${this.getTableName()}_submit`, true, { ShowPopup: false, LabelErrorClass: 'error' },
    );
    if (validator.checkValues()) {
      const params = validator.getFormParameters();

      const msg = this.doCustomValidation(params);
      if (msg == null) {
        $('#EmployeeLeaveAll_submit_error').html('');
        $('#EmployeeLeaveAll_submit_error').hide();
        const id = $(`#${this.getTableName()}_submit #id`).val();
        if (id !== null && id !== undefined && id !== '') {
          params.id = id;
        }
        const object = { start_date: params.date_start, end_date: params.date_end, leave_type: params.leave_type };
        const reqJson = JSON.stringify(object);

        const callBackData = [];
        callBackData.callBackData = [];
        callBackData.callBackSuccess = 'getLeaveDaysSuccessCallBack';
        callBackData.callBackFail = 'getLeaveDaysFailCallBack';

        this.customAction('getLeaveDays', 'modules=leaves', reqJson, callBackData);
      } else {
        $('#EmployeeLeaveAll_submit_error').html(msg);
        $('#EmployeeLeaveAll_submit_error').show();
      }
    }
  }


  getLeaveDaysSuccessCallBack(callBackData) {
    let days;
    let partialLeave;
    [days, this.leaveInfo, this.currentLeaveRule, partialLeave] = callBackData;

    // Enforce the leave type's "Require a supporting document" setting up-front,
    // before the day-selection modal. The backend returns attachment_required and
    // addLeave remains the final safety net. The request form stays open with the
    // entered values so the user can just add the attachment.
    const attachmentRequired = this.currentLeaveRule
      && this.currentLeaveRule.attachment_required === 'Yes';
    const attachmentValue = this.pendingFormValues && this.pendingFormValues.attachment;
    const hasAttachment = attachmentValue !== undefined
      && attachmentValue !== null
      && String(attachmentValue).trim() !== '';
    if (attachmentRequired && !hasAttachment) {
      if (this.formContainer && this.formContainer.current) {
        this.formContainer.current.setState({ loading: false });
      }
      this.showMessage(
        'Attachment Required',
        'A supporting document is required for this leave type. '
        + 'Please attach a document before applying.',
      );
      return false;
    }

    // Enforce the leave type's "Require a reason" setting up-front (the reason maps
    // to the "details" field). Frontend-only guard; the form stays open so the user
    // can just add the reason.
    const reasonRequired = this.currentLeaveRule
      && this.currentLeaveRule.reason_required === 'Yes';
    const reasonValue = this.pendingFormValues && this.pendingFormValues.details;
    const hasReason = reasonValue !== undefined
      && reasonValue !== null
      && String(reasonValue).trim() !== '';
    if (reasonRequired && !hasReason) {
      if (this.formContainer && this.formContainer.current) {
        this.formContainer.current.setState({ loading: false });
      }
      this.showMessage(
        'Reason Required',
        'A reason is required for this leave type. Please enter a reason before applying.',
      );
      return false;
    }

    // Count working days
    let numberOfWorkingDays = 0;
    Object.keys(days).forEach((key) => {
      if (days[key] !== '2') {
        numberOfWorkingDays++;
      }
    });

    if (numberOfWorkingDays === 0) {
      // Remove loading state and close modal on error
      if (this.formContainer && this.formContainer.current) {
        this.formContainer.current.setState({ loading: false });
      }
      if (this.closeLeaveRequestModal) {
        this.closeLeaveRequestModal();
        this.closeLeaveRequestModal = null;
      }
      this.showMessage('No Working Days', 'No working days are selected in leave period. Please change leave period');
      return false;
    }

    // Create a container for the modal if it doesn't exist
    if (!this.leaveDaysModalContainer) {
      this.leaveDaysModalContainer = document.createElement('div');
      document.body.appendChild(this.leaveDaysModalContainer);
    }

    // Render the React modal component
    ReactDOM.render(
      shellThemeWrap(
        <LeaveDaysSelectionModal
          days={days}
          leaveInfo={this.leaveInfo}
          partialLeave={partialLeave}
          adapter={this}
        />,
      ),
      this.leaveDaysModalContainer,
    );

    // Remove loading state and close the Leave Request modal now that Select Leave Days modal is shown
    if (this.formContainer && this.formContainer.current) {
      this.formContainer.current.setState({ loading: false });
    }
    if (this.closeLeaveRequestModal) {
      this.closeLeaveRequestModal();
      this.closeLeaveRequestModal = null;
    }

    return true;
  }

  getLeaveDaysFailCallBack(callBackData) {
    // Remove loading state and close modal on error
    if (this.formContainer && this.formContainer.current) {
      this.formContainer.current.setState({ loading: false });
    }
    if (this.closeLeaveRequestModal) {
      this.closeLeaveRequestModal();
      this.closeLeaveRequestModal = null;
    }
    this.showMessage('Error Occured while Applying Leave', callBackData);
  }

  doCustomValidation(params) {
    try {
      if (params.date_start !== params.date_end) {
        const ds = new Date(params.date_start);
        const de = new Date(params.date_end);
        if (de < ds) {
          return 'Start date should be earlier than end date of the leave period';
        }
      }
    } catch (e) {
      // Do nothing
    }
    return null;
  }

  showLeaveView() {
    // Clean up leave days modal
    if (this.leaveDaysModalContainer) {
      ReactDOM.unmountComponentAtNode(this.leaveDaysModalContainer);
      this.leaveDaysModalContainer = null;
    }
    
    // Hide any legacy DOM elements
    $('#leave_days_table_cont').hide();
    
    // Reopen the React form modal with previous values
    if (this.pendingFormValues && this.formContainer && this.formContainer.current) {
      this.formContainer.current.show(this.pendingFormValues);
    }
  }

  getLeaveDaysReadonly(leaveId) {
    const object = { leave_id: leaveId };
    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getLeaveDaysReadonlySuccessCallBack';
    callBackData.callBackFail = 'getLeaveDaysReadonlyFailCallBack';

    this.customAction('getLeaveDaysReadonly', 'modules=leaves', reqJson, callBackData);
  }

  getLeaveDaysReadonlySuccessCallBack(callBackData) {
    const days = callBackData[0];
    const leaveInfo = callBackData[1];
    const leaveId = callBackData[2];
    const leave = callBackData[3];
    const leaveLogs = callBackData[4];
    const approvalChain = callBackData[5];

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
          approvalChain={approvalChain}
          adapter={this}
        />,
      ),
      this.leaveDaysReadonlyModalContainer,
    );
  }

  // Cancel a still-pending leave request natively (antd confirm + delete).
  cancelMyLeave(id) {
    Modal.confirm({
      title: this.gt('Cancel Leave'),
      content: this.gt('Are you sure you want to cancel this leave request?'),
      okText: this.gt('Cancel Leave'),
      okType: 'danger',
      cancelText: this.gt('Keep'),
      onOk: () => new Promise((resolve) => {
        this.cleanDelete(id, (httpStatus, status) => {
          if (httpStatus === 200 && status === 'SUCCESS') {
            message.success(this.gt('Leave cancelled'));
            this.get([]);
          } else {
            message.error(this.gt('Could not cancel leave'), 5);
          }
          resolve();
        });
      }),
    });
  }

  // eslint-disable-next-line no-unused-vars
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
        <Tag color="blue" onClick={() => modJs.getLeaveDaysReadonly(record.id)} style={{ cursor: 'pointer' }}>
          <InfoCircleOutlined />
          {` ${adapter.gt('Leave Days')}`}
        </Tag>
        {record.status === 'Pending' && (
          <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
            <DeleteOutlined />
            {` ${adapter.gt('Cancel Leave')}`}
          </Tag>
        )}
      </Space>
    );
  }

  getFilters() {
    return [
      ['leave_period', {
        label: 'Leave Period', type: 'select2', 'allow-null': true, 'remote-source': ['LeavePeriod', 'id', 'name'],
      }],
    ];
  }
}


/*
 * EmployeeApprovedLeaveAdapter
 */
class EmployeeApprovedLeaveAdapter extends EmployeeLeaveAdapter {
  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        <Tag color="blue" onClick={() => modJs.getLeaveDaysReadonly(record.id)} style={{ cursor: 'pointer' }}>
          <InfoCircleOutlined />
          {` ${adapter.gt('Leave Days')}`}
        </Tag>
        <Tag color="volcano" onClick={() => modJs.cancelLeave(record.id)} style={{ cursor: 'pointer' }}>
          <DeleteOutlined />
          {` ${adapter.gt('Cancel Leave')}`}
        </Tag>
      </Space>
    );
  }

  cancelLeave(id) {
    const object = {};
    object.id = id;

    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'cancelSuccessCallBack';
    callBackData.callBackFail = 'cancelFailCallBack';

    this.customAction('cancelLeave', 'modules=leaves', reqJson, callBackData);
  }

  // eslint-disable-next-line no-unused-vars
  cancelSuccessCallBack(callBackData) {
    this.showMessage('Successful', 'Leave cancellation request sent');
    this.get([]);
  }

  cancelFailCallBack(callBackData) {
    this.showMessage('Error Occurred while cancelling Leave', callBackData);
  }

  getFilters() {
    return [
      ['leave_period', {
        label: 'Leave Period', type: 'select2', 'allow-null': true, 'remote-source': ['LeavePeriod', 'id', 'name'],
      }],
    ];
  }
}


/*
 * Subordinate Leaves
 */

class SubEmployeeLeaveAdapter extends EmployeeLeaveAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.leaveStatusChangeId = null;
  }

  // The statuses a leave can transition to (label -> value). Exposed so the
  // native card list's approve workflow renders the Change Status action and
  // its status modal (submits via changeLeaveStatus on modules=leaves).
  getStatusOptionsData(currentStatus) {
    const data = {};
    if (currentStatus === 'Pending') {
      data.Approved = 'Approved';
      data.Pending = 'Pending';
      data.Rejected = 'Rejected';
    } else if (currentStatus === 'Cancellation Requested') {
      data['Cancellation Requested'] = 'Cancellation Requested';
      data.Cancelled = 'Cancelled';
      data.Approved = 'Approved';
    } else if (currentStatus === 'Approved') {
      data.Approved = 'Approved';
      data.Rejected = 'Rejected';
      data.Cancelled = 'Cancelled';
    } else if (currentStatus === 'Rejected') {
      data.Approved = 'Approved';
      data.Rejected = 'Rejected';
    } else {
      data.Cancelled = 'Cancelled';
    }
    return data;
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
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy') : '',
        sorter: true,
      },
      {
        title: 'Leave End Date',
        dataIndex: 'date_end',
        render: (text) => text ? Date.parse(text).toString('MMM dd, yyyy') : '',
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
        label: 'Employee',
        type: 'select',
        'allow-null': false,
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['leave_type', { label: 'Leave Type', type: 'select', 'remote-source': ['LeaveType', 'id', 'name'] }],
      ['date_start', { label: 'Leave Start Date', type: 'date', validation: '' }],
      ['date_end', { label: 'Leave End Date', type: 'date', validation: '' }],
      ['details', { label: 'Reason', type: 'textarea', validation: 'none' }],
    ];
  }


  isSubProfileTable() {
    return true;
  }

  // Legacy method - React handles table rendering automatically
  // getCustomSuccessCallBack removed - ReactModalAdapterBase handles rendering

  getLeaveOptions(currentStatus) {
    const data = {};
    if (currentStatus === 'Approved') {
      // data["Approved"] = "Approved";
      // data["Pending"] = "Pending";
      // data["Rejected"] = "Rejected";
      // data["Cancelled"] = "Cancelled";

    } else if (currentStatus === 'Pending') {
      data.Approved = 'Approved';
      // data["Pending"] = "Pending";
      data.Rejected = 'Rejected';
    } else if (currentStatus === 'Rejected') {
      // data["Approved"] = "Approved";
      data.Rejected = 'Rejected';
    } else if (currentStatus === 'Cancelled') {
      // data["Cancelled"] = "Cancelled";

    } else if (currentStatus === 'Processing') {
      // data["Processing"] = "Processing";
    } else {
      data['Cancellation Requested'] = 'Cancellation Requested';
      data.Cancelled = 'Cancelled';
    }

    return this.generateOptions(data);
  }

  openLeaveStatus(leaveId, status) {
    $('#leaveStatusModel').modal('show');
    $('#leave_status').html(this.getLeaveOptions(status));
    $('#leave_status').val(status);
    $('#leave_reason').val('');
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
    const object = { id: this.leaveStatusChangeId, status: leaveStatus, reason };

    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'changeLeaveStatusSuccessCallBack';
    callBackData.callBackFail = 'changeLeaveStatusFailCallBack';

    this.customAction('changeLeaveStatus', 'modules=leaves', reqJson, callBackData);

    this.closeLeaveStatus();
    this.leaveStatusChangeId = null;
  }

  // eslint-disable-next-line no-unused-vars
  changeLeaveStatusSuccessCallBack(callBackData) {
    this.showMessage('Successful', 'Leave status changed successfully');
    this.get([]);
  }

  // eslint-disable-next-line no-unused-vars
  changeLeaveStatusFailCallBack(callBackData) {
    this.showMessage('Error', 'Error occurred while changing leave status');
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        <Tag color="blue" onClick={() => modJs.getLeaveDaysReadonly(record.id)} style={{ cursor: 'pointer' }}>
          <InfoCircleOutlined />
          {` ${adapter.gt('Leave Days')}`}
        </Tag>
        {(record.status === 'Pending' || record.status === 'Cancellation Requested') && (
          <Tag color="green" onClick={() => modJs.openLeaveStatus(record.id, record.status)} style={{ cursor: 'pointer' }}>
            <SettingOutlined />
            {` ${adapter.gt('Change Status')}`}
          </Tag>
        )}
      </Space>
    );
  }


  getFilters() {
    return [
      ['employee', {
        label: 'Employee',
        type: 'select2',
        'allow-null': true,
        'null-label': 'All Employees',
        'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['leave_type', {
        label: 'Leave Type',
        type: 'select',
        'allow-null': true,
        'null-label': 'All Leave Types',
        'remote-source': ['LeaveType', 'id', 'name'],
      }],
      ['leave_period', {
        label: 'Leave Period', type: 'select2', 'allow-null': true, 'remote-source': ['LeavePeriod', 'id', 'name'],
      }],
      ['status', {
        label: 'Status',
        type: 'select',
        source: [
          ['Approved', 'Approved'],
          ['Pending', 'Pending'],
          ['Rejected', 'Rejected'],
          ['Cancelled', 'Cancelled'],
          ['Cancellation Requested', 'Cancellation Requested'],
          ['Processing', 'Processing'],
        ],
      }],
    ];
  }
}


/*
 * Subordinate Leaves
 */

class EmployeeLeaveApprovalAdapter extends SubEmployeeLeaveAdapter {
  isSubProfileTable() {
    return false;
  }

  // Multi-level approval queue: only "Processing" rows are actionable, and an
  // approver may Approve or Reject them. Drives the native status workflow.
  getStatusOptionsData(currentStatus) {
    if (currentStatus === 'Processing') {
      return { Approved: 'Approved', Rejected: 'Rejected' };
    }
    return {};
  }


  getLeaveOptions(currentStatus) {
    const data = {};
    if (currentStatus === 'Approved') {
      // data["Approved"] = "Approved";

    } else if (currentStatus === 'Pending') {
      // data["Pending"] = "Pending";

    } else if (currentStatus === 'Rejected') {
      // data["Rejected"] = "Rejected";

    } else if (currentStatus === 'Cancelled') {
      // data["Cancelled"] = "Cancelled";

    } else if (currentStatus === 'Processing') {
      // data["Processing"] = "Processing";
      data.Approved = 'Approved';
      data.Rejected = 'Rejected';
    } else {
      // data["Cancellation Requested"] = "Cancellation Requested";
    }

    return this.generateOptions(data);
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        <Tag color="blue" onClick={() => modJs.getLeaveDaysReadonly(record.id)} style={{ cursor: 'pointer' }}>
          <InfoCircleOutlined />
          {` ${adapter.gt('Leave Days')}`}
        </Tag>
        {record.status === 'Processing' && (
          <Tag color="green" onClick={() => modJs.openLeaveStatus(record.id, record.status)} style={{ cursor: 'pointer' }}>
            <SettingOutlined />
            {` ${adapter.gt('Change Status')}`}
          </Tag>
        )}
      </Space>
    );
  }
}


/*
 EmployeeLeaveEntitlementAdapter
 */


class EmployeeLeaveEntitlementAdapter extends ObjectAdapter {
  getDataMapping() {
    return [
      'id',
      'name',
      'availableLeaves',
      'totalLeaves',
      'carriedForward',
      'deductedFromLeaveCarriedForward',
      'carriedForwardAvailable',
      'carriedForwardLeaveExpireDate',
      'paidTimeOff',
      'pendingLeaves',
      'approvedLeaves',
      'rejectedLeaves',
      'tobeAccrued',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Leave Type' },
      { sTitle: 'Available Leave for Current Period' },
      { sTitle: 'Total for Current Period' },
      { sTitle: 'Leave Carried Forward' },
      { sTitle: 'Deducted from Carried Forward Leave' },
      { sTitle: 'Available Carried Forward Leave' },
      { sTitle: 'Carried Forward Leave Available Until' },
      { sTitle: 'Paid Time Off' },
      { sTitle: 'Pending Leave' },
      { sTitle: 'Approved Leave' },
      { sTitle: 'Rejected Leave' },
      { sTitle: 'Leave to be Accrued' },

    ];
  }

  getFormFields() {
    return [

    ];
  }

  addDomEvents(object) {

  }

  getTemplateName() {
    return 'leave_entitlement.html';
  }

  preProcessTableData(row) {
    return row;
  }

  showActionButtons() {
    return false;
  }

  get(callBackData, loadMore) {
    const that = this;

    this.hideLoadError();

    if (!loadMore) {
      this.currentPage = 1;
      if (this.container != null) {
        this.container.html('');
      }
      this.hasMoreData = true;
      this.tableData = [];
    }

    this.start = (this.currentPage - 1) * this.pageSize;


    this.container = $(`#${this.getTableName()}`).find('.objectList');

    that.showLoader();


    const object = {};
    const reqJson = JSON.stringify(object);
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'getEntitlementSuccessCallBack';
    callBackData.callBackFail = 'getEntitlementFailCallBack';

    this.customAction('getEntitlement', 'modules=leaves', reqJson, callBackData);

    that.initFieldMasterData();

    this.trackEvent('get', this.tab, this.table);
  }


  getEntitlementSuccessCallBack(data) {
    const callBackData = [];
    this.getSuccessCallBack(callBackData, data);

    this.leaveEntitlementData = data;

    this.hideLoader();
  }

  // Promise-based fetch for the native (SPA) entitlement summary cards.
  fetchEntitlement() {
    return new Promise((resolve) => {
      const callBackData = [];
      callBackData.callBackData = [];
      callBackData.callBackSuccess = 'entitlementPromiseOk';
      callBackData.callBackFail = 'entitlementPromiseFail';
      this.entitlementResolve = resolve;
      this.customAction('getEntitlement', 'modules=leaves', JSON.stringify({}), callBackData);
    });
  }

  entitlementPromiseOk(data) {
    this.leaveEntitlementData = data;
    if (this.entitlementResolve) {
      this.entitlementResolve(data);
      this.entitlementResolve = null;
    }
  }

  entitlementPromiseFail() {
    if (this.entitlementResolve) {
      this.entitlementResolve([]);
      this.entitlementResolve = null;
    }
  }

  // eslint-disable-next-line no-unused-vars
  getEntitlementFailCallBack(data) {

  }

  showLeaveCalculation(id) {
    const leaveEntitlement = this.leaveEntitlementData.find((item) => `${item.id}` === id);
    const calculations = leaveEntitlement.calculation.map((item) => `<li>${item}</li>`);
    const message = `<ul style="font-size:13px;list-style-type:none">${calculations.join('')}</ul>`;
    this.showMessage('Leave Calculation Details', message);
  }
}

module.exports = {
  EmployeeLeaveAdapter,
  EmployeeApprovedLeaveAdapter,
  SubEmployeeLeaveAdapter,
  EmployeeLeaveApprovalAdapter,
  EmployeeLeaveEntitlementAdapter,
};
