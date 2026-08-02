const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=metadata&m=admin_System',
  'Nationality',
  false,
);
context('Admin Metadata Module - Nationality Tab', () => {
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
    test.editElement(cy, [['#name', 'Afghan']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'Afghan']]);
  });
});
