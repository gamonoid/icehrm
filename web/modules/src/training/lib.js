/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';

/**
 * EmployeeTrainingSessions Adapter
 */

class EmployeeTrainingSessionAdapter extends AdapterBase {
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


class TrainingSessionAdapter extends AdapterBase {
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

class CoordinatedTrainingSessionAdapter extends AdapterBase {
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
