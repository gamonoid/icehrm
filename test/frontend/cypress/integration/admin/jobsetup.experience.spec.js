const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=jobsetup&m=admin_Admin',
  'ExperienceLevel',
  true,
);

context('Admin Jobsetup Module - Edit Experience Levels Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 7);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy),
    test.editElement(cy, [['#name', 'Manager']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'Manager']]);
  });
});
