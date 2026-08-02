const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=qualifications&m=admin_Admin',
  'Language',
  false,
);
context('Admin Qualifications Module - Languages Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#description', 'English']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'English']]);
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#description', 'English']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'English']]);
  });
});
