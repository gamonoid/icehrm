const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=teams&m=admin_Employees',
  'TeamMembers',
  false,
);

context('Admin Teams Module - Team Members Tab', () => {
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
    cy.get('#TeamMembers table tbody').find('tr').first().find('.center div img[data-original-title=\'Edit\']')
      .click();
    test.select2Click('team', 'beta');
    test.clickSave(cy);
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
    cy.get('#TeamMembers table tbody').find('tr').first().find('.center div img[data-original-title=\'Edit\']')
      .click();
    test.select2Click('team', 'beta');
    test.clickSave(cy);
  });
});
