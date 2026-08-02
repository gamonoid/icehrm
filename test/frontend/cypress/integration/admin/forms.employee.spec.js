const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=forms&m=admin_Employees',
  'EmployeeForm',
  false,
);

context('Admin Forms Module - Employee Forms Tab', () => {
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
    cy.get('#EmployeeForm table tbody').find('tr').first().find('.center div img[title=\'Edit\']')
      .click();
    test.select2Click('employee', 'Ronald Carl');
    test.clickSave(cy);
    test.select2ClickValidate(cy, [['employee', 'Ronald Carl']],'.center div img[title=\'Edit\']');
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
    cy.get('#EmployeeForm table tbody').find('tr').first().find('.center div img[title=\'Edit\']')
      .click();
    test.select2Click('employee', 'Ronald Carl');
    test.clickSave(cy);
    test.select2ClickValidate(cy, [['employee', 'Ronald Carl']],'.center div img[title=\'Edit\']');
  });
});
