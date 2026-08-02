const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=metadata&m=admin_System',
  'Country',
  false,
);
context('Admin Metadata Module - Ethnicity Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#name', 'Afghanistan']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'Afghanistan']]);
  });
});
