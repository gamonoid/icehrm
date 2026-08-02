const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=loans&m=admin_Admin',
  'CompanyLoan',
  false,
);
context('Admin Loans Module - Loan Types Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#details', 'House construction']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#details', 'House construction']]);
  });
});
