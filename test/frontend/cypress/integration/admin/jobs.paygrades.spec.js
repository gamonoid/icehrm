const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=jobs&m=admin_Admin',
  'PayGrades',
  false,
);

context('Admin Jobs Module - Pay Grades Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.switchTab(cy);
    test.element = 'PayGrade';
    test.loadTable(cy, 4);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.element = 'PayGrades';
    test.switchTab(cy);
    test.element = 'PayGrade';
    test.editElement(cy, [['#min_salary', '7000.00']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#min_salary', '7000.00']]);
  });
});
