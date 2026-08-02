/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import { Space, Tag } from 'antd';
import {
  CheckCircleOutlined, DeleteOutlined, EditOutlined, LoginOutlined, MonitorOutlined,
} from '@ant-design/icons';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';

/**
 * EmployeeTrainingSessions Adapter
 */

class EmployeeTrainingSessionAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'trainingSession',
      'status',
      'courseId',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Training Session' },
      { sTitle: 'Status' },
      { sTitle: 'Course ID', bVisible: false },
    ];
  }

  getTableColumns() {
    return [
      { title: 'Training Session', dataIndex: 'trainingSession', sorter: true },
      { title: 'Status', dataIndex: 'status' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['trainingSession', {
        label: 'Training Session', type: 'placeholder', 'allow-null': false, 'remote-source': ['TrainingSession', 'id', 'name'],
      }],
      ['status', { label: 'Status', type: 'placeholder', source: [['Scheduled', 'Scheduled'], ['Attended', 'Attended'], ['Not-Attended', 'Not-Attended']] }],
      ['proof', { label: 'Proof of Completion', type: 'fileupload', validation: 'none' }],
      ['feedBack', { label: 'Feedback', type: 'textarea', validation: 'none' }],
    ];
  }

  completed(trainingSessionId) {
    const object = {};
    object.sessionId = trainingSessionId;
    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'completedSuccessCallBack';
    callBackData.callBackFail = 'completedFailCallBack';

    this.customAction('sessionAttended', 'modules=training', reqJson, callBackData);
  }


  completedSuccessCallBack(callBackData) {
    this.showMessage('Successful', callBackData);
    this.get([]);
  }

  completedFailCallBack(callBackData) {
    this.showMessage('Error Occurred while completing training session', callBackData);
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        {adapter.hasAccess('save') && adapter.showEdit && (
          <Tag color="green" onClick={() => this.edit(record.id)} style={{ cursor: 'pointer' }}>
            <EditOutlined />
            {` ${adapter.gt('Provide Feedback')}`}
          </Tag>
        )}
        {record.status === 'Scheduled' && (
          <Tag color="blue" onClick={() => this.completed(record.id)} style={{ cursor: 'pointer' }}>
            <CheckCircleOutlined />
            {` ${adapter.gt('Completed')}`}
          </Tag>
        )}
        {adapter.hasAccess('delete') && adapter.showDelete && (
          <Tag color="volcano" onClick={() => this.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
            <DeleteOutlined />
            {` ${adapter.gt('Delete')}`}
          </Tag>
        )}
      </Space>
    );
  }

  getActionButtonsHtml(id, data) {
    const editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Provide Feedback" onclick="modJs.edit(_id_);return false;"></img>';
    const deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
    const courseInfoButton = '<img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;margin-left:10px;" rel="tooltip" title="Course Information" onclick="modJsList[\'tabTrainingSession\'].edit(_cid_);return false;"></img>';
    const completedButton = '<img class="tableActionButton" src="_BASE_images/check_icon.png" style="cursor:pointer;margin-left:10px;" rel="tooltip" title="Completed" onclick="modJs.completed(_id_);return false;"></img>';
    let html = '<div style="width:100px;">_edit__course__delete__completed_</div>';

    if (this.showDelete) {
      if (this.checkPermission('Delete Assigned Training Sessions') === 'No'
        && data[3] === 'Assign') {
        html = html.replace('_delete_', '');
      } else {
        html = html.replace('_delete_', deleteButton);
      }
    } else {
      html = html.replace('_delete_', '');
    }

    if (this.showEdit) {
      html = html.replace('_edit_', editButton);
    } else {
      html = html.replace('_edit_', '');
    }

    if (data[2] === 'Scheduled') {
      html = html.replace('_completed_', completedButton);
    } else {
      html = html.replace('_completed_', '');
    }


    html = html.replace('_course_', courseInfoButton);
    html = html.replace(/_id_/g, id);
    // eslint-disable-next-line no-underscore-dangle
    html = html.replace(/_cid_/g, data._org.trainingSession_id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }
}

/**
 * TrainingSessionAdapter
 */


class TrainingSessionAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'course',
      'scheduled',
      'deliveryMethod',
      'deliveryLocation',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Course' },
      { sTitle: 'Scheduled Time' },
      { sTitle: 'Training Type' },
      { sTitle: 'Location' },
    ];
  }

  getTableColumns() {
    return [
      { title: 'Name', dataIndex: 'name', sorter: true },
      { title: 'Course', dataIndex: 'course' },
      { title: 'Scheduled Time', dataIndex: 'scheduled', sorter: true },
      { title: 'Training Type', dataIndex: 'deliveryMethod' },
      { title: 'Location', dataIndex: 'deliveryLocation' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'placeholder', validation: '' }],
      ['course', { label: 'Course', type: 'placeholder', 'remote-source': ['Course', 'id', 'name+code'] }],

      // From course
      // [ "code", {"label":"Code","type":"placeholder","validation":""}],
      // [ "name", {"label":"Name","type":"placeholder","validation":""}],
      ['coordinator', {
        label: 'Coordinator', type: 'placeholder', 'allow-null': false, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['courseDetails', { label: 'Course Details', type: 'placeholder', validation: 'none' }],
      ['description', { label: 'Training Session Details', type: 'placeholder', validation: 'none' }],
      ['trainer', { label: 'Trainer', type: 'placeholder', validation: 'none' }],
      ['trainer_info', { label: 'Trainer Details', type: 'placeholder', validation: 'none' }],
      // ['paymentType', { label: 'Payment Type', type: 'placeholder', source: [['Company Sponsored', 'Company Sponsored'], ['Paid by Employee', 'Paid by Employee']] }],
      // ['currency', { label: 'Currency', type: 'placeholder', 'remote-source': ['CurrencyType', 'code', 'name'] }],
      // ['cost', { label: 'Cost', type: 'placeholder', validation: 'float' }],
      ['courseStatus', { label: 'Course Status', type: 'placeholder', source: [['Active', 'Active'], ['Inactive', 'Inactive']] }],
      ['scheduled', { label: 'Scheduled Time', type: 'placeholder', validation: '' }],
      ['dueDate', { label: 'Assignment Due Date', type: 'placeholder', validation: 'none' }],
      ['deliveryMethod', { label: 'Course Type', type: 'placeholder', source: [['Classroom', 'Classroom'], ['Self Study', 'Self Study'], ['Online', 'Online']] }],
      ['deliveryLocation', { label: 'Location', type: 'placeholder', validation: 'none' }],
      ['status', { label: 'Status', type: 'placeholder', source: [['Pending', 'Pending'], ['Approved', 'Approved'], ['Completed', 'Completed'], ['Cancelled', 'Cancelled']] }],
      ['attendanceType', { label: 'Registration Method', type: 'placeholder', source: [['Sign Up', 'Sign Up'], ['Assign', 'Assign']] }],
      ['attachment', {
        label: 'Attachment', type: 'fileupload', validation: 'none', readonly: true,
      }],
    ];
  }


  postRenderForm(object, $tempDomObj) {
    $tempDomObj.find('.cancelBtn').remove();
    // $tempDomObj.find('#attachment').remove();
    $tempDomObj.find('#attachment_remove').remove();
  }

  signUp(trainingSessionId) {
    const object = {};
    object.sessionId = trainingSessionId;
    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'signUpSuccessCallBack';
    callBackData.callBackFail = 'signUpFailCallBack';


    // eslint-disable-next-line no-shadow
    const signUpCallback = function (reqJson, callBackData) {
      this.customAction('signup', 'modules=training', reqJson, callBackData);
    };

    this.renderYesNoModel('Confirm Sign Up', 'Are you sure that you want to sign up for this course?', 'Yes', 'No', signUpCallback, [reqJson, callBackData]);
  }


  signUpSuccessCallBack(callBackData) {
    this.showMessage('Successful', callBackData);
  }

  signUpFailCallBack(callBackData) {
    this.showMessage('Error Occurred while signing up to training session', callBackData);
  }


  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        <Tag color="blue" onClick={() => this.edit(record.id)} style={{ cursor: 'pointer' }}>
          <MonitorOutlined />
          {` ${adapter.gt('View')}`}
        </Tag>
        <Tag color="green" onClick={() => this.signUp(record.id)} style={{ cursor: 'pointer' }}>
          <LoginOutlined />
          {` ${adapter.gt('Sign Up')}`}
        </Tag>
      </Space>
    );
  }

  // eslint-disable-next-line no-unused-vars
  getActionButtonsHtml(id, data) {
    const editButton = '<img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;" rel="tooltip" title="View" onclick="modJs.edit(_id_);return false;"></img>';
    const signUpButton = '<img class="tableActionButton" src="_BASE_images/run.png" style="cursor:pointer;margin-left:10px;" rel="tooltip" title="Sign Up" onclick="modJs.signUp(_id_);return false;"></img>';
    let html = '<div style="width:80px;">_edit__signup_</div>';

    html = html.replace('_edit_', editButton);
    html = html.replace('_signup_', signUpButton);

    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }
}


/**
 * CoordinatedTrainingSessionAdapter
 */

class CoordinatedTrainingSessionAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'course',
      'scheduled',
      'status',
      'deliveryMethod',
      'deliveryLocation',
      'attendanceType',
      'requireProof',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Course' },
      { sTitle: 'Scheduled Time' },
      { sTitle: 'Status' },
      { sTitle: 'Training Type' },
      { sTitle: 'Location' },
      { sTitle: 'Attendance Type' },
      { sTitle: 'Training Certificate Required' },
    ];
  }

  getTableColumns() {
    return [
      { title: 'Name', dataIndex: 'name', sorter: true },
      { title: 'Course', dataIndex: 'course' },
      { title: 'Scheduled Time', dataIndex: 'scheduled', sorter: true },
      { title: 'Status', dataIndex: 'status' },
      { title: 'Training Type', dataIndex: 'deliveryMethod' },
      { title: 'Location', dataIndex: 'deliveryLocation' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['course', { label: 'Course', type: 'placeholder', 'remote-source': ['Course', 'id', 'name+code'] }],
      ['description', { label: 'Details', type: 'textarea', validation: 'none' }],
      ['scheduled', { label: 'Scheduled Time', type: 'datetime', validation: '' }],
      ['dueDate', { label: 'Assignment Due Date', type: 'date', validation: 'none' }],
      ['deliveryMethod', { label: 'Training Type', type: 'select', source: [['Classroom', 'Classroom'], ['Self Study', 'Self Study'], ['Online', 'Online']] }],
      ['deliveryLocation', { label: 'Location', type: 'text', validation: 'none' }],
      ['attendanceType', { label: 'Registration Method', type: 'select', source: [['Sign Up', 'Sign Up'], ['Assign', 'Assign']] }],
      ['attachment', { label: 'Attachment', type: 'fileupload', validation: 'none' }],
      ['requireProof', { label: 'Training Certificate Required', type: 'select', source: [['Yes', 'Yes'], ['No', 'No']] }],
    ];
  }
}

/**
 * EmployeeTrainingSessions Adapter
 */

class SubEmployeeTrainingSessionAdapter extends EmployeeTrainingSessionAdapter {
  isSubProfileTable() {
    return true;
  }

  getDataMapping() {
    return [
      'id',
      'employee',
      'trainingSession',
      'status',
      'courseId',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Training Session' },
      { sTitle: 'Status' },
      { sTitle: 'Course ID', bVisible: false },
    ];
  }

  getTableColumns() {
    return [
      { title: 'Employee', dataIndex: 'employee', sorter: true },
      { title: 'Training Session', dataIndex: 'trainingSession' },
      { title: 'Status', dataIndex: 'status' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', {
        label: 'Employee', type: 'placeholder', 'allow-null': false, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['trainingSession', {
        label: 'Training Session', type: 'placeholder', 'allow-null': false, 'remote-source': ['TrainingSession', 'id', 'name'],
      }],
      ['status', { label: 'Status', type: 'placeholder', source: [['Scheduled', 'Scheduled'], ['Attended', 'Attended'], ['Not-Attended', 'Not-Attended']] }],
      ['feedBack', { label: 'Feedback', type: 'placeholder', validation: '' }],
      ['proof', { label: 'Proof of Completion', type: 'fileupload', validation: 'none' }],
    ];
  }


  completed(trainingSessionId) {
    const object = {};
    object.sessionId = trainingSessionId;
    const reqJson = JSON.stringify(object);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'completedSuccessCallBack';
    callBackData.callBackFail = 'completedFailCallBack';

    this.customAction('sessionCompleted', 'modules=training', reqJson, callBackData);
  }

  getTableActionButtonJsx(adapter) {
    return (text, record) => (
      <Space size="middle">
        {adapter.hasAccess('save') && adapter.showEdit && (
          <Tag color="green" onClick={() => this.edit(record.id)} style={{ cursor: 'pointer' }}>
            <EditOutlined />
            {` ${adapter.gt('Review Feedback')}`}
          </Tag>
        )}
        {record.status === 'Attended' && (
          <Tag color="blue" onClick={() => this.completed(record.id)} style={{ cursor: 'pointer' }}>
            <CheckCircleOutlined />
            {` ${adapter.gt('Approve Completed Status')}`}
          </Tag>
        )}
        {adapter.hasAccess('delete') && adapter.showDelete && (
          <Tag color="volcano" onClick={() => this.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
            <DeleteOutlined />
            {` ${adapter.gt('Delete')}`}
          </Tag>
        )}
      </Space>
    );
  }

  getActionButtonsHtml(id, data) {
    const editButton = '<img class="tableActionButton" src="_BASE_images/edit.png" style="cursor:pointer;" rel="tooltip" title="Review Feedback" onclick="modJs.edit(_id_);return false;"></img>';
    const deleteButton = '<img class="tableActionButton" src="_BASE_images/delete.png" style="margin-left:15px;cursor:pointer;" rel="tooltip" title="Delete" onclick="modJs.deleteRow(_id_);return false;"></img>';
    const courseInfoButton = '<img class="tableActionButton" src="_BASE_images/view.png" style="cursor:pointer;margin-left:10px;" rel="tooltip" title="Course Information" onclick="modJsList[\'tabTrainingSession\'].edit(_cid_);return false;"></img>';
    const completedButton = '<img class="tableActionButton" src="_BASE_images/check_icon.png" style="cursor:pointer;margin-left:10px;" rel="tooltip" title="Approve Completed Status" onclick="modJs.completed(_id_);return false;"></img>';
    let html = '<div style="width:100px;">_edit__course__delete__completed_</div>';

    if (this.showDelete) {
      if (this.checkPermission('Delete Training Sessions of Direct Reports') === 'No') {
        html = html.replace('_delete_', '');
      } else {
        html = html.replace('_delete_', deleteButton);
      }
    } else {
      html = html.replace('_delete_', '');
    }

    if (this.showEdit) {
      html = html.replace('_edit_', editButton);
    } else {
      html = html.replace('_edit_', '');
    }

    if (data[3] === 'Attended') {
      html = html.replace('_completed_', completedButton);
    } else {
      html = html.replace('_completed_', '');
    }


    html = html.replace('_course_', courseInfoButton);
    html = html.replace(/_id_/g, id);
    // eslint-disable-next-line no-underscore-dangle
    html = html.replace(/_cid_/g, data._org.trainingSession_id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }
}

module.exports = {
  EmployeeTrainingSessionAdapter,
  TrainingSessionAdapter,
  CoordinatedTrainingSessionAdapter,
  SubEmployeeTrainingSessionAdapter,
};
