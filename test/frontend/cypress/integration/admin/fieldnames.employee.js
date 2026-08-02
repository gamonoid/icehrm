const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=fieldnames&m=admin_Admin',
  'EmployeeFieldName',
  true,
);

context('Admin Fieldnames Module - Employee Field Names Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#textMapped', 'employee home number']], '.center div img[title=\'Edit\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#textMapped', 'employee home number']], '.center div img[title=\'Edit\']');
  });
});
