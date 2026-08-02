const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=employees&m=admin_Employees',
  'EmployeeDocument',
  true,
);

context('Admin Employee Module - Documents Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.isRemoteTable=false;
    test.switchTab(cy);
    test.loadTable(cy, 3);
  });

  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.isRemoteTable=false;
    test.switchTab(cy);
    test.editElement(cy, [['#details', 'Employee details']], '.center div img[data-original-title=\'Edit\']');
    test.select2Click('employee', 'Carol Linda');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#details', 'Employee details']], '.center div img[data-original-title=\'Edit\']');
  });
});
