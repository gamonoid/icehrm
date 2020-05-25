const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=employees&m=admin_Employees',
  'EmployeeSkill',
  true,
);

context('Admin Employee Module - Skills Tab', () => {

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
    test.editElement(cy, [['#details', 'Employee skill']]);
    test.select2Click('skill_id', 'Networking');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#details', 'Employee skill']]);
  });
});
