import InvoiceAdapter from './lib';
import IceDataPipe from '../../../../web/api/IceDataPipe';

function init(data) {
  const modJsList = [];

  modJsList.tabInvoices =new InvoiceAdapter('Invoices', 'Invoices','','');
  modJsList.tabInvoices.setObjectTypeName('Invoices');
  modJsList.tabInvoices.setDataPipe(new IceDataPipe(modJsList.tabVInvoices));
  modJsList.tabInvoices.setAccess(data.permissions.Invoices);

  window.modJs = modJsList.tabInvoices;
  window.modJsList = modJsList;
}

window.initAdminInvoices = init;

