import React from 'react';
import Advance_reportsAdminExtensionController from './controller';
import Advance_reportsAdminModule from './module';

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new Advance_reportsAdminModule('Advance_reportsAdmin');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.advance_reportsExtensionController = new Advance_reportsAdminExtensionController(
    'admin=advance_reports',
    data.controller_url,
  );
}

window.initAdvance_reportsAdmin = init;

// SPA native mount: expose the view so the shell renders it in-tree (themed).
window.AdvanceReportsAdminView = (require('./view').default || require('./view'));
