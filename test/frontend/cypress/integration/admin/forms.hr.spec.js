const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=forms&m=admin_Employees',
  'Form',
  false,
);

context('Admin Forms Module - HR Forms Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#description', 'feedback form']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'feedback form']]);
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#description', 'feedback form']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'feedback form']]);
  });
});
