const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=travel&m=admin_Admin',
  'TravelCustomField',
  true,
);
context('Admin Travel Module - Travel Requests Tab', () => {
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
    test.editElement(cy, [['#display_section', 'profession']], '.center div img[title=\'Edit\']');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#display_section', 'profession']], '.center div img[title=\'Edit\']');
  });
});
