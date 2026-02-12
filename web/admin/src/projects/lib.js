/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import ReactDOM from 'react-dom';
import { Space, Tag } from 'antd';
import {
  CopyOutlined, DeleteOutlined, EditOutlined, MonitorOutlined, BarChartOutlined,
} from '@ant-design/icons';
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';
import ProjectDetailView from './components/ProjectDetailView';

/**
 * ProjectAdapter
 */

class ProjectAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.fieldNameMap = {};
    this.hiddenFields = {};
    this.tableFields = {};
    this.formOnlyFields = {};
    this.detailViewContainer = null;
    this.selectedProjectId = null;
  }

  // Show the project details view (replaces table)
  showProjectDetails(projectId) {
    this.selectedProjectId = projectId;

    // Hide the table container
    const tableContainer = this.getTableContainer();
    if (tableContainer) {
      tableContainer.style.display = 'none';
    }

    // Hide the table top component
    const tableTop = document.getElementById(`${this.tab}TableTop`);
    if (tableTop) {
      tableTop.style.display = 'none';
    }
    // Also try with hardcoded ID as fallback
    const projectTableTop = document.getElementById('ProjectTableTop');
    if (projectTableTop) {
      projectTableTop.style.display = 'none';
    }

    // Create or show the detail view container
    if (!this.detailViewContainer) {
      this.detailViewContainer = document.createElement('div');
      this.detailViewContainer.id = 'projectDetailView';
      // Insert before table container, or append to its parent
      if (tableContainer && tableContainer.parentNode) {
        tableContainer.parentNode.insertBefore(this.detailViewContainer, tableContainer);
      } else {
        // Fallback: find the tab content area
        const tabPane = document.getElementById(this.tab);
        if (tabPane) {
          tabPane.appendChild(this.detailViewContainer);
        } else {
          document.body.appendChild(this.detailViewContainer);
        }
      }
    }
    this.detailViewContainer.style.display = 'block';

    this.renderDetailView();
  }

  // Close the project details view (shows table again)
  closeProjectDetails() {
    // Hide detail view
    if (this.detailViewContainer) {
      this.detailViewContainer.style.display = 'none';
    }

    // Show the table container
    const tableContainer = this.getTableContainer();
    if (tableContainer) {
      tableContainer.style.display = 'block';
    }

    // Show the table top component
    const tableTop = document.getElementById(`${this.tab}TableTop`);
    if (tableTop) {
      tableTop.style.display = 'block';
    }
    // Also try with hardcoded ID as fallback
    const projectTableTop = document.getElementById('ProjectTableTop');
    if (projectTableTop) {
      projectTableTop.style.display = 'block';
    }
  }

  // Get the table container element
  getTableContainer() {
    // The table is rendered into ${this.tab}Table element
    return document.getElementById(`${this.tab}Table`);
  }

  // Override get() to close detail view first
  get(args) {
    // Close detail view if it's open
    if (this.detailViewContainer && this.detailViewContainer.style.display !== 'none') {
      this.closeProjectDetails();
    }
    // Call parent get()
    super.get(args);
  }

  // Render the project detail view
  renderDetailView() {
    ReactDOM.render(
      <ProjectDetailView
        projectId={this.selectedProjectId}
        onBack={() => this.closeProjectDetails()}
      />,
      this.detailViewContainer,
    );
  }

  getDataMapping() {
    return [
      'id',
      'name',
      'client',
      'document_link',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Client' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Client',
        dataIndex: 'client',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    if (this.showSave) {
      return this.addCustomFields([
        ['id', { label: 'ID', type: 'hidden' }],
        ['name', { label: 'Name', type: 'text' }],
        ['client', {
          label: 'Client', type: 'select2', 'allow-null': true, 'remote-source': ['Client', 'id', 'name'],
        }],
        ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
        ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['On Hold', 'On Hold'], ['Completed', 'Completed'], ['Dropped', 'Dropped']] }],
      ]);
    }
    return this.addCustomFields([
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'placeholder' }],
      ['client', {
        label: 'Client', type: 'placeholder', 'allow-null': true, 'remote-source': ['Client', 'id', 'name'],
      }],
      ['details', { label: 'Details', type: 'placeholder', validation: 'none' }],
      ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['On Hold', 'On Hold'], ['Completed', 'Completed'], ['Dropped', 'Dropped']] }],
    ]);
  }

  getHelpTitle() {
    return this.gt('Projects');
  }

  getHelpDescription() {
    return this.gt('Here you can add details about current projects. Projects are used to capture time spent by employees in timesheets.');
  }

  getHelpLink() {
    return 'https://icehrm.com/explore/docs/projects-and-clients-for-timesheets/';
  }

  getTableActionButtonJsx(adapter) {

    const addViewOnlyParameter = (url) => {
      if(!adapter.hasAccess('save')) {
        return url + '&view=1';
      }

      return url;
    };

    return (text, record) => (
      <Space size="middle">
        <Tag color="purple" onClick={() => modJs.showProjectDetails(record.id)} style={{ cursor: 'pointer' }}>
          <BarChartOutlined />
          {` ${adapter.gt('View')}`}
        </Tag>
        {adapter.hasAccess('save') && adapter.showEdit
              && (
              <Tag color="green" onClick={() => modJs.edit(record.id)} style={{ cursor: 'pointer' }}>
                <EditOutlined />
                {` ${adapter.gt('Edit')}`}
              </Tag>
              )}
        {(adapter.hasAccess('save') || adapter.hasAccess('element')) && record.document_link
              && (
              <Tag color="blue" onClick={() => { window.location.href = addViewOnlyParameter(record.document_link); }} style={{ cursor: 'pointer' }}>
                <MonitorOutlined />
                {` ${adapter.gt('Project Document')}`}
              </Tag>
              )}
        {adapter.hasAccess('delete') && adapter.showDelete
              && (
              <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
                <DeleteOutlined />
                {` ${adapter.gt('Delete')}`}
              </Tag>
              )}
        {adapter.hasAccess('save') && adapter.showAddNew
              && (
              <Tag color="cyan" onClick={() => modJs.copyRow(record.id)} style={{ cursor: 'pointer' }}>
                <CopyOutlined />
                {` ${adapter.gt('Copy')}`}
              </Tag>
              )}
      </Space>
    );
  }
}


/*
 * EmployeeProjectAdapter
 */


class EmployeeProjectAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.fieldNameMap = {};
    this.hiddenFields = {};
    this.tableFields = {};
    this.formOnlyFields = {};
  }

  getDataMapping() {
    return [
      'id',
      'employee',
      'project',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Employee' },
      { sTitle: 'Project' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Employee',
        dataIndex: 'employee',
        sorter: true,
      },
      {
        title: 'Project',
        dataIndex: 'project',
        sorter: true,
      },
    ];
  }


  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'] }],
      ['project', { label: 'Project', type: 'select2', 'remote-source': ['Project', 'id', 'name'] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name', 'getActiveSubordinateEmployees'],
      }],
      ['project', {
        label: 'Project', type: 'select2', 'allow-null': true, 'remote-source': ['Project', 'id', 'name'],
      }],
    ];
  }

  getHelpTitle() {
    return this.gt('Projects Assigned to Employees');
  }

  getHelpDescription() {
    return this.gt('Here you can assign projects to employees. Employees can use these projects when adding entries to timesheets.');
  }
}

/**
 * ClientAdapter
 */

class ClientAdapter extends ReactModalAdapterBase {
  constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.fieldNameMap = {};
    this.hiddenFields = {};
    this.tableFields = {};
    this.formOnlyFields = {};
  }

  getDataMapping() {
    return [
      'id',
      'name',
      'contact_email',
      'address',
      'document_link',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Email' },
      { sTitle: 'Address' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Email',
        dataIndex: 'contact_email',
        sorter: true,
      },
      {
        title: 'Address',
        dataIndex: 'address',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    if (this.showSave) {
      return this.addCustomFields([
        ['id', { label: 'ID', type: 'hidden' }],
        ['name', { label: 'Name', type: 'text' }],
        ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
        ['address', { label: 'Address', type: 'textarea', validation: 'none' }],
        ['contact_email', { label: 'Contact Email', type: 'text', validation: 'none' }],
        ['company_url', { label: 'Company Url', type: 'text', validation: 'none' }],
        ['status', { label: 'Status', type: 'select', source: [['Active', 'Active'], ['Inactive', 'Inactive']] }],
      ]);
    }
    return this.addCustomFields([
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'placeholder' }],
      ['details', { label: 'Details', type: 'placeholder', validation: 'none' }],
      ['address', { label: 'Address', type: 'placeholder', validation: 'none' }],
      ['contact_email', { label: 'Contact Email', type: 'placeholder', validation: 'none' }],
      ['company_url', { label: 'Company Url', type: 'placeholder', validation: 'none' }],
      ['status', { label: 'Status', type: 'placeholder', source: [['Active', 'Active'], ['Inactive', 'Inactive']] }],
    ]);
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
        {(adapter.hasAccess('save') || adapter.hasAccess('element')) && record.document_link
              && (
              <Tag
                color="blue"
                onClick={() => {
                  window.location.href = record.document_link;
                }}
                style={{ cursor: 'pointer' }}
              >
                <MonitorOutlined />
                {` ${adapter.gt('Document')}`}
              </Tag>
              )}
        {adapter.hasAccess('delete') && adapter.showDelete
              && (
              <Tag color="volcano" onClick={() => modJs.deleteRow(record.id)} style={{ cursor: 'pointer' }}>
                <DeleteOutlined />
                {` ${adapter.gt('Delete')}`}
              </Tag>
              )}
        {adapter.hasAccess('save') && adapter.showAddNew
              && (
              <Tag color="cyan" onClick={() => modJs.copyRow(record.id)} style={{ cursor: 'pointer' }}>
                <CopyOutlined />
                {` ${adapter.gt('Copy')}`}
              </Tag>
              )}
      </Space>
    );
  }

  getHelpTitle() {
    return this.gt('Clients of Your Organization');
  }

  getHelpDescription() {
    return this.gt('Here you can add and manage clients your organization is working with. These clients can be attached to Projects.');
  }

  getHelpLink() {
    return 'https://icehrm.com/explore/docs/projects-and-clients-for-timesheets/';
  }
}

module.exports = {
  ProjectAdapter,
  EmployeeProjectAdapter,
  ClientAdapter,
};
