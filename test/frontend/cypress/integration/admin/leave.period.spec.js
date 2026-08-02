const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=leaves&m=admin_Admin',
  'LeavePeriod',
  false,
);
context('Admin Leaves Module - Leave Period Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 5);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#name', 'Year 2014']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'Year 2014']]);
  });
});
