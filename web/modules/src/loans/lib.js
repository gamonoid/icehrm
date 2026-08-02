/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import ReactModalAdapterBase from '../../../api/ReactModalAdapterBase';

class EmployeeCompanyLoanAdapter extends ReactModalAdapterBase {
  getDataMapping() {
    return [
      'id',
      'loan',
      'start_date',
      'period_months',
      'currency',
      'amount',
      'status',
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Loan Type' },
      { sTitle: 'Loan Start Date' },
      { sTitle: 'Loan Period (Months)' },
      { sTitle: 'Currency' },
      { sTitle: 'Amount' },
      { sTitle: 'Status' },
    ];
  }

  getTableColumns() {
    return [
      { title: 'Loan Type', dataIndex: 'loan' },
      { title: 'Loan Start Date', dataIndex: 'start_date', sorter: true },
      { title: 'Loan Period (Months)', dataIndex: 'period_months' },
      { title: 'Currency', dataIndex: 'currency' },
      { title: 'Amount', dataIndex: 'amount' },
      { title: 'Status', dataIndex: 'status' },
    ];
  }

  // Employees only view their loans (added by admins) — the form is read-only.
  getFormFields() {
    return [
      ['id', { label: 'ID', type: 'hidden' }],
      ['loan', { label: 'Loan Type', type: 'placeholder', 'remote-source': ['CompanyLoan', 'id', 'name'] }],
      ['start_date', { label: 'Loan Start Date', type: 'placeholder', validation: '' }],
      ['last_installment_date', { label: 'Last Installment Date', type: 'placeholder', validation: 'none' }],
      ['period_months', { label: 'Loan Period (Months)', type: 'placeholder', validation: 'number' }],
      ['currency', { label: 'Currency', type: 'placeholder', 'remote-source': ['CurrencyType', 'id', 'name'] }],
      ['amount', { label: 'Loan Amount', type: 'placeholder', validation: 'float' }],
      ['monthly_installment', { label: 'Monthly Installment', type: 'placeholder', validation: 'float' }],
      ['status',
        {
          label: 'Status',
          type: 'placeholder',
          source: [['Approved', 'Approved'], ['Paid', 'Paid'], ['Suspended', 'Suspended']],
        },
      ],
      ['details', { label: 'Details', type: 'placeholder', validation: 'none' }],
    ];
  }

  // Resolve remote-source ids (loan type, currency) to their display names for
  // the read-only view — mirrors the legacy placeholder handling in ModuleBase.
  modifyObjectBeforeView(object, viewOnly) {
    if (!object) return object;
    const resolved = { ...object };
    this.getFormFields().forEach((field) => {
      const src = field[1] && field[1]['remote-source'];
      if (!src) return;
      const key = this.getRemoteSourceKey(field);
      const map = this.fieldMasterData && this.fieldMasterData[key];
      const val = resolved[field[0]];
      if (map && val !== undefined && val !== null && map[val] !== undefined) {
        resolved[field[0]] = map[val];
      }
    });
    return resolved;
  }
}

module.exports = { EmployeeCompanyLoanAdapter };
