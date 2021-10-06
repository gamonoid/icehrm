import {InvoiceAdapter} from './lib';
import IceDataPipe from '../../../../web/api/IceDataPipe';

function init(data) {
  const modJsList = [];

  modJsList.tabInvoices =new InvoiceAdapter('Invoice', 'Invoices','','');
  modJsList.tabInvoices.setObjectTypeName('Invoice');
  modJsList.tabInvoices.setDataPipe(new IceDataPipe(modJsList.tabInvoices));
  modJsList.tabInvoices.setAccess(data.permissions.Invoice);

  window.modJs = modJsList.tabInvoices;
  window.modJsList = modJsList;
}

window.initAdminInvoices = init;

