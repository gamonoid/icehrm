const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=documents&m=admin_Employees',
  'CompanyDocument',
  false,
);

context('Admin Document Module - Company Documents Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#details', 'identity card']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#details', 'identity card']]);
  });
});
