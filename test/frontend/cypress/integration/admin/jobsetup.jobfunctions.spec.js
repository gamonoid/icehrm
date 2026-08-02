const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=jobsetup&m=admin_Admin',
  'JobFunction',
  true,
);

context('Admin Jobsetup Module - Edit Job Functions Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy),
    test.editElement(cy, [['#name', 'Programming']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'Programming']]);
  });
});
