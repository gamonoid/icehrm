const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=assets&m=admin_Admin',
  'AssetCustomField',
  false,
);
context('Admin Assets Module - Custom Fields Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.isRemoteTable= true;
    test.switchTab(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    test.isRemoteTable = false;
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.isRemoteTable= true;
    test.switchTab(cy);
    test.editElement(cy, [['#name', 'profit']], '.center div img[title=\'Edit\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'profit']],'.center div img[title=\'Edit\']');
  });
});
