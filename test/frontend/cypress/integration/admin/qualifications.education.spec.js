const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=qualifications&m=admin_Admin',
  'Education',
  false,
);
context('Admin Qualifications Module - Education Tab', () => {
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
    test.editElement(cy, [['#description', 'Bachelors Degree']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'Bachelors Degree']]);
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
    test.editElement(cy, [['#description', 'Bachelors Degree']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'Bachelors Degree']]);
  });
});
