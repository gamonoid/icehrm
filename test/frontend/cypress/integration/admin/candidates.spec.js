const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=candidates&m=admin_Recruitment',
  'Candidate',
  true,
);

context('Admin Recruitment Module - Candidates Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#first_name', 'Kasun']], '.center div img[title=\'Edit Candidate\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#first_name', 'Kasun']], '.center div img[title=\'Edit Candidate\']');
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#first_name', 'Kasun']], '.center div img[title=\'Edit Candidate\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#first_name', 'Kasun']], '.center div img[title=\'Edit Candidate\']');
  });
});
