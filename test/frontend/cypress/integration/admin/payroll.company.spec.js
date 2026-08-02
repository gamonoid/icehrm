const IceCypressTest = require('../../commmon/ice-cypress-test');
const test = new IceCypressTest(
  'g=admin&n=payroll&m=admin_Payroll',
  'PayrollEmployee',
  true,
);
context('Admin Payroll Module - Company Payroll Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    cy.get('#PayrollEmployee table tbody').find('tr').first().find('.center div img[title=\'Edit\']')
      .click();
    test.select2Click('pay_frequency', 'Yearly');
    test.clickSave(cy);
    test.select2ClickValidate(cy, [['pay_frequency', 'Yearly']]);
  });
});
