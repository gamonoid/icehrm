const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=assets&m=admin_Admin',
  'CompanyAsset',
  false,
);
context('Admin Assets Module - Company Assets Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.isRemoteTable = true;
    test.switchTab(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    test.isRemoteTable = false;
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.isRemoteTable = true;
    test.switchTab(cy);
    test.editElement(cy, [['#description', 'personal computer']], '.center div img[title=\'Edit\']');
    test.select2Click('department', 'Head Office');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'personal computer']], '.center div img[title=\'Edit\']');
  });

  it('manager can view list', () => {
    test.isRemoteTable = false;
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.isRemoteTable = true;
    test.switchTab(cy);
    test.loadTable(cy, 2);
  });
  it('manager can edit element', () => {
    test.isRemoteTable = false;
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.isRemoteTable = true;
    test.switchTab(cy);
    test.editElement(cy, [['#description', 'personal computer']], '.center div img[title=\'Edit\']');
    test.select2Click('department', 'Head Office');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'personal computer']], '.center div img[title=\'Edit\']');
  });
});
