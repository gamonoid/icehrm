import React from 'react';
import DirectoryUserExtensionController from './controller';
import DirectoryUserModule from './module';

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new DirectoryUserModule('DirectoryUser');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.directoryExtensionController = new DirectoryUserExtensionController(
    'user=directory',
    data.controller_url,
  );
}

window.initDirectoryUser = init;

// SPA native mount: expose the view so the shell renders it in-tree (themed).
window.DirectoryUserView = (require('./view').default || require('./view'));
