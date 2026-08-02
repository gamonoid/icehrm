const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=metadata&m=admin_System',
  'Ethnicity',
  false,
);
context('Admin Metadata Module - Ethnicity Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 7);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#name', 'indegenous']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'indegenous']]);
  });
});
