import React from 'react';
import DiscussionsAdminExtensionController from './controller';
import DiscussionsAdminModule from './module';
import DiscussionsAdminView from './view';

// Exposed so the SPA shell can render the view natively inside its own React tree
// (inherits the light/dark theme), via the NativeExtensionView mount.
window.DiscussionsAdminView = DiscussionsAdminView;

function init(data) {
  // Exposing modJsList and modJs is required to make sure IceHrm core features
  // are working as expected
  window.modJsList = [];
  window.modJs = new DiscussionsAdminModule('DiscussionsAdmin');
  window.modJsList.push(window.modJs);

  // Defining a controller to handle requests from the extension
  window.discussionsExtensionController = new DiscussionsAdminExtensionController(
    'admin=discussions',
    data.controller_url,
  );
}

window.initDiscussionsAdmin = init;

// IceHrmPro marker: register this module (by its init fn) so the SPA shell can
// license-gate it. Baked into the (obfuscated) bundle, so it travels with the
// code and cannot be bypassed by moving the extension directory.
(window.iceProModules = window.iceProModules || {})['initDiscussionsAdmin'] = true;
