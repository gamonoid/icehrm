const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=audit&m=admin_Admin',
  'Audit',
  true,
);
context('Admin Audit Module - Audit Log Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    //test.loadTable(cy);
  });

});
