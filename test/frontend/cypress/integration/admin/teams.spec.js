const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=teams&m=admin_Employees',
  'Teams',
  false,
);

context('Admin Teams Module - Teams Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });

  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#description', 'creative designing team']]);
    test.select2Click('department', 'Head Office');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'creative designing team']]);
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });

  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#description', 'creative designing team']]);
    test.select2Click('department', 'Head Office');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'creative designing team']]);
  });
});
