import React from 'react';
import DiscussionsUserExtensionController from './controller';
import DiscussionsUserModule from './module';
import DiscussionsUserView from './view';

// Exposed so the SPA shell can render the view natively inside its own React tree
// (inherits the light/dark theme), via the NativeExtensionView mount.
window.DiscussionsUserView = DiscussionsUserView;

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new DiscussionsUserModule('DiscussionsUser');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.discussionsExtensionController = new DiscussionsUserExtensionController(
    'user=discussions',
    data.controller_url,
  );
}

window.initDiscussionsUser = init;

// IceHrmPro marker: register this module (by its init fn) so the SPA shell can
// license-gate it. Baked into the (obfuscated) bundle, so it travels with the
// code and cannot be bypassed by moving the extension directory.
(window.iceProModules = window.iceProModules || {})['initDiscussionsUser'] = true;
