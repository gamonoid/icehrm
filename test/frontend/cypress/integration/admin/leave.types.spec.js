const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=leaves&m=admin_Admin',
  'LeaveType',
  false,
);
context('Admin Leaves Module - Leave Types Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 3);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#name', 'May day']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'May day']]);
  });
});
