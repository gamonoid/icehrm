const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=users&m=admin_System',
  'User',
  false,
);
context('Admin Users Module - Salary Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 11);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#email', 'admin@web-stalk.com']]);
    cy.get('.controls').contains('Save').click();
    test.editElementValidate(cy, [['#email', 'admin@web-stalk.com']]);
  });
});
