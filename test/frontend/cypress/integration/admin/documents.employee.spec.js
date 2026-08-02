const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=documents&m=admin_Employees',
  'EmployeeDocument',
  false,
);

context('Admin Document Module - Employee Document Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 3);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#details', 'identity card']], '.center div img[title=\'Edit\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#details', 'identity card']] , '.center div img[title=\'Edit\']');
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy, 3);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#details', 'identity card']], '.center div img[title=\'Edit\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#details', 'identity card']] , '.center div img[title=\'Edit\']');
  });
});
