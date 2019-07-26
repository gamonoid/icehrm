/*
 Copyright (c) 2018 [Glacies UG, Berlin, Germany] (http://glacies.de)
 Developer: Thilina Hasantha (http://lk.linkedin.com/in/thilinah | https://github.com/thilinah)
 */
import AdapterBase from '../../../api/AdapterBase';

class EmployeeCompanyLoanAdapter extends AdapterBase {
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


  // eslint-disable-next-line no-unused-vars
  getActionButtonsHtml(id, data) {
    const editButton = '<img class="tableActionButton" '
      + 'src="_BASE_images/view.png" '
      + 'style="cursor:pointer;" '
      + 'rel="tooltip" title="View" '
      + 'onclick="modJs.edit(_id_);return false;">'
      + '</img>';

    const deleteButton = '<img class="tableActionButton" '
      + 'src="_BASE_images/delete.png" '
      + 'style="margin-left:15px;cursor:pointer;" '
      + 'rel="tooltip" title="Delete" '
      + 'onclick="modJs.deleteRow(_id_);return false;">'
      + '</img>';
    let html = '<div style="width:80px;">_edit__delete_</div>';

    if (this.showDelete) {
      html = html.replace('_delete_', deleteButton);
    } else {
      html = html.replace('_delete_', '');
    }

    if (this.showEdit) {
      html = html.replace('_edit_', editButton);
    } else {
      html = html.replace('_edit_', '');
    }

    html = html.replace(/_id_/g, id);
    html = html.replace(/_BASE_/g, this.baseUrl);
    return html;
  }
}

module.exports = { EmployeeCompanyLoanAdapter };
