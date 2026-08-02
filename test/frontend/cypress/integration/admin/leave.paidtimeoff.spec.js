const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=leaves&m=admin_Admin',
  'LeaveStartingBalance',
  false,
);
context('Admin Leaves Module - Paid Time Off Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 2);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#note', 'casual leave']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#note', 'casual leave']]);
  });
});
