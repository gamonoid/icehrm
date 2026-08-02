const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=jobs&m=admin_Admin',
  'EmploymentStatus',
  false,
);

context('Admin Jobs Module - Employment Status Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 6);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#description', 'Developing android apps']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'Developing android apps']]);
  });
});
