import React from 'react';
import KudosAdminExtensionController from './controller';
import KudosAdminModule from './module';

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new KudosAdminModule('KudosAdmin');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.kudosExtensionController = new KudosAdminExtensionController(
    'admin=kudos',
    data.controller_url,
  );
}

window.initKudosAdmin = init;
