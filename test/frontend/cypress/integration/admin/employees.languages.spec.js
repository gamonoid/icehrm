const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=employees&m=admin_Employees',
  'EmployeeLanguage',
  true,
);

context('Admin Employee Module - Language Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 3);
  });

  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    cy.get('#EmployeeLanguage table tbody').find('tr').first().find('.center div img[title=\'Edit\']')
      .click();
    test.select2Click('employee', 'Carol Linda');
    test.clickSave(cy);
    test.select2ClickValidate(cy, [['employee', 'Carol Linda']]);
  });
});
