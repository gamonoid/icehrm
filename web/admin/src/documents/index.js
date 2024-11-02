import React from 'react';
import ReactDOM from 'react-dom';
import { Alert, Space } from 'antd';
import {
  DocumentAdapter, CompanyDocumentAdapter, EmployeeDocumentAdapter, EmployeePayslipDocumentAdapter,
} from './lib';

window.DocumentAdapter = DocumentAdapter;
window.CompanyDocumentAdapter = CompanyDocumentAdapter;
window.EmployeeDocumentAdapter = EmployeeDocumentAdapter;
window.EmployeePayslipDocumentAdapter = EmployeePayslipDocumentAdapter;

