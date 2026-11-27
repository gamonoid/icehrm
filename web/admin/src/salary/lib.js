/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import AdapterBase from '../../../api/AdapterBase';
import ReactModalAdapterBase from "../../../api/ReactModalAdapterBase";
import { Avatar } from 'antd';

const SALARY_CHANGE_REASONS = [['New hire', 'New hire'], ['Raise', 'Raise'], ['Promotion', 'Promotion'], ['Adjustment', 'Adjustment'], ['Correction', 'Correction']];

/**
 * SalaryComponentTypeAdapter
 */

class SalaryComponentTypeAdapter extends ReactModalAdapterBase {
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

  getTableColumns() {
    return [
      {
        title: 'Code',
        dataIndex: 'code',
        sorter: true,
      },
      {
        title: 'Name',
        dataIndex: 'name',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['code', { label: 'Code', type: 'text', validation: '' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
    ];
  }
}


/**
 * SalaryComponentAdapter
 */

class SalaryComponentAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'name',
      'componentType',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Name' },
      { sTitle: 'Salary Component Type' },
      { sTitle: 'Details' },
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
        title: 'Salary Component Type',
        dataIndex: 'componentType',
        sorter: true,
      },
      {
        title: 'Details',
        dataIndex: 'details',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['name', { label: 'Name', type: 'text', validation: '' }],
      ['componentType', { label: 'Salary Component Type', type: 'select2', 'remote-source': ['SalaryComponentType', 'id', 'name'] }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
    ];
  }
}


/*
 * EmployeeSalaryAdapter
 */

class EmployeeSalaryAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'image',
      'employee',
      'component',
      'amount',
      'details',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: '' },
      { sTitle: 'Employee' },
      { sTitle: 'Salary Component' },
      { sTitle: 'Amount' },
      { sTitle: 'Details' },
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
        title: 'Salary Component',
        dataIndex: 'component',
        sorter: true,
      },
      {
        title: 'Amount',
        dataIndex: 'amount',
        sorter: true,
      },
      {
        title: 'Details',
        dataIndex: 'details',
        sorter: true,
      },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],
      ['component', { label: 'Salary Component', type: 'select2', 'remote-source': ['SalaryComponent', 'id', 'name'] }],
      ['amount', { label: 'Amount', type: 'text', validation: 'float' }],
      ['details', { label: 'Details', type: 'textarea', validation: 'none' }],
      // Not stored on the salary line: recorded on the Salary History row this
      // save creates when the amount changes.
      ['history_effective_date', {
        label: 'Effective Date', type: 'date', validation: 'none', help: 'Date the new amount applies from. Defaults to today.',
      }],
      ['history_reason', {
        label: 'Reason for Change', type: 'select', 'allow-null': true, source: SALARY_CHANGE_REASONS,
      }],
    ];
  }

  getFilters() {
    return [
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],

    ];
  }
}

/**
 * EmployeeSalaryHistoryAdapter — read-only log of salary changes.
 */

class EmployeeSalaryHistoryAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'image',
      'employee',
      'component',
      'old_amount',
      'new_amount',
      'effective_date',
      'change_reason',
      'changed_by',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: '' },
      { sTitle: 'Employee' },
      { sTitle: 'Salary Component' },
      { sTitle: 'Previous Amount' },
      { sTitle: 'New Amount' },
      { sTitle: 'Effective Date' },
      { sTitle: 'Reason' },
      { sTitle: 'Changed By' },
    ];
  }

  getTableColumns() {
    return [
      {
        title: '',
        dataIndex: 'image',
        render: (text) => <Avatar src={text} />,
      },
      { title: 'Employee', dataIndex: 'employee', sorter: true },
      { title: 'Salary Component', dataIndex: 'component', sorter: true },
      { title: 'Previous Amount', dataIndex: 'old_amount' },
      { title: 'New Amount', dataIndex: 'new_amount' },
      { title: 'Effective Date', dataIndex: 'effective_date', sorter: true },
      { title: 'Reason', dataIndex: 'change_reason', sorter: true },
      { title: 'Changed By', dataIndex: 'changed_by' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],
      ['component', { label: 'Salary Component', type: 'select2', 'remote-source': ['SalaryComponent', 'id', 'name'] }],
      ['old_amount', { label: 'Previous Amount', type: 'text', validation: 'none' }],
      ['new_amount', { label: 'New Amount', type: 'text', validation: 'none' }],
      ['effective_date', { label: 'Effective Date', type: 'date', validation: 'none' }],
      ['change_reason', { label: 'Reason', type: 'text', validation: 'none' }],
      ['changed_by', { label: 'Changed By', type: 'select2', 'allow-null': true, 'remote-source': ['User', 'id', 'username'] }],
    ];
  }

  getFilters() {
    return [
      ['employee', {
        label: 'Employee', type: 'select2', 'allow-null': true, 'remote-source': ['Employee', 'id', 'first_name+last_name'],
      }],
      ['component', {
        label: 'Salary Component', type: 'select2', 'allow-null': true, 'remote-source': ['SalaryComponent', 'id', 'name'],
      }],
    ];
  }

  getHelpTitle() {
    return this.gt('Salary History');
  }

  getHelpDescription() {
    return this.gt('Every change to an employee salary line is recorded here automatically, with the previous and new amount, the effective date and the reason. Payroll is calculated from the Salary tab only.');
  }
}

module.exports = {
  SalaryComponentTypeAdapter,
  SalaryComponentAdapter,
  EmployeeSalaryAdapter,
  EmployeeSalaryHistoryAdapter,
};
