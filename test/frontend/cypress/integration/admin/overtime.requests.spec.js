const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=overtime&m=admin_Admin',
  'EmployeeOvertime',
  true,
);
context('Admin Overtime Module - Overtime Requests Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#notes', '2 hours minimum']]);
    test.select2Click('employee', 'Carol Linda');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#notes', '2 hours minimum']]);
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 2);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#notes', '2 hours minimum']]);
    test.select2Click('employee', 'Carol Linda');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#notes', '2 hours minimum']]);
  });
});
