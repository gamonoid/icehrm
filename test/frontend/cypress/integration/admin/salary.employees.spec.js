const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=salary&m=admin_Payroll',
  'EmployeeSalary',
  false,
);
context('Admin Salary Module - Employee Salary Components Tab', () => {
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
    test.editElement(cy, [['#details', 'basic allowance']],'.center div img[title=\'Edit\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#details', 'basic allowance']],'.center div img[title=\'Edit\']');
  });
});
