const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=loans&m=admin_Admin',
  'EmployeeCompanyLoan',
  false,
);
context('Admin Loans Module - Employee Loans Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 1);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#period_months', '18']]);
    test.select2Click('currency' ,'Swiss Franc');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#period_months', '18']]);
  });
});
