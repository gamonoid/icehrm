const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=jobpositions&m=admin_Recruitment',
  'Job',
  false,
);

context('Admin Job Positions Module - Job Positions Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#textMapped', 'employee home number']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#textMapped', 'employee home number']]);
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#textMapped', 'employee home number']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#textMapped', 'employee home number']]);
  });
});
