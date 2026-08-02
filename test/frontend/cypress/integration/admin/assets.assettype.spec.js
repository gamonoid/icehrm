const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=assets&m=admin_Admin',
  'AssetType',
  false,
);
context('Admin Assets Module - Asset Type Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    test.isRemoteTable = false;
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#description', 'personal computer']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'personal computer']]);
  });

  it('manager can view list', () => {
    test.isRemoteTable = false;
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('manager can edit element', () => {
    test.isRemoteTable = false;
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#description', 'personal computer']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'personal computer']]);
  });
});
