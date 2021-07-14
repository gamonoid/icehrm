import ReactModalAdapterBase from '../../../../web/api/ReactModalAdapterBase';

/**
 * VatInvoiceAdapter
 */

class InvoiceAdapter extends ReactModalAdapterBase {
  /*constructor(endPoint, tab, filter, orderBy) {
    super(endPoint, tab, filter, orderBy);
    this.fieldNameMap = {};
    this.hiddenFields = {};
    this.tableFields = {};
    this.formOnlyFields = {};
  }*/

  getDataMapping() {
    return [
      'id',
      'paymentId',
      'invoiceId',
      'description',
      'buyerName',
      'buyerAddress',
      'buyerPostalAddress',
      'buyerVatId',
      'buyerEmail',
      'sellerName',
      'sellerAddress',
      'sellerVatId',
      'amount',
      'vat',
      'vatRate',
      'issuedDate',
      'dueDate',
      'paidDate',
      'status',
      'acceptPayments',
      'created',
      'updated',
      'link',
      'paymentLink'
    ];
  }

  getHeaders() {
    return [
      { sTitle: 'ID', bVisible: false },
      { sTitle: 'Payment Id' },
      { sTitle: 'Invoice ID' },
      { sTitle: 'Description' },
      { sTitle: 'Buyer Name' },
      { sTitle: 'Buyer Address' },
      { sTitle: 'Buyer Postal Code' },
      { sTitle: 'Buyer Country' },
      { sTitle: 'Buyer Vat Id' },
      { sTitle: 'Buyer Email' },
      { sTitle: 'Seller Name' },
      { sTitle: 'Seller Country' },
      { sTitle: 'Seller Vat Id' },
      { sTitle: 'Amount' },
      { sTitle: 'Vat' },
      { sTitle: 'Vat Rate' },
      { sTitle: 'Issued Date' },
      { sTitle: 'Paid Date' },
      { sTitle: 'Status' },
      { sTitle: 'Accept Payments' },
      { sTitle: 'Created' },
      { sTitle: 'Updated' },
      { sTitle: 'Link' },
      { sTitle: 'Payment Link' },
    ];
  }

  getCountryList() {
    return [
      ['DE', 'Germany'],
      ['LK', 'Sri Lanka'],
    ];
  }

  getFormFields() {
    return [
        [ 'id', {"label":"ID","type":"hidden"}],
        [ 'paymentId', {"label":"Payment Id","type":"text","validation":"int"}],
        [ 'invoiceId', {"label":"Invoice Id","type":"text","validation":"int"}],
        [ 'description', {"label":"Description","type":"textarea","validation":"none"}],
        [ 'buyerName', {"label":"Buyer Name","type":"text"}],
        [ 'buyerAddress', {"label":"Buyer Address","type":"textarea"}],
        [ 'buyerPostalCode', {"label":"Buyer Postal Code","type":"text"}],
        [ 'buyerCountry', {"label":"Buyer Country","type":"select2", "remote-source": ["Country", "code", "name"]}],
        [ 'buyerVatId', {"label":"Buyer Vat Id","type":"text","validation":"none"}],
        [ 'buyerEmail', {"label":"Buyer Email","type":"text","validation":"email"}],
        [ 'sellerName', {"label":"Seller Name","type":"text"}],
        [ 'sellerAddress', {"label":"Seller Address","type":"text"}],
        [ 'sellerCountry', {"label":"Seller Country","type":"select2", "remote-source": ["Country", "code", "name"]}],
        [ 'sellerVatId', {"label":"Seller Vat Id","type":"text"}],
        [ 'amount', {"label":"Amount with VAT","type":"text", "validation":"float"}],
        [ 'vat', {"label":"Vat","type":"text", "validation":"float"}],
        [ 'vatRate', {"label":"Vat Rate","type":"text", "validation":"float"}],
        [ 'issuedDate', {"label":"Issued Date","type":"datetime", "validation":""}],
        [ 'dueDate', {"label":"Due Date","type":"datetime", "validation":""}],
        [ 'paidDate', {"label":"Paid Date","type":"datetime", "validation":""}],
        [ 'status', {"label":"Status","type":"select","source":[["Pending","Pending"],["Paid","Paid"],["Processing","Processing"],["Draft","Draft"],["Sent","Sent"],["Canceled","Canceled"]]}],
        [ 'acceptPayments', {"label":"Accept Payments","type":"select","source":[["0","No"],["1","Yes"]]}],
        [ 'created', {"label":"Created","type":"datetime", "validation":""}],
        [ 'updated', {"label":"Updated","type":"datetime", "validation":""}],
        [ 'link', {"label":"Link","type":"placeholder"}],
        [ 'paymentLink', {"label":"Payment Link","type":"placeholder"}],
        ['items', {
          label: 'Items',
          type: 'datagroup',
          form: [
            ['description', { label: 'Description', type: 'textarea', validation: '' }],
            ['rate', { label: 'Rate', type: 'text', validation: '' }],
            ['qty', { label: 'Quantity', type: 'text', validation: '' }],
            ['lineTotal', { label: 'Line Total', type: 'text', validation: '' }],
          ],
          html: '<div id="#_id_#" class="panel panel-default"><div class="panel-body">#_delete_##_edit_#<span style="color:#999;font-size:13px;font-weight:bold">Date: #_date_#</span><hr/>#_note_#</div></div>',
          validation: 'none',
          columns: [
            {
              title: 'Description',
              dataIndex: 'description',
              key: 'description',
            },
            {
              title: 'Rate',
              dataIndex: 'rate',
              key: 'rate',
            },
            {
              title: 'Quantity',
              dataIndex: 'qty',
              key: 'qty',
            },
            {
              title: 'Line Total',
              dataIndex: 'lineTotal',
              key: 'lineTotal',
            },
          ],
          'sort-function': function (a, b) {
            const t1 = Date.parse(a.date).getTime();
            const t2 = Date.parse(b.date).getTime();
  
            return (t1 < t2);
          },
          'custom-validate-function': function (data) {
            const res = {};
            res.valid = true;
            data.date = new Date().toString('d-MMM-yyyy hh:mm tt');
            res.params = data;
            return res;
          },
  
        }],
      ];
  }

  getTableColumns() {
    return [
      {
        title: 'Invoice Id',
        dataIndex: 'invoiceId',
        sorter: true,
      },
      {
        title: 'Description',
        dataIndex: 'description',
        sorter: true,
      },
    ];
  }
  
}

module.exports ={InvoiceAdapter};