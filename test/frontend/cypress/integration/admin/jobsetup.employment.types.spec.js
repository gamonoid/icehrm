const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=jobsetup&m=admin_Admin',
  'EmployementType',
  true,
);

context('Admin Jobsetup Module - Job Titles Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy, 5);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#name', 'permanent']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'permanent']]);
  });
});
