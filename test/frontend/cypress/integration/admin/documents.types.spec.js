const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=documents&m=admin_Employees',
  'Document',
  false,
);

context('Admin Document Module - Document Types Tab', () => {
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
    test.editElement(cy, [['#details', 'identity card']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#details', 'identity card']]);
  });
});
