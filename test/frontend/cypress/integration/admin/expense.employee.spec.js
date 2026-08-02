const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=expenses&m=admin_Admin',
  'EmployeeExpense',
  false,
);
context('Admin Expenses Module - Employee Expenses Tab', () => {
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
    test.editElement(cy, [['#notes', 'transport cost']] ,'.center div img[title=\'Edit\']');
    test.select2Click('payment_method', 'Credit Card');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#notes', 'transport cost']], '.center div img[title=\'Edit\']');
  });

  it('manager can view list', () => {
    test.isRemoteTable=false;
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
    test.editElement(cy, [['#notes', 'transport cost']] ,'.center div img[title=\'Edit\']');
    test.select2Click('payment_method', 'Credit Card');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#notes', 'transport cost']], '.center div img[title=\'Edit\']');
  });
});
