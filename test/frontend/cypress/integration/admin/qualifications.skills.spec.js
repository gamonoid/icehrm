const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=qualifications&m=admin_Admin',
  'Skill',
  false,
);
context('Admin Qualifications Module - Skills Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 10);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#description', 'based on react and node js']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'based on react and node js']]);
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy, 10);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#description', 'based on react and node js']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'based on react and node js']]);
  });
});
