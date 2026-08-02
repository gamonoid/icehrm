const IceCypressTest = require('../../commmon/ice-cypress-test');
const config = require('../../support/config');

const test = new IceCypressTest(
  'g=admin&n=attendance&m=admin_Employee',
  'Attendance',
  true,
);

context('Admin Attendance Module - Monitor Attendance Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#note', 'late']]);
    test.select2Click('employee', 'Richard Amy');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#note', 'late']]);
  });

  it('manager can view list', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.loadTable(cy);
  });
  it('manager can edit element', () => {
    cy.login('manager', config.DEFAULT_USER_PASS);
    test.loadModule(cy);
    test.editElement(cy, [['#note', 'late']]);
    test.select2Click('employee', 'Richard Amy');
    test.clickSave(cy);
    test.editElementValidate(cy, [['#note', 'late']]);
  });
});
