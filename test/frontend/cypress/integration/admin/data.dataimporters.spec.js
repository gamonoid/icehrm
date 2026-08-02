const IceCypressTest = require('../../commmon/ice-cypress-test');

const test = new IceCypressTest(
  'g=admin&n=data&m=admin_System',
  'DataImport',
  false,
);

context('Admin Data Module - Data Importers Tab', () => {
  it('admin can view list', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.loadTable(cy,3);
  });
  it('admin can edit element', () => {
    cy.login('admin', 'admin');
    test.loadModule(cy);
    test.editElement(cy, [['#details', 'importing employee data']]);
    test.clickSave(cy);
    test.editElementValidate(cy, [['#details', 'importing employee data']]);
  });
});
