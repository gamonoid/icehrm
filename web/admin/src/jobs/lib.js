/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';

/**
 * JobTitleAdapter
 */

class JobTitleAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'code',
      'name',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Code' },
      { sTitle: 'Name' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['code', { label: 'Job Title Code', type: 'text' }],
      ['name', { label: 'Job Title', type: 'text' }],
      ['description', { label: 'Description', type: 'textarea' }],
      ['specification', { label: 'Specification', type: 'textarea' }],
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Job Title Code',
        dataIndex: 'code',
        sorter: true,
      },
      {
        title: 'Job Title',
        dataIndex: 'name',
        sorter: true,
      },
    ];
  }

  getHelpTitle() {
    return this.gt('Job Titles');
  }

  getHelpDescription() {
    return this.gt('Here you can manage the job titles in your organization. Each employee needs to be assigned a job title.');
  }

  getHelpLink() {
    return 'https://icehrm.com/explore/docs/job-details-and-qualifications-set-up/';
  }
}


/**
 * PayGradeAdapter
 */

class PayGradeAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'currency',
      'min_salary',
      'max_salary',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Currency' },
      { sTitle: 'Min Salary' },
      { sTitle: 'Max Salary' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Pay Grade Name', type: 'text' }],
      ['currency', { label: 'Currency', type: 'select2', 'remote-source': ['CurrencyType', 'code', 'name'] }],
      ['min_salary', { label: 'Min Salary', type: 'text', validation: 'float' }],
      ['max_salary', { label: 'Max Salary', type: 'text', validation: 'float' }],
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
        title: 'Currency',
        dataIndex: 'currency',
      },
      {
        title: 'Min Salary',
        dataIndex: 'min_salary',
      },
      {
        title: 'Max Salary',
        dataIndex: 'max_salary',
      },
    ];
  }

  doCustomValidation(params) {
    try {
      if (parseFloat(params.min_salary) > parseFloat(params.max_salary)) {
        return 'Min Salary should be smaller than Max Salary';
      }
    } catch (e) {
      // D/N
    }
    return null;
  }

  getHelpTitle() {
    return this.gt('Pay Grades');
  }

  getHelpDescription() {
    return this.gt('Here you can define the different pay grades in your organization.');
  }
}


/**
 * EmploymentStatusAdapter
 */

class EmploymentStatusAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'description',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID' },
      { sTitle: 'Name' },
      { sTitle: 'Description' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Employment Status', type: 'text' }],
      ['description', { label: 'Description', type: 'textarea', validation: '' }],
    ];
  }

  getTableColumns() {
    return [
      {
        title: 'Employment Status',
        dataIndex: 'name',
        sorter: true,
      },
      {
        title: 'Description',
        dataIndex: 'description',
      },
    ];
  }

  getHelpTitle() {
    return this.gt('Employment Status');
  }

  getHelpDescription() {
    return this.gt('Here you can define the different type of employment states in your organization. Each employee should be assigned an employment state.');
  }
}


module.exports = { JobTitleAdapter, PayGradeAdapter, EmploymentStatusAdapter };
