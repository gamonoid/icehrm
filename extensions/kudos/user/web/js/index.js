import React from 'react';
import KudosUserExtensionController from './controller';
import KudosUserModule from './module';
import KudosUserView from './view';

// Exposed so the SPA shell can render the view natively inside its own React tree
// (inherits the light/dark theme), via the NativeExtensionView mount.
window.KudosUserView = KudosUserView;

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new KudosUserModule('KudosUser');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.kudosExtensionController = new KudosUserExtensionController(
    'user=kudos',
    data.controller_url,
  );
}

window.initKudosUser = init;

// IceHrmPro marker: register this module (by its init fn) so the SPA shell can
// license-gate it. Baked into the (obfuscated) bundle, so it travels with the
// code and cannot be bypassed by moving the extension directory.
(window.iceProModules = window.iceProModules || {})['initKudosUser'] = true;
