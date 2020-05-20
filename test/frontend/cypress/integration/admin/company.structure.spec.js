const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=company_structure&m=admin_Admin',
  'CompanyStructure',
);

context('Admin Company Structure Module - Company Structure Tab', () => {

  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 9);
  });

  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#address', 'Address 1']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#address', 'Address 1']]);
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy, 9);
  });

  it('manager can not edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.canNotEditElement(cy);
  });
});
