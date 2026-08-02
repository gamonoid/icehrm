const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=training&m=admin_Admin',
  'Course',
  true,
);
context('Admin Training Module - Courses Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#trainer', 'Mark Adams']], '.center div img[title=\'Edit\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#trainer', 'Mark Adams']], '.center div img[title=\'Edit\']');
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#trainer', 'Mark Adams']], '.center div img[title=\'Edit\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#trainer', 'Mark Adams']], '.center div img[title=\'Edit\']');
  });
});
