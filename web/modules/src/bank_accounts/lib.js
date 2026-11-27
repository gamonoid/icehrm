/*
 Self-service bank accounts (About You → Bank Accounts).
 Account numbers and IBANs come back masked ("****1234"); saving without changing a
 masked value keeps the stored one (see Employees\Common\Model\EmployeeBankAccount).
 */

import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';

const YES_NO = [['Yes', 'Yes'], ['No', 'No']];
const SPLIT_TYPES = [['Percentage', 'Percentage'], ['Fixed amount', 'Fixed amount'], ['Remainder', 'Remainder']];

class EmployeeBankAccountAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'account_holder',
      'bank_name',
      'account_number',
      'is_primary',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Account Holder' },
      { sTitle: 'Bank Name' },
      { sTitle: 'Account Number' },
      { sTitle: 'Primary' },
    ];
  }

  getTableColumns() {
    return [
      { title: 'Account Holder', dataIndex: 'account_holder', sorter: true },
      { title: 'Bank Name', dataIndex: 'bank_name', sorter: true },
      { title: 'Account Number', dataIndex: 'account_number' },
      { title: 'Primary', dataIndex: 'is_primary' },
    ];
  }

  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['account_holder', { label: 'Account Holder', type: 'text', validation: '' }],
      ['bank_name', { label: 'Bank Name', type: 'text', validation: '' }],
      ['branch', { label: 'Branch', type: 'text', validation: 'none' }],
      ['account_number', {
        label: 'Account Number', type: 'text', validation: 'none', help: 'Shown masked after saving. Leave it as it is to keep the saved number.',
      }],
      ['iban', { label: 'IBAN', type: 'text', validation: 'none' }],
      ['swift_bic', { label: 'SWIFT / BIC', type: 'text', validation: 'none' }],
      ['routing_code', {
        label: 'Routing / Sort Code', type: 'text', validation: 'none', help: 'ABA routing number, sort code, BSB, IFSC or similar',
      }],
      ['currency', {
        label: 'Currency', type: 'select2', 'allow-null': true, 'remote-source': ['CurrencyType', 'id', 'code+name'],
      }],
      ['is_primary', { label: 'Primary Account', type: 'select', source: YES_NO }],
      ['split_type', {
        label: 'Salary Split', type: 'select', 'allow-null': true, 'null-label': 'Whole salary', source: SPLIT_TYPES,
      }],
      ['split_value', { label: 'Split Value', type: 'text', validation: 'none' }],
      ['notes', { label: 'Notes', type: 'textarea', validation: 'none' }],
    ];
  }

  getHelpTitle() {
    return this.gt('Bank Accounts');
  }

  getHelpDescription() {
    return this.gt('The bank accounts your salary is paid into. Account numbers are stored encrypted and shown with only the last four characters.');
  }
}

module.exports = { EmployeeBankAccountAdapter };
