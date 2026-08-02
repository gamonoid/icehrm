import React from 'react';
import Advance_reportsUserExtensionController from './controller';
import Advance_reportsUserModule from './module';

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new Advance_reportsUserModule('Advance_reportsUser');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.advance_reportsExtensionController = new Advance_reportsUserExtensionController(
    'user=advance_reports',
    data.controller_url,
  );
}

window.initAdvance_reportsUser = init;

// SPA native mount: expose the view so the shell renders it in-tree (themed).
window.AdvanceReportsUserView = (require('./view').default || require('./view'));
