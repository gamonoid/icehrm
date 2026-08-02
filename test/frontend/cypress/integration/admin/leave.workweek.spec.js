const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=leaves&m=admin_Admin',
  'WorkDay',
  false,
);
context('Admin Leaves Module - Work Week Tab', () => {
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
    test.editElement(cy, [['#exp_days', '2']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#exp_days', '2']]);
  });
});
