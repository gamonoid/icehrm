const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=employees&m=admin_Employees',
  'EmployeeCertification',
  true,
);

context('Admin Employee Module - Certifications Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.loadTable(cy, 4);
  });

  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.editElement(cy, [['#institute', 'UOC']]);
    test.select2Click('employee', 'Carol Linda');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#institute', 'UOC']]);
  });
});
