const config = require('./config');

// ***********************************************
// This example commands.js shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add("login", (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add("drag", { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add("dismiss", { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This is will overwrite an existing command --
// Cypress.Commands.overwrite("visit", (originalFn, url, options) => { ... })

Cypress.Commands.add('login', (user, password) => {
  cy.visit(`${config.BASE_URL}logout.php`);
  cy.get('#username').type(user).should('have.value', user);
  cy.get('#password').type(password).should('have.value', password);
  cy.get('.btn').first().click();
});

Cypress.Commands.add('resetDatabase', () => {
  cy.visit(`${config.BASE_URL}reset-db.php`);
  //cy.exec('vagrant ssh -c \'cd /vagrant/core/robo; php robo.phar reset:db test; php robo.phar create:tables test; php robo.phar migrate:all test; php robo.phar execute:fixtures test\'');
});
