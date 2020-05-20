const config = require('../support/config');

class IceCypressTest {
  constructor(moduleUrl, element, isRemoteTable) {
    this.moduleUrl = moduleUrl;
    this.element = element;
    this.isRemoteTable = isRemoteTable;
    this.titleDataAttributeName = 'data-original-title';
    if (isRemoteTable) {
      this.titleDataAttributeName = 'title';
    }
  }

  loadTable(cy, count = config.DEFAULT_MAX_PAGE_SIZE) {
    cy.get(`#${this.element} table tbody`).find('tr')
      .its('length').should('eq', count);
  }

  viewElement(cy, viewButtonSelector = null) {
    cy.server().route('GET', `/${config.URL_PREFIX}/service.php*`).as('getElement');
    cy.get(`#${this.element} table tbody`).find('tr').first()
      .find(viewButtonSelector || `.center div img[${this.titleDataAttributeName}='View']`)
      .click();

    cy.wait('@getElement').its('status').should('be', config.DEFAULT_WAIT_TIME);
  }

  viewElementValidate(cy, validation = []) {
    validation.forEach((rule) => {
      cy.get(rule[0]).then(element => expect(element.text()).eq(rule[1]));
    });
  }

  editElement(cy, update, editButtonSelector = null) {
    cy.server().route('POST', `/${config.URL_PREFIX}/service.php*`).as('getElement');
    cy.get(`#${this.element} table tbody`).find('tr').first().find(editButtonSelector || `.center div img[${this.titleDataAttributeName}='Edit']`)
      .click();

    cy.wait('@getElement').its('status').should('be', config.DEFAULT_WAIT_TIME);

    update.forEach((item) => {
      cy.get(item[0]).clear().type(item[1]).should('have.value', item[1]);
    });
  }

  select2Click(id, value) {
    cy.get(`#s2id_${id}`).click();
    cy.focused().clear().type(value).should('have.value', value);
    cy.get('.select2-drop:visible').find('.select2-results li').first()
      .click();
  }

  clickSave(cy) {
    cy.get(`#${this.element}Form .saveBtn`).click();
  }

  editElementValidate(cy, validation = [], editButtonSelector) {
    if (this.isRemoteTable) {
      cy.server().route('GET', `/${config.URL_PREFIX}/data.php*`).as('getAfterSave');
    } else {
      cy.server().route('POST', `/${config.URL_PREFIX}/service.php*`).as('getAfterSave');
    }

    // Wait for data table response
    cy.wait('@getAfterSave').its('status').should('be', config.DEFAULT_WAIT_TIME);

    cy.server().route('POST', `/${config.URL_PREFIX}/service.php*`).as('getElementAfterSave');
    // Click on edit and wait
    cy.get(`#${this.element} table tbody`).find('tr').first().find(editButtonSelector || `.center div img[${this.titleDataAttributeName}='Edit']`)
      .click();

    cy.wait('@getElementAfterSave').its('status').should('be', config.DEFAULT_WAIT_TIME);

    validation.forEach((item) => {
      cy.get(item[0]).then(element => expect(element.val()).eq(item[1]));
    });
  }

  canNotEditElement(cy, editButtonSelector = null) {
    cy.get(`#${this.element} table tbody`).find('tr')
      .first().find(editButtonSelector || ".center div img[title='Edit']")
      .should('not.exist');
  }

  loadModule(cy) {
    // Request to watch and wait
    if (this.isRemoteTable) {
      cy.server().route('GET', `/${config.URL_PREFIX}/data.php*`).as('get');
    } else {
      cy.server().route('POST', `/${config.URL_PREFIX}/service.php*`).as('get');
    }


    // Visit module
    cy.visit(`${config.BASE_URL}?${this.moduleUrl}`);

    // Wait for data table response
    cy.wait('@get').its('status').should('be', config.DEFAULT_WAIT_TIME);
  }

  switchTab(cy, tabName = null) {
    if (this.isRemoteTable) {
      cy.server().route('GET', `/${config.URL_PREFIX}/data.php*`).as('getTab');
    } else {
      cy.server().route('POST', `/${config.URL_PREFIX}/service.php*`).as('getTab');
    }
    cy.get(tabName || `#tab${this.element}`).click();
    cy.wait('@getTab').its('status').should('be', config.DEFAULT_WAIT_TIME);
  }
}

module.exports = IceCypressTest;
