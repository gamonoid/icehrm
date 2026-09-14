import React from 'react';
import DemoModeAdminExtensionController from './controller';
import DemoModeAdminModule from './module';

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new DemoModeAdminModule('DemoModeAdmin');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.demoModeExtensionController = new DemoModeAdminExtensionController(
    'admin=demo-mode',
    data.controller_url,
  );
}

window.initDemoModeAdmin = init;

// SPA native mount: expose the view so the shell renders it in-tree (themed).
window.DemoModeAdminView = (require('./view').default || require('./view'));
