/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

/* eslint-disable camelcase,no-underscore-dangle */

/* global d3 */
import React from 'react';
import ReactDOM from 'react-dom';
import QRCode from 'qrcode';
import AdapterBase from '../../../api/AdapterBase';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import EmployeeProfile from './components/EmployeeProfile';


class EmployeeAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.fieldNameMap = {};
    this.fieldNameMapOrig = {};
    this.hiddenFields = {};
    this.tableFields = {};
    this.formOnlyFields = {};
    this.currentUserId = null;
  }

  setFieldNameMap(fields) {
    let field;
    for (let i = 0; i < fields.length; i++) {
      field = fields[i];
      this.fieldNameMap[field.name] = field;
      this.fieldNameMapOrig[field.textOrig] = field.textMapped;
      if (field.display === 'Hidden') {
        this.hiddenFields[field.name] = field;
      } else if (field.display === 'Table and Form') {
        this.tableFields[field.name] = field;
      } else {
        this.formOnlyFields[field.name] = field;
      }
    }
  }

  getDataMapping() {
    return [
      'id',
      'employee_id',
      'first_name',
      'last_name',
      'mobile_phone',
      'department',
      'gender',
      'supervisor',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID' },
      { sTitle: 'Employee Number' },
      { sTitle: 'First Name' },
      { sTitle: 'Last Name' },
      { sTitle: 'Mobile' },
      { sTitle: 'Department' },
      { sTitle: 'Gender' },
      { sTitle: 'Supervisor' },
    ];
  }

  initTable() {
    this.initProfile();
  }

  initProfile(employee) {
    const tableDom = document.getElementById(`${this.tab}`);
    this.tableContainer = React.createRef();
    ReactDOM.render(
      <EmployeeProfile
        ref={this.tableContainer}
        adapter={this}
        element={employee}
      />,
      tableDom,
    );

    this.tableContainer.current.setLoading(!employee);
  }

  get() {
    this.initTable();
    this.masterDataReader.updateAllMasterData()
      .then(() => {
        this.viewElement();
      });

    this.trackEvent('get', this.tab, this.table);
  }

  edit(id) {
    this.setTableLoading(true);
    this.currentId = id;
    this.getElement(id, []);
  }

  getFormOptions() {
    return {
      width: 1024,
      twoColumnLayout: false,
    };
  }

  getFormFields() {
    const newFields = [];
    let employee_id; let ssn_num; let employment_status; let job_title; let pay_grade; let joined_date; let department; let work_email; let
      country;

    if (this.checkPermission('Edit Employee Number') === 'Yes') {
      employee_id = ['employee_id', { label: 'Employee Number', type: 'text', validation: '' }];
    } else {
      employee_id = ['employee_id', { label: 'Employee Number', type: 'placeholder', validation: '' }];
    }

    if (this.checkPermission('Edit EPF/CPF Number') === 'Yes') {
      ssn_num = ['ssn_num', { label: 'EPF/CPF/SS No', type: 'text', validation: 'none' }];
    } else {
      ssn_num = ['ssn_num', { label: 'EPF/CPF/SS No', type: 'placeholder', validation: 'none' }];
    }

    if (this.checkPermission('Edit Employment Status') === 'Yes') {
      employment_status = ['employment_status', { label: 'Employment Status', type: 'select2', 'remote-source': ['EmploymentStatus', 'id', 'name'] }];
    } else {
      employment_status = ['employment_status', { label: 'Employment Status', type: 'placeholder', 'remote-source': ['EmploymentStatus', 'id', 'name'] }];
    }

    if (this.checkPermission('Edit Job Title') === 'Yes') {
      job_title = ['job_title', { label: 'Job Title', type: 'select2', 'remote-source': ['JobTitle', 'id', 'name'] }];
    } else {
      job_title = ['job_title', { label: 'Job Title', type: 'placeholder', 'remote-source': ['JobTitle', 'id', 'name'] }];
    }

    if (this.checkPermission('Edit Pay Grade') === 'Yes') {
      pay_grade = ['pay_grade', {
        label: 'Pay Grade', type: 'select2', 'allow-null': true, 'remote-source': ['PayGrade', 'id', 'name'],
      }];
    } else {
      pay_grade = ['pay_grade', {
        label: 'Pay Grade', type: 'placeholder', 'allow-null': true, 'remote-source': ['PayGrade', 'id', 'name'],
      }];
    }

    if (this.checkPermission('Edit Joined Date') === 'Yes') {
      joined_date = ['joined_date', { label: 'Joined Date', type: 'date', validation: '' }];
    } else {
      joined_date = ['joined_date', { label: 'Joined Date', type: 'placeholder', validation: '' }];
    }

    if (this.checkPermission('Edit Department') === 'Yes') {
      department = ['department', { label: 'Department', type: 'select2', 'remote-source': ['CompanyStructure', 'id', 'title'] }];
    } else {
      department = ['department', { label: 'Department', type: 'placeholder', 'remote-source': ['CompanyStructure', 'id', 'title'] }];
    }

    if (this.checkPermission('Edit Work Email') === 'Yes') {
      work_email = ['work_email', { label: 'Work Email', type: 'text', validation: 'email' }];
    } else {
      work_email = ['work_email', { label: 'Work Email', type: 'placeholder', validation: 'emailOrEmpty' }];
    }

    if (this.checkPermission('Edit Country') === 'Yes') {
      country = ['country', { label: 'Country', type: 'select2', 'remote-source': ['Country', 'code', 'name'] }];
    } else {
      country = ['country', { label: 'Country', type: 'placeholder', 'remote-source': ['Country', 'code', 'name'] }];
    }

    const fields = [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      employee_id,
      ['first_name', { label: 'First Name', type: 'text', validation: '' }],
      ['middle_name', { label: 'Middle Name', type: 'text', validation: 'none' }],
      ['last_name', { label: 'Last Name', type: 'text', validation: '' }],
      ['nationality', { label: 'Nationality', type: 'select2', 'remote-source': ['Nationality', 'id', 'name'] }],
      ['birthday', { label: 'Date of Birth', type: 'date', validation: '' }],
      ['gender', { label: 'Gender', type: 'select', source: [['Male', 'Male'], ['Female', 'Female'], ['Non-binary', 'Non-binary'], ['Other', 'Other']] }],
      ['marital_status', { label: 'Marital Status', type: 'select', source: [['Married', 'Married'], ['Single', 'Single'], ['Divorced', 'Divorced'], ['Widowed', 'Widowed'], ['Other', 'Other']] }],
      ssn_num,
      ['nic_num', { label: 'NIC', type: 'text', validation: 'none' }],
      ['other_id', { label: 'Other ID', type: 'text', validation: 'none' }],
      ['driving_license', { label: 'Driving License No', type: 'text', validation: 'none' }],
      employment_status,
      job_title,
      pay_grade,
      ['work_station_id', { label: 'Work Station Id', type: 'text', validation: 'none' }],
      ['address1', { label: 'Address Line 1', type: 'text', validation: 'none' }],
      ['address2', { label: 'Address Line 2', type: 'text', validation: 'none' }],
      ['city', { label: 'City', type: 'text', validation: 'none' }],
      country,
      ['province', {
        label: 'Province', type: 'select2', 'allow-null': true, 'remote-source': ['Province', 'id', 'name'],
      }],
      ['postal_code', { label: 'Postal/Zip Code', type: 'text', validation: 'none' }],
      ['home_phone', { label: 'Home Phone', type: 'text', validation: 'none' }],
      ['mobile_phone', { label: 'Mobile Phone', type: 'text', validation: 'none' }],
      ['work_phone', { label: 'Work Phone', type: 'text', validation: 'none' }],
      work_email,
      ['private_email', { label: 'Private Email', type: 'text', validation: 'emailOrEmpty' }],
      joined_date,
      department,
    ];

    for (let i = 0; i < this.customFields.length; i++) {
      fields.push(this.customFields[i]);
    }


    for (let i = 0; i < fields.length; i++) {
      const tempField = fields[i];
      if (this.hiddenFields[tempField[0]] === undefined || this.hiddenFields[tempField[0]] === null) {
        if (this.fieldNameMap[tempField[0]] !== undefined && this.fieldNameMap[tempField[0]] !== null) {
          const title = this.fieldNameMap[tempField[0]].textMapped;
          tempField[1].label = title;
        }
        newFields.push(tempField);
      }
    }

    return newFields;
  }

  getMappedFields() {
    const fields = this.getFormFields();
    const steps = [
      {
        title: this.gt('Personal'),
        description: this.gt('Personal Information'),
        fields: [
          'id',
          'employee_id',
          'first_name',
          'middle_name',
          'last_name',
          'nationality',
          'birthday',
          'gender',
          'marital_status',
          'ethnicity',
        ],
      },
      {
        title: this.gt('Identification'),
        description: this.gt('Personal Information'),
        fields: [
          'immigration_status',
          'ssn_num',
          'nic_num',
          'other_id',
          'driving_license',
        ],
      },
      {
        title: this.gt('Work'),
        description: this.gt('Work related details'),
        fields: [
          'employment_status',
          'department',
          'job_title',
          'pay_grade',
          'joined_date',
          'confirmation_date',
          'termination_date',
          'work_station_id',
        ],
      },
      {
        title: this.gt('Contact'),
        description: this.gt('Contact details'),
        fields: [
          'address1', 'address2',
          'city', 'country',
          'province', 'postal_code',
          'home_phone', 'mobile_phone',
          'work_phone', 'work_email',
          'private_email',
        ],
      },
    ];

    if (this.customFields.length > 0) {
      steps.push({
        title: this.gt('Other'),
        description: this.gt('Additional details'),
        fields: this.customFields.map((item) => item[0]),
      });
    }

    return this.addActualFields(steps, fields);
  }

  getMappedText(text) {
    return this.fieldNameMapOrig[text] ? this.fieldNameMapOrig[text] : text;
  }

  addActualFields(steps, fields) {
    return steps.map((item) => {
      item.fields = item.fields.reduce((acc, fieldName) => {
        const field = fields.find(([name]) => name === fieldName);
        if (field) {
          acc.push(field);
        }
        return acc;
      }, []);

      return item;
    });
  }

  getSourceMapping() {
    const k = this.sourceMapping;
    k.supervisor = ['Employee', 'id', 'first_name+last_name'];
    return k;
  }

  viewElement(id) {
    const sourceMappingJson = JSON.stringify(this.getSourceMapping());

    const req = { map: sourceMappingJson };
    const reqJson = JSON.stringify(req);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'modEmployeeGetSuccessCallBack';
    callBackData.callBackFail = 'modEmployeeGetFailCallBack';

    this.customAction('get', 'modules=employees', reqJson, callBackData);
  }

  deleteProfileImage(empId) {
    const req = { id: empId };
    const reqJson = JSON.stringify(req);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'modEmployeeDeleteProfileImageCallBack';
    callBackData.callBackFail = 'modEmployeeDeleteProfileImageCallBack';

    this.customAction('deleteProfileImage', 'modules=employees', reqJson, callBackData);
  }

  modEmployeeDeleteProfileImageCallBack(data) {
    // eslint-disable-next-line no-restricted-globals
    top.location.href = top.location.href;
  }

  modEmployeeGetSuccessCallBack(data) {
    const currentEmpId = data[1];
    const userEmpId = data[2];
    const [element] = data;
    this.initProfile(element);
  }

  modEmployeeGetSuccessCallBack1(data) {
    const fields = this.getFormFields();
    const currentEmpId = data[1];
    const userEmpId = data[2];
    [data] = data;
    let html = this.getCustomTemplate('myDetails.html');

    for (let i = 0; i < fields.length; i++) {
      if (this.fieldNameMap[fields[i][0]] !== undefined && this.fieldNameMap[fields[i][0]] !== null) {
        const title = this.fieldNameMap[fields[i][0]].textMapped;
        html = html.replace(`#_label_${fields[i][0]}_#`, this.gt(title));
      }
    }

    html = html.replace(/#_.+_#/gi, '');

    html = html.replace(/_id_/g, data.id);

    $(`#${this.getTableName()}`).html(html);

    for (let i = 0; i < fields.length; i++) {
      $(`#${this.getTableName()} #${fields[i][0]}`).html(data[fields[i][0]]);
      $(`#${this.getTableName()} #${fields[i][0]}_Name`).html(data[`${fields[i][0]}_Name`]);
    }

    $(`#${this.getTableName()} #supervisor_Name`).html(data.supervisor_Name);

    let subordinates = '';
    for (let i = 0; i < data.subordinates.length; i++) {
      if (data.subordinates[i].first_name !== undefined && data.subordinates[i].first_name !== null) {
        subordinates += `${data.subordinates[i].first_name} `;
      }

      if (data.subordinates[i].middle_name !== undefined && data.subordinates[i].middle_name !== null && data.subordinates[i].middle_name !== '') {
        subordinates += `${data.subordinates[i].middle_name} `;
      }

      if (data.subordinates[i].last_name !== undefined && data.subordinates[i].last_name !== null && data.subordinates[i].last_name !== '') {
        subordinates += data.subordinates[i].last_name;
      }
      subordinates += '<br/>';
    }

    // Add custom fields
    if (data.customFields !== undefined && data.customFields !== null && Object.keys(data.customFields).length > 0) {
      const ct = '<div class="col-xs-6 col-md-3" style="font-size:16px;"><label class="control-label col-xs-12" style="font-size:13px;">#_label_#</label><label class="control-label col-xs-12 iceLabel" style="font-size:13px;font-weight: bold;">#_value_#</label></div>';

      const sectionTemplate = '<div class="panel panel-default" style="width:97.5%;"><div class="panel-heading"><h4>#_section.name_#</h4></div> <div class="panel-body"  id="cont_#_section_#"> </div></div>';
      let customFieldHtml;
      for (const index in data.customFields) {
        if (!data.customFields[index][1]) {
          data.customFields[index][1] = this.gt('Other Details');
        }

        let sectionId = data.customFields[index][1].toLocaleLowerCase();
        sectionId = sectionId.replace(' ', '_');

        if ($(`#cont_${sectionId}`).length <= 0) {
          // Add section
          let sectionHtml = sectionTemplate;
          sectionHtml = sectionHtml.replace('#_section_#', sectionId);
          sectionHtml = sectionHtml.replace('#_section.name_#', data.customFields[index][1]);
          $('#customFieldsCont').append($(sectionHtml));
        }

        customFieldHtml = ct;
        customFieldHtml = customFieldHtml.replace('#_label_#', index);
        if (data.customFields[index][2] === 'fileupload') {
          customFieldHtml = customFieldHtml.replace(
            '#_value_#',
            `<button onclick="download('${data.customFields[index][0]}');return false;" class="btn btn-mini btn-inverse" type="button">View: ${index}</button>`,
          );
        } else {
          customFieldHtml = customFieldHtml.replace('#_value_#', data.customFields[index][0]);
        }
        $(`#cont_${sectionId}`).append($(customFieldHtml));
      }
    } else {
      $('#customFieldsCont').remove();
    }

    $(`#${this.getTableName()} #subordinates`).html(subordinates);


    $(`#${this.getTableName()} #name`).html(`${data.first_name} ${data.last_name}`);
    this.currentUserId = data.id;

    $(`#${this.getTableName()} #profile_image_${data.id}`).attr('src', data.image);

    if (this.checkPermission('Upload/Delete Profile Image') === 'No') {
      $('#employeeUploadProfileImage').remove();
      $('#employeeDeleteProfileImage').remove();
    }

    if (this.checkPermission('Edit Employee Details') === 'No') {
      $('#employeeProfileEditInfo').remove();
    }

    if (currentEmpId !== userEmpId) {
      $('#employeeUpdatePassword').remove();
    }

    this.cancel();
  }

  modEmployeeGetFailCallBack(data) {

  }

  editEmployee() {
    this.edit(this.currentUserId);
  }

  changePassword() {
    $('#adminUsersModel').modal('show');
    $('#adminUsersChangePwd #newpwd').val('');
    $('#adminUsersChangePwd #conpwd').val('');
    $('#adminUsersChangePwd_error').hide();
  }

  changePasswordConfirm() {
    $('#adminUsersChangePwd_error').hide();

    const password = $('#adminUsersChangePwd #newpwd').val();
    const conPassword = $('#adminUsersChangePwd #conpwd').val();

    if (conPassword !== password) {
      $('#adminUsersChangePwd_error').html("Passwords don't match");
      $('#adminUsersChangePwd_error').show();

      return;
    }

    const validatePasswordResult = this.validatePassword(password);

    if (validatePasswordResult != null) {
      $('#adminUsersChangePwd_error').html(validatePasswordResult);
      $('#adminUsersChangePwd_error').show();

      return;
    }

    const req = { pwd: conPassword };
    const reqJson = JSON.stringify(req);

    const callBackData = [];
    callBackData.callBackData = [];
    callBackData.callBackSuccess = 'changePasswordSuccessCallBack';
    callBackData.callBackFail = 'changePasswordFailCallBack';

    this.customAction('changePassword', 'modules=employees', reqJson, callBackData);
  }

  closeChangePassword() {
    $('#adminUsersModel').modal('hide');
  }

  changePasswordSuccessCallBack(callBackData, serverData) {
    this.closeChangePassword();
    this.showMessage('Password Change', 'Password changed successfully');
  }

  changePasswordFailCallBack(callBackData, serverData) {
    this.closeChangePassword();
    this.showMessage('Error', callBackData);
  }
}


/*
 * Company Graph
 */

class CompanyStructureAdapter extends AdapterBase {
  getDataMapping() {
    return [
      'id',
      'title',
      'address',
      'type',
      'country',
      'parent',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Address' },
      { sTitle: 'Type' },
      { sTitle: 'Country', sClass: 'center' },
      { sTitle: 'Parent Structure' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden', validation: '' }],
      ['title', { label: 'Name', type: 'text', validation: '' }],
      ['description', { label: 'Details', type: 'textarea', validation: '' }],
      ['address', { label: 'Address', type: 'textarea', validation: 'none' }],
      ['type', { label: 'Type', type: 'select', source: [['Company', 'Company'], ['Head Office', 'Head Office'], ['Regional Office', 'Regional Office'], ['Department', 'Department'], ['Unit', 'Unit'], ['Sub Unit', 'Sub Unit'], ['Other', 'Other']] }],
      ['country', { label: 'Country', type: 'select', 'remote-source': ['Country', 'code', 'name'] }],
      ['parent', {
        label: 'Parent Structure', type: 'select', 'allow-null': true, 'remote-source': ['CompanyStructure', 'id', 'title'],
      }],
    ];
  }
}


class CompanyGraphAdapter extends CompanyStructureAdapter {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.nodeIdCounter = 0;
  }

  convertToTree(data) {
    const ice = {};
    ice.id = -1;
    ice.title = '';
    ice.name = '';
    ice.children = [];

    let parent = null;

    const added = {};


    for (let i = 0; i < data.length; i++) {
      data[i].name = data[i].title;

      if (data[i].parent !== null && data[i].parent !== undefined) {
        parent = this.findParent(data, data[i].parent);
        if (parent !== null) {
          if (parent.children === undefined || parent.children === null) {
            parent.children = [];
          }
          parent.children.push(data[i]);
        }
      }
    }

    for (let i = 0; i < data.length; i++) {
      if (data[i].parent === null || data[i].parent === undefined) {
        ice.children.push(data[i]);
      }
    }

    return ice;
  }


  findParent(data, parent) {
    for (let i = 0; i < data.length; i++) {
      if (data[i].title === parent || data[i].title === parent) {
        return data[i];
      }
    }
    return null;
  }


  createTable(elementId) {
    $('#tabPageCompanyGraph').html('');
    const that = this;
    // eslint-disable-next-line prefer-destructuring
    const sourceData = this.sourceData;

    // this.fixCyclicParent(sourceData);
    const treeData = this.convertToTree(sourceData);

    const m = [20, 120, 20, 120];


    const w = 5000 - m[1] - m[3];


    const h = 1000 - m[0] - m[2];

    const tree = d3.layout.tree()
      .size([h, w]);

    this.diagonal = d3.svg.diagonal()
      .projection((d) => [d.y, d.x]);

    this.vis = d3.select('#tabPageCompanyGraph').append('svg:svg')
      .attr('width', w + m[1] + m[3])
      .attr('height', h + m[0] + m[2])
      .append('svg:g')
      .attr('transform', `translate(${m[3]},${m[0]})`);

    const root = treeData;
    root.x0 = h / 2;
    root.y0 = 0;

    function toggleAll(d) {
      if (d.children) {
        console.log(d.name);
        d.children.forEach(toggleAll);
        that.toggle(d);
      }
    }
    this.update(root, tree, root);
  }

  update(source, tree, root) {
    const that = this;
    const duration = d3.event && d3.event.altKey ? 5000 : 500;

    // Compute the new tree layout.
    const nodes = tree.nodes(root).reverse();

    // Normalize for fixed-depth.
    nodes.forEach((d) => { d.y = d.depth * 180; });

    // Update the nodes�
    const node = that.vis.selectAll('g.node')
      // eslint-disable-next-line no-return-assign
      .data(nodes, (d) => d.id || (d.id = ++that.nodeIdCounter));

    // Enter any new nodes at the parent's previous position.
    const nodeEnter = node.enter().append('svg:g')
      .attr('class', 'node')
      .attr('transform', (d) => `translate(${source.y0},${source.x0})`)
      .on('click', (d) => { that.toggle(d); that.update(d, tree, root); });

    nodeEnter.append('svg:circle')
      .attr('r', 1e-6)
      // eslint-disable-next-line no-underscore-dangle
      .style('fill', (d) => (d._children ? 'lightsteelblue' : '#fff'));

    nodeEnter.append('svg:text')
      .attr('x', (d) => (d.children || d._children ? -10 : 10))
      .attr('dy', '.35em')
      .attr('text-anchor', (d) => (d.children || d._children ? 'end' : 'start'))
      .text((d) => d.name)
      .style('fill-opacity', 1e-6);

    // Transition nodes to their new position.
    const nodeUpdate = node.transition()
      .duration(duration)
      .attr('transform', (d) => `translate(${d.y},${d.x})`);

    nodeUpdate.select('circle')
      .attr('r', 4.5)
      .style('fill', (d) => (d._children ? 'lightsteelblue' : '#fff'));

    nodeUpdate.select('text')
      .style('fill-opacity', 1);

    // Transition exiting nodes to the parent's new position.
    const nodeExit = node.exit().transition()
      .duration(duration)
      .attr('transform', (d) => `translate(${source.y},${source.x})`)
      .remove();

    nodeExit.select('circle')
      .attr('r', 1e-6);

    nodeExit.select('text')
      .style('fill-opacity', 1e-6);

    // Update the links�
    const link = that.vis.selectAll('path.link')
      .data(tree.links(nodes), (d) => d.target.id);

    // Enter any new links at the parent's previous position.
    link.enter().insert('svg:path', 'g')
      .attr('class', 'link')
      .attr('d', (d) => {
        const o = { x: source.x0, y: source.y0 };
        return that.diagonal({ source: o, target: o });
      })
      .transition()
      .duration(duration)
      .attr('d', that.diagonal);

    // Transition links to their new position.
    link.transition()
      .duration(duration)
      .attr('d', that.diagonal);

    // Transition exiting nodes to the parent's new position.
    link.exit().transition()
      .duration(duration)
      .attr('d', (d) => {
        const o = { x: source.x, y: source.y };
        return that.diagonal({ source: o, target: o });
      })
      .remove();

    // Stash the old positions for transition.
    nodes.forEach((d) => {
      d.x0 = d.x;
      d.y0 = d.y;
    });
  }

  // Toggle children.
  toggle(d) {
    if (d.children) {
      d._children = d.children;
      d.children = null;
    } else {
      d.children = d._children;
      d._children = null;
    }
  }


  getSourceDataById(id) {
    for (let i = 0; i < this.sourceData.length; i++) {
      if (this.sourceData[i].id === id) {
        return this.sourceData[i];
      }
    }

    return null;
  }

  fixCyclicParent(sourceData) {
    let errorMsg = '';
    for (let i = 0; i < sourceData.length; i++) {
      const obj = sourceData[i];


      let curObj = obj;
      const parentIdArr = {};
      parentIdArr[curObj.id] = 1;

      while (curObj.parent != null && curObj.parent !== undefined) {
        const parent = this.getSourceDataById(curObj.parent);
        if (parent == null) {
          break;
        } else if (parentIdArr[parent.id] === 1) {
          errorMsg = `${obj.title}'s parent structure set to ${parent.title}<br/>`;
          obj.parent = null;
          break;
        }
        parentIdArr[parent.id] = 1;
        curObj = parent;
      }
    }

    if (errorMsg !== '') {
      this.showMessage('Company Structure is having a cyclic dependency', `We found a cyclic dependency due to following reasons:<br/>${errorMsg}`);
      return false;
    }

    return true;
  }
}


/*
 * Api Access
 */

class ApiAccessAdapter extends AdapterBase {
  getDataMapping() {
    return [
    ];
  }

  getHeaders() {
    return [

    ];
  }

  getFormFields() {
    return [
    ];
  }

  setToken(token) {
    this.token = token;
  }

  get() {
    const canvas = document.getElementById('apiQRcode');
    QRCode.toCanvas(canvas, JSON.stringify({
      key: 'IceHrm',
      url: this.apiUrl,
      token: this.token,
    }), (error) => {
      if (error) {
        console.log(error);
      }
    });
  }
}


module.exports = {
  EmployeeAdapter,
  CompanyGraphAdapter,
  ApiAccessAdapter,
};
