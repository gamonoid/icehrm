import React from 'react';
import __namespace__ExtensionController from './controller';
import __namespace__Module from './module';

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new __namespace__Module('__namespace__');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.__variable_name__ExtensionController = new __namespace__ExtensionController(
    '__type__=__name__',
    data.controller_url,
  );
}

window.init__namespace__ = init;
