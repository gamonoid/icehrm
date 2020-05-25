const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=employees&m=admin_Employees',
  'Employee',
  true,
);

context('Admin Employee Module - Employee Tab', () => {

  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy);
  });

  it('admin can view element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.viewElement(cy);
    test.viewElementValidate(cy, [['#name', 'IceHrm Employee']]);
  });

  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#middle_name', 'Middle Name']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#middle_name', 'Middle Name']]);
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy);
  });

  it('manager can view element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.viewElement(cy);
    test.viewElementValidate(cy, [['#name', 'IceHrm Employee']]);
  });

  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#middle_name', 'Middle Name 1']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#middle_name', 'Middle Name 1']]);
  });
});
