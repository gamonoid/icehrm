const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=salary&m=admin_Payroll',
  'SalaryComponentType',
  false,
);
context('Admin Salary Module - Salary Component Types Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 3);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#name', 'Basic']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'Basic']]);
  });
});
