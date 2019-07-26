/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import AdapterBase from '../../../api/AdapterBase';

/**
 * JobTitleAdapter
 */

class JobTitleAdapter extends AdapterBase {
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

  getHelpLink() {
    return 'http://blog.icehrm.com/docs/jobdetails/';
  }
}


/**
 * PayGradeAdapter
 */

class PayGradeAdapter extends AdapterBase {
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
}


/**
 * EmploymentStatusAdapter
 */

class EmploymentStatusAdapter extends AdapterBase {
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
}


module.exports = { JobTitleAdapter, PayGradeAdapter, EmploymentStatusAdapter };
