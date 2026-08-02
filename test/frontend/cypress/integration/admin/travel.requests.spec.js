const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=travel&m=admin_Admin',
  'EmployeeTravelRecord',
  true,
);
context('Admin Travel Module - Travel Requests Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#profession', 'manager']],'.center div img[title=\'Edit\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#profession', 'manager']],'.center div img[title=\'Edit\']');
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#profession', 'manager']], '.center div img[title=\'Edit\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#profession', 'manager']], '.center div img[title=\'Edit\']');
  });
});
