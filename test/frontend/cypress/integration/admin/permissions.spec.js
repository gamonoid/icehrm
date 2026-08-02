const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=permissions&m=admin_Payroll',
  'Permission',
  false,
);
context('Admin Permission Module - Permissions Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    cy.get('#Permission table tbody').find('tr').first().find('.center div img[data-original-title=\'Edit\']')
      .click();
    test.select2Click('value', 'Yes');
    test.clickSave(cy);
    test.select2ClickValidate(cy, [['value', 'Yes']]);
  });
});
