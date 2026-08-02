const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=expenses&m=admin_Admin',
  'ExpensesCategory',
  false,
);
context('Admin Expenses Module - Employee Expenses Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#name', 'other']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'other']]);
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#name', 'other']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'other']]);
  });
});
