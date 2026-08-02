const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=payroll&m=admin_Payroll',
  'DeductionGroup',
  true,
);
context('Admin Payroll Module - Company Payroll Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#description', 'deduction group 2']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#description', 'deduction group 2']]);
  });
});
