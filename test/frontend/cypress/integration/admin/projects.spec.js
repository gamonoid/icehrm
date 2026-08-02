const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=projects&m=admin_Admin',
  'Project',
  false,
);
context('Admin Projects Module - Projects Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 4);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#details', 'construction project']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#details', 'construction project']]);
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 4);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#details', 'construction project']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#details', 'construction project']]);
  });
});
