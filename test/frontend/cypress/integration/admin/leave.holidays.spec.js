const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=leaves&m=admin_Admin',
  'HoliDay',
  false,
);
context('Admin Leaves Module - Holidays Tab', () => {
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
    test.editElement(cy, [['#name', 'May day']], '.center div img[title=\'Edit\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'May day']], '.center div img[title=\'Edit\']');
  });
});
