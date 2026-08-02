const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=metadata&m=admin_System',
  'CurrencyType',
  false,
);
context('Admin Metadata Module - Currency Types Tab', () => {
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
    test.editElement(cy, [['#name', 'Utd. Arab Emir. Dirham']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'Utd. Arab Emir. Dirham']]);
  });
});
