const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=modules&m=admin_System',
  'Module',
  false,
);
context('Admin Module Module - Modules Tab', () => {
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
    test.editElement(cy, [['#label', 'Resources']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#label', 'Resources']]);
  });
});
