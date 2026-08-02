const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=jobsetup&m=admin_Admin',
  'EducationLevel',
  true,
);

context('Admin Jobsetup Module - Edit Education Level Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 13);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy),
    test.editElement(cy, [['#name', 'undergraduate']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'undergraduate']]);
  });
});
