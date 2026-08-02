const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=jobsetup&m=admin_Admin',
  'Benifit',
  true,
);

context('Admin Jobsetup Module - Edit Benefits Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 4);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#name', 'permanent']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'permanent']]);
  });
});
