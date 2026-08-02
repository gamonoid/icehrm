const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=employees&m=admin_Employees',
  'EmergencyContact',
  true,
);

context('Admin Employee Module - Emergency Contact Tab', () => {
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
    test.editElement(cy, [['#name', 'Mike Taylor']]);
    test.select2Click('employee', 'Carol Linda');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#name', 'Mike Taylor']]);
  });
});
