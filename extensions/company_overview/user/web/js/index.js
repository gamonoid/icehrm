import React from 'react';
import Company_overviewUserExtensionController from './controller';
import Company_overviewUserModule from './module';

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new Company_overviewUserModule('Company_overviewUser');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.company_overviewExtensionController = new Company_overviewUserExtensionController(
    'user=company_overview',
    data.controller_url,
  );
}

window.initCompany_overviewUser = init;

// SPA native mount: expose the view so the shell renders it in-tree (themed).
window.CompanyOverviewUserView = (require('./view').default || require('./view'));
