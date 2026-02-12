/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */

import React from 'react';
import AdapterBase from '../../../api/AdapterBase';
import ReactModalAdapterBase from "../../../api/ReactModalAdapterBase";
import { Avatar } from 'antd';

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
    ];
  }

  getFilters() {
    return [
      ['employee', { label: 'Employee', type: 'select2', 'remote-source': ['Employee', 'id', 'first_name+last_name'] }],

    ];
  }
}

module.exports = {
  SalaryComponentTypeAdapter,
  SalaryComponentAdapter,
  EmployeeSalaryAdapter,
};
